<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $title : 'Manajemen Klien' ?></title>
    <!-- Panggil CSS Tailwind -->
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/responsive.css'); ?>">
</head>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggle-sidebar');

        if(toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                // Cek status (terbuka/tertutup)
                const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                
                // Ubah atribut. Tailwind akan otomatis menghandle animasinya!
                sidebar.setAttribute('data-collapsed', !isCollapsed);

                // Simpan di Cookie agar saat pindah halaman tidak berkedip (reset)
                document.cookie = "sidebar_collapsed=" + (!isCollapsed) + ";path=/";
            });
        }
    });
</script>
<body class="bg-[#f8f9fa] font-sans text-gray-800 antialiased overflow-hidden">

    <div class="flex h-screen">

        <!-- 1. PANGGIL SIDEBAR -->
        <?php $this->load->view('layouts/sidebar'); ?>

        <!-- 2. AREA KONTEN UTAMA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-y-auto">

            <!-- Topbar Mobile -->
           <header class="shrink-0 sticky top-0 flex justify-between items-center p-4 bg-white shadow-sm md:hidden z-10">
    <div class="text-xl font-bold text-gray-800">Tridatu</div>
    <button id="mobile-menu-button" class="text-gray-500 hover:text-gray-700">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
    </button>
</header>

            <!-- 3. PANGGIL KONTEN DINAMIS -->
            <?php $this->load->view($content); ?>

        </div>
    </div>

    <script>
        const btn = document.getElementById('mobile-menu-button');
        const sidebar = document.getElementById('sidebar');
        if(btn && sidebar) {
            btn.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });
        }
    </script>
</body>
</html>