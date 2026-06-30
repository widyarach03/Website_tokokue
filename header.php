<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Kue - <?php echo $page_title ?? 'Home'; ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <a href="index.php">🍰 Toko Kue</a>
            </div>
            <ul class="nav-menu">
                <?php if(isset($_SESSION['user_id'])): 
                    // Cek role user
                    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $nav_user = $stmt->fetch();
                    $is_admin = ($nav_user && $nav_user['role'] == 'admin');
                ?>
                    <?php if($is_admin): ?>
                        <li><a href="dashboard.php">Dashboard Admin</a></li>
                    <?php else: ?>
                        <li><a href="dashboard_user.php">Dashboard</a></li>
                        <li><a href="order.php">Pesan Kue</a></li>
                        <li><a href="orders.php">Riwayat</a></li>
                    <?php endif; ?>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php" class="btn-logout">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php" class="btn-register">Register</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main>