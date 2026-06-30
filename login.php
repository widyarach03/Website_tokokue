<?php
$page_title = 'Login';
require_once 'database.php';

if(isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if($user && $user['role'] == 'admin') {
        header('Location: dashboard.php');
    } else {
        header('Location: dashboard_user.php');
    }
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if(empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND password = ?");
        $stmt->execute([$username, $username, $password]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            
            if($user['role'] == 'admin') {
                header('Location: dashboard.php');
            } else {
                header('Location: dashboard_user.php');
            }
            exit();
        } else {
            $error = 'Username atau password salah!';
        }
    }
}

include 'header.php';
?>

<div class="container">
    <div class="auth-form">
        <h2>Login</h2>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username atau Email</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        
        <p style="margin-top: 1rem; text-align: center;">
            Belum punya akun? <a href="register.php" style="color: #ff6b6b;">Daftar sekarang</a>
        </p>
        
    </div>
</div>

<?php include 'footer.php'; ?>