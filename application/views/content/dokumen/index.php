<?php
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

    <!-- Notifikasi -->
    <?php if($this->session->flashdata('success')): ?>
        <div id="alert-success" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('success'); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-success').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if($this->session->flashdata('error')): ?>
        <div id="alert-error" class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Card Tabel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col xl:flex-row gap-3 items-center justify-between">
            <div class="relative w-full xl:w-72">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-2xl block w-full pl-10 p-2.5 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all" placeholder="Cari Nama Dokumen...">
            </div>
            
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

                <button type="button" id="btnResetFilter" class="hidden flex-shrink-0 px-3.5 py-2.5 bg-red-50 text-[#9f2b2b] hover:bg-red-100 border border-red-100 text-sm font-medium rounded-2xl transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Reset
                </button>
            </div>
        </div>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
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
                        <tr>
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
                            $diff = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($d->tanggal_berakhir))->format('%r%a');
                            if ($diff < 0)      { $status_class = 'bg-red-50 text-red-600 border-red-100';       $status_text = 'Expired'; }
                            elseif ($diff <= 30){ $status_class = 'bg-orange-50 text-orange-600 border-orange-100'; $status_text = 'Akan Berakhir'; }
                            else                { $status_class = 'bg-blue-50 text-blue-600 border-blue-100';    $status_text = 'Aktif'; }

                            $rowDeviceId  = $d->device_id ?? '';
                            $isCloud      = ($d->storage_type === 'cloud');
                            $isOwnLocal   = (!$isCloud && $rowDeviceId !== '' && $rowDeviceId === $currentDeviceId);
                            $isOtherLocal = (!$isCloud && !$isOwnLocal);
                        ?>
                        <tr class="data-row border-b border-gray-100 hover:bg-gray-50/60 transition-colors group"
                            data-client="<?= htmlspecialchars($d->nama_klien ?? '', ENT_QUOTES) ?>"
                            data-jenis="<?= htmlspecialchars($d->jenis_dokumen ?? '', ENT_QUOTES) ?>"
                            data-status="<?= $status_text ?>">

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex-shrink-0 <?= $isOtherLocal ? 'text-gray-400' : 'text-blue-500' ?>"
                                         title="<?= $isCloud ? 'Tersimpan di Server Sistem' : ($isOwnLocal ? 'Tersimpan di Perangkat Ini' : 'Tersimpan di Perangkat Lain') ?>">
                                        <?php if ($isCloud): ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                        <?php else: ?>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($isOtherLocal): ?>
                                        <span class="text-sm font-semibold text-gray-500 line-clamp-2 cursor-not-allowed" title="Terkunci di Perangkat Lain"><?= htmlspecialchars($d->nama_dokumen) ?></span>
                                    <?php else: ?>
                                        <a href="<?= site_url('dokumen/detail/'.$d->id) ?>" class="text-sm font-semibold text-blue-600 underline underline-offset-2 decoration-blue-300 hover:text-blue-800 hover:decoration-blue-500 line-clamp-2" title="Lihat Detail Dokumen"><?= htmlspecialchars($d->nama_dokumen) ?></a>
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

                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center items-center gap-1.5">
                                    <?php if ($isCloud): ?>
                                        <a href="<?= site_url('dokumen/unduh/'.$d->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Unduh Dokumen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    <?php elseif ($isOwnLocal): ?>
                                        <button type="button" 
                                                data-sync-id="<?= (int)$d->id ?>" 
                                                data-sync-name="<?= htmlspecialchars($d->file_name, ENT_QUOTES) ?>"
                                                class="btn-sync p-1.5 bg-white border border-blue-300 text-blue-600 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-all shadow-sm" title="Unggah ke Server Sistem">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    <?php else: ?>
                                        <span class="p-1.5 bg-white border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed" title="File tersimpan di Perangkat Lain">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        </span>
                                    <?php endif; ?>

                                    <a href="<?= site_url('dokumen/edit/' . (int)$d->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>

                                    <!-- PERBAIKAN: Tambah data-file-name & data-storage agar JS bisa hapus file fisik via Agent -->
                                    <button type="button" class="btn-hapus p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                            data-id="<?= (int)$d->id ?>"
                                            data-nama="<?= htmlspecialchars($d->nama_dokumen, ENT_QUOTES) ?>"
                                            data-file-name="<?= htmlspecialchars($d->file_name, ENT_QUOTES) ?>"
                                            data-storage="<?= $d->storage_type ?>"
                                            title="Hapus Dokumen">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="flex flex-col border-t border-gray-200 bg-gray-50/30 px-5 py-4 gap-4">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-600">
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>Server Sistem</div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Ini</div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Lain</div>
                <div class="hidden sm:block h-4 w-px bg-gray-300"></div>
                <div class="inline-flex items-center gap-1.5"><svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Reminder email otomatis aktif</div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200/60 pt-3">
                <span id="pagination-info" class="text-sm text-gray-500">Menampilkan 0-0 dari 0 Dokumen</span>
                <div id="pagination-controls" class="flex items-center gap-1.5"></div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="fixed top-5 right-5 z-[60] flex flex-col gap-2 pointer-events-none"></div>
</main>

<!-- Modal Hapus -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>
    <div id="modalContent" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">
        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
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

<style>
    @keyframes toastIn  { from { opacity:0; transform:translateX(100%); } to { opacity:1; transform:translateX(0); } }
    @keyframes toastOut { from { opacity:1; transform:translateX(0); }    to { opacity:0; transform:translateX(100%); } }
    .toast-item { animation: toastIn 0.3s ease-out; }
    .toast-item.hide { animation: toastOut 0.3s ease-in forwards; }
</style>

<script>
    /* ========== TOAST ========== */
    const TOAST_CFG = {
        success: { bg: 'bg-emerald-50 border-emerald-200 text-emerald-800', ic: 'text-emerald-500', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        error:   { bg: 'bg-red-50 border-red-200 text-red-800',             ic: 'text-red-500',     d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        info:    { bg: 'bg-blue-50 border-blue-200 text-blue-800',          ic: 'text-blue-500',    d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
    };

    function showToast(msg, type = 'success') {
        const c = TOAST_CFG[type] || TOAST_CFG.info;
        const el = document.createElement('div');
        el.className = `toast-item pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg max-w-sm ${c.bg}`;
        el.innerHTML = `
            <svg class="w-5 h-5 flex-shrink-0 ${c.ic}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${c.d}"/></svg>
            <span class="text-sm font-medium flex-1">${msg}</span>
            <button type="button" class="opacity-60 hover:opacity-100 flex-shrink-0" onclick="this.parentElement.remove()"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>`;
        document.getElementById('toastContainer').appendChild(el);
        setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 300); }, 3000);
    }

    /* ========== UTILITY ========== */
    function getDeviceId() {
        let id = localStorage.getItem('tridatu_device_id');
        if (!id) {
            id = crypto.randomUUID();
            localStorage.setItem('tridatu_device_id', id);
            document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;
        }
        return id;
    }

    async function hitungHash(blob) {
        const buf = await crypto.subtle.digest('SHA-256', await blob.arrayBuffer());
        return [...new Uint8Array(buf)].map(b => b.toString(16).padStart(2, '0')).join('');
    }

    /* ========== DROPDOWN ========== */
    function closeDD(dd) {
        dd.classList.remove('open');
        const menu = dd.querySelector('.dropdown-menu');
        menu.classList.add('opacity-0', 'scale-95');
        menu.classList.remove('opacity-100', 'scale-100');
        dd.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
        setTimeout(() => menu.classList.add('hidden'), 200);
    }

    function openDD(dd) {
        document.querySelectorAll('.custom-dropdown').forEach(o => { if (o !== dd) closeDD(o); });
        const menu = dd.querySelector('.dropdown-menu');
        dd.classList.add('open');
        menu.classList.remove('hidden');
        void menu.offsetWidth;
        menu.classList.remove('opacity-0', 'scale-95');
        menu.classList.add('opacity-100', 'scale-100');
        dd.querySelector('.dropdown-btn svg').classList.add('rotate-180');
    }

    function setDropdownValue(targetId, value, label) {
        const dd = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);
        if (!dd) return;
        dd.querySelector('input[type="hidden"]').value = value;
        dd.querySelector('.dropdown-text').textContent = label;
        dd.querySelectorAll('li').forEach(li => {
            li.classList.remove('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
            if (li.dataset.value === value) li.classList.add('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
        });
        dd.querySelector('input[type="hidden"]').dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn = dd.querySelector('.dropdown-btn');

        btn.addEventListener('click', e => {
            e.stopPropagation();
            dd.classList.contains('open') ? closeDD(dd) : openDD(dd);
        });

        dd.addEventListener('click', e => {
            if (e.target.tagName !== 'LI') return;
            const label = e.target.textContent.trim();
            setDropdownValue(dd.dataset.target, e.target.dataset.value, label);
            closeDD(dd);
            checkResetButton();
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown').forEach(closeDD);
    });

    function checkResetButton() {
        const has = ['filterClient', 'filterJenis', 'filterStatus', 'searchInput']
            .some(id => document.getElementById(id).value);
        document.getElementById('btnResetFilter').classList.toggle('hidden', !has);
    }

    document.getElementById('btnResetFilter').addEventListener('click', () => {
        ['searchInput', 'filterClient', 'filterJenis', 'filterStatus'].forEach(id => document.getElementById(id).value = '');
        setDropdownValue('filterClient', '', 'Semua Klien');
        setDropdownValue('filterJenis', '', 'Semua Jenis');
        setDropdownValue('filterStatus', '', 'Semua Status');
        document.getElementById('btnResetFilter').classList.add('hidden');
        applyFilters();
    });

    /* ========== PAGINATION & FILTER ========== */
    const ROWS_PER_PAGE = 5;
    let currentPage = 1;
    let allRows = [];
    let filteredRows = [];

    document.addEventListener('DOMContentLoaded', function() {
        allRows = [...document.querySelectorAll('.data-row')];
        filteredRows = [...allRows];

        const unique = { client: new Set(), jenis: new Set(), status: new Set() };
        (<?= json_encode(array_column($jenis_dokumen ?? [], 'name')) ?>).forEach(v => { if (v) unique.jenis.add(v); });

        allRows.forEach(row => {
            if (row.dataset.client) unique.client.add(row.dataset.client);
            if (row.dataset.jenis)  unique.jenis.add(row.dataset.jenis);
            if (row.dataset.status) unique.status.add(row.dataset.status);
        });

        const fill = (targetId, set) => {
            const ul = document.querySelector(`.custom-dropdown[data-target="${targetId}"] .dropdown-list`);
            set.forEach(v => {
                const li = document.createElement('li');
                li.className = 'px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors truncate';
                li.title = v;
                li.dataset.value = v;
                li.textContent = v;
                ul.appendChild(li);
            });
        };
        fill('filterClient', unique.client);
        fill('filterJenis', unique.jenis);
        fill('filterStatus', unique.status);

        updateTablePagination();

        ['searchInput', 'filterClient', 'filterJenis', 'filterStatus'].forEach(id => {
            const el = document.getElementById(id);
            el.addEventListener(id === 'searchInput' ? 'keyup' : 'change', () => { applyFilters(); checkResetButton(); });
        });

        setTimeout(() => {
            ['alert-success', 'alert-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
            });
        }, 4000);
    });

    function applyFilters() {
        const q  = document.getElementById('searchInput').value.toLowerCase();
        const fc = document.getElementById('filterClient').value;
        const fj = document.getElementById('filterJenis').value;
        const fs = document.getElementById('filterStatus').value;

        filteredRows = allRows.filter(row => {
            const name = row.querySelector('td:nth-child(1)').innerText.toLowerCase();
            return name.includes(q)
                && (!fc || row.dataset.client === fc)
                && (!fj || row.dataset.jenis === fj)
                && (!fs || row.dataset.status === fs);
        });
        currentPage = 1;
        updateTablePagination();
    }

    function updateTablePagination() {
        const total = filteredRows.length;
        const totalPages = Math.ceil(total / ROWS_PER_PAGE);
        const tbody = document.getElementById('tableBody');

        document.getElementById('search-empty-row')?.remove();

        if (total === 0 && allRows.length > 0) {
            const tr = document.createElement('tr');
            tr.id = 'search-empty-row';
            tr.innerHTML = `<td colspan="7" class="px-6 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <p class="text-sm font-medium">Data tidak ditemukan.</p>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci atau filter lain.</p>
            </td>`;
            tbody.appendChild(tr);
        }

        currentPage = Math.min(Math.max(1, currentPage), totalPages || 1);
        allRows.forEach(r => r.style.display = 'none');

        if (total > 0) {
            const start = (currentPage - 1) * ROWS_PER_PAGE;
            const end = Math.min(start + ROWS_PER_PAGE, total);
            for (let i = start; i < end; i++) filteredRows[i].style.display = '';
            document.getElementById('pagination-info').innerText = `Menampilkan ${start + 1}–${end} dari ${total} Dokumen`;
        } else {
            document.getElementById('pagination-info').innerText = `Menampilkan 0 dari 0 Dokumen`;
        }

        renderPagination(totalPages);
    }

    function renderPagination(totalPages) {
        const c = document.getElementById('pagination-controls');
        c.innerHTML = '';
        if (totalPages <= 1) return;

        const NAV = 'flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
        const numCls = on => on
            ? 'flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-[#9f2b2b] bg-[#9f2b2b] text-white text-sm font-medium shadow-sm'
            : 'flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors';

        const mkNav = (d, disabled, fn) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = NAV;
            b.disabled = disabled;
            b.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${d}"/></svg>`;
            b.onclick = fn;
            return b;
        };

        c.appendChild(mkNav('M15 19l-7-7 7-7', currentPage === 1, () => { currentPage--; updateTablePagination(); }));

        for (let i = 1; i <= totalPages; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = numCls(i === currentPage);
            b.textContent = i;
            b.onclick = () => { currentPage = i; updateTablePagination(); };
            c.appendChild(b);
        }

        c.appendChild(mkNav('M9 5l7 7-7 7', currentPage === totalPages, () => { currentPage++; updateTablePagination(); }));
    }

    /* ========== SYNC TO CLOUD (LOCAL SERVICE) ========== */
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.syncId;
            const fileName = this.dataset.syncName;
            const originalHtml = btn.innerHTML;

            try {
                // 1. Cek koneksi ke TridatuAgent
                try {
                    await fetch('http://localhost:8080/ping', { mode: 'cors' });
                } catch (e) {
                    throw new Error('Aplikasi TridatuAgent belum dibuka. Buka aplikasinya untuk melakukan sinkronisasi.');
                }

                btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';
                btn.disabled = true;

                // 2. Ambil file dari Local Agent
                const resFile = await fetch(`http://localhost:8080/file?filename=${encodeURIComponent(fileName)}`);
                if (!resFile.ok) throw new Error('File fisik tidak ditemukan di laptop ini.');
                const blob = await resFile.blob();

                // 3. Siapkan form data untuk server
                const formData = new FormData();
                formData.append('id', id);
                formData.append('device_id', getDeviceId());
                formData.append('file_hash', await hitungHash(blob));
                formData.append('file', blob, fileName);

                // 4. Kirim ke CodeIgniter Controller (sync_to_cloud)
                const response = await fetch('<?= site_url('dokumen/sync_to_cloud') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                const res = await response.json();

                if (res.status) {
                    showToast('Berhasil diunggah ke Server Sistem!', 'success');
                    btn.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                    btn.classList.remove('border-blue-300', 'text-blue-600', 'hover:bg-blue-50', 'hover:border-blue-400');
                    btn.classList.add('border-emerald-300', 'text-emerald-600');
                    setTimeout(() => window.location.reload(), 1200);
                } else {
                    throw new Error(res.msg || 'Gagal mengunggah dokumen.');
                }
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    });

    /* ========== HAPUS (LOCAL SERVICE & CLOUD) ========== */
    let hapusId = null;
    let hapusFile = null;
    let hapusStorage = null;
    const modal = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function() {
            hapusId = this.dataset.id;
            hapusFile = this.dataset.fileName;
            hapusStorage = this.dataset.storage;
            
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
        hapusFile = null;
        hapusStorage = null;
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
    }

    async function eksekusiHapus() {
        if (!hapusId) return;

        // Jika dokumen adalah lokal, perintahkan Agent untuk hapus file fisik terlebih dahulu
        if (hapusStorage === 'local' && hapusFile) {
            try {
                await fetch(`http://localhost:8080/delete?filename=${encodeURIComponent(hapusFile)}`, { method: 'DELETE' });
            } catch (err) {
                console.warn('TridatuAgent mati atau file sudah tidak ada. Melanjutkan hapus database.');
            }
        }

        // Lanjutkan hapus data di database via CodeIgniter
        window.location.href = '<?= site_url('dokumen/hapus/') ?>' + hapusId;
    }
</script>