<?php
$page_title = 'Struk Pembelian';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if(!$order) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$status_badge = [
    'pending' => 'badge-warning',
    'processing' => 'badge-info',
    'completed' => 'badge-success',
    'cancelled' => 'badge-danger'
];

include 'header.php';
?>

<div class="container">
    <div style="text-align: center; margin-bottom: 2rem;">
        <h1>🧾 Struk Pembelian</h1>
        <p style="color: #777;">Terima kasih telah berbelanja di Toko Kue</p>
    </div>
    
    <div class="receipt">
        <div class="receipt-header">
            <h2>🍰 Toko Kue</h2>
            <p>Jl. Rawamangun No. 123, jakarta</p>
            <p>Telp: 0812-3456-7890</p>
            <p style="margin-top: 0.5rem; font-weight: 600;">
                <?php echo $order['order_number']; ?>
            </p>
        </div>
        
        <div class="receipt-details">
            <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>Telepon:</strong> <?php echo htmlspecialchars($order['customer_phone'] ?? '-'); ?></p>
            <p><strong>Alamat:</strong> <?php echo htmlspecialchars($order['customer_address']); ?></p>
            <p><strong>Status:</strong> 
                <span class="badge <?php echo $status_badge[$order['status']] ?? 'badge-info'; ?>">
                    <?php 
                        $status_text = [
                            'pending' => 'Menunggu',
                            'processing' => 'Diproses',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan'
                        ];
                        echo $status_text[$order['status']] ?? ucfirst($order['status']); 
                    ?>
                </span>
            </p>
            <p><strong>Metode Bayar:</strong> <?php 
                $methods = [
                    'cash' => 'Tunai',
                    'bank_transfer' => 'Transfer Bank',
                    'credit_card' => 'Kartu Kredit'
                ];
                echo $methods[$order['payment_method']] ?? ucfirst(str_replace('_', ' ', $order['payment_method']));
            ?></p>
        </div>
        
        <div class="receipt-items">
            <h3 style="border-bottom: 1px solid #ddd; padding-bottom: 0.5rem;">Detail Pesanan</h3>
            <?php foreach($items as $item): ?>
            <div class="receipt-item">
                <span>
                    <?php echo htmlspecialchars($item['cake_name']); ?> 
                    <span style="color: #777; font-size: 0.85rem;">x<?php echo $item['quantity']; ?></span>
                </span>
                <span>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="receipt-total">
            <span>TOTAL</span>
            <span>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></span>
        </div>
        
        <div class="receipt-footer">
            <p>Terima kasih atas kepercayaan Anda</p>
            <p style="font-size: 0.8rem; margin-top: 0.5rem;">
                Simpan struk ini sebagai bukti pembelian
            </p>
        </div>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;" class="no-print">
        <a href="order.php" class="btn btn-secondary">🛒 Kembali Belanja</a>
        <a href="orders.php" class="btn btn-primary">📋 Lihat Riwayat</a>
        <a href="receipt.php?order_id=<?php echo $order_id; ?>" class="btn btn-success" onclick="window.print(); return false;">🖨 Cetak Struk</a>
    </div>
</div>

<?php include 'footer.php'; ?>