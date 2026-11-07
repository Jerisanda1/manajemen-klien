<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-gradient-to-br from-[#f4f6f9] to-[#e6e9ef] font-sans text-gray-800 antialiased min-h-screen flex items-center justify-center p-4 md:p-6">

    <!-- Container Utama -->
    <div class="w-full max-w-[950px]">
        
        <!-- Header (Di luar kotak) -->
        <div class="mb-4 pl-2">
            <a href="<?= site_url('klien') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-1 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Klien</h2>
        </div>

        <!-- Kotak Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-white overflow-hidden flex flex-col md:flex-row">
            
            <!-- KIRI: Form Input -->
            <div class="w-full md:w-[55%] p-7 md:p-8 bg-white flex flex-col justify-center relative z-10">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Klien</h3>
                
                <!-- Notifikasi Error Validasi -->
                <?php if(validation_errors()): ?>
                    <div class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm border border-red-200">
                        <div class="flex items-center gap-2 mb-1 font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Mohon periksa kembali isian Anda:
                        </div>
                        <?= validation_errors(); ?>
                    </div>
                <?php endif; ?>

                <!-- Form dengan Onsubmit untuk mencegah double klik -->
                <form action="<?= site_url('klien/simpan') ?>" method="POST" id="formTambahKlien" onsubmit="disableButton(this)">
                    <!-- Grid 2 Kolom -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 mb-4">
                        
                        <!-- Nama Klien -->
                        <div>
                            <label for="nama_klien" class="block text-xs font-medium text-gray-700 mb-1.5">
                                Nama Klien <span class="text-red-500">*</span>
                            </label>
                            <!-- Tambahkan set_value() agar data tidak hilang saat error -->
                            <input type="text" id="nama_klien" name="nama_klien" value="<?= set_value('nama_klien') ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="Contoh: PT Nusa Digital" required>
                        </div>
                        
                        <!-- Tanggal Mulai -->
                        <div>
                            <label for="tanggal_mulai" class="block text-xs font-medium text-gray-700 mb-1.5">
                                Tanggal Mulai <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= set_value('tanggal_mulai') ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>

                        <!-- Nama PIC -->
                        <div>
                            <label for="nama_pic" class="block text-xs font-medium text-gray-700 mb-1.5">
                                Nama PIC <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="nama_pic" name="nama_pic" value="<?= set_value('nama_pic') ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="Contoh: Budi Santoso" required>
                        </div>

                        <!-- Nomor HP -->
                        <div>
                            <label for="no_hp" class="block text-xs font-medium text-gray-700 mb-1.5">
                                Nomor HP <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="no_hp" name="no_hp" value="<?= set_value('no_hp') ?>" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="0812xxxxxx" required>
                            <p class="text-[10px] text-gray-400 mt-1">Gunakan format angka, tanpa spasi.</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-xs font-medium text-gray-700 mb-1.5">Email (Opsional)</label>
                        <input type="email" id="email" name="email" value="<?= set_value('email') ?>" autocomplete="email" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="email@perusahaan.com">
                    </div>

                    <!-- Alamat -->
                    <div class="mb-5">
                        <label for="alamat" class="block text-xs font-medium text-gray-700 mb-1.5">Alamat (Opsional)</label>
                        <textarea id="alamat" name="alamat" rows="2" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all resize-none" placeholder="Alamat kantor..."><?= set_value('alamat') ?></textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <a href="<?= site_url('klien') ?>" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                        
                        <!-- Tombol Simpan dengan ID untuk JS -->
                        <button type="submit" id="btnSimpan" class="px-5 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            Simpan Klien
                        </button>
                    </div>
                </form>
            </div>

            <!-- KANAN: Ilustrasi -->
            <div class="hidden md:flex w-[45%] bg-[#4A4545] items-center justify-center p-6 relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#9f2b2b]/20 rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 translate-y-1/2 pointer-events-none"></div>
                
                <img src="<?= base_url('assets/img/klien_form.png'); ?>" alt="Ilustrasi Tambah Klien" class="w-full max-h-[280px] object-contain drop-shadow-2xl relative z-10">
            </div>

        </div>
    </div>

    <!-- JavaScript untuk Mencegah Double Submit -->
    <script>
        function disableButton(form) {
            const btn = form.querySelector('#btnSimpan');
            // Ubah teks tombol menjadi loading
            btn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Menyimpan...
            `;
            // Nonaktifkan tombol agar tidak bisa diklik dua kali
            btn.disabled = true;
            return true; // Lanjutkan proses submit form
        }
    </script>
</body>
</html>