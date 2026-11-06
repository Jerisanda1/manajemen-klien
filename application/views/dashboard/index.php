<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Tridatu Solution</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #4A4545;
            color: white;
        }
        .sidebar a {
            color: #ddd;
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin-bottom: 5px;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #d9534f;
            color: white;
        }
        .card-stat {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3 d-none d-md-block">
                <div class="d-flex align-items-center mb-4 mt-2">
                    <i class="fa-solid fa-shapes text-danger fs-4 me-2"></i>
                    <h5 class="fw-bold m-0 text-white">Tridatu</h5>
                </div>
                <ul class="list-unstyled">
                    <li><a href="#" class="active"><i class="fa-solid fa-house me-2"></i> Dashboard</a></li>
                    <li><a href="#"><i class="fa-solid fa-users me-2"></i> Data Klien</a></li>
                    <li><a href="#"><i class="fa-solid fa-gear me-2"></i> Pengaturan</a></li>
                    <hr>
                    <li><a href="<?= site_url('dashboard/logout'); ?>" class="text-danger"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4">
                <!-- Topbar sederhana -->
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <h4 class="fw-bold text-dark">Dashboard Utama</h4>
                    <span class="text-muted"><i class="fa-solid fa-user-circle me-1"></i> Administrator</span>
                </div>

                <!-- Konten Statistik / Kartu Sambutan -->
                <div class="row">
                    <div class="col-md-12 mb-4">
                        <div class="card p-4 card-stat bg-white">
                            <h3 class="fw-bold text-dark">Halo, Selamat Datang!</h3>
                            <p class="text-muted mb-0">Anda berhasil masuk ke sistem manajemen klien **Tridatu Solution**. Gunakan menu di samping untuk mulai mengelola data.</p>
                        </div>
                    </div>
                </div>

                <!-- Contoh Kotak Statistik Kecil -->
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <div class="card card-stat p-3 bg-white border-start border-danger border-4">
                            <h6 class="text-muted">Total Klien</h6>
                            <h3 class="fw-bold text-dark">24</h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card card-stat p-3 bg-white border-start border-primary border-4">
                            <h6 class="text-muted">Proyek Aktif</h6>
                            <h3 class="fw-bold text-dark">12</h3>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="card card-stat p-3 bg-white border-start border-success border-4">
                            <h6 class="text-muted">Laporan Selesai</h6>
                            <h3 class="fw-bold text-dark">48</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>