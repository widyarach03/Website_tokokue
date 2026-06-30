<?php
$page_title = 'Dashboard Admin';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Cek role user
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$is_admin = ($user['role'] == 'admin');

if(!$is_admin) {
    header('Location: dashboard_user.php');
    exit();
}

// Statistik
$stmt = $pdo->query("SELECT COUNT(*) as total FROM cakes");
$total_cakes = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT SUM(stock) as total FROM cakes");
$total_stock = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$total_orders = $stmt->fetch()['total'];

// Data kue
$cakes = $pdo->query("SELECT * FROM cakes ORDER BY id DESC")->fetchAll();

// Data pemesanan (semua user)
$orders = $pdo->query("SELECT o.*, u.username FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC")->fetchAll();

include 'header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1>Dashboard Admin</h1>
            <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong>!</p>
            <p style="color: #777; margin-bottom: 1rem;">
                <span class="badge badge-success">Admin</span> - Anda memiliki akses penuh untuk mengelola data
            </p>
        </div>
        <div>
            <a href="add_cake.php" class="btn btn-primary">+ Tambah Kue</a>
        </div>
    </div>
    
    <!-- STATISTIK -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $total_cakes; ?></div>
            <div class="label">Total Kue</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_orders; ?></div>
            <div class="label">Total Pemesanan</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_users; ?></div>
            <div class="label">Total Pengguna</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_stock; ?></div>
            <div class="label">Total Stok Kue</div>
        </div>
    </div>

    <!-- DATA KUE -->
    <div class="card">
        <h2>📦 Data Kue</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kue</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($cakes) > 0): ?>
                        <?php foreach($cakes as $cake): ?>
                        <tr>
                            <td><?php echo $cake['id']; ?></td>
                            <td><?php echo htmlspecialchars($cake['name']); ?></td>
                            <td><?php echo htmlspecialchars($cake['category']); ?></td>
                            <td>Rp <?php echo number_format($cake['price'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge <?php echo $cake['stock'] > 0 ? 'badge-success' : 'badge-danger'; ?>">
                                    <?php echo $cake['stock']; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_cake.php?id=<?php echo $cake['id']; ?>" class="btn btn-secondary" style="padding: 0.2rem 0.8rem; font-size: 0.85rem;">Edit</a>
                                <a href="delete_cake.php?id=<?php echo $cake['id']; ?>" class="btn btn-danger" style="padding: 0.2rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                Belum ada data kue
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DATA PEMESANAN SEMUA USER -->
    <div class="card">
        <h2>📋 Data Pemesanan (Semua User)</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Pelanggan</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($orders) > 0): ?>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td><strong><?php echo $order['order_number']; ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($order['customer_name']); ?>
                                <br>
                                <small style="color: #777;">@<?php echo htmlspecialchars($order['username'] ?? 'Guest'); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                            <td>
                                <span class="badge 
                                    <?php 
                                        if($order['status'] == 'pending') echo 'badge-warning';
                                        elseif($order['status'] == 'processing') echo 'badge-info';
                                        elseif($order['status'] == 'completed') echo 'badge-success';
                                        elseif($order['status'] == 'cancelled') echo 'badge-danger';
                                    ?>
                                ">
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
                            </td>
                            <td>
                                <?php if($order['status'] == 'pending'): ?>
                                    <a href="update_status.php?id=<?php echo $order['id']; ?>&status=processing" class="btn btn-primary" style="padding: 0.2rem 0.8rem; font-size: 0.85rem;">Proses</a>
                                <?php endif; ?>
                                <?php if($order['status'] == 'processing'): ?>
                                    <a href="update_status.php?id=<?php echo $order['id']; ?>&status=completed" class="btn btn-success" style="padding: 0.2rem 0.8rem; font-size: 0.85rem;">Selesai</a>
                                <?php endif; ?>
                                <?php if($order['status'] != 'cancelled' && $order['status'] != 'completed'): ?>
                                    <a href="update_status.php?id=<?php echo $order['id']; ?>&status=cancelled" class="btn btn-danger" style="padding: 0.2rem 0.8rem; font-size: 0.85rem;" onclick="return confirm('Yakin batalkan pesanan ini?')">Batal</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                Belum ada pemesanan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>