<?php
$page_title = 'Riwayat Order';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Cek role - ADMIN TIDAK BISA LIHAT RIWAYAT
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if($user['role'] == 'admin') {
    header('Location: dashboard.php');
    exit();
}


$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$status_badge = [
    'pending' => 'badge-warning',
    'processing' => 'badge-info',
    'completed' => 'badge-success',
    'cancelled' => 'badge-danger'
];

$status_text = [
    'pending' => 'Menunggu',
    'processing' => 'Diproses',
    'completed' => 'Selesai',
    'cancelled' => 'Dibatalkan'
];

$methods = [
    'cash' => 'Tunai',
    'bank_transfer' => 'Transfer Bank',
    'credit_card' => 'Kartu Kredit'
];

include 'header.php';
?>

<div class="container">
    <h1>📋 Riwayat Order</h1>
    
    <?php if(count($orders) > 0): ?>
    <div class="card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>No. Order</th>
                        <th>Tanggal</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Metode Bayar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $order): ?>
                    <tr>
                        <td><strong><?php echo $order['order_number']; ?></strong></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                        <td>Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge <?php echo $status_badge[$order['status']] ?? 'badge-info'; ?>">
                                <?php echo $status_text[$order['status']] ?? ucfirst($order['status']); ?>
                            </span>
                        </td>
                        <td><?php echo $methods[$order['payment_method']] ?? ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                        <td>
                            <a href="receipt.php?order_id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="padding: 0.3rem 0.8rem; font-size: 0.85rem;">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php else: ?>
    <div class="card" style="text-align: center; padding: 3rem;">
        <div style="font-size: 4rem; margin-bottom: 1rem;">📭</div>
        <h3>Belum Ada Pesanan</h3>
        <p style="color: #777;">Anda belum melakukan pemesanan apapun</p>
        <a href="order.php" class="btn btn-primary" style="margin-top: 1rem;">Pesan Sekarang</a>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>