<?php
/**
 * File konfigurasi aplikasi
 */

// Konfigurasi WhatsApp
define('WHATSAPP_NUMBER', '6281234567890'); // Nomor WhatsApp kasir (format: 62xxxxxxxxxx)
define('WHATSAPP_MESSAGE_PREFIX', '🍽️ *PESANAN BARU* 🍽️');

// Optional: WhatsApp Cloud API (isi agar pengiriman pesan otomatis dari server)
// Dapatkan dari Meta for Developers: phone_number_id dan access token
if (!defined('WHATSAPP_CLOUD_PHONE_NUMBER_ID')) define('WHATSAPP_CLOUD_PHONE_NUMBER_ID', '');
if (!defined('WHATSAPP_CLOUD_ACCESS_TOKEN')) define('WHATSAPP_CLOUD_ACCESS_TOKEN', '');

// Konfigurasi aplikasi
define('APP_NAME', 'Restoran Digital');
define('APP_VERSION', '1.0.0');

// Konfigurasi database (jika belum ada di database.php)
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'restoran');
}
?>
