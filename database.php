<?php
// File database.php - Koneksi database dan semua query
// Semua operasi database untuk aplikasi restoran

// Konfigurasi database (hanya tetapkan jika belum didefinisikan di config.php)
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'restoran');

class Database {
    private $pdo;
    
    private function sendWhatsAppCloudMessage($toNumber, $message) {
        // Kirim via WhatsApp Cloud API jika kredensial tersedia
        if (!defined('WHATSAPP_CLOUD_PHONE_NUMBER_ID') || !defined('WHATSAPP_CLOUD_ACCESS_TOKEN')) {
            return false;
        }
        if (empty(WHATSAPP_CLOUD_PHONE_NUMBER_ID) || empty(WHATSAPP_CLOUD_ACCESS_TOKEN)) {
            return false;
        }
        $url = 'https://graph.facebook.com/v17.0/' . WHATSAPP_CLOUD_PHONE_NUMBER_ID . '/messages';
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $toNumber,
            'type' => 'text',
            'text' => ['body' => $message]
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . WHATSAPP_CLOUD_ACCESS_TOKEN,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($response === false || $httpCode >= 400) {
            error_log('WhatsApp Cloud API error: ' . ($response ?: curl_error($ch)) . ' HTTP ' . $httpCode);
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return true;
    }
    
    public function __construct() {
        try {
            $this->pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    // ==================== KATEGORI ====================
    
    public function getAllKategori() {
        $stmt = $this->pdo->query("SELECT * FROM kategori ORDER BY nama");
        return $stmt->fetchAll();
    }
    
    public function getKategoriById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM kategori WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createKategori($nama, $deskripsi = '') {
        // Cek apakah nama kategori sudah ada
        $stmt = $this->pdo->prepare("SELECT id FROM kategori WHERE nama = ?");
        $stmt->execute([$nama]);
        if ($stmt->fetch()) {
            throw new Exception('Nama kategori sudah ada');
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO kategori (nama, deskripsi) VALUES (?, ?)");
        $result = $stmt->execute([$nama, $deskripsi]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        throw new Exception('Gagal menambahkan kategori');
    }
    
    public function updateKategori($id, $nama, $deskripsi = '') {
        // Cek apakah nama kategori sudah ada (kecuali untuk kategori yang sedang diedit)
        $stmt = $this->pdo->prepare("SELECT id FROM kategori WHERE nama = ? AND id != ?");
        $stmt->execute([$nama, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Nama kategori sudah ada');
        }
        
        $stmt = $this->pdo->prepare("UPDATE kategori SET nama = ?, deskripsi = ? WHERE id = ?");
        return $stmt->execute([$nama, $deskripsi, $id]);
    }
    
    public function deleteKategori($id) {
        // Cek apakah kategori memiliki menu
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM menu WHERE kategori_id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception('Tidak dapat menghapus kategori yang memiliki menu');
        }
        
        // Cek apakah ini kategori dasar (ID 1 atau 2)
        if ($id == 1 || $id == 2) {
            throw new Exception('Tidak dapat menghapus kategori dasar');
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM kategori WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // ==================== MENU ====================
    
    public function getAllMenu() {
        $stmt = $this->pdo->query("
            SELECT m.*, k.nama as kategori_nama, m.kategori_id 
            FROM menu m 
            JOIN kategori k ON m.kategori_id = k.id 
            ORDER BY k.nama, m.nama
        ");
        return $stmt->fetchAll();
    }
    
    public function getMenuById($id) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, k.nama as kategori_nama 
            FROM menu m 
            JOIN kategori k ON m.kategori_id = k.id 
            WHERE m.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getMenuByKategori($kategori_id) {
        $stmt = $this->pdo->prepare("
            SELECT m.*, k.nama as kategori_nama 
            FROM menu m 
            JOIN kategori k ON m.kategori_id = k.id 
            WHERE m.kategori_id = ? AND m.status = 'tersedia' 
            ORDER BY m.nama
        ");
        $stmt->execute([$kategori_id]);
        return $stmt->fetchAll();
    }
    
    public function createMenu($nama, $deskripsi, $harga, $gambar, $kategori_id, $status = 'tersedia') {
        // Validasi kategori_id
        $stmt = $this->pdo->prepare("SELECT id FROM kategori WHERE id = ?");
        $stmt->execute([$kategori_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Kategori tidak valid');
        }
        
        $stmt = $this->pdo->prepare("
            INSERT INTO menu (nama, deskripsi, harga, gambar, kategori_id, status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $result = $stmt->execute([$nama, $deskripsi, $harga, $gambar, $kategori_id, $status]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        throw new Exception('Gagal menambahkan menu');
    }
    
    public function updateMenu($id, $nama, $deskripsi, $harga, $gambar, $kategori_id, $status) {
        // Validasi kategori_id
        $stmt = $this->pdo->prepare("SELECT id FROM kategori WHERE id = ?");
        $stmt->execute([$kategori_id]);
        if (!$stmt->fetch()) {
            throw new Exception('Kategori tidak valid');
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE menu 
            SET nama = ?, deskripsi = ?, harga = ?, gambar = ?, kategori_id = ?, status = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$nama, $deskripsi, $harga, $gambar, $kategori_id, $status, $id]);
    }
    
    public function deleteMenu($id) {
        $stmt = $this->pdo->prepare("DELETE FROM menu WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // ==================== MEJA ====================
    
    public function getAllMeja() {
        $stmt = $this->pdo->query("SELECT * FROM meja ORDER BY nomor");
        return $stmt->fetchAll();
    }
    
    public function getMejaById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM meja WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getMejaByNomor($nomor) {
        $stmt = $this->pdo->prepare("SELECT * FROM meja WHERE nomor = ?");
        $stmt->execute([$nomor]);
        return $stmt->fetch();
    }
    
    public function createMeja($nomor, $kapasitas, $status = 'tersedia') {
        // Cek apakah nomor meja sudah ada
        $stmt = $this->pdo->prepare("SELECT id FROM meja WHERE nomor = ?");
        $stmt->execute([$nomor]);
        if ($stmt->fetch()) {
            throw new Exception('Nomor meja sudah ada');
        }
        
        $stmt = $this->pdo->prepare("INSERT INTO meja (nomor, kapasitas, status) VALUES (?, ?, ?)");
        $result = $stmt->execute([$nomor, $kapasitas, $status]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        throw new Exception('Gagal menambahkan meja');
    }
    
    public function updateMeja($id, $nomor, $kapasitas, $status) {
        // Cek apakah nomor meja sudah ada (kecuali untuk meja yang sedang diedit)
        $stmt = $this->pdo->prepare("SELECT id FROM meja WHERE nomor = ? AND id != ?");
        $stmt->execute([$nomor, $id]);
        if ($stmt->fetch()) {
            throw new Exception('Nomor meja sudah ada');
        }
        
        $stmt = $this->pdo->prepare("UPDATE meja SET nomor = ?, kapasitas = ?, status = ? WHERE id = ?");
        return $stmt->execute([$nomor, $kapasitas, $status, $id]);
    }
    
    public function deleteMeja($id) {
        // Cek apakah meja memiliki pesanan aktif
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM pesanan WHERE meja_id = ? AND status IN ('pending', 'dikonfirmasi', 'diproses')");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            throw new Exception('Tidak dapat menghapus meja yang memiliki pesanan aktif');
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM meja WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    // ==================== PESANAN ====================
    
    public function createPesanan($meja_id, $total, $nomor_pelanggan = null) {
        $stmt = $this->pdo->prepare("INSERT INTO pesanan (meja_id, nomor_pelanggan, total, status) VALUES (?, ?, ?, 'pending')");
        $result = $stmt->execute([$meja_id, $nomor_pelanggan, $total]);
        
        if ($result) {
            return $this->pdo->lastInsertId();
        }
        throw new Exception('Gagal membuat pesanan');
    }
    
    public function createDetailPesanan($pesanan_id, $menu_id, $quantity, $harga, $subtotal) {
        $stmt = $this->pdo->prepare("
            INSERT INTO detail_pesanan (pesanan_id, menu_id, quantity, harga, subtotal) 
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$pesanan_id, $menu_id, $quantity, $harga, $subtotal]);
    }
    
    public function getAllPesanan() {
        $stmt = $this->pdo->query("
            SELECT p.*, m.nomor as meja_nomor 
            FROM pesanan p 
            JOIN meja m ON p.meja_id = m.id 
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function getPesananById($id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, m.nomor as meja_nomor 
            FROM pesanan p 
            JOIN meja m ON p.meja_id = m.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function getDetailPesanan($pesanan_id) {
        $stmt = $this->pdo->prepare("
            SELECT dp.*, menu.nama as menu_nama 
            FROM detail_pesanan dp 
            JOIN menu ON dp.menu_id = menu.id 
            WHERE dp.pesanan_id = ?
        ");
        $stmt->execute([$pesanan_id]);
        return $stmt->fetchAll();
    }
    
    public function getDetailPesananByPesananId($pesanan_id) {
        $stmt = $this->pdo->prepare("
            SELECT dp.*, menu.nama as menu_nama 
            FROM detail_pesanan dp 
            JOIN menu ON dp.menu_id = menu.id 
            WHERE dp.pesanan_id = ?
        ");
        $stmt->execute([$pesanan_id]);
        return $stmt->fetchAll();
    }
    
    public function updateStatusPesanan($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }
    
    // ==================== UTILITY ====================
    
    public function formatRupiah($angka) {
        return 'Rp ' . number_format($angka, 0, ',', '.');
    }
    
    // ==================== ORDER PROCESSING ====================
    
    public function processTableSelection($meja_id) {
        $meja = $this->getMejaById($meja_id);
        if ($meja && $meja['status'] === 'tersedia') {
            // Update status meja menjadi terisi
            $this->updateMeja($meja_id, $meja['nomor'], $meja['kapasitas'], 'terisi');
            return $meja;
        }
        return false;
    }
    
    public function processOrderCreation($meja_id, $items, $nomor_pelanggan = null) {
        if (!$items || count($items) === 0) {
            throw new Exception('Tidak ada item dalam pesanan');
        }
        
        // Hitung total
        $total = 0;
        foreach ($items as $item) {
            $total += $item['subtotal'];
        }
        
        // Buat pesanan
        $pesanan_id = $this->createPesanan($meja_id, $total, $nomor_pelanggan);
        
        // Buat detail pesanan
        foreach ($items as $item) {
            $this->createDetailPesanan(
                $pesanan_id, 
                $item['menu_id'], 
                $item['quantity'], 
                $item['harga'], 
                $item['subtotal']
            );
        }
        
        return $pesanan_id;
    }
    
    public function getOrderConfirmationData($order_id) {
        $pesanan = $this->getPesananById($order_id);
        if (!$pesanan) {
            throw new Exception('Pesanan tidak ditemukan');
        }
        
        $meja = $this->getMejaById($pesanan['meja_id']);
        $detailPesanan = $this->getDetailPesananByPesananId($order_id);
        
        // Hitung total
        $total = 0;
        foreach ($detailPesanan as $detail) {
            $total += $detail['subtotal'];
        }
        
        return [
            'pesanan' => $pesanan,
            'meja' => $meja,
            'detailPesanan' => $detailPesanan,
            'total' => $total
        ];
    }
    
    public function generateWhatsAppMessage($orderData, $prefix = '') {
        $pesanan = $orderData['pesanan'];
        $meja = $orderData['meja'];
        $detailPesanan = $orderData['detailPesanan'];
        $total = $orderData['total'];
        
        $message = $prefix . "\n\n";
        $message .= "📍 *Meja:* " . $meja['nomor'] . "\n";
        $message .= "🕐 *Waktu:* " . date('d/m/Y H:i:s', strtotime($pesanan['created_at'])) . "\n";
        $message .= "🆔 *ID Pesanan:* #" . str_pad($pesanan['id'], 4, '0', STR_PAD_LEFT) . "\n\n";
        $message .= "📋 *Detail Pesanan:*\n";
        foreach ($detailPesanan as $detail) {
            $message .= "• " . $detail['menu_nama'] . " x" . $detail['quantity'] . " = Rp " . number_format($detail['subtotal'], 0, ',', '.') . "\n";
        }
        $message .= "\n💰 *Total Pembayaran:* Rp " . number_format($total, 0, ',', '.') . "\n\n";
        $message .= "Terima kasih atas pesanan Anda! 🙏";
        
        return $message;
    }
    
    public function generateCustomerNotificationMessage($pesanan, $status) {
        $statusMessages = [
            'pending' => '⏳ Pesanan Anda sedang menunggu konfirmasi',
            'dikonfirmasi' => '✅ Pesanan Anda telah dikonfirmasi dan sedang dipersiapkan',
            'diproses' => '👨‍🍳 Pesanan Anda sedang diproses di dapur',
            'selesai' => '🎉 Pesanan Anda sudah siap! Silakan ambil di meja Anda',
            'dibatalkan' => '❌ Maaf, pesanan Anda dibatalkan. Silakan hubungi kasir untuk informasi lebih lanjut'
        ];
        
        $message = "🍽️ *UPDATE PESANAN RESTORAN*\n\n";
        $message .= "🆔 *ID Pesanan:* #" . str_pad($pesanan['id'], 4, '0', STR_PAD_LEFT) . "\n";
        $message .= "📍 *Meja:* " . $pesanan['meja_nomor'] . "\n";
        $message .= "📱 *Status:* " . ucfirst($status) . "\n\n";
        $message .= $statusMessages[$status] ?? "Status pesanan telah diupdate";
        $message .= "\n\nTerima kasih! 🙏";
        
        return $message;
    }
    
    public function sendCustomerNotification($pesanan_id, $status) {
        $pesanan = $this->getPesananById($pesanan_id);
        if (!$pesanan || !$pesanan['nomor_pelanggan']) {
            return false;
        }
        
        $message = $this->generateCustomerNotificationMessage($pesanan, $status);
        
        // Jika kredensial Cloud API ada, kirim otomatis dari server
        if ($this->sendWhatsAppCloudMessage($pesanan['nomor_pelanggan'], $message)) {
            return true; // terkirim otomatis
        }
        
        // Fallback: kembalikan URL agar bisa dibuka manual jika perlu
        $whatsappUrl = "https://wa.me/" . $pesanan['nomor_pelanggan'] . "?text=" . urlencode($message);
        return $whatsappUrl;
    }
    
    // ==================== AUTHENTICATION ====================
    
    public function authenticateUser($username, $password) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, password, nama, email, role, status 
            FROM users 
            WHERE username = ? AND status = 'active'
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function getUserById($id) {
        $stmt = $this->pdo->prepare("
            SELECT id, username, nama, email, role, status, created_at 
            FROM users 
            WHERE id = ? AND status = 'active'
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function createUser($username, $password, $nama, $email = null, $role = 'admin') {
        // Cek apakah username sudah ada
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()['count'] > 0) {
            throw new Exception('Username sudah ada');
        }
        
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, password, nama, email, role, status) 
            VALUES (?, ?, ?, ?, ?, 'active')
        ");
        return $stmt->execute([$username, $hashedPassword, $nama, $email, $role]);
    }
    
    public function updateUser($id, $username, $nama, $email = null, $role = 'admin') {
        // Cek apakah username sudah ada (kecuali untuk user yang sama)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) as count FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetch()['count'] > 0) {
            throw new Exception('Username sudah ada');
        }
        
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET username = ?, nama = ?, email = ?, role = ? 
            WHERE id = ?
        ");
        return $stmt->execute([$username, $nama, $email, $role, $id]);
    }
    
    public function changePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashedPassword, $id]);
    }
    
    public function getAllUsers() {
        $stmt = $this->pdo->query("
            SELECT id, username, nama, email, role, status, created_at 
            FROM users 
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll();
    }
    
    public function deleteUser($id) {
        // Jangan hapus user admin default
        $stmt = $this->pdo->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        
        if ($user && $user['username'] === 'admin') {
            throw new Exception('Tidak dapat menghapus user admin default');
        }
        
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }
}

// Global instance
$db = new Database();
