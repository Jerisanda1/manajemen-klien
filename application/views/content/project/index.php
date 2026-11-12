<?php
/**
 * Manajemen Project — Konsisten dengan Dokumen & Klien
 * Variabel: $projects, $klienList, $jmlProject, $jmlBerjalan, $total,
 *           $page, $perPage, $totalPages, $q, $fKlien, $fStatus
 */
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

$badge = [
    'Berjalan' => 'bg-blue-50 text-blue-600 border-blue-100',
    'Selesai'  => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    'Ditunda'  => 'bg-orange-50 text-orange-600 border-orange-100',
];

// Link pagination: pertahankan filter
$urlHalaman = function ($p) use ($q, $fKlien, $fStatus) {
    return site_url('project') . '?' . http_build_query(['q' => $q, 'klien' => $fKlien, 'status' => $fStatus, 'page' => $p]);
};

$awal  = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$akhir = $total > 0 ? min($page * $perPage, $total) : 0;

// Label dropdown aktif (sama seperti custom-dropdown di dokumen)
$labelKlien  = 'Semua Klien';
$labelStatus = 'Semua Status';
if ($fKlien !== '') {
    foreach ($klienList as $k) {
        if ((string) $k->id === (string) $fKlien) { $labelKlien = $k->nama_klien; break; }
    }
}
if ($fStatus !== '') $labelStatus = $fStatus;

$adaFilter = ($q !== '' || $fKlien !== '' || $fStatus !== '');
?>
<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6">

    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Project</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data project klien dan pantau status pengerjaannya.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                <span class="font-bold text-gray-800"><?= (int) $jmlProject ?></span>
                <span class="text-gray-500 text-sm">Project</span>
            </div>

            <a href="<?= site_url('project/tambah') ?>" class="bg-[#9f2b2b] hover:bg-[#8a2525] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all shadow-md shadow-[#9f2b2b]/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Project
            </a>
        </div>
    </div>

    <!-- Notifikasi -->
    <?php if ($this->session->flashdata('success')): ?>
        <div id="alert-success" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm"><?= $e($this->session->flashdata('success')) ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-success').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        <div id="alert-error" class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="font-medium text-sm"><?= $e($this->session->flashdata('error')) ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Card Tabel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

        <!-- Toolbar Filter (Custom Dropdown, konsisten dengan Dokumen) -->
        <form method="get" action="<?= site_url('project') ?>" id="filterForm"
              class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col xl:flex-row gap-3 items-center justify-between">

            <!-- Search Box -->
            <div class="relative w-full xl:w-72">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="q" id="searchInput" value="<?= $e($q) ?>"
                       class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl block w-full pl-10 p-2.5 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all"
                       placeholder="Cari nama project...">
            </div>

            <div class="flex flex-nowrap items-center gap-2 w-full xl:w-auto mt-2 xl:mt-0">
                <!-- Filter Klien -->
                <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-target="filterKlien">
                    <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-xl w-full sm:min-w-[150px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
                        <span class="dropdown-text truncate"><?= $e($labelKlien) ?></span>
                        <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu absolute z-50 hidden mt-2 w-full sm:min-w-[150px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                            <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $fKlien === '' ? 'bg-red-50 text-[#9f2b2b] font-bold' : 'font-medium' ?>" data-value="">Semua Klien</li>
                            <?php foreach ($klienList as $k): ?>
                                <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors truncate <?= (string) $fKlien === (string) $k->id ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>"
                                    data-value="<?= (int) $k->id ?>" title="<?= $e($k->nama_klien) ?>"><?= $e($k->nama_klien) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <input type="hidden" name="klien" id="filterKlien" value="<?= $e($fKlien) ?>">
                </div>

                <!-- Filter Status -->
                <div class="relative custom-dropdown flex-shrink-0 w-full sm:w-auto" data-target="filterStatus">
                    <button type="button" class="dropdown-btn flex items-center justify-between bg-white border border-gray-300 hover:border-gray-400 text-gray-700 text-sm font-medium rounded-xl w-full sm:min-w-[130px] pl-3 pr-9 py-2.5 outline-none focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all cursor-pointer">
                        <span class="dropdown-text truncate"><?= $e($labelStatus) ?></span>
                        <svg class="w-4 h-4 text-gray-500 absolute right-3 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>
                    <div class="dropdown-menu absolute z-50 hidden mt-2 w-full sm:min-w-[130px] bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden transform opacity-0 scale-95 transition-all duration-200">
                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                            <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $fStatus === '' ? 'bg-red-50 text-[#9f2b2b] font-bold' : 'font-medium' ?>" data-value="">Semua Status</li>
                            <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                                <li class="px-3 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $fStatus === $s ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>"
                                    data-value="<?= $s ?>"><?= $s ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <input type="hidden" name="status" id="filterStatus" value="<?= $e($fStatus) ?>">
                </div>

                <!-- Reset (muncul hanya saat ada filter aktif) -->
                <button type="button" id="btnResetFilter"
                        class="<?= $adaFilter ? '' : 'hidden' ?> flex-shrink-0 px-3.5 py-2.5 bg-red-50 text-[#9f2b2b] hover:bg-red-100 border border-red-100 text-sm font-medium rounded-xl transition-all flex items-center justify-center gap-1.5 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    Reset
                </button>
            </div>
        </form>

        <!-- Tabel -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Project</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Klien</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Deskripsi</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Mulai</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Selesai</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if (empty($projects)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <?php if ($adaFilter): ?>
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                        <p class="text-sm font-medium">Data tidak ditemukan.</p>
                                        <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci atau filter lain.</p>
                                    <?php else: ?>
                                        <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                                        <p class="text-sm font-medium">Belum ada data project.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($projects as $p): ?>
                            <tr class="data-row border-b border-gray-100 hover:bg-gray-50/60 transition-colors group">
                                <td class="px-5 py-4 text-sm font-bold text-gray-900 group-hover:text-[#9f2b2b] transition-colors"><?= $e($p->nama_project) ?></td>
                                <td class="px-5 py-4 text-sm text-gray-700 font-medium"><?= $e($p->nama_klien ?: '-') ?></td>
                                <td class="max-w-[220px] px-5 py-4 text-sm text-gray-600">
                                    <p class="line-clamp-2" title="<?= $e($p->deskripsi) ?>"><?= $p->deskripsi ? $e($p->deskripsi) : '<span class="text-gray-400 italic">-</span>' ?></p>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 text-center"><?= $tgl($p->tanggal_mulai) ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-gray-600 text-center"><?= $tgl($p->tanggal_selesai) ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $badge[$p->status] ?? 'bg-slate-50 text-slate-600 border-slate-100' ?>">
                                        <?= $e($p->status) ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="flex justify-center items-center gap-1.5">
                                        <a href="<?= site_url('project/detail/' . (int) $p->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Lihat Detail">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </a>

                                        <a href="<?= site_url('project/edit/' . (int) $p->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        </a>

                                        <button type="button"
                                                data-id="<?= (int) $p->id ?>"
                                                data-nama="<?= $e($p->nama_project) ?>"
                                                onclick="bukaModalHapus(this.dataset.id, this.dataset.nama)"
                                                class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm" title="Hapus Project">
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

        <!-- Footer & Pagination (Server-Side) -->
        <div class="flex flex-col sm:flex-row items-center justify-between border-t border-gray-200 bg-gray-50/30 px-5 py-4 gap-4">
            <span class="text-sm text-gray-500">
                Menampilkan <?= $awal ?>–<?= $akhir ?> dari <?= (int) $total ?> Project
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
                    <a href="<?= $urlHalaman($i) ?>"
                       class="flex h-8 min-w-[2rem] items-center justify-center rounded-lg border text-sm font-medium transition-colors <?= $i === $page ? 'border-[#9f2b2b] bg-[#9f2b2b] text-white shadow-sm' : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50' ?>">
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
</main>

<!-- MODAL KONFIRMASI HAPUS -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="tutupModalHapus()"></div>

    <div id="modalContent" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">
        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Project?</h3>
        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus project <br>
            <strong id="namaProjectHapus" class="text-gray-900 text-base"></strong>?<br>
            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
        </p>

        <form method="post" action="<?= site_url('project/hapus') ?>" class="flex justify-center gap-3">
            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>
            <input type="hidden" name="id" id="inputHapusId">

            <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
            <button type="submit" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm">Ya, Hapus</button>
        </form>
    </div>
</div>

<script>
    /* ========== CUSTOM DROPDOWN (sama dengan dokumen) ========== */
    const filterForm = document.getElementById('filterForm');
    const searchInput = document.getElementById('searchInput');
    const resetBtn = document.getElementById('btnResetFilter');
    let debounceTimer = null;

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

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn = dd.querySelector('.dropdown-btn');

        btn.addEventListener('click', e => {
            e.stopPropagation();
            dd.classList.contains('open') ? closeDD(dd) : openDD(dd);
        });

        dd.addEventListener('click', e => {
            if (e.target.tagName !== 'LI') return;
            const value = e.target.dataset.value;
            const label = e.target.textContent.trim();
            dd.querySelector('input[type="hidden"]').value = value;
            dd.querySelector('.dropdown-text').textContent = label;
            closeDD(dd);
            filterForm.submit(); // filter server-side → reload dengan parameter GET
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('.custom-dropdown').forEach(closeDD);
    });

    /* ========== SEARCH (debounce + Enter) ========== */
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => filterForm.submit(), 500);
    });
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(debounceTimer);
            filterForm.submit();
        }
    });

    /* ========== RESET ========== */
    resetBtn.addEventListener('click', function() {
        searchInput.value = '';
        document.getElementById('filterKlien').value = '';
        document.getElementById('filterStatus').value = '';
        window.location.href = '<?= site_url('project') ?>';
    });

    /* ========== AUTO-FADE ALERT (konsisten dengan dokumen) ========== */
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            ['alert-success', 'alert-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.style.opacity = '0'; setTimeout(() => el.remove(), 300); }
            });
        }, 4000);
    });

    /* ========== MODAL HAPUS ========== */
    let hapusId = null;
    const modal = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    function bukaModalHapus(id, nama) {
        hapusId = id;
        document.getElementById('inputHapusId').value = id;
        document.getElementById('namaProjectHapus').innerText = nama;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalHapus() {
        hapusId = null;
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape' && !modal.classList.contains('hidden')) tutupModalHapus();
    });
</script>