<?php
$page_title = 'Home';
require_once 'database.php';
include 'header.php';
?>

<div class="container">
    <div class="hero">
        <h1>Selamat Datang di Toko Kue</h1>
        <p>Rasakan kelezatan kue-kue terbaik kami</p>
        
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard.php" class="btn">Dashboard</a>
            <a href="order.php" class="btn" style="background: #28a745; color: white;">Pesan Kue</a>
        <?php else: ?>
            <a href="register.php" class="btn">Mulai Sekarang</a>
            <a href="login.php" class="btn" style="background: #28a745; color: white;">Login</a>
        <?php endif; ?>
    </div>

    <div class="card-grid">
        <div class="card-item">
            <div class="icon">🎂</div>
            <h3>Kue Spesial</h3>
            <p>Berbagai macam kue lezat untuk setiap momen</p>
        </div>
        <div class="card-item">
            <div class="icon">👨‍🍳</div>
            <h3>Chef Profesional</h3>
            <p>Dibuat oleh chef berpengalaman</p>
        </div>
        <div class="card-item">
            <div class="icon">🚚</div>
            <h3>Pengiriman Cepat</h3>
            <p>Diantar langsung ke pintu Anda</p>
        </div>
    </div>

    <?php
    $stmt = $pdo->query("SELECT * FROM cakes ORDER BY id DESC LIMIT 3");
    $cakes = $stmt->fetchAll();
    
    if($cakes):
    ?>
    <div class="card" style="margin-top: 2rem;">
        <h2 style="margin-bottom: 1rem;">Kue Terbaru</h2>
        <div class="card-grid">
            <?php foreach($cakes as $cake): ?>
            <div class="card-item">
                <div class="icon">🍰</div>
                <h3><?php echo htmlspecialchars($cake['name']); ?></h3>
                <p><?php echo htmlspecialchars(substr($cake['description'], 0, 50)) . '...'; ?></p>
                <p style="color: #ff6b6b; font-weight: 700; margin-top: 0.5rem;">
                    Rp <?php echo number_format($cake['price'], 0, ',', '.'); ?>
                </p>
                <p style="font-size: 0.85rem; color: #555;">
                    Stok: <?php echo $cake['stock']; ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>