<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restoran Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .hero-section { padding: 100px 0; color: white; text-align: center; }
        .hero-title { font-size: 3.5rem; font-weight: bold; margin-bottom: 1rem; }
        .hero-subtitle { font-size: 1.2rem; margin-bottom: 2rem; opacity: 0.9; }
        .card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); transition: transform 0.3s ease; }
        .card:hover { transform: translateY(-10px); }
        .btn-custom { border-radius: 50px; padding: 15px 30px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }
        .feature-icon { font-size: 3rem; margin-bottom: 1rem; }
        .navbar { background: rgba(255,255,255,0.1) !important; backdrop-filter: blur(10px); }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .text-white-75 { color: rgba(255,255,255,0.75) !important; }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>
                Restoran Digital
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#features">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">
                            <i class="fas fa-sign-in-alt me-1"></i>Admin Login
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="hero-title">
                        <i class="fas fa-utensils me-3"></i>
                        Selamat Datang di Restoran Digital
                    </h1>
                    <p class="hero-subtitle">
                        Nikmati pengalaman memesan yang mudah dan cepat dengan sistem digital kami
                    </p>
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="menu.php" class="btn btn-light btn-custom">
                            <i class="fas fa-shopping-cart me-2"></i>
                            Mulai Pesan
                        </a>
                        <a href="admin.php" class="btn btn-outline-light btn-custom">
                            <i class="fas fa-cogs me-2"></i>
                            Admin Panel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="text-white mb-3">Fitur Unggulan</h2>
                    <p class="text-white-50">Sistem restoran digital yang lengkap dan mudah digunakan</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-chair feature-icon text-primary"></i>
                            <h5 class="card-title">Pemilihan Meja</h5>
                            <p class="card-text">Pilih meja yang tersedia dengan mudah dan cepat. Sistem real-time menunjukkan status meja.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fas fa-utensils feature-icon text-success"></i>
                            <h5 class="card-title">Menu Digital</h5>
                            <p class="card-text">Lihat menu makanan dan minuman dengan gambar menarik. Harga dan deskripsi lengkap.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center p-4">
                        <div class="card-body">
                            <i class="fab fa-whatsapp feature-icon text-success"></i>
                            <h5 class="card-title">Pesan via WhatsApp</h5>
                            <p class="card-text">Kirim pesanan langsung ke kasir melalui WhatsApp dengan detail lengkap.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="py-5" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(10px);">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center mb-5">
                    <h2 class="text-white mb-3 fw-bold">Cara Kerja</h2>
                    <p class="text-white fs-5">Hanya 3 langkah mudah untuk memesan</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center text-white">
                        <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-lg" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold text-white">1</span>
                        </div>
                        <h5 class="fw-bold mb-3">Pilih Meja</h5>
                        <p class="text-white-75">Pilih meja yang tersedia sesuai dengan jumlah orang</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center text-white">
                        <div class="bg-success rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-lg" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold text-white">2</span>
                        </div>
                        <h5 class="fw-bold mb-3">Pilih Menu</h5>
                        <p class="text-white-75">Pilih makanan dan minuman yang diinginkan</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center text-white">
                        <div class="bg-warning rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-lg" style="width: 80px; height: 80px;">
                            <span class="fs-2 fw-bold text-white">3</span>
                        </div>
                        <h5 class="fw-bold mb-3">Kirim Pesanan</h5>
                        <p class="text-white-75">Kirim pesanan ke WhatsApp kasir untuk konfirmasi</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center">
                    <h2 class="text-white mb-4">Tentang Sistem Kami</h2>
                    <p class="text-white-50 mb-4">
                        Sistem restoran digital yang dirancang untuk memberikan pengalaman memesan yang mudah, cepat, dan efisien. 
                        Dengan teknologi modern, kami memudahkan pelanggan untuk memesan makanan favorit mereka.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-white">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Interface yang user-friendly</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-white">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Real-time status meja</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-white">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Menu dengan gambar menarik</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center text-white">
                                <i class="fas fa-check-circle text-success me-3"></i>
                                <span>Integrasi WhatsApp</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="text-white-50 mb-0">
                        &copy; 2024 Restoran Digital. All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <a href="admin.php" class="text-white-50 text-decoration-none me-3">
                        <i class="fas fa-cogs me-1"></i>Admin
                    </a>
                    <a href="menu.php" class="text-white-50 text-decoration-none">
                        <i class="fas fa-utensils me-1"></i>Menu
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navbar
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if (window.scrollY > 50) {
                navbar.style.background = 'rgba(0,0,0,0.9)';
            } else {
                navbar.style.background = 'rgba(255,255,255,0.1)';
            }
        });

        // Add animation to cards on scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe all cards
        document.querySelectorAll('.card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>