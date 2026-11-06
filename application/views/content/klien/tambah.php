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

        <!-- Kotak Form (Tanpa batasan max-height/scroll) -->
        <div class="bg-white rounded-2xl shadow-xl border border-white overflow-hidden flex flex-col md:flex-row">
            
            <!-- KIRI: Form Input -->
            <div class="w-full md:w-[55%] p-7 md:p-8 bg-white flex flex-col justify-center">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Klien</h3>
                
                <form action="<?= site_url('klien/simpan') ?>" method="POST">
                    <!-- Grid 2 Kolom (Jarak dirapatkan) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 mb-4">
                        
                        <!-- Nama Klien -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama Klien</label>
                            <input type="text" name="nama_klien" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="[Nama Klien]" required>
                        </div>
                        
                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>

                        <!-- Nama PIC -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama PIC</label>
                            <input type="text" name="nama_pic" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="[Nama PIC]" required>
                        </div>

                        <!-- Nomor HP -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Nomor HP</label>
                            <input type="text" name="no_hp" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="[No.HP]" required>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="[Email PIC]">
                    </div>

                    <!-- Alamat -->
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Alamat</label>
                        <!-- Textarea dibuat hanya 2 baris -->
                        <textarea name="alamat" rows="2" class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all resize-none" placeholder="[Alamat Klien]"></textarea>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <a href="<?= site_url('klien') ?>" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                        <button type="submit" class="px-5 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20">Simpan Klien</button>
                    </div>
                </form>
            </div>

            <!-- KANAN: Ilustrasi -->
            <div class="hidden md:flex w-[45%] bg-[#4A4545] items-center justify-center p-6 relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#9f2b2b]/20 rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 translate-y-1/2"></div>
                
                <img src="<?= base_url('assets/img/klien_form.png'); ?>" alt="Ilustrasi Tambah Klien" class="w-full max-h-[280px] object-contain drop-shadow-2xl relative z-10">
            </div>

        </div>
    </div>

</body>
</html>