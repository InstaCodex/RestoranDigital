<?php
require_once 'database.php';
require_once 'config.php';

$order_id = $_GET['order_id'] ?? null;
$status = $_GET['status'] ?? 'error';
$error_message = $_GET['message'] ?? '';

if (!$order_id && $status !== 'error') {
    header('Location: index.php');
    exit;
}

// Ambil data pesanan jika order_id ada
if ($order_id) {
    try {
        $orderData = $db->getOrderConfirmationData($order_id);
        $pesanan = $orderData['pesanan'];
        $meja = $orderData['meja'];
        $detailPesanan = $orderData['detailPesanan'];
        $total = $orderData['total'];
        
        // Generate WhatsApp message
        $whatsappMessage = $db->generateWhatsAppMessage($orderData, WHATSAPP_MESSAGE_PREFIX);
        $whatsappUrl = "https://wa.me/" . WHATSAPP_NUMBER . "?text=" . urlencode($whatsappMessage);
    } catch (Exception $e) {
        $status = 'error';
        $error_message = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pesanan - Restoran Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .confirmation-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            margin: 2rem 0;
        }
        .success-header {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .error-header {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .order-details {
            padding: 2rem;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 1rem 0;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .total-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 1rem;
        }
        .btn-custom {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .whatsapp-status {
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
        }
        .status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Mobile button spacing */
        @media (max-width: 768px) {
            .gap-2 > * + * {
                margin-top: 0.5rem !important;
            }
            .btn-custom {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="confirmation-card">
                    <?php if ($status === 'success'): ?>
                        <div class="success-header">
                            <i class="fas fa-check-circle fa-4x mb-3"></i>
                            <h2 class="mb-2">Pesanan Berhasil Dibuat!</h2>
                            <p class="mb-0">Silakan kirim pesanan ke WhatsApp kasir</p>
                        </div>
                    <?php else: ?>
                        <div class="error-header">
                            <i class="fas fa-exclamation-triangle fa-4x mb-3"></i>
                            <h2 class="mb-2">Terjadi Kesalahan</h2>
                            <p class="mb-0"><?php echo $error_message ?: 'Gagal membuat pesanan'; ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="order-details">
                        <h4 class="mb-4">
                            <i class="fas fa-receipt me-2"></i>
                            Detail Pesanan
                        </h4>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>ID Pesanan:</strong> #<?php echo str_pad($pesanan['id'], 4, '0', STR_PAD_LEFT); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Meja:</strong> <?php echo $meja['nomor']; ?>
                            </div>
                        </div>
                        
                        <?php if ($pesanan['nomor_pelanggan']): ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>WhatsApp:</strong> 
                                <i class="fab fa-whatsapp text-success"></i>
                                <?php echo htmlspecialchars($pesanan['nomor_pelanggan']); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Waktu Pesan:</strong> <?php echo date('d/m/Y H:i:s', strtotime($pesanan['created_at'])); ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> 
                                <span class="badge bg-warning"><?php echo ucfirst($pesanan['status']); ?></span>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h5 class="mb-3">Item Pesanan:</h5>
                        <?php foreach ($detailPesanan as $detail): ?>
                            <div class="order-item">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <strong><?php echo $detail['menu_nama']; ?></strong>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <span class="badge bg-primary"><?php echo $detail['quantity']; ?>x</span>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        Rp <?php echo number_format($detail['harga'], 0, ',', '.'); ?>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <strong>Rp <?php echo number_format($detail['subtotal'], 0, ',', '.'); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="total-section">
                            <div class="row">
                                <div class="col-md-8">
                                    <h4 class="mb-0">Total Pembayaran:</h4>
                                </div>
                                <div class="col-md-4 text-end">
                                    <h4 class="mb-0 text-primary">Rp <?php echo number_format($total, 0, ',', '.'); ?></h4>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($status === 'success'): ?>
                            <div class="whatsapp-status status-success">
                                <i class="fab fa-whatsapp me-2"></i>
                                <strong>Pesanan berhasil dibuat!</strong><br>
                                <small>Klik tombol WhatsApp di bawah untuk mengirim pesanan ke kasir.</small>
                                <?php if ($pesanan['nomor_pelanggan']): ?>
                                    <br><small><i class="fas fa-info-circle me-1"></i>Admin akan mengirim update status pesanan ke WhatsApp Anda saat mengubah status.</small>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-center mt-3 mb-3">
                                <a href="<?php echo $whatsappUrl; ?>" target="_blank" class="btn btn-success btn-lg btn-custom">
                                    <i class="fab fa-whatsapp me-2"></i>
                                    Kirim ke WhatsApp
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="whatsapp-status status-error">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Gagal membuat pesanan</strong><br>
                                <small>Silakan coba lagi atau hubungi kasir secara langsung.</small>
                            </div>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                                <a href="menu.php" class="btn btn-primary btn-custom">
                                    <i class="fas fa-plus me-2"></i>
                                    Pesan Lagi
                                </a>
                                <a href="index.php" class="btn btn-outline-secondary btn-custom">
                                    <i class="fas fa-home me-2"></i>
                                    Kembali ke Beranda
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
