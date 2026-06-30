<?php
$page_title = 'Dashboard';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Cek role user
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Kalau admin, redirect ke dashboard admin
if($user && $user['role'] == 'admin') {
    header('Location: dashboard.php');
    exit();
}

// Statistik untuk user biasa
$stmt = $pdo->query("SELECT COUNT(*) as total FROM cakes");
$total_cakes = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$total_orders = $stmt->fetch()['total'];

$cakes = $pdo->query("SELECT * FROM cakes ORDER BY id DESC LIMIT 5")->fetchAll();

include 'header.php';
?>

<div class="container">
    <h1>Dashboard</h1>
    <p>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?></strong>!</p>
    <p style="color: #777; margin-bottom: 1rem;">
        <span class="badge badge-info">User</span> - Silakan pesan kue favorit Anda
    </p>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $total_cakes; ?></div>
            <div class="label">Total Kue Tersedia</div>
        </div>
        <div class="stat-card">
            <div class="number"><?php echo $total_orders; ?></div>
            <div class="label">Pesanan Saya</div>
        </div>
    </div>

    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <h2>🍰 Kue Terbaru</h2>
            <a href="order.php" class="btn btn-primary">Pesan Sekarang</a>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Kue</th>
                        <th>Kategori</th>
                        <th>Harga</th>
                        <th>Stok</th>
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
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">
                                Belum ada data kue
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>