<?php
// Cookie dibaca untuk render awal (di-set oleh JS saat first visit)
$currentDeviceId = $_COOKIE['tridatu_device_id'] ?? '';
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6">
    
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Dokumen Kerja Sama</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data dokumen kerja sama.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="<?= site_url('dokumen/tambah') ?>" class="bg-gradient-to-r from-[#9f2b2b] to-[#b83a3a] hover:from-[#8a2525] hover:to-[#a63333] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Dokumen
            </a>
        </div>
    </div>

    <!-- Notifikasi Sukses / Error -->
    <?php if($this->session->flashdata('success')): ?>
        <div id="alert-success" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('success'); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-success').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Notifikasi Khusus Edit via AJAX (Lokal) -->
    <?php if($this->input->get('edit_success') == '1'): ?>
        <div id="alert-success-ajax" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm">Dokumen berhasil diperbarui!</span>
            </div>
            <button type="button" onclick="document.getElementById('alert-success-ajax').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div id="alert-error" class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Card Tabel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Search & Custom Dropdown Filter -->
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col xl:flex-row gap-3 items-center justify-between">
            
            <!-- Search Box -->
            <div class="relative w-full xl:w-72">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl block w-full pl-10 p-2.5 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all" placeholder="Cari Nama Dokumen...">
            </div>
            
            <!-- Filter Custom Dropdown (flex-nowrap, no scroll) -->
            <div class="flex flex-nowrap items-center gap-2 w-full xl:w-auto mt-2 xl:mt-0">
                
                <!-- Filter Klien -->
                <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-target="filterClient">
                    <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-2xl w-full sm:min-w-[130px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
                        <span class="dropdown-text truncate">Semua Klien</span>
                        <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu absolute z-50 hidden mt-2 w-full sm:min-w-[130px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                            <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Semua Klien</li>
                        </ul>
                    </div>
                    <input type="hidden" id="filterClient" value="">
                </div>

                <!-- Filter Jenis -->
                <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-target="filterJenis">
                    <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-2xl w-full sm:min-w-[130px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
                        <span class="dropdown-text truncate">Semua Jenis</span>
                        <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu absolute z-50 hidden mt-2 w-full sm:min-w-[130px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                            <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Semua Jenis</li>
                        </ul>
                    </div>
                    <input type="hidden" id="filterJenis" value="">
                </div>

                <!-- Filter Status -->
                <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-target="filterStatus">
                    <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-2xl w-full sm:min-w-[130px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
                        <span class="dropdown-text truncate">Semua Status</span>
                        <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu absolute z-50 hidden mt-2 w-full sm:min-w-[130px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                            <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Semua Status</li>
                        </ul>
                    </div>
                    <input type="hidden" id="filterStatus" value="">
                </div>

                <!-- Tombol Reset Filter -->
                <button type="button" id="btnResetFilter" class="hidden flex-shrink-0 px-3.5 py-2.5 bg-red-50 text-[#9f2b2b] hover:bg-red-100 border border-red-100 text-sm font-medium rounded-2xl transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Reset
                </button>

            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="dokumenTable">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Dokumen</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Klien</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Mulai</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Selesai</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if(empty($documents)): ?>
                        <tr id="empty-db-row">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-medium">Belum ada data dokumen.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($documents as $d): ?>
                        <?php
                            // Status berdasarkan tanggal berakhir
                            $today = new DateTime(date('Y-m-d'));
                            $end   = new DateTime($d->tanggal_berakhir);
                            $diff  = (int)$today->diff($end)->format('%r%a');
                            if ($diff < 0) {
                                $status_class = 'bg-red-50 text-red-600 border-red-100';
                                $status_text  = 'Expired';
                            } elseif ($diff <= 30) {
                                $status_class = 'bg-orange-50 text-orange-600 border-orange-100';
                                $status_text  = 'Akan Berakhir';
                            } else {
                                $status_class = 'bg-blue-50 text-blue-600 border-blue-100';
                                $status_text  = 'Aktif';
                            }

                            // Tentukan kondisi storage
                            $rowDeviceId  = $d->device_id ?? '';
                            $isCloud      = ($d->storage_type === 'cloud');
                            $isOwnLocal   = (!$isCloud && $rowDeviceId !== '' && $rowDeviceId === $currentDeviceId);
                            $isOtherLocal = (!$isCloud && !$isOwnLocal);
                        ?>
                        <tr class="data-row border-b border-gray-100 hover:bg-gray-50/60 transition-colors group"
                            data-client="<?= htmlspecialchars($d->nama_klien ?? '', ENT_QUOTES) ?>"
                            data-jenis="<?= htmlspecialchars($d->jenis_dokumen ?? '', ENT_QUOTES) ?>"
                            data-status="<?= $status_text ?>"
                            data-storage="<?= $isCloud ? 'cloud' : 'local' ?>"
                            data-device-id="<?= htmlspecialchars($rowDeviceId, ENT_QUOTES) ?>">

                            <!-- Nama Dokumen + ikon storage -->
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="storage-icon flex-shrink-0 <?= $isOtherLocal ? 'text-gray-400' : 'text-blue-500' ?>"
                                         title="<?= $isCloud ? 'Tersimpan di Server Sistem' : ($isOwnLocal ? 'Tersimpan di Perangkat Ini' : 'Tersimpan di Perangkat Lain') ?>">
                                        <?php if ($isCloud): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- LINK KE HALAMAN DETAIL -->
                                    <?php if ($isOtherLocal): ?>
                                        <span class="text-sm font-semibold text-gray-500 line-clamp-2 cursor-not-allowed" title="Terkunci di Perangkat Lain">
                                            <?= htmlspecialchars($d->nama_dokumen) ?>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?= site_url('dokumen/detail/'.$d->id) ?>" 
                                           class="text-sm font-semibold text-blue-600 underline underline-offset-2 decoration-blue-300 hover:text-blue-800 hover:decoration-blue-500 line-clamp-2"
                                           title="Lihat Detail Dokumen">
                                            <?= htmlspecialchars($d->nama_dokumen) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-sm text-gray-700 font-medium"><?= htmlspecialchars($d->nama_klien) ?></td>
                            <td class="px-5 py-4 text-sm text-gray-600"><?= $d->nama_project ? htmlspecialchars($d->nama_project) : '<span class="text-gray-400 italic">-</span>' ?></td>
                            <td class="px-5 py-4 text-sm text-gray-600 text-center whitespace-nowrap"><?= date('d M Y', strtotime($d->tanggal_mulai)) ?></td>
                            <td class="px-5 py-4 text-sm text-gray-600 text-center whitespace-nowrap"><?= date('d M Y', strtotime($d->tanggal_berakhir)) ?></td>

                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>"><?= $status_text ?></span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center items-center gap-1.5">
                                    <?php if ($isCloud): ?>
                                        <!-- Unduh Cloud (Hanya untuk Cloud) -->
                                        <a href="<?= site_url('dokumen/unduh/'.$d->id) ?>"
                                           class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Unduh Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                        </a>
                                    <?php elseif ($isOwnLocal): ?>
                                        <!-- HANYA Sync (Tombol Download Lokal Dihapus) -->
                                        <button type="button" data-sync-id="<?= (int)$d->id ?>"
                                                class="btn-sync p-1.5 bg-white border border-blue-300 text-blue-600 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-all shadow-sm" title="Unggah ke Server Sistem">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                        </button>
                                    <?php else: ?>
                                        <!-- Gembok (Perangkat Lain) -->
                                        <span class="btn-lock p-1.5 bg-white border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed" title="File tersimpan di Perangkat Lain">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Edit -->
                                    <a href="<?= site_url('dokumen/edit/' . (int)$d->id) ?>"
                                       class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>

                                    <!-- Hapus -->
                                    <button type="button" class="btn-hapus p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                            data-id="<?= (int)$d->id ?>"
                                            data-nama="<?= htmlspecialchars($d->nama_dokumen, ENT_QUOTES) ?>"
                                            title="Hapus Dokumen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer: Legend, Reminder & Pagination -->
        <div class="flex flex-col border-t border-gray-200 bg-gray-50/30 px-5 py-4 gap-4">
            
            <!-- BARIS 1: Legend & Reminder -->
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-600">
                <!-- Legend -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                    Server Sistem
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Perangkat Ini
                </div>
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Perangkat Lain
                </div>

                <!-- Garis Pemisah (Hanya Desktop) -->
                <div class="hidden sm:block h-4 w-px bg-gray-300"></div>

                <!-- Reminder -->
                <div class="inline-flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    Reminder email otomatis aktif
                </div>
            </div>

            <!-- BARIS 2: Info Data & Pagination (Sejajar) -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200/60 pt-3">
                <span id="pagination-info" class="text-sm text-gray-500">
                    Menampilkan 0-0 dari 0 Dokumen
                </span>
                <div id="pagination-controls" class="flex items-center gap-1.5"></div>
            </div>
        </div>
    </div>
</main>

<!-- MODAL KONFIRMASI HAPUS -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="tutupModalHapus()"></div>
    
    <div id="modalContent" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">
        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        
        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Dokumen?</h3>
        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus dokumen <br>
            <strong id="namaDokumenHapus" class="text-gray-900 text-base"></strong>?<br>
            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
        </p>
        
        <div class="flex justify-center gap-3">
            <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
            <button type="button" onclick="eksekusiHapus()" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- ============================================== -->
<!-- JAVASCRIPT LOGIC                               -->
<!-- ============================================== -->
<script>
    // ==========================================
    // 1. UTILITY: DEVICE ID, HASH & INDEXEDDB
    // ==========================================
    function getDeviceId() {
        let id = localStorage.getItem('tridatu_device_id');
        if (!id) {
            id = crypto.randomUUID();
            localStorage.setItem('tridatu_device_id', id);
            document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;
        }
        return id;
    }
    async function hitungHash(file) {
        const arrayBuffer = await file.arrayBuffer();
        const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
        return Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
    }
    function bukaDB() {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open('TridatuFileSystem', 1);
            req.onupgradeneeded = () => req.result.createObjectStore('handles');
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }
    async function ambilHandle(id) {
        const db = await bukaDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction('handles', 'readonly');
            const req = tx.objectStore('handles').get(String(id));
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
    }

    // ==========================================
    // 2. CUSTOM DROPDOWN LOGIC & RESET
    // ==========================================
    document.querySelectorAll('.custom-dropdown').forEach(dropdown => {
        const btn = dropdown.querySelector('.dropdown-btn');
        const menu = dropdown.querySelector('.dropdown-menu');
        const text = dropdown.querySelector('.dropdown-text');
        const input = dropdown.querySelector('input[type="hidden"]');
        const icon = btn.querySelector('svg');

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            document.querySelectorAll('.custom-dropdown').forEach(d => {
                if(d !== dropdown) {
                    d.classList.remove('open');
                    d.querySelector('.dropdown-menu').classList.add('hidden', 'opacity-0', 'scale-95');
                    d.querySelector('.dropdown-menu').classList.remove('opacity-100', 'scale-100');
                    d.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
                }
            });
            
            const isOpen = dropdown.classList.contains('open');
            if(isOpen) {
                dropdown.classList.remove('open');
                menu.classList.add('opacity-0', 'scale-95');
                menu.classList.remove('opacity-100', 'scale-100');
                icon.classList.remove('rotate-180');
                setTimeout(() => menu.classList.add('hidden'), 200);
            } else {
                dropdown.classList.add('open');
                menu.classList.remove('hidden');
                void menu.offsetWidth;
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
                icon.classList.add('rotate-180');
            }
        });

        dropdown.addEventListener('click', (e) => {
            if (e.target.tagName === 'LI') {
                const val = e.target.dataset.value;
                const label = e.target.textContent;
                
                input.value = val;
                text.textContent = label;
                
                dropdown.querySelectorAll('li').forEach(li => {
                    li.classList.remove('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
                    if (li.dataset.value === val) li.classList.add('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
                });
                
                dropdown.classList.remove('open');
                menu.classList.add('opacity-0', 'scale-95');
                menu.classList.remove('opacity-100', 'scale-100');
                icon.classList.remove('rotate-180');
                setTimeout(() => menu.classList.add('hidden'), 200);

                input.dispatchEvent(new Event('change'));
                checkResetButton();
            }
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown').forEach(d => {
            d.classList.remove('open');
            const menu = d.querySelector('.dropdown-menu');
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            d.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
            setTimeout(() => menu.classList.add('hidden'), 200);
        });
    });

    // Logika Tombol Reset
    function checkResetButton() {
        const c = document.getElementById('filterClient').value;
        const j = document.getElementById('filterJenis').value;
        const s = document.getElementById('filterStatus').value;
        const search = document.getElementById('searchInput').value;
        
        const btnReset = document.getElementById('btnResetFilter');
        if (c || j || s || search) {
            btnReset.classList.remove('hidden');
        } else {
            btnReset.classList.add('hidden');
        }
    }

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('filterClient').value = '';
        document.getElementById('filterJenis').value = '';
        document.getElementById('filterStatus').value = '';
        
        document.querySelector('.custom-dropdown[data-target="filterClient"] .dropdown-text').textContent = 'Semua Klien';
        document.querySelector('.custom-dropdown[data-target="filterJenis"] .dropdown-text').textContent = 'Semua Jenis';
        document.querySelector('.custom-dropdown[data-target="filterStatus"] .dropdown-text').textContent = 'Semua Status';
        
        document.querySelectorAll('.dropdown-list li').forEach(li => {
            li.classList.remove('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
        });

        document.getElementById('btnResetFilter').classList.add('hidden');
        applyFilters();
    });

    // ==========================================
    // 3. LOGIKA PAGINATION, FILTER & SEARCH
    // ==========================================
    const rowsPerPage = 5; 
    let currentPage = 1;
    let allRows = [];
    let filteredRows = [];

    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('tableBody');
        allRows = Array.from(tableBody.querySelectorAll('.data-row'));
        filteredRows = [...allRows];

        const uniqueClients = new Set();
        const uniqueJenis = new Set();
        const uniqueStatus = new Set();

        // TAMBAHAN: Ambil data dari master Jenis Dokumen (Pengaturan)
        const masterJenis = <?= json_encode(array_column($jenis_dokumen ?? [], 'name')) ?>;
        masterJenis.forEach(val => {
            if(val) uniqueJenis.add(val);
        });

        allRows.forEach(row => {
            if(row.dataset.client) uniqueClients.add(row.dataset.client);
            if(row.dataset.jenis) uniqueJenis.add(row.dataset.jenis);
            if(row.dataset.status) uniqueStatus.add(row.dataset.status);
        });

        function populateDropdown(targetId, uniqueSet) {
            const dropdown = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);
            const ul = dropdown.querySelector('.dropdown-list');
            uniqueSet.forEach(val => {
                ul.innerHTML += `<li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors truncate" title="${val}" data-value="${val}">${val}</li>`;
            });
        }

        populateDropdown('filterClient', uniqueClients);
        populateDropdown('filterJenis', uniqueJenis);
        populateDropdown('filterStatus', uniqueStatus);

        updateTablePagination();

        document.getElementById('searchInput').addEventListener('keyup', () => { applyFilters(); checkResetButton(); });
        document.getElementById('filterClient').addEventListener('change', applyFilters);
        document.getElementById('filterJenis').addEventListener('change', applyFilters);
        document.getElementById('filterStatus').addEventListener('change', applyFilters);

        setTimeout(() => {
            const successAlert = document.getElementById('alert-success');
            const errorAlert = document.getElementById('alert-error');
            if (successAlert) { successAlert.style.opacity = '0'; setTimeout(() => successAlert.remove(), 300); }
            if (errorAlert) { errorAlert.style.opacity = '0'; setTimeout(() => errorAlert.remove(), 300); }
        }, 4000);
    });

    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const clientTerm = document.getElementById('filterClient').value;
        const jenisTerm = document.getElementById('filterJenis').value;
        const statusTerm = document.getElementById('filterStatus').value;

        filteredRows = allRows.filter(row => {
            const textName = row.querySelector('td:nth-child(1)').innerText.toLowerCase();
            const matchSearch = textName.includes(searchTerm);
            const matchClient = clientTerm === "" || row.dataset.client === clientTerm;
            const matchJenis = jenisTerm === "" || row.dataset.jenis === jenisTerm;
            const matchStatus = statusTerm === "" || row.dataset.status === statusTerm;

            return matchSearch && matchClient && matchJenis && matchStatus;
        });

        currentPage = 1;
        updateTablePagination();
    }

    function updateTablePagination() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const tableBody = document.getElementById('tableBody');

        const emptyRow = document.getElementById('search-empty-row');
        if (emptyRow) emptyRow.remove();

        if (totalRows === 0 && allRows.length > 0) {
            const tr = document.createElement('tr');
            tr.id = 'search-empty-row';
            tr.innerHTML = `<td colspan="7" class="px-6 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-sm font-medium">Data tidak ditemukan.</p>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci atau filter lain.</p>
            </td>`;
            tableBody.appendChild(tr);
        }

        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        allRows.forEach(row => row.style.display = 'none');

        if (totalRows > 0) {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalRows);

            for (let i = startIndex; i < endIndex; i++) {
                filteredRows[i].style.display = '';
            }

            document.getElementById('pagination-info').innerText = `Menampilkan ${startIndex + 1}–${endIndex} dari ${totalRows} Dokumen`;
        } else {
            document.getElementById('pagination-info').innerText = `Menampilkan 0 dari 0 Dokumen`;
        }

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('pagination-controls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = `flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors`;
        prevBtn.disabled = currentPage === 1;
        prevBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>`;
        prevBtn.onclick = () => { currentPage--; updateTablePagination(); };
        container.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            if (i === currentPage) {
                btn.className = `flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-[#9f2b2b] bg-[#9f2b2b] text-white text-sm font-medium shadow-sm`;
            } else {
                btn.className = `flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors`;
            }
            btn.innerText = i;
            btn.onclick = () => { currentPage = i; updateTablePagination(); };
            container.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = `flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors`;
        nextBtn.disabled = currentPage === totalPages;
        nextBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>`;
        nextBtn.onclick = () => { currentPage++; updateTablePagination(); };
        container.appendChild(nextBtn);
    }

    // ==========================================
    // 4. LOGIKA SINKRONISASI KE SERVER SISTEM
    // ==========================================
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.syncId;
            const handle = await ambilHandle(id);
            
            if (!handle) {
                return alert('File fisik tidak ditemukan di Perangkat Ini. Mungkin sudah dihapus atau Anda membuka dari perangkat lain.');
            }

            const permission = await handle.requestPermission({ mode: 'read' });
            if (permission !== 'granted') {
                return alert('Izin akses file ditolak.');
            }

            const file = await handle.getFile();
            const formData = new FormData();
            formData.append('id', id);
            formData.append('device_id', getDeviceId());
            formData.append('file_hash', await hitungHash(file));
            formData.append('file', file, file.name);

            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
            btn.disabled = true;

            try {
                const response = await fetch('<?= site_url('dokumen/sync_to_cloud') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const res = await response.json();

                if (res.status) {
                    alert('Berhasil diunggah ke Server Sistem!');
                    window.location.reload();
                } else {
                    alert(res.msg);
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
                    btn.disabled = false;
                }
            } catch (err) {
                alert('Gagal mengunggah ke Server Sistem.');
                btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>';
                btn.disabled = false;
            }
        });
    });

    // ==========================================
    // 5. LOGIKA HAPUS (MODAL)
    // ==========================================
    let hapusId = null;
    const modal = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function() {
            hapusId = this.dataset.id;
            document.getElementById('namaDokumenHapus').innerText = this.dataset.nama;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        });
    });

    function tutupModalHapus() {
        hapusId = null;
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function eksekusiHapus() {
        if (hapusId) {
            window.location.href = '<?= site_url('dokumen/hapus/') ?>' + hapusId;
        }
    }
</script>