<?php
$page_title = 'Edit Kue';
require_once 'database.php';

if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM cakes WHERE id = ?");
$stmt->execute([$id]);
$cake = $stmt->fetch();

if(!$cake) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? 0;
    $category = trim($_POST['category'] ?? '');
    $stock = $_POST['stock'] ?? 0;
    
    if(empty($name) || empty($price)) {
        $error = 'Nama dan harga wajib diisi!';
    } else {
        $stmt = $pdo->prepare("UPDATE cakes SET name = ?, description = ?, price = ?, category = ?, stock = ? WHERE id = ?");
        if($stmt->execute([$name, $description, $price, $category, $stock, $id])) {
            $success = 'Kue berhasil diupdate!';
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM cakes WHERE id = ?");
            $stmt->execute([$id]);
            $cake = $stmt->fetch();
        } else {
            $error = 'Gagal mengupdate kue!';
        }
    }
}

include 'header.php';
?>

<div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <h1>Edit Kue</h1>
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <div class="card">
        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Kue <span style="color: red;">*</span></label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($cake['name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($cake['description']); ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="price">Harga (Rp) <span style="color: red;">*</span></label>
                    <input type="number" id="price" name="price" value="<?php echo $cake['price']; ?>" required min="0">
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($cake['category']); ?>" placeholder="Contoh: Chocolate, Fruit, Classic">
                </div>
            </div>
            
            <div class="form-group">
                <label for="stock">Stok</label>
                <input type="number" id="stock" name="stock" value="<?php echo $cake['stock']; ?>" min="0">
            </div>
            
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>