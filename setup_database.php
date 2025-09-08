<?php
// Setup script untuk membuat database dan tabel yang diperlukan

error_reporting(E_ALL);
ini_set('display_errors', 1);

// JANGAN memuat database.php karena akan mencoba koneksi ke DB yang belum ada
// Tetapkan konstanta koneksi di sini (samakan dengan database.php)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'restoran');

function echo_line($text) {
    echo $text . "<br>\n";
}

try {
    // 1) Koneksi tanpa database untuk memastikan database dibuat
    $pdoNoDb = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 2) Buat database jika belum ada
    $charset = 'utf8mb4';
    $collation = 'utf8mb4_unicode_ci';
    $pdoNoDb->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET {$charset} COLLATE {$collation}");
    echo_line("✔ Database '" . DB_NAME . "' siap.");

    // 3) Koneksi ke database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // 4) Buat tabel-tabel (InnoDB, utf8mb4)
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS kategori (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL UNIQUE,
    deskripsi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'kategori' siap.");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS menu (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    deskripsi TEXT NULL,
    harga INT UNSIGNED NOT NULL,
    gambar VARCHAR(255) NOT NULL DEFAULT '',
    kategori_id INT UNSIGNED NOT NULL,
    status ENUM('tersedia','habis') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_kategori_id (kategori_id),
    CONSTRAINT fk_menu_kategori FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'menu' siap.");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS meja (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor INT UNSIGNED NOT NULL UNIQUE,
    kapasitas INT UNSIGNED NOT NULL DEFAULT 4,
    status ENUM('tersedia','terisi','nonaktif') NOT NULL DEFAULT 'tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'meja' siap.");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS pesanan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    meja_id INT UNSIGNED NOT NULL,
    nomor_pelanggan VARCHAR(20) NULL,
    total INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('pending','dikonfirmasi','diproses','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_meja_id (meja_id),
    CONSTRAINT fk_pesanan_meja FOREIGN KEY (meja_id) REFERENCES meja(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'pesanan' siap.");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS detail_pesanan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT UNSIGNED NOT NULL,
    menu_id INT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    harga INT UNSIGNED NOT NULL,
    subtotal INT UNSIGNED NOT NULL,
    INDEX idx_pesanan_id (pesanan_id),
    INDEX idx_menu_id (menu_id),
    CONSTRAINT fk_detail_pesanan_pesanan FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_detail_pesanan_menu FOREIGN KEY (menu_id) REFERENCES menu(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'detail_pesanan' siap.");

    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    role ENUM('admin','kasir') NOT NULL DEFAULT 'admin',
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL);
    echo_line("✔ Tabel 'users' siap.");

    // 5) Seed data penting
    // Pastikan ada 2 kategori dasar (id 1 dan 2) agar selaras dengan logika penghapusan kategori di aplikasi
    $pdo->exec("INSERT IGNORE INTO kategori (id, nama, deskripsi) VALUES (1, 'Makanan', 'Kategori makanan'), (2, 'Minuman', 'Kategori minuman')");
    echo_line("✔ Seed kategori dasar (Makanan, Minuman).");

    // Tambahkan beberapa meja default jika belum ada
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM meja");
    $countMeja = (int)$stmt->fetch()['c'];
    if ($countMeja === 0) {
        $insertMeja = $pdo->prepare("INSERT INTO meja (nomor, kapasitas, status) VALUES (?, ?, 'tersedia')");
        for ($i = 1; $i <= 5; $i++) {
            $insertMeja->execute([$i, 4]);
        }
        echo_line("✔ Seed meja 1-5.");
    } else {
        echo_line("ℹ Meja sudah ada: {$countMeja} baris.");
    }

    // Tambahkan admin default jika belum ada
    $stmt = $pdo->prepare("SELECT COUNT(*) AS c FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    $existsAdmin = (int)$stmt->fetch()['c'] > 0;
    if (!$existsAdmin) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $ins = $pdo->prepare("INSERT INTO users (username, password, nama, email, role, status) VALUES (?, ?, ?, ?, 'admin', 'active')");
        $ins->execute(['admin', $password, 'Administrator', 'admin@example.com']);
        echo_line("✔ User admin default dibuat (username: admin, password: admin123) – ganti segera.");
    } else {
        echo_line("ℹ User admin sudah ada.");
    }

    echo '<hr>';
    echo_line("Selesai. Demi keamanan, hapus file <code>setup_database.php</code> setelah proses ini.");
    echo_line("Buka <a href='index.php'>index.php</a> atau <a href='admin.php'>admin.php</a>.");
} catch (PDOException $e) {
    http_response_code(500);
    echo_line('Database error: ' . htmlspecialchars($e->getMessage()));
} catch (Throwable $e) {
    http_response_code(500);
    echo_line('Unexpected error: ' . htmlspecialchars($e->getMessage()));
}

?>


