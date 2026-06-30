<?php
$page_title = 'Profile';
require_once 'database.php';

// Cek login
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user dengan cara yang lebih aman dan jelas
try {
    // Coba ambil data dengan SELECT spesifik
    $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, phone, address, role, created_at FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG: Tampilkan hasil query
    // echo "<pre>"; print_r($user); echo "</pre>";
    
} catch(PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Kalau user tidak ditemukan
if(!$user || empty($user['username'])) {
    // Coba ambil semua data tanpa filter
    try {
        $stmt = $pdo->query("SELECT * FROM users");
        $all_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div style='background:#f8d7da; padding:15px; margin:10px 0; border-radius:5px;'>";
        echo "<h3 style='color:#721c24;'>⚠️ DEBUG - Data User Tidak Ditemukan</h3>";
        echo "<p><strong>User ID yang dicari:</strong> " . $user_id . "</p>";
        echo "<p><strong>Jumlah user di database:</strong> " . count($all_users) . "</p>";
        echo "<p><strong>Data user di database:</strong></p>";
        echo "<pre>";
        print_r($all_users);
        echo "</pre>";
        echo "</div>";
        exit();
    } catch(PDOException $e) {
        die("Error cek database: " . $e->getMessage());
    }
}

// Cek role
$is_admin = ($user['role'] == 'admin');

// Proses update profile
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    
    if(empty($full_name)) {
        $error = 'Nama lengkap harus diisi!';
    } else {
        try {
            if(!empty($new_password) && strlen($new_password) >= 4) {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, password = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $phone, $address, $new_password, $user_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
                $result = $stmt->execute([$full_name, $phone, $address, $user_id]);
            }
            
            if($result) {
                $_SESSION['full_name'] = $full_name;
                $success = 'Profile berhasil diupdate!';
                
                // Ambil ulang data user
                $stmt = $pdo->prepare("SELECT id, username, email, password, full_name, phone, address, role, created_at FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                $is_admin = ($user['role'] == 'admin');
            }
        } catch(PDOException $e) {
            $error = 'Gagal update: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>

<div class="container">
    <h1>Profile Saya</h1>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <div class="profile-grid">
        <div class="card">
            <h2>Informasi Profile</h2>
            <div class="profile-info">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username'] ?? '-'); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? '-'); ?></p>
                <p><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($user['full_name'] ?? '-'); ?></p>
                <p><strong>Telepon:</strong> <?php echo htmlspecialchars($user['phone'] ?? '-'); ?></p>
                <p><strong>Alamat:</strong> <?php echo htmlspecialchars($user['address'] ?? '-'); ?></p>
                <p><strong>Role:</strong> 
                    <span class="badge <?php echo $is_admin ? 'badge-success' : 'badge-info'; ?>">
                        <?php echo $is_admin ? 'Admin' : 'User'; ?>
                    </span>
                </p>
                <p><strong>Member sejak:</strong> <?php echo date('d/m/Y H:i', strtotime($user['created_at'] ?? 'now')); ?></p>
            </div>
        </div>
        
        <div class="card">
            <h2>Edit Profile</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="full_name">Nama Lengkap <span style="color: red;">*</span></label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Telepon</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="Contoh: 08123456789">
                </div>
                
                <div class="form-group">
                    <label for="address">Alamat</label>
                    <textarea id="address" name="address" rows="3" placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Masukkan password baru (min 4 karakter)">
                </div>
                
                <button type="submit" class="btn btn-primary">Update Profile</button>
            </form>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>