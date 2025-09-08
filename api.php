<?php
/**
 * Fixed API untuk Restoran Digital
 * Versi yang lebih stabil dan sederhana
 */

// Start session first
session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    // Muat konfigurasi terlebih dahulu agar konstanta WhatsApp tersedia
    if (file_exists(__DIR__ . '/config.php')) {
        require_once __DIR__ . '/config.php';
    }
    require_once 'database.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        // ==================== KATEGORI ====================
        case 'get_kategori':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $data = $db->getAllKategori();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'create_kategori':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['nama'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nama kategori required']);
                exit();
            }
            $id = $db->createKategori($input['nama'], $input['deskripsi'] ?? '');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Kategori berhasil ditambahkan']);
            break;

        case 'update_kategori':
            if ($method !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id']) || !isset($input['nama'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID dan nama kategori required']);
                exit();
            }
            $result = $db->updateKategori($input['id'], $input['nama'], $input['deskripsi'] ?? '');
            echo json_encode(['success' => $result, 'message' => 'Kategori berhasil diperbarui']);
            break;

        case 'delete_kategori':
            if ($method !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit();
            }
            $result = $db->deleteKategori($id);
            echo json_encode(['success' => $result, 'message' => 'Kategori berhasil dihapus']);
            break;

        // ==================== MENU ====================
        case 'get_menu':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $data = $db->getAllMenu();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'create_menu':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['nama']) || !isset($input['harga']) || !isset($input['kategori_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nama, harga, dan kategori_id required']);
                exit();
            }
            $id = $db->createMenu(
                $input['nama'], 
                $input['deskripsi'] ?? '', 
                $input['harga'], 
                $input['gambar'] ?? '', 
                $input['kategori_id'], 
                $input['status'] ?? 'tersedia'
            );
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Menu berhasil ditambahkan']);
            break;

        case 'update_menu':
            if ($method !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id']) || !isset($input['nama']) || !isset($input['harga']) || !isset($input['kategori_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID, nama, harga, dan kategori_id required']);
                exit();
            }
            $result = $db->updateMenu(
                $input['id'], 
                $input['nama'], 
                $input['deskripsi'] ?? '', 
                $input['harga'], 
                $input['gambar'] ?? '', 
                $input['kategori_id'], 
                $input['status'] ?? 'tersedia'
            );
            echo json_encode(['success' => $result, 'message' => 'Menu berhasil diperbarui']);
            break;

        case 'delete_menu':
            if ($method !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit();
            }
            $result = $db->deleteMenu($id);
            echo json_encode(['success' => $result, 'message' => 'Menu berhasil dihapus']);
            break;

        // ==================== MEJA ====================
        case 'get_meja':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $data = $db->getAllMeja();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'create_meja':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['nomor']) || !isset($input['kapasitas'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nomor dan kapasitas meja required']);
                exit();
            }
            $id = $db->createMeja($input['nomor'], $input['kapasitas'], $input['status'] ?? 'tersedia');
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Meja berhasil ditambahkan']);
            break;

        case 'update_meja':
            if ($method !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id']) || !isset($input['nomor']) || !isset($input['kapasitas'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID, nomor, dan kapasitas meja required']);
                exit();
            }
            $result = $db->updateMeja($input['id'], $input['nomor'], $input['kapasitas'], $input['status'] ?? 'tersedia');
            echo json_encode(['success' => $result, 'message' => 'Meja berhasil diperbarui']);
            break;

        case 'delete_meja':
            if ($method !== 'DELETE') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit();
            }
            $result = $db->deleteMeja($id);
            echo json_encode(['success' => $result, 'message' => 'Meja berhasil dihapus']);
            break;

        // ==================== PESANAN ====================
        case 'get_pesanan':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $data = $db->getAllPesanan();
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'create_pesanan':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid JSON input: ' . json_last_error_msg()]);
                exit();
            }
            
            if (!$input) {
                http_response_code(400);
                echo json_encode(['error' => 'Empty input data']);
                exit();
            }
            
            if (!isset($input['meja_id']) || !isset($input['items'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing required fields: meja_id, items']);
                exit();
            }
            
            $pesanan_id = $db->processOrderCreation($input['meja_id'], $input['items'], $input['nomor_pelanggan'] ?? null);
            echo json_encode(['success' => true, 'pesanan_id' => $pesanan_id, 'message' => 'Pesanan berhasil dibuat']);
            break;

        case 'update_status_pesanan':
            if ($method !== 'PUT') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['id']) || !isset($input['status'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID dan status required']);
                exit();
            }
            
            $result = $db->updateStatusPesanan($input['id'], $input['status']);
            
            // Kirim notifikasi ke pelanggan jika ada nomor WhatsApp
            $notificationResult = $db->sendCustomerNotification($input['id'], $input['status']);
            
            echo json_encode([
                'success' => $result, 
                'message' => 'Status pesanan berhasil diperbarui',
                'notification' => $notificationResult
            ]);
            break;

        case 'get_pesanan_detail':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $id = $_GET['id'] ?? '';
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['error' => 'ID required']);
                exit();
            }
            $data = $db->getOrderConfirmationData($id);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        // ==================== AUTHENTICATION ====================
        case 'login':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['username']) || !isset($input['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Username dan password required']);
                exit();
            }
            
            $user = $db->authenticateUser($input['username'], $input['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];
                
                echo json_encode(['success' => true, 'user' => $user, 'message' => 'Login berhasil']);
            } else {
                http_response_code(401);
                echo json_encode(['error' => 'Username atau password salah']);
            }
            break;

        case 'logout':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            session_destroy();
            echo json_encode(['success' => true, 'message' => 'Logout berhasil']);
            break;

        case 'check_auth':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            if (isset($_SESSION['user_id'])) {
                $user = $db->getUserById($_SESSION['user_id']);
                echo json_encode(['success' => true, 'authenticated' => true, 'user' => $user]);
            } else {
                echo json_encode(['success' => true, 'authenticated' => false]);
            }
            break;

        // ==================== UTILITY ====================
        case 'get_menu_by_kategori':
            if ($method !== 'GET') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $kategori_id = $_GET['kategori_id'] ?? '';
            if (empty($kategori_id)) {
                http_response_code(400);
                echo json_encode(['error' => 'Kategori ID required']);
                exit();
            }
            $data = $db->getMenuByKategori($kategori_id);
            echo json_encode(['success' => true, 'data' => $data]);
            break;

        case 'select_table':
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit();
            }
            $input = json_decode(file_get_contents('php://input'), true);
            if (!$input || !isset($input['meja_id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Meja ID required']);
                exit();
            }
            
            $meja = $db->processTableSelection($input['meja_id']);
            if ($meja) {
                echo json_encode(['success' => true, 'meja' => $meja, 'message' => 'Meja berhasil dipilih']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Meja tidak tersedia']);
            }
            break;

        default:
            http_response_code(404);
            echo json_encode(['error' => 'Action not found']);
            break;
    }
} catch (Exception $e) {
    error_log("API Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} catch (Error $e) {
    error_log("PHP Error: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
