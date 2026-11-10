<?php
/**
 * Detail Klien — Manajemen Klien
 * Variabel dari controller: $title, $klien, $list_project, $list_dokumen
 */

// Escape output
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

// Format tanggal Indonesia (15 Feb 2025)
$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl   = function ($tanggal) use ($bulan) {
    if (empty($tanggal)) return '-';
    $t = strtotime($tanggal);
    return $t ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

// Gaya badge status
$badge = [
    'Berjalan'      => ['bg-sky-50 text-sky-700 ring-sky-200',             'bg-sky-500'],
    'Selesai'       => ['bg-emerald-50 text-emerald-700 ring-emerald-200', 'bg-emerald-500'],
    'Ditunda'       => ['bg-slate-50 text-slate-700 ring-slate-200',       'bg-slate-400'],
    'Aktif'         => ['bg-sky-50 text-sky-700 ring-sky-200',             'bg-sky-500'],
    'Akan Berakhir' => ['bg-orange-50 text-orange-700 ring-orange-200',    'bg-orange-500'],
    'Kedaluwarsa'   => ['bg-red-50 text-red-700 ring-red-200',             'bg-red-500'],
];

// ---- DATA ASLI dari controller (fallback [] kalau belum diset) ----
$projects = isset($list_project) ? $list_project : [];
$dokumen  = isset($list_dokumen) ? $list_dokumen : [];
// -------------------------------------------------------------------

// Hitung status dokumen dari tanggal_berakhir
// (tabel documents tidak punya kolom status)
$statusDokumen = function ($d) {
    if (empty($d->tanggal_berakhir)) return 'Aktif';
    $today = strtotime(date('Y-m-d'));
    $akhir = strtotime($d->tanggal_berakhir);
    if (!$akhir) return 'Aktif';
    $selisihHari = (int) floor(($akhir - $today) / 86400);
    if ($selisihHari < 0)  return 'Kedaluwarsa';
    if ($selisihHari <= 30) return 'Akan Berakhir';
    return 'Aktif';
};

// Hitung ringkasan
$jmlProject  = count($projects);
$jmlBerjalan = count(array_filter($projects, function ($p) {
    return isset($p->status) && $p->status === 'Berjalan';
}));
$jmlDokumen  = count($dokumen);
$jmlBerakhir = count(array_filter($dokumen, function ($d) use ($statusDokumen) {
    return $statusDokumen($d) === 'Akan Berakhir';
}));

// Komponen kecil: badge status
$renderBadge = function ($status) use ($badge) {
    [$kelas, $dot] = $badge[$status] ?? ['bg-slate-50 text-slate-700 ring-slate-200', 'bg-slate-400'];
    return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset ' . $kelas . '">'
         . '<span class="h-1.5 w-1.5 rounded-full ' . $dot . '"></span>' . htmlspecialchars($status) . '</span>';
};

// Komponen kecil: footer tabel + pagination
$renderFooter = function ($total) {
    $teks = $total > 0 ? 'Menampilkan 1–' . $total . ' dari ' . $total . ' data' : 'Menampilkan 0 data';
    return '<div class="flex shrink-0 items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">'
         . '<p class="text-xs text-slate-500">' . $teks . '</p>'
         . '<div class="flex items-center gap-1.5">'
         . '<button type="button" disabled aria-label="Sebelumnya" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-400 disabled:cursor-not-allowed">'
         . '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg></button>'
         . '<span class="flex h-7 min-w-[1.75rem] items-center justify-center rounded-md bg-indigo-600 px-2 text-xs font-semibold text-white">' . ($total > 0 ? '1' : '0') . '</span>'
         . '<button type="button" disabled aria-label="Berikutnya" class="flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-400 disabled:cursor-not-allowed">'
         . '<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></button>'
         . '</div></div>';
};
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-slate-800 antialiased lg:h-screen lg:overflow-hidden">

    <div class="flex w-full flex-col px-4 py-4 md:px-6 lg:h-full lg:px-8 lg:py-5">

        <!-- Header halaman -->
        <header class="mb-4 flex shrink-0 flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <a href="<?= site_url('klien') ?>"
                   class="mb-1 inline-flex items-center gap-1.5 rounded-md text-sm font-medium text-slate-500 transition-colors hover:text-slate-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali
                </a>
                <h1 class="text-2xl font-semibold tracking-tight text-slate-900 md:text-3xl"><?= $e($klien->nama_klien) ?></h1>
            </div>

            <div class="flex items-center gap-2">
                <a href="<?= site_url('klien/edit/' . $klien->id) ?>"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                    Edit
                </a>
                <button type="button"
                        data-id="<?= (int) $klien->id ?>"
                        data-nama="<?= $e($klien->nama_klien) ?>"
                        onclick="bukaModalHapus(this.dataset.id, this.dataset.nama)"
                        class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition-all hover:-translate-y-0.5 hover:bg-red-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </div>
        </header>

        <!-- Informasi klien & ringkasan -->
        <section class="mb-4 grid shrink-0 grid-cols-1 gap-4 lg:grid-cols-3">

            <!-- Informasi klien -->
            <div class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 lg:col-span-2">
                <h2 class="mb-4 shrink-0 text-base font-semibold text-slate-900">Informasi Klien</h2>
                <dl class="grid flex-1 grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2 lg:grid-cols-3 lg:grid-rows-2 lg:gap-y-0">
                    <div class="flex items-center gap-3 lg:pb-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">Klien</dt>
                            <dd class="mt-0.5 break-words text-sm font-semibold uppercase text-slate-900"><?= $e($klien->nama_klien) ?></dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 lg:pb-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">Klien Sejak</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900"><?= $tgl($klien->tanggal_mulai) ?></dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 lg:pb-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">Nama PIC</dt>
                            <dd class="mt-0.5 break-words text-sm font-semibold text-slate-900"><?= $e($klien->nama_pic) ?></dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 lg:border-t lg:border-slate-100 lg:pt-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">No. HP</dt>
                            <dd class="mt-0.5 text-sm font-semibold text-slate-900"><?= $e($klien->no_hp) ?></dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 lg:border-t lg:border-slate-100 lg:pt-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">Email</dt>
                            <dd class="mt-0.5 break-all text-sm font-semibold text-slate-900"><?php if (!empty($klien->email)): ?>
                                <a href="mailto:<?= $e($klien->email) ?>" class="text-indigo-600 hover:underline"><?= $e($klien->email) ?></a>
                            <?php else: ?>
                                <span class="text-slate-400">-</span>
                            <?php endif; ?></dd>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 lg:border-t lg:border-slate-100 lg:pt-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-500">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <dt class="text-xs text-slate-500">Alamat</dt>
                            <dd class="mt-0.5 text-sm font-semibold leading-snug text-slate-900"><?= !empty($klien->alamat) ? $e($klien->alamat) : '<span class="text-slate-400">-</span>' ?></dd>
                        </div>
                    </div>
                </dl>
            </div>

            <!-- Ringkasan -->
<div class="rounded-2xl border border-slate-200 bg-white p-5">
    <h2 class="mb-3 text-base font-semibold text-slate-900">Ringkasan</h2>
    <div class="grid grid-cols-2 gap-2.5">

        <!-- Total Project -->
        <div class="group relative overflow-hidden rounded-xl bg-indigo-50/60 p-3 ring-1 ring-inset ring-indigo-100/70 transition-colors hover:bg-indigo-50">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-indigo-600/80">Total Project</p>
                <svg class="h-3.5 w-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-slate-900"><?= $jmlProject ?></p>
        </div>

        <!-- Project Berjalan -->
        <div class="group relative overflow-hidden rounded-xl bg-sky-50/60 p-3 ring-1 ring-inset ring-sky-100/70 transition-colors hover:bg-sky-50">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-sky-600/80">Berjalan</p>
                <svg class="h-3.5 w-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-slate-900"><?= $jmlBerjalan ?></p>
        </div>

        <!-- Total Dokumen -->
        <div class="group relative overflow-hidden rounded-xl bg-emerald-50/60 p-3 ring-1 ring-inset ring-emerald-100/70 transition-colors hover:bg-emerald-50">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-emerald-600/80">Total Dokumen</p>
                <svg class="h-3.5 w-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-slate-900"><?= $jmlDokumen ?></p>
        </div>

        <!-- Akan Berakhir -->
        <div class="group relative overflow-hidden rounded-xl bg-amber-50/60 p-3 ring-1 ring-inset ring-amber-100/70 transition-colors hover:bg-amber-50">
            <div class="flex items-start justify-between">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-amber-600/80">Akan Berakhir</p>
                <svg class="h-3.5 w-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-1.5 text-2xl font-bold tabular-nums text-slate-900"><?= $jmlBerakhir ?></p>
        </div>

    </div>
</div>
        </section>

        <!-- Project & dokumen -->
        <section class="grid min-h-0 flex-1 grid-cols-1 gap-5 rounded-2xl border border-slate-200 bg-white p-4 lg:grid-rows-[minmax(0,1fr)_minmax(0,1fr)] xl:grid-cols-2 xl:grid-rows-1">

            <!-- Project -->
            <div class="flex min-h-0 flex-col">
                <h2 class="mb-2 shrink-0 text-base font-semibold text-slate-900">Project</h2>
                <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200">
                    <div class="min-h-0 flex-1 overflow-y-auto overflow-x-auto">
                        <!-- FIX: h-full dihapus agar baris tidak vertikal di tengah saat data sedikit -->
                        <table class="w-full text-left text-[13px]">
                            <thead class="sticky top-0 z-10 h-10 bg-slate-50">
                                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-medium text-slate-500">
                                    <th class="px-4 py-2">Nama Project</th>
                                    <th class="px-4 py-2">Mulai</th>
                                    <th class="px-4 py-2">Selesai</th>
                                    <th class="px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($projects)): ?>
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-slate-500">Belum ada data project.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($projects as $p): ?>
                                    <tr class="transition-colors hover:bg-slate-50">
                                        <!-- FIX: nama project jadi teks biasa, bukan link -->
                                        <td class="px-4 py-2 font-semibold text-slate-900"><?= $e($p->nama_project) ?></td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-600"><?= $e($tgl($p->tanggal_mulai)) ?></td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-600"><?= $e($tgl($p->tanggal_selesai)) ?></td>
                                        <td class="whitespace-nowrap px-4 py-2"><?= $renderBadge($p->status ?: '-') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?= $renderFooter($jmlProject) ?>
                </div>
            </div>

            <!-- Dokumen kerja sama -->
            <div class="flex min-h-0 flex-col">
                <h2 class="mb-2 shrink-0 text-base font-semibold text-slate-900">Dokumen Kerja Sama</h2>
                <div class="flex min-h-0 flex-1 flex-col overflow-hidden rounded-xl border border-slate-200">
                    <div class="min-h-0 flex-1 overflow-y-auto overflow-x-auto">
                        <!-- FIX: h-full dihapus juga agar konsisten -->
                        <table class="w-full text-left text-[13px]">
                            <thead class="sticky top-0 z-10 h-10 bg-slate-50">
                                <tr class="border-b border-slate-200 bg-slate-50 text-xs font-medium text-slate-500">
                                    <th class="px-4 py-2">Nama Dokumen</th>
                                    <th class="px-4 py-2">Project</th>
                                    <th class="px-4 py-2">Jenis</th>
                                    <th class="px-4 py-2">Berakhir</th>
                                    <th class="px-4 py-2">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php if (empty($dokumen)): ?>
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">Belum ada data dokumen.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($dokumen as $d): ?>
                                        <?php $statusDok = $statusDokumen($d); ?>
                                        <tr class="transition-colors hover:bg-slate-50">
                                            <td class="px-4 py-2">
                                                <a href="<?= site_url('dokumen/detail/' . $d->id) ?>" class="font-medium text-indigo-600 hover:underline">
                                                    <?= $e($d->nama_dokumen) ?>
                                                </a>
                                            </td>
                                            <td class="px-4 py-2 font-semibold text-slate-900"><?= $e($d->nama_project ?: '-') ?></td>
                                            <td class="whitespace-nowrap px-4 py-2">
                                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700"><?= $e($d->jenis_dokumen) ?></span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-2 <?= $statusDok === 'Akan Berakhir' ? 'font-medium text-orange-700' : 'text-slate-600' ?>"><?= $e($tgl($d->tanggal_berakhir)) ?></td>
                                            <td class="whitespace-nowrap px-4 py-2"><?= $renderBadge($statusDok) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?= $renderFooter($jmlDokumen) ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Modal hapus -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judulModalHapus">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>
        <div id="modalContent" class="relative z-10 w-full max-w-md scale-95 transform rounded-2xl bg-white p-6 opacity-0 shadow-xl transition-all duration-200">
            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-red-50">
                    <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <h3 id="judulModalHapus" class="text-lg font-semibold text-slate-900">Hapus klien?</h3>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        <strong id="namaKlienHapus" class="font-semibold text-slate-900"></strong>
                        akan dihapus permanen beserta datanya. Tindakan ini tidak dapat dibatalkan.
                    </p>
                </div>
            </div>
            <div class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                <button type="button" onclick="tutupModalHapus()" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Batal</button>
                <button type="button" onclick="eksekusiHapus()" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-500 focus-visible:ring-offset-2">Hapus klien</button>
            </div>
        </div>
    </div>

    <script>
        let hapusId = null;
        const modal = document.getElementById('modalHapus');
        const modalContent = document.getElementById('modalContent');

        function bukaModalHapus(id, nama) {
            hapusId = id;
            document.getElementById('namaKlienHapus').textContent = nama;
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
            }, 200);
        }

        function eksekusiHapus() {
            if (hapusId) {
                window.location.href = '<?= site_url('klien/hapus/') ?>' + hapusId;
            }
        }

        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && !modal.classList.contains('hidden')) tutupModalHapus();
        });
    </script>
</body>
</html>