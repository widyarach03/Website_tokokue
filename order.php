<?php
$page_title = 'Pesan Kue';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Cek role - ADMIN TIDAK BISA PESAN
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($user['role'] == 'admin') {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Add to cart
if(isset($_GET['add'])) {
    $cake_id = (int)$_GET['add'];
    $quantity = isset($_GET['qty']) ? (int)$_GET['qty'] : 1;
    
    $stmt = $pdo->prepare("SELECT * FROM cakes WHERE id = ? AND stock > 0");
    $stmt->execute([$cake_id]);
    $cake = $stmt->fetch();
    
    if($cake) {
        if($quantity > $cake['stock']) {
            $error = "Stok tidak mencukupi! Tersisa {$cake['stock']}.";
        } else {
            if(isset($cart[$cake_id])) {
                $new_qty = $cart[$cake_id]['quantity'] + $quantity;
                if($new_qty > $cake['stock']) {
                    $error = "Stok tidak mencukupi! Tersisa {$cake['stock']}.";
                } else {
                    $cart[$cake_id]['quantity'] = $new_qty;
                    $cart[$cake_id]['subtotal'] = $new_qty * $cake['price'];
                }
            } else {
                $cart[$cake_id] = [
                    'id' => $cake['id'],
                    'name' => $cake['name'],
                    'price' => $cake['price'],
                    'quantity' => $quantity,
                    'subtotal' => $quantity * $cake['price']
                ];
            }
            $_SESSION['cart'] = $cart;
            $success = "Kue berhasil ditambahkan ke keranjang!";
        }
    } else {
        $error = "Kue tidak ditemukan atau stok habis!";
    }
}

// Remove from cart
if(isset($_GET['remove'])) {
    $cake_id = (int)$_GET['remove'];
    if(isset($cart[$cake_id])) {
        unset($cart[$cake_id]);
        $_SESSION['cart'] = $cart;
        $success = "Kue dihapus dari keranjang!";
    }
}

// Clear cart
if(isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    $cart = [];
    $success = "Keranjang dikosongkan!";
}

// Update cart
if(isset($_POST['update_cart'])) {
    foreach($_POST['qty'] as $id => $qty) {
        $id = (int)$id;
        $qty = (int)$qty;
        if($qty <= 0) {
            unset($cart[$id]);
        } else {
            $stmt = $pdo->prepare("SELECT stock, price FROM cakes WHERE id = ?");
            $stmt->execute([$id]);
            $cake = $stmt->fetch();
            if($cake && $qty <= $cake['stock']) {
                $cart[$id]['quantity'] = $qty;
                $cart[$id]['subtotal'] = $qty * $cake['price'];
            } else {
                $error = "Stok tidak mencukupi untuk salah satu item!";
            }
        }
    }
    $_SESSION['cart'] = $cart;
    $success = "Keranjang berhasil diupdate!";
}

// Checkout
if(isset($_POST['checkout']) && !empty($cart)) {
    $customer_name = trim($_POST['customer_name'] ?? '');
    $customer_phone = trim($_POST['customer_phone'] ?? '');
    $customer_address = trim($_POST['customer_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? 'cash';
    
    if(empty($customer_name)) {
        $error = "Nama lengkap wajib diisi!";
    } elseif(empty($customer_address)) {
        $error = "Alamat pengiriman wajib diisi!";
    } else {
        $total = 0;
        foreach($cart as $item) {
            $total += $item['subtotal'];
        }
        
        $order_number = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO orders (order_number, user_id, customer_name, customer_phone, customer_address, total_amount, payment_method) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$order_number, $_SESSION['user_id'], $customer_name, $customer_phone, $customer_address, $total, $payment_method]);
            $order_id = $pdo->lastInsertId();
            
            foreach($cart as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, cake_id, cake_name, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['id'], $item['name'], $item['quantity'], $item['price'], $item['subtotal']]);
                
                $stmt = $pdo->prepare("UPDATE cakes SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['id']]);
            }
            
            $pdo->commit();
            $_SESSION['cart'] = [];
            
            header("Location: receipt.php?order_id=" . $order_id);
            exit();
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $error = "Gagal memproses pesanan: " . $e->getMessage();
        }
    }
}

$cakes = $pdo->query("SELECT * FROM cakes WHERE stock > 0 ORDER BY id DESC")->fetchAll();

include 'header.php';
?>

<div class="container">
    <h1>Pesan Kue</h1>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="order-layout">
        <div>
            <h2>Daftar Kue</h2>
            <div class="card-grid">
                <?php foreach($cakes as $cake): ?>
                <div class="card-item">
                    <?php 
                    // CARI GAMBAR BERDASARKAN ID KUE
                    $image_file = '';
                    $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    foreach($extensions as $ext) {
                        if(file_exists('uploads/cakes/' . $cake['id'] . '.' . $ext)) {
                            $image_file = 'uploads/cakes/' . $cake['id'] . '.' . $ext;
                            break;
                        }
                    }
                    if(!$image_file) {
                        $image_file = 'uploads/cakes/default.jpg';
                    }
                    ?>
                    <!-- TAMPILKAN GAMBAR -->
                    <img src="<?php echo $image_file; ?>" alt="<?php echo htmlspecialchars($cake['name']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 1rem;">
                    
                    <h3><?php echo htmlspecialchars($cake['name']); ?></h3>
                    <p style="font-size: 0.9rem; color: #777;"><?php echo htmlspecialchars(substr($cake['description'], 0, 60)); ?></p>
                    <p style="color: #ff6b6b; font-weight: 700; margin: 0.5rem 0;">
                        Rp <?php echo number_format($cake['price'], 0, ',', '.'); ?>
                    </p>
                    <p style="font-size: 0.85rem; color: #555;">
                        Stok: <?php echo $cake['stock']; ?>
                    </p>
                    <?php if($cake['stock'] > 0): ?>
                    <form method="GET" action="" style="margin-top: 0.5rem;">
                        <input type="hidden" name="add" value="<?php echo $cake['id']; ?>">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <input type="number" name="qty" value="1" min="1" max="<?php echo $cake['stock']; ?>" style="width: 60px; padding: 0.3rem; border: 2px solid #e0e0e0; border-radius: 5px; text-align: center;">
                            <button type="submit" class="btn btn-primary" style="padding: 0.3rem 1rem; font-size: 0.9rem;">Pesan</button>
                        </div>
                    </form>
                    <?php else: ?>
                    <p style="color: #dc3545; font-weight: 600; margin-top: 0.5rem;">Stok Habis</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                
                <?php if(count($cakes) == 0): ?>
                <p style="grid-column: 1/-1; text-align: center; padding: 2rem; color: #777;">
                    Belum ada kue yang tersedia
                </p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if(!empty($cart)): ?>
        <div>
            <div class="card" style="position: sticky; top: 100px;">
                <h2>🛒 Keranjang</h2>
                <form method="POST" action="">
                    <?php foreach($cart as $id => $item): ?>
                    <div class="cart-item">
                        <span style="font-weight: 500;"><?php echo htmlspecialchars($item['name']); ?></span>
                        <div class="qty-control">
                            <input type="number" name="qty[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0">
                        </div>
                        <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
                        <a href="?remove=<?php echo $id; ?>" style="color: #dc3545; text-decoration: none; font-weight: 700;">✕</a>
                    </div>
                    <?php endforeach; ?>
                    
                    <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem;">
                        <button type="submit" name="update_cart" class="btn btn-secondary" style="padding: 0.3rem 1rem; font-size: 0.9rem;">Update</button>
                        <a href="?clear=1" class="btn btn-danger" style="padding: 0.3rem 1rem; font-size: 0.9rem; text-decoration: none;">Kosongkan</a>
                    </div>
                    
                    <div class="cart-total">
                        Total: Rp <?php 
                            $total = 0;
                            foreach($cart as $item) { $total += $item['subtotal']; }
                            echo number_format($total, 0, ',', '.');
                        ?>
                    </div>
                    
                    <hr style="margin: 1rem 0;">
                    
                    <h3>Informasi Pemesanan</h3>
                    <div class="form-group">
                        <label>Nama Lengkap *</label>
                        <input type="text" name="customer_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telepon</label>
                        <input type="text" name="customer_phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label>Alamat Pengiriman *</label>
                        <textarea name="customer_address" rows="3" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Metode Pembayaran</label>
                        <select name="payment_method" id="payment_method">
                            <option value="cash">Tunai</option>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="credit_card">Kartu Kredit</option>
                        </select>
                    </div>
                    <div class="bank-box" id="bankSection" style="display:none;">
                        <label class="bank-label">🏦 Pilih Bank</label>
                        <select id="bank" name="bank" class="bank-select">
                            <option value="">Pilih Bank</option>
                            <option value="BCA">🏦 BCA</option>
                            <option value="BRI">🏦 BRI</option>
                            <option value="BNI">🏦 BNI</option>
                            <option value="Mandiri">🏦 Mandiri</option>
                        </select>
                        <div id="rekeningInfo"></div>
                    </div>
                    
                    <button type="submit" name="checkout" class="btn btn-success" style="width: 100%; font-size: 1.1rem;">
                        ✅ Proses Pesanan
                    </button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div>
            <div class="card" style="text-align: center; padding: 3rem;">
                <div style="font-size: 4rem; margin-bottom: 1rem;">🛒</div>
                <h3>Keranjang Kosong</h3>
                <p style="color: #777;">Pilih kue di sebelah kiri untuk memesan</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

    const payment = document.getElementById("payment_method");
    const bankSection = document.getElementById("bankSection");
    const bank = document.getElementById("bank");
    const rekeningInfo = document.getElementById("rekeningInfo");

    payment.addEventListener("change", function () {

        if (this.value === "bank_transfer") {
            bankSection.style.display = "block";
        } else {
            bankSection.style.display = "none";
            rekeningInfo.innerHTML = "";
            bank.value = "";
        }

    });

    bank.addEventListener("change", function () {

        let rekening = "";
        let namaBank = "";
        let warna = "";

        switch (this.value) {

            case "BCA":
                namaBank = "Bank BCA";
                rekening = "18299731";
                warna = "#00529B";
                break;

            case "BRI":
                namaBank = "Bank BRI";
                rekening = "98765432";
                warna = "#00529B";
                break;

            case "BNI":
                namaBank = "Bank BNI";
                rekening = "11223346";
                warna = "#F37021";
                break;

            case "Mandiri":
                namaBank = "Bank Mandiri";
                rekening = "55637288";
                warna = "#003D79";
                break;

            default:
                rekeningInfo.innerHTML = "";
                return;
        }

        rekeningInfo.innerHTML = `
            <div style="
                margin-top:15px;
                background:#fff;
                border-radius:12px;
                border:1px solid #e5e5e5;
                box-shadow:0 5px 15px rgba(0,0,0,.08);
                overflow:hidden;
            ">

                <div style="
                    background:${warna};
                    color:white;
                    padding:12px 18px;
                    font-size:18px;
                    font-weight:bold;
                ">
                    🏦 ${namaBank}
                </div>

                <div style="padding:18px;">

                    <small style="color:#888;">
                        Nomor Rekening
                    </small>

                    <div style="
                        display:flex;
                        justify-content:space-between;
                        align-items:center;
                        margin-top:8px;
                    ">

                        <h2 style="
                            margin:0;
                            color:#333;
                            letter-spacing:2px;
                        ">
                            ${rekening}
                        </h2>

                        <button
                            type="button"
                            onclick="copyRekening('${rekening}')"
                            style="
                                background:#28a745;
                                color:white;
                                border:none;
                                padding:8px 14px;
                                border-radius:8px;
                                cursor:pointer;
                                font-weight:bold;
                            ">
                            📋 Salin
                        </button>

                    </div>

                    <hr style="margin:15px 0;">

                    <p style="margin:0;">
                        <strong>Atas Nama</strong><br>
                        Toko Kue Manis
                    </p>

                    <div style="
                        margin-top:15px;
                        padding:12px;
                        background:#fff7e6;
                        border-left:4px solid orange;
                        border-radius:6px;
                        font-size:14px;
                    ">
                        Setelah melakukan transfer, klik
                        <strong>Proses Pesanan</strong>.
                    </div>

                </div>

            </div>
        `;

    });

});

function copyRekening(noRek){

    navigator.clipboard.writeText(noRek);

    alert("Nomor rekening berhasil disalin!");

}
</script>
<?php include 'footer.php'; ?>