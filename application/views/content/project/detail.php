<?php
/**
 * Detail Project — Halaman scroll (min-h-screen).
 * Pagination di tiap tabel (client-side).
 * Custom dropdown untuk Status Project.
 * + Notifikasi Flashdata (Auto-fade).
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    if (empty($tanggal)) return '-';
    $t = strtotime($tanggal);
    return $t ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

// PERBAIKAN: Menggunakan amber-100 yang lebih tegas agar tidak terlihat kosong
$badge = [
    'Berjalan'      => ['bg-sky-50 text-sky-700 ring-sky-200',             'bg-sky-500'],
    'Selesai'       => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
    'Ditunda'       => ['bg-amber-100 text-amber-800 ring-amber-200',      'bg-amber-500'], // Lebih tebal
    'Disetujui'     => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
    'Belum dibayar' => ['bg-orange-50 text-orange-700 ring-orange-200',    'bg-orange-500'],
    'Lunas'         => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
];

// PERBAIKAN: Normalisasi teks agar aman dari huruf besar/kecil (misal 'ditunda' -> 'Ditunda')
$renderBadge = function ($status) use ($badge) {
    $status = ucwords(strtolower(trim($status))); 
    [$kelas, $dot] = $badge[$status] ?? ['bg-slate-200 text-slate-700 ring-slate-300', 'bg-slate-500'];
    
    return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $kelas . '">'
         . '<span class="h-1.5 w-1.5 rounded-full ' . $dot . '"></span>' . htmlspecialchars($status) . '</span>';
};

// ---- DATA DUMMY ----
$dokumen = [
    ['id' => 1, 'nama' => 'Kerja Sistem ERP',       'jenis' => 'Mou',                'sampai' => '2026-11-15'],
    ['id' => 2, 'nama' => 'Persetujuan Sistem ERP', 'jenis' => 'Kontrak Kerja sama', 'sampai' => '2026-11-15'],
    ['id' => 3, 'nama' => 'Addendum Sistem ERP',    'jenis' => 'Addendum',           'sampai' => '2026-12-15'],
    ['id' => 4, 'nama' => 'Perjanjian Kerahasiaan', 'jenis' => 'NDA',                'sampai' => '2027-01-10'],
    ['id' => 5, 'nama' => 'Kontrak Maintenance',    'jenis' => 'Kontrak Kerja sama', 'sampai' => '2027-02-20'],
    ['id' => 6, 'nama' => 'Surat Penunjukan',       'jenis' => 'Lainnya',            'sampai' => '2026-10-01'],
];
$penawaran = [
    ['id' => 1, 'no' => 'PNW/2026/018', 'tanggal' => '2026-05-01', 'status' => 'Disetujui'],
    ['id' => 2, 'no' => 'PNW/2026/019', 'tanggal' => '2026-06-15', 'status' => 'Disetujui'],
    ['id' => 3, 'no' => 'PNW/2026/020', 'tanggal' => '2026-07-10', 'status' => 'Disetujui'],
    ['id' => 4, 'no' => 'PNW/2026/021', 'tanggal' => '2026-08-01', 'status' => 'Disetujui'],
    ['id' => 5, 'no' => 'PNW/2026/022', 'tanggal' => '2026-09-01', 'status' => 'Disetujui'],
    ['id' => 6, 'no' => 'PNW/2026/023', 'tanggal' => '2026-10-01', 'status' => 'Disetujui'],
];
$invoice = [
    ['id' => 1, 'no' => 'INV/2026/014', 'tempo' => '2026-11-01', 'status' => 'Belum dibayar'],
    ['id' => 2, 'no' => 'INV/2026/015', 'tempo' => '2026-11-15', 'status' => 'Lunas'],
    ['id' => 3, 'no' => 'INV/2026/016', 'tempo' => '2026-12-01', 'status' => 'Belum dibayar'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title ?? 'Detail Project') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-slate-800 antialiased min-h-screen">

    <div class="max-w-[1200px] mx-auto px-4 py-6 md:px-6 lg:px-8">

        <!-- Header Halaman -->
        <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <a href="<?= site_url('project') ?>" class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl"><?= $e($project->nama_project) ?></h1>
                    <?= $renderBadge($project->status) ?>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="<?= site_url('project/edit/' . (int) $project->id . '?from=detail') ?>" class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
                </a>
                <form method="post" action="<?= site_url('project/hapus') ?>" onsubmit="return confirm('Hapus project ini secara permanen?');" class="m-0">
                    <?php if ($this->config->item('csrf_protection')): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <?php endif; ?>
                    <input type="hidden" name="id" value="<?= (int) $project->id ?>">
                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition-all hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        Hapus
                    </button>
                </form>
            </div>
        </header>

        <!-- Notifikasi Flashdata -->
        <?php if ($this->session->flashdata('success')): ?>
            <div id="alert-success" class="mb-6 flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm transition-all duration-300">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium text-sm"><?= $this->session->flashdata('success'); ?></span>
                </div>
                <button type="button" onclick="document.getElementById('alert-success').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('error')): ?>
            <div id="alert-error" class="mb-6 flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-sm transition-all duration-300">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
                </div>
                <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>

        <!-- Grid Utama -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

            <!-- ==========================================
                 KOLOM KIRI (2 Kolom)
                 ========================================== -->
            <div class="lg:col-span-2 flex flex-col gap-6">

                <!-- Card: Info Project -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-4 text-lg font-bold text-slate-900">Informasi Project</h2>
                    <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Nama project</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $e($project->nama_project) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Klien</dt>
                            <dd class="text-sm font-semibold text-slate-900">
                                <?php if ($project->nama_klien && $project->klien_id): ?>
                                    <a href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>" class="text-indigo-600 hover:underline"><?= $e($project->nama_klien) ?></a>
                                <?php else: ?>-<?php endif; ?>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Tanggal mulai</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $tgl($project->tanggal_mulai) ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Tanggal selesai</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $tgl($project->tanggal_selesai) ?></dd>
                        </div>
                    </dl>
                    <div class="pt-4 border-t border-slate-100">
                        <dt class="text-xs text-slate-500 mb-1">Deskripsi</dt>
                        <dd class="text-sm text-slate-700 leading-relaxed whitespace-pre-line"><?= $project->deskripsi ? $e($project->deskripsi) : '<span class="text-slate-400 italic">-</span>' ?></dd>
                    </div>
                </section>

                <!-- Card: Dokumen Kerja Sama -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-3 text-lg font-bold text-slate-900">Dokumen kerja sama</h2>
                    <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]" id="table-dokumen">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr class="text-xs font-medium text-slate-500">
                                        <th class="px-4 py-3">Nama dokumen</th>
                                        <th class="px-4 py-3">Jenis</th>
                                        <th class="px-4 py-3">Berlaku sampai</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (empty($dokumen)): ?>
                                        <tr class="empty-row"><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada dokumen.</td></tr>
                                    <?php else: foreach ($dokumen as $d): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <a href="<?= site_url('dokumen/detail/' . (int) $d['id']) ?>" class="font-semibold text-blue-600 hover:underline"><?= $e($d['nama']) ?></a>
                                            </td>
                                            <td class="px-4 py-3 text-slate-700 font-medium"><?= $e($d['jenis']) ?></td>
                                            <td class="px-4 py-3 text-slate-600"><?= $tgl($d['sampai']) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination Container (diisi JS) -->
                        <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                            <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                            <div class="pagination-controls flex items-center gap-1.5"></div>
                        </div>
                    </div>
                </section>

                <!-- Card: Penawaran -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-3 text-lg font-bold text-slate-900">Penawaran</h2>
                    <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]" id="table-penawaran">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr class="text-xs font-medium text-slate-500">
                                        <th class="px-4 py-3">No. Penawaran</th>
                                        <th class="px-4 py-3">Tanggal</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (empty($penawaran)): ?>
                                        <tr class="empty-row"><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada penawaran.</td></tr>
                                    <?php else: foreach ($penawaran as $p): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <a href="<?= site_url('penawaran') ?>" class="font-semibold text-blue-600 hover:underline"><?= $e($p['no']) ?></a>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600"><?= $tgl($p['tanggal']) ?></td>
                                            <td class="px-4 py-3"><?= $renderBadge($p['status']) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                            <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                            <div class="pagination-controls flex items-center gap-1.5"></div>
                        </div>
                    </div>
                </section>

                <!-- Card: Invoice -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-3 text-lg font-bold text-slate-900">Invoice</h2>
                    <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-[13px]" id="table-invoice">
                                <thead class="bg-slate-50 border-b border-slate-200">
                                    <tr class="text-xs font-medium text-slate-500">
                                        <th class="px-4 py-3">No. Invoice</th>
                                        <th class="px-4 py-3">Jatuh tempo</th>
                                        <th class="px-4 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php if (empty($invoice)): ?>
                                        <tr class="empty-row"><td colspan="3" class="px-4 py-8 text-center text-slate-500">Belum ada invoice.</td></tr>
                                    <?php else: foreach ($invoice as $i): ?>
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3">
                                                <a href="<?= site_url('invoice') ?>" class="font-semibold text-blue-600 hover:underline"><?= $e($i['no']) ?></a>
                                            </td>
                                            <td class="px-4 py-3 text-slate-600"><?= $tgl($i['tempo']) ?></td>
                                            <td class="px-4 py-3"><?= $renderBadge($i['status']) ?></td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                            <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                            <div class="pagination-controls flex items-center gap-1.5"></div>
                        </div>
                    </div>
                </section>

            </div>

            <!-- ==========================================
                 KOLOM KANAN (1 Kolom)
                 ========================================== -->
            <div class="flex flex-col gap-6">

                <!-- Card: Status Project (Custom Dropdown) -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-4 text-lg font-bold text-slate-900">Status Project</h2>
                    <form method="post" action="<?= site_url('project/status/' . (int) $project->id) ?>" class="m-0" id="formStatus">
                        <?php if ($this->config->item('csrf_protection')): ?>
                            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                        <?php endif; ?>
                        
                        <label class="block text-xs font-medium text-slate-700 mb-1.5">Ubah Status</label>
                        <div class="relative custom-dropdown mb-4" data-target="status">
                            <button type="button" class="dropdown-btn w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                <span class="dropdown-text truncate"><?= $e($project->status) ?></span>
                                <svg class="w-4 h-4 text-slate-500 flex-shrink-0 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden origin-top transform opacity-0 scale-95 transition-all duration-150">
                                <ul class="dropdown-list py-1 text-sm text-slate-700 max-h-60 overflow-y-auto">
                                    <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                                        <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $project->status === $s ? 'bg-red-50 text-[#9f2b2b] font-medium' : '' ?>" data-value="<?= $s ?>"><?= $s ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <input type="hidden" name="status" id="status" value="<?= $e($project->status) ?>">
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#9f2b2b] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#8a2525] shadow-sm">
                            Simpan status
                        </button>
                    </form>
                </section>

                <!-- Card: Informasi Klien (Terpisah) -->
                <section class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h2 class="mb-4 text-lg font-bold text-slate-900">Informasi Klien</h2>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Klien</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $e($project->nama_klien ?: '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">PIC</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $e($project->nama_pic ?: '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">No. HP</dt>
                            <dd class="text-sm font-semibold text-slate-900"><?= $e($project->no_hp ?: '-') ?></dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-500 mb-0.5">Alamat</dt>
                            <dd class="text-sm font-semibold text-slate-900 leading-snug"><?= $e($project->alamat ?: '-') ?></dd>
                        </div>
                    </dl>
                    <?php if ($project->klien_id): ?>
                        <div class="mt-5 pt-4 border-t border-slate-100">
                            <a href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>" class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:underline">
                                Lihat detail klien
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    <?php endif; ?>
                </section>

            </div>
        </div>
    </div>

    <script>
        // ==========================================
        // 1. PAGINATION (Client-side per tabel)
        // ==========================================
        function setupPagination(tableId, rowsPerPage = 5) {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const wrapper = table.closest('.table-wrapper');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.classList.contains('empty-row'));
            
            // Jika tabel kosong
            if (rows.length === 0) {
                wrapper.querySelector('.pagination-info').textContent = 'Menampilkan 0 data';
                wrapper.querySelector('.pagination-controls').innerHTML = '';
                return;
            }

            let currentPage = 1;
            const totalPages = Math.ceil(rows.length / rowsPerPage);
            
            function render() {
                const start = (currentPage - 1) * rowsPerPage;
                const end = Math.min(start + rowsPerPage, rows.length);
                
                // Sembunyikan semua baris, lalu tampilkan hanya yang masuk halaman aktif
                rows.forEach((r, i) => {
                    r.style.display = (i >= start && i < end) ? '' : 'none';
                });
                
                wrapper.querySelector('.pagination-info').textContent = `Menampilkan ${start+1}–${end} dari ${rows.length} data`;
                
                const c = wrapper.querySelector('.pagination-controls');
                c.innerHTML = '';

                // Tombol Previous
                const prevBtn = document.createElement('button');
                prevBtn.type = 'button';
                prevBtn.className = 'flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
                prevBtn.disabled = currentPage === 1;
                prevBtn.innerHTML = `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>`;
                prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; render(); } };
                c.appendChild(prevBtn);

                // Tombol Angka Halaman
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.type = 'button';
                    pageBtn.className = `flex h-7 min-w-[1.75rem] items-center justify-center rounded-md border text-xs font-semibold transition-colors ${i === currentPage ? 'border-[#9f2b2b] bg-[#9f2b2b] text-white' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'}`;
                    pageBtn.textContent = i;
                    pageBtn.onclick = () => { currentPage = i; render(); };
                    c.appendChild(pageBtn);
                }

                // Tombol Next
                const nextBtn = document.createElement('button');
                nextBtn.type = 'button';
                nextBtn.className = 'flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
                nextBtn.disabled = currentPage === totalPages;
                nextBtn.innerHTML = `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>`;
                nextBtn.onclick = () => { if (currentPage < totalPages) { currentPage++; render(); } };
                c.appendChild(nextBtn);
            }

            render();
        }

        // ==========================================
        // 2. CUSTOM DROPDOWN (untuk Status)
        // ==========================================
        function closeDD(dd) {
            dd.classList.remove('open', 'drop-up');
            const menu = dd.querySelector('.dropdown-menu');
            const arrow = dd.querySelector('.dropdown-btn svg');
            if (!menu) return;
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            if (arrow) arrow.classList.remove('rotate-180');
            setTimeout(() => menu.classList.add('hidden'), 150);
        }

        function openDD(dd) {
            document.querySelectorAll('.custom-dropdown.open').forEach(o => { if (o !== dd) closeDD(o); });
            const menu = dd.querySelector('.dropdown-menu');
            const btn = dd.querySelector('.dropdown-btn');
            const arrow = dd.querySelector('.dropdown-btn svg');
            if (!menu || !btn) return;

            dd.classList.add('open');
            menu.classList.remove('hidden');
            void menu.offsetWidth;
            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
            if (arrow) arrow.classList.add('rotate-180');
        }

        function setDropdownValue(targetId, value, label) {
            const dd = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);
            if (!dd) return;
            const hidden = dd.querySelector('input[type="hidden"]');
            const text = dd.querySelector('.dropdown-text');
            hidden.value = value;
            text.textContent = label;
            
            dd.querySelectorAll('li[data-value]').forEach(li => {
                const isActive = li.dataset.value === String(value);
                li.classList.toggle('bg-red-50', isActive);
                li.classList.toggle('text-[#9f2b2b]', isActive);
                li.classList.toggle('font-medium', isActive);
            });
        }

        document.querySelectorAll('.custom-dropdown').forEach(dd => {
            const btn = dd.querySelector('.dropdown-btn');
            if (!btn) return;

            btn.addEventListener('click', e => {
                e.stopPropagation();
                dd.classList.contains('open') ? closeDD(dd) : openDD(dd);
            });

            dd.addEventListener('click', e => {
                const li = e.target.closest('li[data-value]');
                if (!li) return;
                setDropdownValue(dd.dataset.target, li.dataset.value, li.textContent.trim());
                closeDD(dd);
                btn.focus();
            });
        });

        document.addEventListener('click', e => {
            document.querySelectorAll('.custom-dropdown.open').forEach(dd => {
                if (!dd.contains(e.target)) closeDD(dd);
            });
        });

        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.custom-dropdown.open').forEach(closeDD);
            }
        });

        // ==========================================
        // 3. INISIALISASI SAAT HALAMAN DI-LOAD
        // ==========================================
        document.addEventListener('DOMContentLoaded', () => {
            // Pagination Tabel (3 baris per halaman)
            setupPagination('table-dokumen', 3);
            setupPagination('table-penawaran', 3);
            setupPagination('table-invoice', 3);

            // Auto-hide alert notifikasi setelah 4 detik
            setTimeout(() => {
                ['alert-success', 'alert-error'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) {
                        el.style.opacity = '0';
                        setTimeout(() => el.remove(), 300);
                    }
                });
            }, 4000);
        });
    </script>

</body>
</html>