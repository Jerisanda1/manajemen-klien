<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tridatu Solution</title>
    <!-- Script Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #555555;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            width: 900px;
            max-width: 100%;
        }
        .left-side {
            padding: 40px 50px;
            background: #f4f6f9;
        }
        .right-side {
            background: #4A4545;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            color: #ffffff;
            text-align: center;
        }
        .brand-title {
            font-weight: bold;
            font-size: 1.2rem;
            color: #000;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 15px 12px 45px;
            background-color: #ffffff;
            border: 1px solid #ddd;
        }
        .input-group {
            position: relative;
        }
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #dc3545;
            z-index: 10;
        }
        .btn-custom {
            background-color: #d9534f;
            border: none;
            border-radius: 8px;
            padding: 12px;
            color: white;
            font-weight: bold;
            width: 100%;
        }
        .btn-custom:hover {
            background-color: #c9302c;
            color: white;
        }
        /* Responsif untuk HP / layar kecil */
        @media (max-width: 768px) {
            .right-side {
                display: none; /* Menyembunyikan panel kanan di layar kecil */
            }
            .left-side {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>

    <div class="login-card row g-0">
        <!-- Bagian Kiri: Form Login -->
        <div class="col-md-6 left-side">
            <div class="d-flex align-items-center mb-4">
                <div class="me-2 text-danger fs-4">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <span class="brand-title">Tridatu Solution</span>
            </div>

            <h3 class="fw-bold mb-1">Halo!</h3>
            <p class="text-muted mb-4">Masuk Untuk mengelola data klien anda</p>

            <?php 
                $CI =& get_instance();
                if($CI->session->flashdata('error')): 
            ?>
                <div class="alert alert-danger py-2"><?= $CI->session->flashdata('error'); ?></div>
            <?php endif; ?>

            <?= form_open('auth/proses_login'); ?>
                <div class="mb-3 input-group">
                    <span class="input-icon"><i class="fa-solid fa-user"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Username" required autocomplete="off">
                </div>

                <div class="mb-3 input-group">
                    <span class="input-icon"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>

                <!-- Widget Cloudflare Turnstile -->
                <div class="mb-3">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAAF2cRL6tM2MWqf6C"></div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember">
                    <label class="form-check-label text-muted small" for="remember">Remember Me</label>
                </div>

                <button type="submit" class="btn btn-custom shadow-sm">Masuk</button>
            <?= form_close(); ?>
        </div>

        <!-- Bagian Kanan: Ilustrasi Lokal -->
        <div class="col-md-6 right-side">
            <img src="<?= base_url('assets/img/Gemini.png'); ?>" alt="Ilustrasi Login" class="img-fluid" style="max-height: 320px;">
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>