<!-- Sidebar -->
<?php 
    // 1. BACA COOKIE (Agar PHP tahu status tanpa kedipan)
    $is_collapsed = ($this->input->cookie('sidebar_collapsed') === 'true') ? 'true' : 'false';
    $active_menu = $this->uri->segment(1); 
    if(empty($active_menu)) { $active_menu = 'dashboard'; }
?>

<!-- 2. TAMBAHKAN ATRIBUT data-collapsed DAN CLASS group -->
<aside id="sidebar" data-collapsed="<?= $is_collapsed ?>" class="group fixed inset-y-0 left-0 z-40 w-64 bg-[#4b4b4b] text-gray-300 transform -translate-x-full md:relative md:translate-x-0 transition-all duration-300 ease-in-out flex flex-col h-full shadow-xl flex-shrink-0 data-[collapsed=true]:w-20">
    
    <!-- Logo & Tombol Toggle -->
    <div class="relative flex items-center justify-between h-20 px-4 flex-shrink-0 overflow-hidden transition-all duration-300 group-data-[collapsed=true]:px-0">
        
        <!-- Wadah Logo -->
        <div class="flex items-center gap-3 whitespace-nowrap transition-all duration-300 group-data-[collapsed=true]:justify-center group-data-[collapsed=true]:w-full group-data-[collapsed=true]:gap-0">
            <img src="<?= base_url('assets/img/tridatu_logo.png') ?>" alt="Tridatu Logo" class="h-8 w-8 object-contain flex-shrink-0">
            
            <!-- Teks Logo (Menggunakan 'hidden' agar benar-benar hilang dari ruang) -->
            <div class="flex items-baseline transition-all duration-300 opacity-100 max-w-[200px] group-data-[collapsed=true]:hidden">
                <span class="text-lg font-bold text-white tracking-wide">Tridatu</span>
                <span class="text-lg font-light text-gray-200 ml-1">Solution</span>
            </div>
        </div>

        <!-- Tombol Toggle (Dibuat absolute agar tidak menggeser logo) -->
        <button id="toggle-sidebar" class="absolute right-4 hidden md:flex text-gray-400 hover:text-white focus:outline-none transition-all duration-300 group-data-[collapsed=true]:right-1 group-data-[collapsed=true]:rotate-180">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <rect width="18" height="18" x="3" y="3" rx="2" ry="2"></rect>
                <line x1="9" x2="9" y1="3" y2="21"></line>
                <path d="m16 15-3-3 3-3"></path>
            </svg>
        </button>
    </div>

    <div class="px-4 flex-shrink-0 group-data-[collapsed=true]:px-2">
        <hr class="border-t border-[#5c5c5c]">
    </div>

    <!-- Menu Navigasi -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto overflow-x-hidden">
        
        <?php 
            $menus = [
                ['url' => 'dashboard', 'name' => 'Dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                ['url' => 'klien', 'name' => 'Klien', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['url' => 'project', 'name' => 'Project', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['url' => 'dokumen', 'name' => 'Dokumen', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['url' => 'pembayaran', 'name' => 'Pembayaran', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['url' => 'pengaturan', 'name' => 'Pengaturan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z']
            ];
        ?>

        <?php foreach($menus as $menu): ?>
            <a href="<?= site_url($menu['url']) ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition-all duration-300 ease-in-out group/item <?= ($active_menu == $menu['url']) ? 'bg-[#9f5b5b] text-white shadow-md font-semibold' : 'text-gray-300 hover:bg-[#5a5a5a] hover:text-white' ?> group-data-[collapsed=true]:justify-center group-data-[collapsed=true]:px-0 group-data-[collapsed=true]:gap-0" title="<?= $menu['name'] ?>">
                
                <!-- Ikon: Membesar dan bergeser ke kanan saat hover -->
                <svg class="w-5 h-5 flex-shrink-0 <?= ($active_menu == $menu['url']) ? 'text-white' : 'text-gray-300 group-hover/item:text-white' ?> transform group-hover/item:scale-110 group-hover/item:translate-x-0.5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?= $menu['icon'] ?>"></path>
                    <?php if($menu['url'] == 'pengaturan'): ?>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <?php endif; ?>
                </svg>
                
                <!-- Teks: Ikut bergeser ke kanan saat hover -->
                <span class="text-sm whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[200px] transform group-hover/item:translate-x-1 group-data-[collapsed=true]:opacity-0 group-data-[collapsed=true]:max-w-0 group-data-[collapsed=true]:ml-0"><?= $menu['name'] ?></span>
            </a>
        <?php endforeach; ?>

    </nav>

           <!-- Logout Button -->
    <div class="p-4 mt-auto flex-shrink-0 overflow-hidden group-data-[collapsed=true]:px-2">
        <!-- HREF diubah mengarah ke auth/logout -->
        <a href="<?= site_url('auth/logout') ?>" class="group/logout flex items-center justify-center gap-2 px-3 py-2.5 bg-gradient-to-r from-[#9f5b5b] to-[#854848] hover:from-[#b06666] hover:to-[#9f5b5b] text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 text-sm font-semibold w-full group-data-[collapsed=true]:px-0 group-data-[collapsed=true]:gap-0" title="Keluar">
            
            <svg class="w-5 h-5 flex-shrink-0 transform group-hover/logout:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
            </svg>
            
            <span class="whitespace-nowrap overflow-hidden transition-all duration-300 opacity-100 max-w-[100px] group-data-[collapsed=true]:opacity-0 group-data-[collapsed=true]:max-w-0 group-data-[collapsed=true]:ml-0">Keluar</span>
        </a>
    </div>
</aside>