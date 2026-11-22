<?php

$awal  = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$akhir = $total > 0 ? min($page * $perPage, $total) : 0;

// Nilai default filter (dikirim dari controller)
$fKlien       = $fKlien       ?? '';
$fProject     = $fProject     ?? '';
$klien_list   = $klien_list   ?? [];
$project_list = $project_list ?? [];
$jenis_list   = array_map(function ($j) { return $j->name; }, $jenis_dokumen);
$adaFilter    = ($q || $fJenis || $fKlien || $fProject);

// Escape helper
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

// Format tanggal singkat Indonesia (sama seperti halaman Project)
$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

// Badge status dokumen (gaya ring-inset + dot, seperti Project)
$badgeDok = [
    'Aktif'         => ['bg-sky-50 text-sky-700 ring-sky-200',       'bg-sky-500'],
    'Akan Berakhir' => ['bg-amber-50 text-amber-700 ring-amber-200', 'bg-amber-500'],
    'Expired'       => ['bg-rose-50 text-rose-700 ring-rose-200',    'bg-rose-500'],
];

$renderBadgeDok = function ($status) use ($badgeDok) {
    [$kelas, $dot] = $badgeDok[$status] ?? ['bg-slate-200 text-slate-700 ring-slate-300', 'bg-slate-500'];
    return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $kelas . '">'
         . '<span class="h-1.5 w-1.5 rounded-full ' . $dot . '"></span>' . htmlspecialchars($status) . '</span>';
};

// Fungsi pembantu untuk link pagination (mempertahankan semua filter)
$urlHalaman = function ($p) use ($q, $fJenis, $fKlien, $fProject) {
    return site_url('dokumen') . '?' . http_build_query([
        'q'       => $q,
        'jenis'   => $fJenis,
        'klien'   => $fKlien,
        'project' => $fProject,
        'page'    => $p,
    ]);
};

// Render dropdown filter custom (gaya sama seperti halaman Project)
// Dipakai untuk Jenis, Klien, dan Project. Opsi $searchable menambah kolom cari di dalam menu.
$renderFilter = function ($key, $name, $semuaLabel, $options, $selected, $searchable = false) {
    $esc = function ($s) { return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8'); };
    $sel = (string) $selected;
?>
    <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-key="<?= $key ?>">
        <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-xl w-full sm:min-w-[150px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
            <span class="dropdown-text truncate"><?= $sel !== '' ? $esc($sel) : $esc($semuaLabel) ?></span>
            <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
        </button>

        <div class="dropdown-menu absolute left-0 z-50 hidden mt-2 w-full sm:min-w-[150px] min-w-[220px] max-w-[320px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
            <?php if ($searchable): ?>
                <div class="p-1.5 border-b border-gray-100">
                    <input type="text" class="dropdown-search w-full bg-gray-50 border border-gray-200 text-sm text-gray-900 rounded-lg px-3 py-2 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-2 focus:ring-[#9f2b2b]/10" placeholder="Cari..." autocomplete="off" onkeydown="if(event.key === 'Enter') event.preventDefault()">
                </div>
            <?php endif; ?>

            <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                <li class="dropdown-option px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $sel === '' ? 'bg-red-50 text-[#9f2b2b] font-bold' : 'font-medium' ?>" data-value="" data-text=""><?= $esc($semuaLabel) ?></li>
                <?php foreach ($options as $opt): ?>
                    <li class="dropdown-option px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors truncate <?= $sel === (string) $opt ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>"
                        data-value="<?= $esc($opt) ?>" data-text="<?= $esc(mb_strtolower((string) $opt)) ?>" title="<?= $esc($opt) ?>"><?= $esc($opt) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if ($searchable): ?>
                <p class="dropdown-empty hidden px-3 py-3 text-sm text-gray-400 text-center">Tidak ditemukan.</p>
            <?php endif; ?>
        </div>

        <input type="hidden" name="<?= $name ?>" id="filter-<?= $key ?>-input" value="<?= $esc($sel) ?>">
    </div>
<?php
};
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6">

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Dokumen Kerja Sama</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data dokumen kerja sama.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 017-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span class="font-bold text-gray-800"><?= (int) $total ?></span>&#x20;
                <span class="text-gray-500 text-sm">Dokumen</span>
            </div>
            <a href="<?= site_url('dokumen/tambah') ?>" class="bg-[#9f2b2b] hover:bg-[#8a2525] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all shadow-md shadow-[#9f2b2b]/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Dokumen
            </a>
        </div>
    </div>

    <!-- Card Tabel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Toolbar -->
        <form method="GET" action="<?= site_url('dokumen') ?>" id="filterDokumenForm" class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col xl:flex-row gap-3 items-stretch xl:items-center justify-between">

            <!-- Search -->
            <div class="relative w-full xl:w-64 xl:flex-shrink-0">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl block w-full pl-10 p-2.5 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all" placeholder="Cari Nama Dokumen..." onkeydown="if(event.key === 'Enter') this.form.submit()">
            </div>

            <div class="flex flex-wrap xl:flex-nowrap items-center gap-2 w-full xl:w-auto xl:flex-1 xl:justify-end mt-0">

                <?php $renderFilter('jenis',   'jenis',   'Semua Jenis',   $jenis_list,   $fJenis); ?>
                <?php $renderFilter('klien',   'klien',   'Semua Klien',   $klien_list,   $fKlien,   true); ?>
                <?php $renderFilter('project', 'project', 'Semua Project', $project_list, $fProject, true); ?>

                <!-- Reset -->
                <?php if ($adaFilter): ?>
                    <a href="<?= site_url('dokumen') ?>" class="flex-shrink-0 px-3.5 py-2.5 bg-red-50 text-[#9f2b2b] hover:bg-red-100 border border-red-100 text-sm font-medium rounded-xl transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>

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
                    <?php if (empty($documents)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <?php if ($adaFilter): ?>
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <p class="text-sm font-medium">Data tidak ditemukan.</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci atau filter lain.</p>
                                    <?php else: ?>
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <p class="text-sm font-medium">Belum ada data dokumen.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($documents as $d): ?>
                            <?php
                                $tgl_akhir = !empty($d->tanggal_berakhir) ? $d->tanggal_berakhir : '2099-12-31';
                                $diff = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($tgl_akhir))->format('%r%a');

                                if ($diff < 0)       { $status_text = 'Expired'; }
                                elseif ($diff <= 30) { $status_text = 'Akan Berakhir'; }
                                else                 { $status_text = 'Aktif'; }

                                $rowDeviceId  = $d->device_id ?? '';
                                $isCloud      = ($d->storage_type === 'cloud');
                                $isOwnLocal   = (!$isCloud && $rowDeviceId !== '' && $rowDeviceId === $currentDeviceId);
                                $isOtherLocal = (!$isCloud && !$isOwnLocal);
                            ?>
                            <tr class="data-row border-b border-gray-100 hover:bg-gray-50/60 transition-colors group">

                                <!-- Nama Dokumen -->
                                <td class="px-5 py-4 max-w-[260px]">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="flex-shrink-0 <?= $isOtherLocal ? 'text-gray-400' : 'text-blue-500' ?>">
                                            <?php if ($isCloud): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                            <?php else: ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <?php endif; ?>
                                        </div>

                                        <?php if ($isOtherLocal): ?>
                                            <span class="min-w-0 truncate text-sm font-semibold text-gray-500 cursor-not-allowed" title="Terkunci di Perangkat Lain"><?= $e($d->nama_dokumen) ?></span>
                                        <?php else: ?>
                                            <a href="<?= site_url('dokumen/detail/' . (int) $d->id) ?>" class="min-w-0 truncate text-sm font-semibold text-blue-600 underline underline-offset-2 decoration-blue-300 hover:text-blue-800 hover:decoration-blue-500 transition-colors" title="Lihat Detail"><?= $e($d->nama_dokumen) ?></a>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Klien -->
                                <td class="px-5 py-4 text-sm text-gray-700 font-medium max-w-[160px] truncate" title="<?= $e($d->nama_klien) ?>"><?= $e($d->nama_klien) ?></td>

                                <!-- Project -->
                                <td class="px-5 py-4 text-sm text-gray-600 max-w-[160px] truncate" title="<?= $e($d->nama_project ?? '') ?>"><?= !empty($d->nama_project) ? $e($d->nama_project) : '<span class="text-gray-400 italic">-</span>' ?></td>

                                <!-- Tanggal -->
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 text-center"><?= $tgl($d->tanggal_mulai ?? null) ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 text-center"><?= $tgl($d->tanggal_berakhir ?? null) ?></td>

                                <!-- Status -->
                                <td class="whitespace-nowrap px-5 py-4 text-center"><?= $renderBadgeDok($status_text) ?></td>

                                <!-- Aksi -->
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center items-center gap-1.5">

                                        <?php if ($isCloud): ?>
                                            <a href="<?= site_url('dokumen/unduh/' . (int) $d->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Unduh Dokumen">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        <?php elseif ($isOwnLocal): ?>
                                            <button
                                                type="button"
                                                data-sync-id="<?= (int) $d->id ?>"
                                                data-sync-name="<?= $e($d->file_name ?? '') ?>"
                                                data-klien-id="<?= (int) ($d->client_id ?? 0) ?>"
                                                data-klien-nama="<?= $e($d->nama_klien ?? '') ?>"
                                                data-file-hash="<?= $e($d->file_hash ?? '') ?>"
                                                class="btn-sync p-1.5 bg-white border border-blue-300 text-blue-600 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-all shadow-sm"
                                                title="Unggah ke Cloud">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        <?php else: ?>
                                            <span class="p-1.5 bg-white border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed" title="Terkunci di Perangkat Lain">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        <?php endif; ?>

                                        <?php if (!$isOtherLocal): ?>
                                            <a href="<?= site_url('dokumen/edit/' . (int) $d->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </a>
                                        <?php else: ?>
                                            <span class="p-1.5 bg-white border border-gray-200 text-gray-300 rounded-lg cursor-not-allowed" title="Tidak dapat diedit dari perangkat ini">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                            </span>
                                        <?php endif; ?>

                                        <button
                                            type="button"
                                            class="btn-hapus p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                            data-id="<?= (int) $d->id ?>"
                                            data-nama="<?= $e($d->nama_dokumen ?? '') ?>"
                                            data-is-own-local="<?= $isOwnLocal ? '1' : '0' ?>"
                                            data-klien-id="<?= (int) ($d->client_id ?? 0) ?>"
                                            data-klien-nama="<?= $e($d->nama_klien ?? '') ?>"
                                            data-file-name="<?= $e($d->file_name ?? '') ?>"
                                            data-file-hash="<?= $e($d->file_hash ?? '') ?>"
                                            title="Hapus">
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

        <!-- Footer & Server-Side Pagination -->
        <div class="flex flex-col border-t border-gray-200 bg-gray-50/30 px-5 py-4 gap-4">
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-gray-600">
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>Server Sistem</div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Ini</div>
                <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Lain</div>

                <!-- Catatan pengingat otomatis -->
                <div class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Dokumen yang akan berakhir diingatkan otomatis melalui WhatsApp atau email.
                </div>
            </div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-200/60 pt-3">
                <span class="text-sm text-gray-500">
                    Menampilkan <?= $awal ?>–<?= $akhir ?> dari <?= (int) $total ?> Dokumen
                </span>
                <div class="flex items-center gap-1.5">
                    <!-- Prev -->
                    <?php if ($page > 1): ?>
                        <a href="<?= $urlHalaman($page - 1) ?>" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 transition-colors">
                    <?php else: ?>
                        <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-300">
                    <?php endif; ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <?= $page > 1 ? '</a>' : '</span>' ?>

                    <!-- Numbers -->
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?= $urlHalaman($i) ?>" class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg border text-sm font-medium transition-colors <?= $i === $page ? 'border-[#9f2b2b] bg-[#9f2b2b] text-white shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next -->
                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $urlHalaman($page + 1) ?>" class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 transition-colors">
                    <?php else: ?>
                        <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-300">
                    <?php endif; ?>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <?= $page < $totalPages ? '</a>' : '</span>' ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Container untuk Toast -->
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

<script src="<?= base_url('assets/js/tridatu-fs.js') ?>"></script>
<script>
    // ==========================================
    // CUSTOM DROPDOWN FILTER (JENIS, KLIEN, PROJECT)
    // ==========================================
    const filterDokumenForm = document.getElementById('filterDokumenForm');

    function closeDD(dd) {
        dd.classList.remove('open');
        const menu = dd.querySelector('.dropdown-menu');
        menu.classList.add('opacity-0', 'scale-95');
        menu.classList.remove('opacity-100', 'scale-100');
        dd.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
        setTimeout(() => { if (!dd.classList.contains('open')) menu.classList.add('hidden'); }, 200);
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

    function filterOpsiDropdown(dd, keyword) {
        const kw = keyword.trim().toLowerCase();
        let adaHasil = false;

        dd.querySelectorAll('.dropdown-option').forEach(opt => {
            const cocok = kw === '' || opt.dataset.value === '' || opt.dataset.text.includes(kw);
            opt.style.display = cocok ? '' : 'none';
            if (cocok && opt.dataset.value !== '') adaHasil = true;
        });

        const kosong = dd.querySelector('.dropdown-empty');
        if (kosong) kosong.classList.toggle('hidden', kw === '' || adaHasil);
    }

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn    = dd.querySelector('.dropdown-btn');
        const menu   = dd.querySelector('.dropdown-menu');
        const search = dd.querySelector('.dropdown-search');
        const input  = dd.querySelector('input[type="hidden"]');

        btn.addEventListener('click', e => {
            e.stopPropagation();
            if (dd.classList.contains('open')) {
                closeDD(dd);
                return;
            }
            openDD(dd);
            if (search) {
                search.value = '';
                filterOpsiDropdown(dd, '');
                search.focus();
            }
        });

        // Klik di dalam menu (mis. kolom cari) tidak menutup dropdown
        menu.addEventListener('click', e => e.stopPropagation());

        if (search) {
            search.addEventListener('input', () => filterOpsiDropdown(dd, search.value));
        }

        dd.querySelectorAll('.dropdown-option').forEach(li => {
            li.addEventListener('click', () => {
                input.value = li.dataset.value;
                dd.querySelector('.dropdown-text').textContent = li.textContent.trim();
                closeDD(dd);
                filterDokumenForm.submit(); // filter server-side → reload dengan parameter GET
            });
        });
    });

    // Tutup dropdown jika klik di luar
    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown.open').forEach(closeDD);
    });

    // Tutup dropdown dengan tombol Escape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') document.querySelectorAll('.custom-dropdown.open').forEach(closeDD);
    });

    // --- TOAST CONFIG ---
    const TOAST_CFG = {
        success: { bg: 'bg-emerald-50 border-emerald-200 text-emerald-800', ic: 'text-emerald-500', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
        error:   { bg: 'bg-red-50 border-red-200 text-red-800',             ic: 'text-red-500',     d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
        info:    { bg: 'bg-blue-50 border-blue-200 text-blue-800',          ic: 'text-blue-500',    d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
    };

    function showToast(msg, type = 'success') {
        const c = TOAST_CFG[type] || TOAST_CFG.info;
        const el = document.createElement('div');
        el.className = `toast-item pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg max-w-sm ${c.bg}`;
        el.innerHTML = `<svg class="w-5 h-5 flex-shrink-0 ${c.ic}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${c.d}"/></svg><span class="text-sm font-medium flex-1">${msg}</span><button type="button" class="opacity-60 hover:opacity-100 flex-shrink-0" onclick="this.parentElement.remove()"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>`;
        document.getElementById('toastContainer').appendChild(el);
        setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 300); }, 3000);
    }

    // ==========================================
    // MEMUNCULKAN NOTIFIKASI PHP (FLASHDATA) VIA TOAST
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
        <?php if ($this->session->flashdata('success')): ?>
            showToast('<?= addslashes($this->session->flashdata('success')) ?>', 'success');
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            showToast('<?= addslashes($this->session->flashdata('error')) ?>', 'error');
        <?php endif; ?>
    });

    // --- UTILITY ---
    const DOCUMENT_CSRF_TOKEN = <?= json_encode($documentCsrfToken) ?>;

    function getDeviceId() {
        const serverId = <?= json_encode($currentDeviceId) ?>;
        let id = serverId || localStorage.getItem('tridatu_device_id');
        if (!id) id = crypto.randomUUID();
        localStorage.setItem('tridatu_device_id', id);

        const cookieId = document.cookie.split('; ').find(row => row.startsWith('tridatu_device_id='))?.split('=')[1];

        if (cookieId !== id) {
            document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;

            if (!serverId && !sessionStorage.getItem('tridatu_device_bootstrap')) {
                sessionStorage.setItem('tridatu_device_bootstrap', '1');
                window.location.reload();
            }
        }

        return id;
    }

    const DEVICE_ID = getDeviceId();

    async function hitungHash(blob) {
        const buf = await crypto.subtle.digest('SHA-256', await blob.arrayBuffer());
        return [...new Uint8Array(buf)].map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // --- SYNC TO CLOUD (FILE SYSTEM ACCESS API) ---
    document.querySelectorAll('.btn-sync').forEach(btn => {
        btn.addEventListener('click', async function () {
            const id = this.dataset.syncId;
            const fileName = this.dataset.syncName;
            const klienId = Number(this.dataset.klienId || 0);
            const klienNama = this.dataset.klienNama || '';
            const expectedHash = (this.dataset.fileHash || '').toLowerCase();
            const originalHtml = this.innerHTML;

            if (btn.disabled) return;

            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>';

            try {
                if (typeof TridatuFS === 'undefined') {
                    throw new Error('Helper TridatuFS belum berhasil dimuat.');
                }

                if (!klienId || !fileName || !expectedHash) {
                    throw new Error('Informasi file atau klien tidak lengkap.');
                }

                let file = null;

                // 1. Coba baca file dari folder yang dipilih menggunakan File System Access API.
                if (TridatuFS.isSupported()) {
                    try {
                        file = await TridatuFS.readFile({
                            klienId: klienId,
                            klienNama: klienNama,
                            fileName: fileName,
                            fileHash: expectedHash
                        });
                    } catch (readError) {
                        // Jika akses folder gagal, tetap sediakan pemilihan file manual.
                        console.warn('Tidak dapat membaca folder lokal:', readError);
                    }
                }

                // 2. Fallback: pilih file secara manual jika browser tidak mendukung
                //    File System Access API atau file tidak ditemukan di folder.
                if (!file) {
                    showToast('Pilih file dokumen yang akan diunggah.', 'info');
                    file = await TridatuFS.pickFile();
                }

                // Pengguna membatalkan pemilihan file.
                if (!file) return;

                // 3. Validasi agar file yang diunggah sama dengan file yang tercatat.
                const actualHash = (await hitungHash(file)).toLowerCase();
                if (actualHash !== expectedHash) {
                    throw new Error('File berbeda dari aslinya. Pastikan memilih file yang benar.');
                }

                // 4. Kirim file ke server untuk diubah status penyimpanannya menjadi Cloud.
                const formData = new FormData();
                formData.append('id', id);
                formData.append('device_id', DEVICE_ID);
                formData.append('file_hash', actualHash);
                formData.append('dokumen_csrf_token', DOCUMENT_CSRF_TOKEN);
                formData.append('file', file, fileName);

                const response = await fetch('<?= site_url('dokumen/sync_to_cloud') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const result = await response.json();

                if (!response.ok || !result.status) {
                    throw new Error(result.msg || 'Gagal mengunggah dokumen ke Cloud.');
                }

                showToast('Berhasil diunggah ke Cloud!', 'success');
                setTimeout(() => window.location.reload(), 1200);

            } catch (err) {
                console.error('Sync dokumen gagal:', err);
                showToast(err.message || 'Terjadi kesalahan saat mengunggah.', 'error');
            } finally {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        });
    });

    // --- HAPUS ---
    let hapusId = null;
    let hapusMeta = null;

    const modal = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    document.querySelectorAll('.btn-hapus').forEach(btn => {
        btn.addEventListener('click', function() {
            hapusId = this.dataset.id;
            hapusMeta = {
                isOwnLocal: this.dataset.isOwnLocal === '1',
                klienId: Number(this.dataset.klienId || 0),
                klienNama: this.dataset.klienNama || '',
                fileName: this.dataset.fileName || '',
                fileHash: this.dataset.fileHash || ''
            };
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
        hapusMeta = null;

        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    async function eksekusiHapus() {
        if (!hapusId) return;

        try {
            const formData = new FormData();
            formData.append('dokumen_csrf_token', DOCUMENT_CSRF_TOKEN);

            const response = await fetch('<?= site_url('dokumen/hapus/') ?>' + hapusId, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.status) {
                throw new Error(result.msg || 'Dokumen gagal dihapus.');
            }

            // Hapus file fisik hanya jika file lokal tersebut memang milik perangkat ini.
            // Data dokumen di server sudah berhasil dihapus sebelum bagian ini dijalankan.
            if (hapusMeta && hapusMeta.isOwnLocal && hapusMeta.fileName && hapusMeta.fileHash) {
                try {
                    if (typeof TridatuFS !== 'undefined' && TridatuFS.isSupported()) {
                        const deleteResult = await TridatuFS.deleteFile({
                            klienId: hapusMeta.klienId,
                            klienNama: hapusMeta.klienNama,
                            fileName: hapusMeta.fileName,
                            fileHash: hapusMeta.fileHash
                        });

                        if (!deleteResult.ok) {
                            console.warn('File lokal tidak berhasil dihapus:', deleteResult.msg);
                            showToast('Data dokumen terhapus, tetapi file lokal belum berhasil dihapus.', 'info');
                            await new Promise(resolve => setTimeout(resolve, 1500));
                        }
                    } else {
                        // Pada browser tanpa File System Access API, file hasil fallback
                        // tersimpan di Downloads dan tidak dapat dihapus oleh browser.
                        showToast('Data dokumen terhapus. File di Downloads perlu dihapus manual.', 'info');
                        await new Promise(resolve => setTimeout(resolve, 1800));
                    }
                } catch (fileError) {
                    console.warn('Data terhapus, tetapi file fisik gagal dihapus:', fileError);
                    showToast('Data dokumen terhapus, tetapi file lokal perlu diperiksa manual.', 'info');
                    await new Promise(resolve => setTimeout(resolve, 1800));
                }
            }

            window.location.href = result.redirect || '<?= site_url('dokumen') ?>';

        } catch (err) {
            showToast(err.message || 'Dokumen gagal dihapus.', 'error');
        }
    }
</script>