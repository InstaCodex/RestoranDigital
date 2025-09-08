<?php
require_once 'database.php';
require_once 'config.php';

// Ambil data meja dan menu
$mejaData = $db->getAllMeja();
$menuData = $db->getAllMenu();

// Group menu by kategori
$menuMakanan = array_filter($menuData, function($item) {
    return $item['kategori_id'] == 1;
});
$menuMinuman = array_filter($menuData, function($item) {
    return $item['kategori_id'] == 2;
});

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            switch ($_POST['action']) {
                case 'select_table':
                    $meja_id = $_POST['meja_id'];
                    $selectedTable = $db->processTableSelection($meja_id);
                    break;
                    
                case 'create_order':
                    $meja_id = $_POST['meja_id'];
                    $items = json_decode($_POST['items'], true);
                    $nomor_pelanggan = $_POST['nomor_pelanggan'] ?? null;
                    
                    $pesanan_id = $db->processOrderCreation($meja_id, $items, $nomor_pelanggan);
                    
                    // Redirect ke halaman konfirmasi dengan data pesanan
                    header("Location: order_confirmation.php?order_id=" . $pesanan_id . "&status=success");
                    exit();
            }
        } catch (Exception $e) {
            // Handle error - redirect with error status
            header("Location: order_confirmation.php?status=error&message=" . urlencode($e->getMessage()));
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Menu - Restoran Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border: none; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-5px); }
        .menu-card { height: 100%; }
        .menu-card .card-img-top { height: 200px; object-fit: cover; }
        .btn { border-radius: 10px; }
        .nav-tabs .nav-link { border-radius: 10px 10px 0 0; margin-right: 5px; }
        .nav-tabs .nav-link.active { background-color: #0d6efd; color: white; border-color: #0d6efd; }
        .cart-summary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .input-group .btn { border-radius: 8px; }
        .badge { font-size: 0.8rem; }
        
        /* Mobile button spacing */
        @media (max-width: 768px) {
            .gap-2 > * + * {
                margin-top: 0.5rem !important;
            }
            .btn-lg {
                width: 100%;
                margin-bottom: 0.25rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>
                Restoran Digital
            </a>
            <div class="d-flex">
                <span class="navbar-text me-3">
                    <i class="fas fa-chair me-1"></i>
                    <span id="selectedTable">Pilih Meja</span>
                </span>
                <a href="index.php" class="btn btn-outline-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>
                    Kembali
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Table Selection -->
        <div class="row mb-4" id="tableSelection">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chair me-2"></i>
                            Pilih Meja Anda
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <?php foreach ($mejaData as $meja): ?>
                            <div class="col-md-2 col-sm-3 col-4">
                                <div class="card h-100 table-card <?php echo $meja['status'] === 'tersedia' ? 'border-success' : 'border-danger'; ?>" 
                                     onclick="selectTable(<?php echo $meja['id']; ?>)" style="cursor: pointer;">
                                    <div class="card-body text-center">
                                        <i class="fas fa-table fa-2x mb-2 <?php echo $meja['status'] === 'tersedia' ? 'text-success' : 'text-danger'; ?>"></i>
                                        <h6>Meja <?php echo $meja['nomor']; ?></h6>
                                        <small class="text-muted"><?php echo $meja['kapasitas']; ?> orang</small>
                                        <br>
                                        <span class="badge <?php echo $meja['status'] === 'tersedia' ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $meja['status']; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Summary -->
        <div class="row mb-4" id="cartSummary" style="display: none;">
            <div class="col-12">
                <div class="card cart-summary">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 class="mb-0">
                                    <i class="fas fa-shopping-cart me-2"></i>
                                    Keranjang Pesanan
                                </h5>
                                <small id="cartItems">Belum ada pesanan</small>
                            </div>
                            <div class="col-md-4 text-end">
                                <h4 class="mb-0" id="cartTotal">Rp 0</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu Section -->
        <div id="menuSection" style="display: none;">
            <!-- Menu Tabs -->
            <ul class="nav nav-tabs" id="menuTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="makanan-tab" data-bs-toggle="tab" data-bs-target="#makanan" type="button">
                        <i class="fas fa-hamburger me-2"></i>
                        Makanan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="minuman-tab" data-bs-toggle="tab" data-bs-target="#minuman" type="button">
                        <i class="fas fa-coffee me-2"></i>
                        Minuman
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="menuTabsContent">
                <!-- Tab Makanan -->
                <div class="tab-pane fade show active" id="makanan" role="tabpanel">
                    <div class="row g-4 mt-2">
                        <?php foreach ($menuMakanan as $menu): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card h-100 menu-card">
                                <img src="<?php echo $menu['gambar'] ?: 'https://via.placeholder.com/300x200/ccc/fff?text=No+Image'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($menu['nama']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($menu['nama']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($menu['deskripsi'] ?: ''); ?></p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="h5 text-primary mb-0">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $menu['id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" id="qty-<?php echo $menu['id']; ?>" value="0" min="0" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $menu['id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Tab Minuman -->
                <div class="tab-pane fade" id="minuman" role="tabpanel">
                    <div class="row g-4 mt-2">
                        <?php foreach ($menuMinuman as $menu): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="card h-100 menu-card">
                                <img src="<?php echo $menu['gambar'] ?: 'https://via.placeholder.com/300x200/ccc/fff?text=No+Image'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($menu['nama']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($menu['nama']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($menu['deskripsi'] ?: ''); ?></p>
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <span class="h5 text-primary mb-0">Rp <?php echo number_format($menu['harga'], 0, ',', '.'); ?></span>
                                        </div>
                                        <div class="input-group">
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $menu['id']; ?>, -1)">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                            <input type="number" class="form-control text-center" id="qty-<?php echo $menu['id']; ?>" value="0" min="0" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="updateQuantity(<?php echo $menu['id']; ?>, 1)">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-2">
                        <button type="button" class="btn btn-secondary btn-lg" onclick="changeTable()">
                            <i class="fas fa-arrow-left me-2"></i>
                            Ganti Meja
                        </button>
                        <button type="button" class="btn btn-success btn-lg" id="orderBtn" onclick="processOrder()" disabled>
                            <i class="fab fa-whatsapp me-2"></i>
                            Pesan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Input Data Pelanggan -->
    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="customerModalLabel">
                        <i class="fab fa-whatsapp me-2"></i>
                        Nomor WhatsApp
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Informasi:</strong> Nomor WhatsApp ini akan digunakan untuk mengirim notifikasi update status pesanan.
                    </div>
                    <form id="customerForm">
                        <div class="mb-3">
                            <label for="nomorPelanggan" class="form-label">
                                <i class="fab fa-whatsapp me-1"></i>Nomor WhatsApp
                            </label>
                            <input type="tel" class="form-control" id="nomorPelanggan" placeholder="08xxxxxxxxxx" required>
                            <div class="form-text">Contoh: 081234567890 (tanpa +62)</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="button" class="btn btn-success" onclick="confirmOrder()">
                        <i class="fab fa-whatsapp me-1"></i>Pesan Sekarang
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/api.js"></script>
    <script>
        // Global variables
        let selectedTable = null;
        let cart = {};
        let menuData = {
            makanan: <?php echo json_encode(array_values($menuMakanan)); ?>,
            minuman: <?php echo json_encode(array_values($menuMinuman)); ?>
        };

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateCartDisplay();
        });

        // ==================== MEJA ====================
        function selectTable(mejaId) {
            // Get meja data from PHP
            const mejaCards = document.querySelectorAll('.table-card');
            let meja = null;
            
            mejaCards.forEach(card => {
                if (card.onclick.toString().includes(mejaId)) {
                    const status = card.classList.contains('border-success');
                    if (status) {
                        meja = {
                            id: mejaId,
                            nomor: card.querySelector('h6').textContent.replace('Meja ', ''),
                            kapasitas: card.querySelector('small').textContent.replace(' orang', ''),
                            status: 'tersedia'
                        };
                    }
                }
            });
            
            if (!meja) {
                showAlert('warning', 'Meja ini tidak tersedia');
                return;
            }
            
            selectedTable = meja;
            document.getElementById('selectedTable').textContent = `Meja ${meja.nomor}`;
            document.getElementById('tableSelection').style.display = 'none';
            document.getElementById('menuSection').style.display = 'block';
            document.getElementById('cartSummary').style.display = 'block';
            
            // Reset cart
            cart = {};
            updateCartDisplay();
        }

        function changeTable() {
            selectedTable = null;
            document.getElementById('selectedTable').textContent = 'Pilih Meja';
            document.getElementById('tableSelection').style.display = 'block';
            document.getElementById('menuSection').style.display = 'none';
            document.getElementById('cartSummary').style.display = 'none';
            
            // Reset cart
            cart = {};
        }

        // ==================== MENU ====================
        function updateQuantity(menuId, change) {
            const input = document.getElementById(`qty-${menuId}`);
            let currentQty = parseInt(input.value) || 0;
            let newQty = currentQty + change;
            
            if (newQty < 0) newQty = 0;
            
            input.value = newQty;
            
            if (newQty > 0) {
                cart[menuId] = newQty;
            } else {
                delete cart[menuId];
            }
            
            updateCartDisplay();
        }

        function updateCartDisplay() {
            let totalItems = 0;
            let totalPrice = 0;
            let itemsText = '';
            
            for (const [menuId, qty] of Object.entries(cart)) {
                totalItems += qty;
                
                // Find menu item
                let menuItem = null;
                for (const category of Object.values(menuData)) {
                    const found = category.find(item => item.id == menuId);
                    if (found) {
                        menuItem = found;
                        break;
                    }
                }
                
                if (menuItem) {
                    totalPrice += parseFloat(menuItem.harga) * qty;
                    if (itemsText) itemsText += ', ';
                    itemsText += `${menuItem.nama} (${qty})`;
                }
            }
            
            // Update cart display
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const orderBtn = document.getElementById('orderBtn');
            
            if (totalItems > 0) {
                cartItems.textContent = `${totalItems} item: ${itemsText}`;
                cartTotal.textContent = `Rp ${totalPrice.toLocaleString('id-ID')}`;
                orderBtn.disabled = false;
            } else {
                cartItems.textContent = 'Belum ada pesanan';
                cartTotal.textContent = 'Rp 0';
                orderBtn.disabled = true;
            }
        }

        // ==================== ORDER ====================
        function processOrder() {
            if (!selectedTable) {
                showAlert('error', 'Silakan pilih meja terlebih dahulu');
                return;
            }
            
            if (Object.keys(cart).length === 0) {
                showAlert('error', 'Silakan pilih menu terlebih dahulu');
                return;
            }
            
            // Show customer data modal
            const customerModal = new bootstrap.Modal(document.getElementById('customerModal'));
            customerModal.show();
        }
        
        async function confirmOrder() {
            const nomorPelanggan = document.getElementById('nomorPelanggan').value.trim();
            
            // Validate input
            if (!nomorPelanggan) {
                showAlert('error', 'Nomor WhatsApp harus diisi');
                return;
            }
            
            // Validate phone number format
            const phoneRegex = /^08[0-9]{8,11}$/;
            if (!phoneRegex.test(nomorPelanggan)) {
                showAlert('error', 'Format nomor WhatsApp tidak valid. Gunakan format: 08xxxxxxxxxx');
                return;
            }
            
            // Prepare order data
            const orderData = {
                meja_id: selectedTable.id,
                nomor_pelanggan: nomorPelanggan,
                items: []
            };
            
            for (const [menuId, qty] of Object.entries(cart)) {
                // Find menu item
                let menuItem = null;
                for (const category of Object.values(menuData)) {
                    const found = category.find(item => item.id == menuId);
                    if (found) {
                        menuItem = found;
                        break;
                    }
                }
                
                if (menuItem) {
                    orderData.items.push({
                        menu_id: menuId,
                        quantity: qty,
                        harga: parseFloat(menuItem.harga),
                        subtotal: parseFloat(menuItem.harga) * qty
                    });
                }
            }
            
            // Show loading
            const submitBtn = document.querySelector('#customerModal .btn-success');
            showLoading(submitBtn, true);
            
            try {
                const result = await api.createPesanan(orderData);
                
                showAlert('success', 'Pesanan berhasil dibuat!');
                bootstrap.Modal.getInstance(document.getElementById('customerModal')).hide();
                
                // Redirect to confirmation page
                window.location.href = `order_confirmation.php?order_id=${result.pesanan_id}&status=success`;
                
            } catch (error) {
                showAlert('error', 'Gagal membuat pesanan: ' + error.message);
            } finally {
                showLoading(submitBtn, false);
                submitBtn.innerHTML = '<i class="fab fa-whatsapp me-1"></i>Pesan Sekarang';
            }
        }

        // ==================== UTILITY ====================
        function showAlert(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'warning' ? 'alert-warning' : 'alert-danger';
            const icon = type === 'success' ? 'fa-check-circle' : 
                        type === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle';
            
            const alert = document.createElement('div');
            alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alert.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            alert.innerHTML = `
                <i class="fas ${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alert);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.parentNode.removeChild(alert);
                }
            }, 5000);
        }
    </script>
</body>
</html>