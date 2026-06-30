<?php
$page_title = 'Register';
require_once 'database.php';

if(isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$form_data = ['username' => '', 'email' => '', 'full_name' => ''];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_data['username'] = trim($_POST['username'] ?? '');
    $form_data['email'] = trim($_POST['email'] ?? '');
    $form_data['full_name'] = trim($_POST['full_name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if(empty($form_data['username']) || empty($form_data['email']) || empty($password)) {
        $error = 'Semua field wajib diisi!';
    } elseif($password !== $confirm_password) {
        $error = 'Password tidak cocok!';
    } elseif(strlen($password) < 4) {
        $error = 'Password minimal 4 karakter!';
    } elseif(!filter_var($form_data['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Email tidak valid!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$form_data['username'], $form_data['email']]);
        
        if($stmt->rowCount() > 0) {
            $error = 'Username atau email sudah terdaftar!';
        } else {
            // Default role = user
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, 'user')");
            if($stmt->execute([$form_data['username'], $form_data['email'], $password, $form_data['full_name']])) {
                $success = 'Registrasi berhasil! Silakan <a href="login.php">login</a>.';
                $form_data = ['username' => '', 'email' => '', 'full_name' => ''];
            } else {
                $error = 'Registrasi gagal, silakan coba lagi!';
            }
        }
    }
}

include 'header.php';
?>

<div class="container">
    <div class="auth-form">
        <h2>Registrasi</h2>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="full_name">Nama Lengkap</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($form_data['full_name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($form_data['username']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($form_data['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Daftar</button>
        </form>
        
        <p style="margin-top: 1rem; text-align: center;">
            Sudah punya akun? <a href="login.php" style="color: #ff6b6b;">Login</a>
        </p>
    </div>
</div>

<?php include 'footer.php'; ?>