<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?= isset($settings->nama_perusahaan) && $settings->nama_perusahaan ? htmlspecialchars($settings->nama_perusahaan) : 'Tridatu Solution' ?></title>
    
    <!-- Script Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    
    <!-- Load Tailwind CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        
        /* Kustomisasi Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="bg-gradient-to-br from-[#6b6b6b] to-[#4a4a4a] min-h-screen flex items-center justify-center p-4 md:p-6 antialiased overflow-hidden">

    <!-- Card Utama -->
    <div class="bg-white rounded-2xl shadow-[0_15px_40px_rgba(0,0,0,0.25)] overflow-hidden flex flex-col md:flex-row w-full max-w-[850px] min-h-[500px] animate-fade-in">
        
        <!-- Bagian Kiri: Form Login -->
        <div class="w-full md:w-[55%] bg-[#f0f4f8] p-8 md:p-10 flex flex-col justify-center relative z-10">
            
            <!-- Brand / Logo Dinamis -->
            <div class="flex items-center gap-2 text-lg font-extrabold text-gray-900 mb-8">
                <?php 
                    $s = isset($settings) ? $settings : (object)[]; 
                    $hasLogo = !empty($s->logo) && file_exists(FCPATH . $s->logo);
                    $namaPT  = !empty($s->nama_perusahaan) ? htmlspecialchars($s->nama_perusahaan) : 'Tridatu Solution';
                ?>

                <?php if ($hasLogo): ?>
                    <img src="<?= base_url($s->logo) ?>" alt="Logo" class="h-8 w-auto object-contain max-w-[130px] hover:scale-105 transition-transform duration-300">
                <?php else: ?>
                    <div class="text-[#d9534f] transform hover:rotate-12 transition-transform duration-300">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 22h20L12 2zm0 4.5l6.5 13h-13L12 6.5z"/></svg>
                    </div>
                <?php endif; ?>

                <span class="truncate max-w-[180px] tracking-tight" title="<?= $namaPT ?>">
                    <?= $namaPT ?>
                </span>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-1.5 leading-tight">Halo!</h1>
            <p class="text-[14px] text-gray-600 mb-6">Masuk untuk mengelola data klien anda</p>

            <!-- Notifikasi Error -->
            <?php 
                $CI =& get_instance();
                if($CI->session->flashdata('error')): 
            ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-3 py-2 rounded-lg mb-5 text-xs flex items-center gap-2 animate-pulse">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span><?= $CI->session->flashdata('error'); ?></span>
                </div>
            <?php endif; ?>

            <?= form_open('auth/proses_login', ['id' => 'loginForm']); ?>
                
                <!-- Input Username -->
                <div class="relative mb-4 group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-[#d9534f] transition-transform duration-300 group-focus-within:scale-110">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <input type="text" name="username" id="username" 
                           class="w-full pl-12 pr-5 py-3 bg-white border border-[#f3d4d4] rounded-full shadow-[0_2px_10px_rgba(217,83,79,0.06)] text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#d9534f] focus:ring-2 focus:ring-[#d9534f]/20 transition-all duration-300 hover:shadow-[0_4px_15px_rgba(217,83,79,0.12)]" 
                           placeholder="Username" required autocomplete="off">
                </div>

                <!-- Input Password -->
                <div class="relative mb-6 group">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-5 pointer-events-none text-[#d9534f] transition-transform duration-300 group-focus-within:scale-110">
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    </div>
                    <input type="password" name="password" id="password" 
                           class="w-full pl-12 pr-5 py-3 bg-white border border-[#f3d4d4] rounded-full shadow-[0_2px_10px_rgba(217,83,79,0.06)] text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-[#d9534f] focus:ring-2 focus:ring-[#d9534f]/20 transition-all duration-300 hover:shadow-[0_4px_15px_rgba(217,83,79,0.12)]" 
                           placeholder="Password" required>
                </div>

                <!-- Widget Cloudflare Turnstile -->
                <div class="mb-6 flex justify-start pl-1">
                    <div class="cf-turnstile" data-sitekey="0x4AAAAAAF2cRL6tM2MWqf6C"></div>
                </div>

                <!-- Tombol Masuk -->
                <button type="submit" class="w-full py-3 bg-[#e55353] hover:bg-[#d14242] text-white text-[15px] font-bold rounded-full shadow-[0_4px_15px_rgba(229,83,83,0.3)] transition-all duration-300 hover:-translate-y-0.5 hover:shadow-[0_6px_20px_rgba(229,83,83,0.4)] focus:outline-none focus:ring-2 focus:ring-[#e55353]/50 flex justify-center items-center gap-2 group">
                    Masuk
                    <svg class="w-4 h-4 opacity-0 -ml-3 group-hover:opacity-100 group-hover:ml-0 transition-all duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </button>
            <?= form_close(); ?>
        </div>

        <!-- Bagian Kanan: Ilustrasi -->
        <div class="hidden md:flex w-[45%] bg-[#4b4647] items-center justify-center p-6 relative overflow-hidden group">
            <!-- Ornamen lingkaran estetik di background -->
            <div class="absolute -top-12 -right-12 w-48 h-48 bg-white/5 rounded-full mix-blend-overlay filter blur-2xl group-hover:scale-110 transition-transform duration-1000"></div>
            <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-red-400/10 rounded-full mix-blend-overlay filter blur-2xl group-hover:scale-110 transition-transform duration-1000"></div>
            
            <img src="<?= base_url('assets/img/Gemini.png'); ?>" alt="Ilustrasi Login" class="relative z-10 w-full max-w-[280px] object-contain drop-shadow-2xl animate-float">
        </div>
        
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-8px); }
            100% { transform: translateY(0px); }
        }
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
    </style>
</body>
</html>