<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-5 lg:p-6">
    
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-3">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Selamat Datang, Admin</h2>
            <p class="text-sm text-gray-500 mt-1">Ringkasan data Tridatu Solution</p>
        </div>
        <div class="bg-white px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 flex items-center gap-2 shadow-sm">
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            <?= date('l, d F Y'); ?>
        </div>
    </div>

    <!-- Section 1: Data Utama -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-3">
            <div class="bg-green-50 text-green-500 p-1.5 rounded-md">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            </div>
            <h3 class="text-base font-bold text-gray-800">Data Utama</h3>
        </div>
        
        <!-- Grid 5 Kolom -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4">
            
            <!-- Card 1: Total Klien -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex justify-start mb-4">
                    <div class="w-10 h-10 bg-pink-50 text-pink-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_klien']) ?></h4>
                    <p class="text-xs text-gray-500 font-medium mt-1">Total Klien</p>
                </div>
            </div>
            
            <!-- Card 2: Total Project -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex justify-start mb-4">
                    <div class="w-10 h-10 bg-purple-50 text-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_project']) ?></h4>
                    <p class="text-xs text-gray-500 font-medium mt-1">Total Project</p>
                </div>
            </div>

            <!-- Card 3: Project Berjalan -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex justify-start mb-4">
                    <div class="w-10 h-10 bg-green-50 text-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['project_berjalan']) ?></h4>
                    <p class="text-xs text-gray-500 font-medium mt-1">Project Berjalan</p>
                </div>
            </div>

            <!-- Card 4: Total Dokumen -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex justify-start mb-4">
                    <div class="w-10 h-10 bg-orange-50 text-orange-400 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_dokumen']) ?></h4>
                    <p class="text-xs text-gray-500 font-medium mt-1">Total Dokumen</p>
                </div>
            </div>

            <!-- Card 5: Invoice (Placeholder) -->
            <div class="bg-white rounded-xl p-4 shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition-shadow">
                <div class="flex justify-start mb-4">
                    <div class="w-10 h-10 bg-blue-50 text-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 8h6m-5 4h4m-4 4h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['total_invoice']) ?></h4>
                    <p class="text-xs text-gray-500 font-medium mt-1"> Total Invoice</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 2: Perlu Perhatian -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 mb-6">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-9 h-9 bg-red-50 text-red-500 border border-red-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-base font-bold text-gray-800">Perlu perhatian</h3>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Warning Card 1 -->
            <a href="<?= site_url('dokumen') ?>" class="bg-[#fef2f2] border border-red-100 rounded-xl p-4 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-red-100 text-red-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <svg class="w-4 h-4 text-red-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['doc_akan_berakhir']) ?></h4>
                    <p class="text-xs text-red-500 font-medium mt-1">Dokumen akan berakhir</p>
                </div>
            </a>

            <!-- Warning Card 2 -->
            <a href="<?= site_url('dokumen') ?>" class="bg-[#fef2f2] border border-red-100 rounded-xl p-4 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-red-100 text-red-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </div>
                    <svg class="w-4 h-4 text-red-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['doc_expired']) ?></h4>
                    <p class="text-xs text-red-500 font-medium mt-1">Dokumen expired</p>
                </div>
            </a>

            <!-- Warning Card 3 (Placeholder) -->
            <a href="#" class="bg-[#f0fdf4] border border-green-100 rounded-xl p-4 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-green-100 text-green-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <svg class="w-4 h-4 text-green-500 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['invoice_belum_bayar']) ?></h4>
                    <p class="text-xs text-green-600 font-medium mt-1">Invoice Belum dibayar</p>
                </div>
            </a>

            <!-- Warning Card 4 (Placeholder) -->
            <a href="#" class="bg-[#fef2f2] border border-red-100 rounded-xl p-4 flex flex-col justify-between group cursor-pointer hover:shadow-md transition-shadow">
                <div class="flex justify-between items-start mb-4">
                    <div class="p-2 bg-red-100 text-red-500 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <svg class="w-4 h-4 text-red-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <div>
                    <h4 class="text-2xl font-bold text-gray-900"><?= number_format($stats['invoice_jatuh_tempo']) ?></h4>
                    <p class="text-xs text-red-500 font-medium mt-1">Invoice Jatuh Tempo</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Section 3: Aktivitas Terbaru -->
    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-9 h-9 bg-blue-50 text-blue-500 border border-blue-100 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4l3-9 5 18 3-9h4"></path></svg>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-base font-bold text-gray-800">Aktivitas terbaru</h3>
                <span class="text-xs text-gray-400 hidden md:inline">Pembaharuan dan aktivitas terbaru dari sistem.</span>
            </div>
        </div>

        <!-- List Aktivitas Dinamis -->
        <div class="divide-y divide-gray-100">
            <?php if(empty($activities)): ?>
                <div class="py-6 text-center text-gray-500 text-sm">Belum ada aktivitas terbaru.</div>
            <?php else: ?>
                <?php foreach($activities as $act): ?>
                    <?php 
                        // Tentukan warna dan ikon berdasarkan tipenya
                        $bg_color = 'bg-gray-50'; $text_color = 'text-gray-500'; $icon_svg = '';
                        if ($act->type == 'klien') {
                            $bg_color = 'bg-pink-50'; $text_color = 'text-pink-500';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>';
                        } elseif ($act->type == 'project') {
                            $bg_color = 'bg-purple-50'; $text_color = 'text-purple-500';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>';
                        } elseif ($act->type == 'dokumen') {
                            $bg_color = 'bg-orange-50'; $text_color = 'text-orange-500';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>';
                        } elseif ($act->type == 'invoice') {
                            $bg_color = 'bg-blue-50'; $text_color = 'text-blue-500';
                            $icon_svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 8h6m-5 4h4m-4 4h6M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>';
                        }

                        // PERBAIKAN: Pisahkan judul dan nama dari deskripsi (format: "Aksi: Nama")
                        $desc_parts = explode(': ', $act->description, 2);
                        $title_text = $desc_parts[0];
                        $entity_name = isset($desc_parts[1]) ? $desc_parts[1] : '';

                        // Hitung waktu relatif
                        $time_diff = time() - strtotime($act->created_at);
                        if ($time_diff < 60) $time_text = "Baru saja";
                        elseif ($time_diff < 3600) $time_text = floor($time_diff / 60) . " Menit lalu";
                        elseif ($time_diff < 86400) $time_text = floor($time_diff / 3600) . " Jam lalu";
                        else $time_text = date('d M Y', strtotime($act->created_at));
                    ?>
                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 <?= $bg_color ?> <?= $text_color ?> rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><?= $icon_svg ?></svg>
                            </div>
                            <div>
                                <!-- Judul Aksi (Contoh: Klien Baru ditambahkan) -->
                                <p class="text-sm font-bold text-gray-800"><?= htmlspecialchars($title_text) ?></p>
                                <!-- Nama Entitas (Contoh: PT. PNB / Sistem ERP) -->
                                <?php if(!empty($entity_name)): ?>
                                    <p class="text-xs text-gray-600 font-medium mt-0.5"><?= htmlspecialchars($entity_name) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0"><?= $time_text ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>