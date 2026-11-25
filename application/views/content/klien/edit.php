<?php
/**
 * Form Edit Klien
 * + Smart Back Button — Kembali & Batal menuju halaman asal form dibuka
 *   (daftar klien / detail klien). $urlKembali jadi fallback.
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$from = isset($from) ? $from : 'list';

// Fallback bila asal tidak diketahui (mis. URL dibuka langsung)
$urlKembali = site_url($from === 'detail' ? 'klien/detail/' . (int) $klien->id : 'klien');

// Kunci tetap agar asal tidak hilang saat URL berubah (mis. setelah validasi gagal)
$backKey = 'klien-form-' . (int) $klien->id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-gradient-to-br from-[#f4f6f9] to-[#e6e9ef] font-sans text-gray-800 antialiased min-h-screen flex items-center justify-center p-4 md:p-6">

    <div class="w-full max-w-[950px]">

        <!-- Header -->
        <div class="mb-4 pl-2">
            <!-- SMART BACK BUTTON (href = fallback bila asal tidak diketahui) -->
            <a href="<?= $urlKembali ?>" id="backBtn" data-back-key="<?= $e($backKey) ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-1 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Klien</h2>
        </div>

        <!-- Kotak Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-white overflow-hidden flex flex-col md:flex-row">

            <!-- KIRI: Form Input -->
            <div class="w-full md:w-[55%] p-7 md:p-8 bg-white flex flex-col justify-center">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Klien</h3>

                <form action="<?= site_url('klien/update') ?>" method="POST" id="formKlien" onsubmit="disableButton(this)">
                    <?php if ($this->config->item('csrf_protection')): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <?php endif; ?>

                    <!-- Hidden ID -->
                    <input type="hidden" name="id" value="<?= (int) $klien->id ?>">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 mb-4">

                        <!-- Nama Klien -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama Klien</label>
                            <input type="text" name="nama_klien" value="<?= $e($klien->nama_klien) ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>

                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" value="<?= $e($klien->tanggal_mulai) ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>

                        <!-- Nama PIC -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama PIC</label>
                            <input type="text" name="nama_pic" value="<?= $e($klien->nama_pic) ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>

                        <!-- Nomor HP -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nomor HP</label>
                            <input type="text" name="no_hp" value="<?= $e($klien->no_hp) ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?= $e($klien->email ?? '') ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all">
                    </div>

                    <!-- Alamat -->
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Alamat</label>
                        <textarea name="alamat" rows="2" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all resize-none"><?= $e($klien->alamat ?? '') ?></textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <!-- Batal juga kembali ke halaman asal (data-smart-back) -->
                        <a href="<?= $urlKembali ?>" data-smart-back class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                        <button type="submit" id="btnSimpan" class="px-5 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">Simpan Perubahan</button>
                    </div>
                </form>
            </div>

            <!-- KANAN: Ilustrasi -->
            <div class="hidden md:flex w-[45%] bg-[#4A4545] items-center justify-center p-6 relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#9f2b2b]/20 rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 translate-y-1/2 pointer-events-none"></div>

                <img src="<?= base_url('assets/img/klien_form.png'); ?>" alt="Ilustrasi Edit Klien" class="w-full max-h-[280px] object-contain drop-shadow-2xl relative z-10">
            </div>

        </div>
    </div>

    <script>
    // --- DOUBLE SUBMIT PREVENTION ---
    function disableButton(form) {
        const btn = form.querySelector('#btnSimpan');
        if (!btn) return true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;
        btn.disabled = true;
        return true;
    }
    </script>

    <!-- SMART BACK BUTTON (script bersama) -->
    <?php $this->load->view('partials/smart_back'); ?>

</body>
</html>