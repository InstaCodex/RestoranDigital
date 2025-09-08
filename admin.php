<?php
session_start();
require_once 'database.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'kategori_create':
                    $db->createKategori($_POST['nama'], $_POST['deskripsi']);
                    $message = 'Kategori berhasil ditambahkan';
                    $messageType = 'success';
                    break;
                    
                case 'kategori_update':
                    $db->updateKategori($_POST['id'], $_POST['nama'], $_POST['deskripsi']);
                    $message = 'Kategori berhasil diperbarui';
                    $messageType = 'success';
                    break;
                    
                case 'kategori_delete':
                    $db->deleteKategori($_POST['id']);
                    $message = 'Kategori berhasil dihapus';
                    $messageType = 'success';
                    break;
                    
                case 'menu_create':
                    // Handle upload gambar
                    $gambarPath = '';
                    if (!empty($_FILES['gambar_file']['name'])) {
                        if (!is_dir(__DIR__ . '/uploads')) {
                            mkdir(__DIR__ . '/uploads', 0755, true);
                        }
                        $allowed = ['jpg','jpeg','png','gif'];
                        $maxSize = 2 * 1024 * 1024; // 2MB
                        $ext = strtolower(pathinfo($_FILES['gambar_file']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed)) {
                            throw new Exception('Format gambar harus jpg, jpeg, png, atau gif');
                        }
                        if ($_FILES['gambar_file']['size'] > $maxSize) {
                            throw new Exception('Ukuran gambar maksimal 2MB');
                        }
                        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['gambar_file']['name']);
                        $dest = __DIR__ . '/uploads/' . $safeName;
                        if (!move_uploaded_file($_FILES['gambar_file']['tmp_name'], $dest)) {
                            throw new Exception('Gagal mengunggah gambar');
                        }
                        $gambarPath = 'uploads/' . $safeName;
                    }
                    $status = $_POST['status'] === 'habis' ? 'habis' : 'tersedia';
                    $db->createMenu($_POST['nama'], $_POST['deskripsi'], $_POST['harga'], $gambarPath, $_POST['kategori_id'], $status);
                    $message = 'Menu berhasil ditambahkan';
                    $messageType = 'success';
                    break;
                    
                case 'menu_update':
                    // Ambil data menu lama untuk mempertahankan gambar jika tidak diubah
                    $menuLama = $db->getMenuById($_POST['id']);
                    $gambarPath = $menuLama && !empty($menuLama['gambar']) ? $menuLama['gambar'] : '';
                    if (!empty($_FILES['gambar_file']['name'])) {
                        if (!is_dir(__DIR__ . '/uploads')) {
                            mkdir(__DIR__ . '/uploads', 0755, true);
                        }
                        $allowed = ['jpg','jpeg','png','gif'];
                        $maxSize = 2 * 1024 * 1024; // 2MB
                        $ext = strtolower(pathinfo($_FILES['gambar_file']['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, $allowed)) {
                            throw new Exception('Format gambar harus jpg, jpeg, png, atau gif');
                        }
                        if ($_FILES['gambar_file']['size'] > $maxSize) {
                            throw new Exception('Ukuran gambar maksimal 2MB');
                        }
                        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.-]/', '_', $_FILES['gambar_file']['name']);
                        $dest = __DIR__ . '/uploads/' . $safeName;
                        if (!move_uploaded_file($_FILES['gambar_file']['tmp_name'], $dest)) {
                            throw new Exception('Gagal mengunggah gambar');
                        }
                        $gambarPath = 'uploads/' . $safeName;
                    }
                    $status = $_POST['status'] === 'habis' ? 'habis' : 'tersedia';
                    $db->updateMenu($_POST['id'], $_POST['nama'], $_POST['deskripsi'], $_POST['harga'], $gambarPath, $_POST['kategori_id'], $status);
                    $message = 'Menu berhasil diperbarui';
                    $messageType = 'success';
                    break;
                    
                case 'menu_delete':
                    $db->deleteMenu($_POST['id']);
                    $message = 'Menu berhasil dihapus';
                    $messageType = 'success';
                    break;
                    
                case 'meja_create':
                    $db->createMeja($_POST['nomor'], $_POST['kapasitas'], $_POST['status']);
                    $message = 'Meja berhasil ditambahkan';
                    $messageType = 'success';
                    break;
                    
                case 'meja_update':
                    $db->updateMeja($_POST['id'], $_POST['nomor'], $_POST['kapasitas'], $_POST['status']);
                    $message = 'Meja berhasil diperbarui';
                    $messageType = 'success';
                    break;
                    
                case 'meja_delete':
                    $db->deleteMeja($_POST['id']);
                    $message = 'Meja berhasil dihapus';
                    $messageType = 'success';
                    break;
                    
                case 'pesanan_update_status':
                    $pesanan_id = $_POST['id'];
                    $status = $_POST['status'];
                    $db->updateStatusPesanan($pesanan_id, $status);
                    
                    // Kirim notifikasi ke pelanggan jika ada nomor WhatsApp
                    $notificationUrl = $db->sendCustomerNotification($pesanan_id, $status);
                    if ($notificationUrl) {
                        // Redirect ke WhatsApp
                        header("Location: " . $notificationUrl);
                        exit();
                    } else {
                        $message = 'Status pesanan berhasil diperbarui (tidak ada nomor WhatsApp pelanggan)';
                    $messageType = 'success';
                    }
                    break;
                    
                    
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
}

// Ambil data untuk ditampilkan
$kategoriData = $db->getAllKategori();
$menuData = $db->getAllMenu();
$mejaData = $db->getAllMeja();
$pesananData = $db->getAllPesanan();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Restoran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .card-header { border-radius: 15px 15px 0 0 !important; border: none; }
        .btn { border-radius: 10px; }
        .table { border-radius: 10px; overflow: hidden; }
        .nav-tabs .nav-link { border-radius: 10px 10px 0 0; margin-right: 5px; }
        .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .modal-content { border-radius: 15px; }
        .alert { border-radius: 10px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <!-- Header -->
        <nav class="navbar navbar-dark bg-primary mb-4">
            <div class="container">
                <span class="navbar-brand mb-0 h1">
                    <i class="fas fa-cogs me-2"></i>
                    Admin Panel - Restoran
                </span>
                <div class="d-flex align-items-center">
                    <div class="text-light me-3">
                        <i class="fas fa-user me-1"></i>
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                        <span class="badge bg-info ms-2"><?php echo ucfirst($_SESSION['role']); ?></span>
                    </div>
                    <a href="index.php" class="btn btn-outline-light me-2">
                        <i class="fas fa-home me-1"></i>
                        Kembali ke Menu
                    </a>
                    <a href="logout.php" class="btn btn-outline-warning">
                        <i class="fas fa-sign-out-alt me-1"></i>
                        Logout
                    </a>
                </div>
            </div>
        </nav>

        <div class="container">
            <!-- Alert Container -->
            <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
                <i class="fas <?php echo $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> me-2"></i>
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="adminTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="kategori-tab" data-bs-toggle="tab" data-bs-target="#kategori" type="button">
                        <i class="fas fa-tags me-2"></i>Kategori
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="menu-tab" data-bs-toggle="tab" data-bs-target="#menu" type="button">
                        <i class="fas fa-utensils me-2"></i>Menu
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="meja-tab" data-bs-toggle="tab" data-bs-target="#meja" type="button">
                        <i class="fas fa-chair me-2"></i>Meja
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pesanan-tab" data-bs-toggle="tab" data-bs-target="#pesanan" type="button">
                        <i class="fas fa-receipt me-2"></i>Pesanan
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="adminTabsContent">
                <!-- Tab Kategori -->
                <div class="tab-pane fade show active" id="kategori" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-tags me-2"></i>
                                    Kelola Kategori
                                </h5>
                                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#kategoriModal">
                                    <i class="fas fa-plus me-1"></i>Tambah Kategori
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Deskripsi</th>
                                            <th>Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($kategoriData as $kategori): ?>
                                        <tr>
                                            <td><?php echo $kategori['id']; ?></td>
                                            <td><?php echo htmlspecialchars($kategori['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($kategori['deskripsi'] ?: '-'); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($kategori['created_at'])); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editKategori(<?php echo $kategori['id']; ?>, '<?php echo htmlspecialchars($kategori['nama']); ?>', '<?php echo htmlspecialchars($kategori['deskripsi']); ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="kategori_delete">
                                                    <input type="hidden" name="id" value="<?php echo $kategori['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Menu -->
                <div class="tab-pane fade" id="menu" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-success text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-utensils me-2"></i>
                                    Kelola Menu
                                </h5>
                                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#menuModal">
                                    <i class="fas fa-plus me-1"></i>Tambah Menu
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Kategori</th>
                                            <th>Harga</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($menuData as $menu): ?>
                                        <tr>
                                            <td><?php echo $menu['id']; ?></td>
                                            <td><?php echo htmlspecialchars($menu['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($menu['kategori_nama']); ?></td>
                                            <td>Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $menu['status'] === 'tersedia' ? 'bg-success' : 'bg-danger'; ?>">
                                                    <?php echo $menu['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editMenu(<?php echo $menu['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="menu_delete">
                                                    <input type="hidden" name="id" value="<?php echo $menu['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus menu ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Meja -->
                <div class="tab-pane fade" id="meja" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-info text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-chair me-2"></i>
                                    Kelola Meja
                                </h5>
                                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#mejaModal">
                                    <i class="fas fa-plus me-1"></i>Tambah Meja
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nomor</th>
                                            <th>Kapasitas</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($mejaData as $meja): ?>
                                        <tr>
                                            <td><?php echo $meja['id']; ?></td>
                                            <td><?php echo $meja['nomor']; ?></td>
                                            <td><?php echo $meja['kapasitas']; ?> orang</td>
                                            <td>
                                                <span class="badge <?php echo $meja['status'] === 'tersedia' ? 'bg-success' : ($meja['status'] === 'terisi' ? 'bg-danger' : 'bg-warning'); ?>">
                                                    <?php echo $meja['status']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editMeja(<?php echo $meja['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="meja_delete">
                                                    <input type="hidden" name="id" value="<?php echo $meja['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus meja ini?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Pesanan -->
                <div class="tab-pane fade" id="pesanan" role="tabpanel">
                    <div class="card mt-3">
                        <div class="card-header bg-warning text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    <i class="fas fa-receipt me-2"></i>
                                    Kelola Pesanan
                                </h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Meja</th>
                                            <th>WhatsApp</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Waktu</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pesananData as $pesanan): ?>
                                        <tr>
                                            <td><?php echo $pesanan['id']; ?></td>
                                            <td>Meja <?php echo $pesanan['meja_nomor']; ?></td>
                                            <td>
                                                <?php if ($pesanan['nomor_pelanggan']): ?>
                                                    <div>
                                                        <i class="fab fa-whatsapp text-success"></i>
                                                        <?php echo htmlspecialchars($pesanan['nomor_pelanggan']); ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>Rp <?php echo number_format($pesanan['total'], 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo $pesanan['status'] === 'pending' ? 'bg-warning' : 
                                                         ($pesanan['status'] === 'dikonfirmasi' ? 'bg-info' : 
                                                         ($pesanan['status'] === 'diproses' ? 'bg-primary' : 
                                                         ($pesanan['status'] === 'selesai' ? 'bg-success' : 'bg-danger'))); 
                                                ?>">
                                                    <?php echo $pesanan['status']; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($pesanan['created_at'])); ?></td>
                                            <td>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="pesanan_update_status">
                                                    <input type="hidden" name="id" value="<?php echo $pesanan['id']; ?>">
                                                    <select name="status" onchange="this.form.submit()" class="form-select form-select-sm">
                                                        <option value="pending" <?php echo $pesanan['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="dikonfirmasi" <?php echo $pesanan['status'] === 'dikonfirmasi' ? 'selected' : ''; ?>>Dikonfirmasi</option>
                                                        <option value="diproses" <?php echo $pesanan['status'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                        <option value="selesai" <?php echo $pesanan['status'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                                        <option value="dibatalkan" <?php echo $pesanan['status'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                    </select>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Kategori -->
    <div class="modal fade" id="kategoriModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="kategoriModalTitle">Tambah Kategori</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="kategoriForm">
                    <div class="modal-body">
                        <input type="hidden" id="kategoriId" name="id">
                        <div class="mb-3">
                            <label for="kategoriNama" class="form-label">Nama Kategori *</label>
                            <input type="text" class="form-control" id="kategoriNama" name="nama" required>
                        </div>
                        <div class="mb-3">
                            <label for="kategoriDeskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="kategoriDeskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Menu -->
    <div class="modal fade" id="menuModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalTitle">Tambah Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="menuForm" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" id="menuId" name="id">
                        <input type="hidden" name="action" value="menu_create">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="menuNama" class="form-label">Nama Menu *</label>
                                    <input type="text" class="form-control" id="menuNama" name="nama" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="menuKategori" class="form-label">Kategori *</label>
                                    <select class="form-select" id="menuKategori" name="kategori_id" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($kategoriData as $kategori): ?>
                                        <option value="<?php echo $kategori['id']; ?>"><?php echo htmlspecialchars($kategori['nama']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="menuDeskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="menuDeskripsi" name="deskripsi" rows="3"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuHarga" class="form-label">Harga *</label>
                                    <input type="number" class="form-control" id="menuHarga" name="harga" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="menuStatus" class="form-label">Status</label>
                                    <select class="form-select" id="menuStatus" name="status">
                                        <option value="tersedia">Tersedia</option>
                                        <option value="habis">Habis</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="menuGambar" class="form-label">Gambar</label>
                            <input type="file" class="form-control" id="menuGambar" name="gambar_file" accept="image/*">
                            <div class="form-text">Format: jpg, jpeg, png, gif. Maks 2MB.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Meja -->
    <div class="modal fade" id="mejaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mejaModalTitle">Tambah Meja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="mejaForm">
                    <div class="modal-body">
                        <input type="hidden" id="mejaId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mejaNomor" class="form-label">Nomor Meja *</label>
                                    <input type="number" class="form-control" id="mejaNomor" name="nomor" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="mejaKapasitas" class="form-label">Kapasitas *</label>
                                    <input type="number" class="form-control" id="mejaKapasitas" name="kapasitas" min="1" value="4" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="mejaStatus" class="form-label">Status</label>
                            <select class="form-select" id="mejaStatus" name="status">
                                <option value="tersedia">Tersedia</option>
                                <option value="terisi">Terisi</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-info">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/api.js"></script>
    <script>
        // Global variables
        let kategoriData = <?php echo json_encode($kategoriData, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let menuData = <?php echo json_encode($menuData, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let mejaData = <?php echo json_encode($mejaData, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        let pesananData = <?php echo json_encode($pesananData, JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        
        // Initialize data on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadAllData();
        });

        // Load all data from API
        async function loadAllData() {
            try {
                const [kategoriRes, menuRes, mejaRes, pesananRes] = await Promise.all([
                    api.getKategori(),
                    api.getMenu(),
                    api.getMeja(),
                    api.getPesanan()
                ]);

                kategoriData = kategoriRes.data;
                menuData = menuRes.data;
                mejaData = mejaRes.data;
                pesananData = pesananRes.data;

                updateTables();
            } catch (error) {
                showNotification('Gagal memuat data: ' + error.message, 'error');
            }
        }

        // Update all tables
        function updateTables() {
            updateKategoriTable();
            updateMenuTable();
            updateMejaTable();
            updatePesananTable();
        }

        // Form handling with AJAX
        document.getElementById('kategoriForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('kategoriId').value;
            const nama = document.getElementById('kategoriNama').value;
            const deskripsi = document.getElementById('kategoriDeskripsi').value;
            const submitBtn = this.querySelector('button[type="submit"]');
            
            showLoading(submitBtn, true);
            
            try {
                if (id) {
                    await api.updateKategori(id, nama, deskripsi);
                } else {
                    await api.createKategori(nama, deskripsi);
                }
                
                showNotification('Kategori berhasil disimpan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('kategoriModal')).hide();
                this.reset();
                await loadAllData();
            } catch (error) {
                showNotification('Gagal menyimpan kategori: ' + error.message, 'error');
            } finally {
                showLoading(submitBtn, false);
                submitBtn.innerHTML = 'Simpan';
            }
        });

        // Reset forms when modals are opened for adding new data
        document.getElementById('kategoriModal').addEventListener('show.bs.modal', function() {
            if (!document.getElementById('kategoriId').value) {
                // Reset form for new kategori
                document.getElementById('kategoriForm').reset();
                document.getElementById('kategoriId').value = '';
                document.getElementById('kategoriModalTitle').textContent = 'Tambah Kategori';
            }
        });

        document.getElementById('menuModal').addEventListener('show.bs.modal', function() {
            if (!document.getElementById('menuId').value) {
                // Reset form for new menu
                document.getElementById('menuForm').reset();
                document.getElementById('menuId').value = '';
                document.getElementById('menuModalTitle').textContent = 'Tambah Menu';
            }
        });

        document.getElementById('mejaModal').addEventListener('show.bs.modal', function() {
            if (!document.getElementById('mejaId').value) {
                // Reset form for new meja
                document.getElementById('mejaForm').reset();
                document.getElementById('mejaId').value = '';
                document.getElementById('mejaKapasitas').value = '4';
                document.getElementById('mejaModalTitle').textContent = 'Tambah Meja';
            }
        });

        // Menu form handling
        document.getElementById('menuForm').addEventListener('submit', function() {
            // Biarkan form submit normal (multipart) ke server
            const id = document.getElementById('menuId').value;
            this.querySelector('input[name="action"]').value = id ? 'menu_update' : 'menu_create';
        });

        // Meja form handling
        document.getElementById('mejaForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('mejaId').value;
            const data = {
                nomor: document.getElementById('mejaNomor').value,
                kapasitas: document.getElementById('mejaKapasitas').value,
                status: document.getElementById('mejaStatus').value
            };
            
            if (id) data.id = id;
            
            const submitBtn = this.querySelector('button[type="submit"]');
            showLoading(submitBtn, true);
            
            try {
                if (id) {
                    await api.updateMeja(data);
                } else {
                    await api.createMeja(data);
                }
                
                showNotification('Meja berhasil disimpan', 'success');
                bootstrap.Modal.getInstance(document.getElementById('mejaModal')).hide();
                this.reset();
                await loadAllData();
            } catch (error) {
                showNotification('Gagal menyimpan meja: ' + error.message, 'error');
            } finally {
                showLoading(submitBtn, false);
                submitBtn.innerHTML = 'Simpan';
            }
        });

        // Update table functions
        function updateKategoriTable() {
            const tbody = document.querySelector('#kategori tbody');
            tbody.innerHTML = '';
            
            kategoriData.forEach(kategori => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${kategori.id}</td>
                    <td>${kategori.nama}</td>
                    <td>${kategori.deskripsi || '-'}</td>
                    <td>${formatDate(kategori.created_at)}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editKategori(${kategori.id}, '${kategori.nama}', '${kategori.deskripsi || ''}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteKategori(${kategori.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function updateMenuTable() {
            const tbody = document.querySelector('#menu tbody');
            tbody.innerHTML = '';
            
            menuData.forEach(menu => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${menu.id}</td>
                    <td>${menu.nama}</td>
                    <td>${menu.kategori_nama}</td>
                    <td>${formatRupiah(menu.harga)}</td>
                    <td>
                        <span class="badge ${menu.status === 'tersedia' ? 'bg-success' : 'bg-danger'}">
                            ${menu.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editMenu(${menu.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMenu(${menu.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function updateMejaTable() {
            const tbody = document.querySelector('#meja tbody');
            tbody.innerHTML = '';
            
            mejaData.forEach(meja => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${meja.id}</td>
                    <td>${meja.nomor}</td>
                    <td>${meja.kapasitas} orang</td>
                    <td>
                        <span class="badge ${meja.status === 'tersedia' ? 'bg-success' : (meja.status === 'terisi' ? 'bg-danger' : 'bg-warning')}">
                            ${meja.status}
                        </span>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary me-1" onclick="editMeja(${meja.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMeja(${meja.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function updatePesananTable() {
            const tbody = document.querySelector('#pesanan tbody');
            tbody.innerHTML = '';
            
            pesananData.forEach(pesanan => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${pesanan.id}</td>
                    <td>Meja ${pesanan.meja_nomor}</td>
                    <td>
                        ${pesanan.nomor_pelanggan ? 
                            `<div><i class="fab fa-whatsapp text-success"></i> ${pesanan.nomor_pelanggan}</div>` : 
                            '<span class="text-muted">-</span>'
                        }
                    </td>
                    <td>${formatRupiah(pesanan.total)}</td>
                    <td>
                        <span class="badge ${getStatusBadgeClass(pesanan.status)}">
                            ${pesanan.status}
                        </span>
                    </td>
                    <td>${formatDate(pesanan.created_at)}</td>
                    <td>
                        <select class="form-select form-select-sm" onchange="updatePesananStatus(${pesanan.id}, this.value)">
                            <option value="pending" ${pesanan.status === 'pending' ? 'selected' : ''}>Pending</option>
                            <option value="dikonfirmasi" ${pesanan.status === 'dikonfirmasi' ? 'selected' : ''}>Dikonfirmasi</option>
                            <option value="diproses" ${pesanan.status === 'diproses' ? 'selected' : ''}>Diproses</option>
                            <option value="selesai" ${pesanan.status === 'selesai' ? 'selected' : ''}>Selesai</option>
                            <option value="dibatalkan" ${pesanan.status === 'dibatalkan' ? 'selected' : ''}>Dibatalkan</option>
                        </select>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function getStatusBadgeClass(status) {
            switch(status) {
                case 'pending': return 'bg-warning';
                case 'dikonfirmasi': return 'bg-info';
                case 'diproses': return 'bg-primary';
                case 'selesai': return 'bg-success';
                case 'dibatalkan': return 'bg-danger';
                default: return 'bg-secondary';
            }
        }

        // CRUD Functions
        async function deleteKategori(id) {
            if (!confirm('Yakin ingin menghapus kategori ini?')) return;
            
            try {
                await api.deleteKategori(id);
                showNotification('Kategori berhasil dihapus', 'success');
                await loadAllData();
            } catch (error) {
                showNotification('Gagal menghapus kategori: ' + error.message, 'error');
            }
        }

        async function deleteMenu(id) {
            if (!confirm('Yakin ingin menghapus menu ini?')) return;
            
            try {
                await api.deleteMenu(id);
                showNotification('Menu berhasil dihapus', 'success');
                await loadAllData();
            } catch (error) {
                showNotification('Gagal menghapus menu: ' + error.message, 'error');
            }
        }

        async function deleteMeja(id) {
            if (!confirm('Yakin ingin menghapus meja ini?')) return;
            
            try {
                await api.deleteMeja(id);
                showNotification('Meja berhasil dihapus', 'success');
                await loadAllData();
            } catch (error) {
                showNotification('Gagal menghapus meja: ' + error.message, 'error');
            }
        }

        async function updatePesananStatus(id, status) {
            try {
                const result = await api.updateStatusPesanan(id, status);
                showNotification('Status pesanan berhasil diperbarui', 'success');
                
                // Jika server tidak bisa kirim otomatis, server akan mengembalikan URL fallback
                if (typeof result.notification === 'string' && result.notification.startsWith('http')) {
                    window.open(result.notification, '_blank');
                }
                
                await loadAllData();
            } catch (error) {
                showNotification('Gagal memperbarui status: ' + error.message, 'error');
            }
        }

        // Edit functions
        function editKategori(id, nama, deskripsi) {
            document.getElementById('kategoriId').value = id;
            document.getElementById('kategoriNama').value = nama;
            document.getElementById('kategoriDeskripsi').value = deskripsi;
            document.getElementById('kategoriModalTitle').textContent = 'Edit Kategori';
            new bootstrap.Modal(document.getElementById('kategoriModal')).show();
        }

        function editMenu(id) {
            console.log('Edit menu called with ID:', id);
            console.log('Menu data:', menuData);
            
            // Find menu data
            const menu = menuData.find(m => m.id == id);
            if (!menu) {
                console.log('Menu not found with ID:', id);
                return;
            }
            
            // Fill form with menu data
            document.getElementById('menuId').value = menu.id;
            document.getElementById('menuNama').value = menu.nama;
            document.getElementById('menuDeskripsi').value = menu.deskripsi || '';
            document.getElementById('menuHarga').value = menu.harga;
            // Gambar tidak bisa diisi otomatis pada input type=file
            document.getElementById('menuKategori').value = menu.kategori_id;
            document.getElementById('menuStatus').value = menu.status;
            
            // Change modal title
            document.getElementById('menuModalTitle').textContent = 'Edit Menu';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('menuModal')).show();
        }

        function editMeja(id) {
            console.log('Edit meja called with ID:', id);
            console.log('Meja data:', mejaData);
            
            // Find meja data
            const meja = mejaData.find(m => m.id == id);
            if (!meja) {
                console.log('Meja not found with ID:', id);
                return;
            }
            
            // Fill form with meja data
            document.getElementById('mejaId').value = meja.id;
            document.getElementById('mejaNomor').value = meja.nomor;
            document.getElementById('mejaKapasitas').value = meja.kapasitas;
            document.getElementById('mejaStatus').value = meja.status;
            
            // Change modal title
            document.getElementById('mejaModalTitle').textContent = 'Edit Meja';
            
            // Show modal
            new bootstrap.Modal(document.getElementById('mejaModal')).show();
        }
        
    </script>
</body>
</html>