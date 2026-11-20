<?php
/**
 * Detail Project — Halaman scroll (min-h-screen).
 * Pagination di tiap tabel (client-side).
 * Custom dropdown untuk Status Project.
 * + Notifikasi Toast & Modal Hapus Kustom.
 * Data dokumen, penawaran, dan invoice diambil dari database.
 */

$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$tgl = function ($tanggal) use ($bulan) {
    if (empty($tanggal)) return '-';

    $t = strtotime($tanggal);

    return $t
        ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t)
        : '-';
};

// Pastikan data tersedia dari controller
$dokumen  = isset($dokumen) ? $dokumen : [];
$penawaran = isset($penawaran) ? $penawaran : [];
$invoice   = isset($invoice) ? $invoice : [];

// Badge status
$badge = [
    'Draft'         => ['bg-slate-100 text-slate-700 ring-slate-200',       'bg-slate-500'],
    'Berjalan'      => ['bg-sky-50 text-sky-700 ring-sky-200',              'bg-sky-500'],
    'Selesai'       => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Batal'          => ['bg-rose-50 text-rose-700 ring-rose-200',           'bg-rose-500'],
    'Disetujui'     => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Belum Dibayar' => ['bg-orange-50 text-orange-700 ring-orange-200',     'bg-orange-500'],
    'Lunas'         => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Dikirim'       => ['bg-sky-50 text-sky-700 ring-sky-200',              'bg-sky-500'],
];

$renderBadge = function ($status) use ($badge) {
    $status = trim((string) $status);

    if ($status === '') {
        return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset bg-slate-100 text-slate-500 ring-slate-200"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>-</span>';
    }

    $key = ucwords(strtolower($status));

    [$kelas, $dot] = $badge[$key] ?? [
        'bg-slate-200 text-slate-700 ring-slate-300',
        'bg-slate-500'
    ];

    return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $kelas . '">'
        . '<span class="h-1.5 w-1.5 rounded-full ' . $dot . '"></span>'
        . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
        . '</span>';
};
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

    <!-- HEADER -->
    <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

        <div>
            <a href="<?= site_url('project') ?>"
               class="mb-2 inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900">

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>

                Kembali
            </a>

            <div class="flex flex-wrap items-center gap-3">

                <h1 class="text-2xl font-bold tracking-tight text-slate-900 md:text-3xl">
                    <?= $e($project->nama_project) ?>
                </h1>

                <?= $renderBadge($project->status) ?>

            </div>
        </div>

        <div class="flex items-center gap-3">

            <a href="<?= site_url('project/edit/' . (int) $project->id . '?from=detail') ?>"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition-all hover:bg-slate-50">

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                </svg>

                Edit
            </a>

            <button type="button"
                    onclick="bukaModalHapus()"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition-all hover:bg-red-50">

                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>

                Hapus
            </button>

        </div>
    </header>

    <!-- GRID UTAMA -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">

        <!-- KOLOM KIRI -->
        <div class="lg:col-span-2 flex flex-col gap-6">

            <!-- INFORMASI PROJECT -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <h2 class="mb-4 text-lg font-bold text-slate-900">
                    Informasi Project
                </h2>

                <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Nama project</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $e($project->nama_project) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Klien</dt>

                        <dd class="text-sm font-semibold text-slate-900">

                            <?php if ($project->nama_klien && $project->klien_id): ?>

                                <a href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>"
                                   class="text-blue-600 hover:underline">

                                    <?= $e($project->nama_klien) ?>

                                </a>

                            <?php else: ?>

                                -

                            <?php endif; ?>

                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Tanggal mulai</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $tgl($project->tanggal_mulai) ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Tanggal selesai</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $tgl($project->tanggal_selesai) ?>
                        </dd>
                    </div>

                </dl>

                <div class="pt-4 border-t border-slate-100">

                    <dt class="text-xs text-slate-500 mb-1">Deskripsi</dt>

                    <dd class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">

                        <?= $project->deskripsi
                            ? $e($project->deskripsi)
                            : '<span class="text-slate-400 italic">-</span>' ?>

                    </dd>

                </div>

            </section>

            <!-- DOKUMEN KERJA SAMA -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <div class="flex items-center justify-between mb-3">

                    <h2 class="text-lg font-bold text-slate-900">
                        Dokumen kerja sama
                    </h2>

                    <a href="<?= site_url('dokumen/tambah?project_id=' . (int) $project->id) ?>"
                       class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        Tambah
                    </a>

                </div>

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

                                    <tr class="empty-row">
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                            Belum ada dokumen untuk project ini.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($dokumen as $d): ?>

                                        <tr class="hover:bg-slate-50 transition-colors">

                                            <td class="px-4 py-3">

                                                <a href="<?= site_url('dokumen/detail/' . (int) $d['id']) ?>"
                                                   class="font-semibold text-blue-600 hover:underline">

                                                    <?= $e($d['nama']) ?>

                                                </a>

                                            </td>

                                            <td class="px-4 py-3 text-slate-700 font-medium">
                                                <?= $e($d['jenis'] ?? '-') ?>
                                            </td>

                                            <td class="px-4 py-3 text-slate-600">
                                                <?= $tgl($d['sampai'] ?? null) ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">

                        <p class="text-xs text-slate-500 pagination-info">
                            Menampilkan 0 data
                        </p>

                        <div class="pagination-controls flex items-center gap-1.5"></div>

                    </div>

                </div>

            </section>

            <!-- PENAWARAN -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <div class="flex items-center justify-between mb-3">

                    <h2 class="text-lg font-bold text-slate-900">
                        Penawaran
                    </h2>

                    <a href="<?= site_url('pembayaran/tambah/penawaran?project_id=' . (int) $project->id . '&from=project_detail') ?>"
                       class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        Tambah
                    </a>

                </div>

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

                                    <tr class="empty-row">
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                            Belum ada penawaran untuk project ini.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($penawaran as $p): ?>

                                        <tr class="hover:bg-slate-50 transition-colors">

                                            <td class="px-4 py-3">

                                                <a href="<?= site_url('penawaran/detail/' . (int) $p['id']) ?>"
                                                   class="font-semibold text-blue-600 hover:underline">

                                                    <?= $e($p['no']) ?>

                                                </a>

                                            </td>

                                            <td class="px-4 py-3 text-slate-600">
                                                <?= $tgl($p['tanggal'] ?? null) ?>
                                            </td>

                                            <td class="px-4 py-3">
                                                <?= $renderBadge($p['status'] ?? '') ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">

                        <p class="text-xs text-slate-500 pagination-info">
                            Menampilkan 0 data
                        </p>

                        <div class="pagination-controls flex items-center gap-1.5"></div>

                    </div>

                </div>

            </section>

            <!-- INVOICE -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <div class="flex items-center justify-between mb-3">

                    <h2 class="text-lg font-bold text-slate-900">
                        Invoice
                    </h2>

                    <a href="<?= site_url('pembayaran/tambah/invoice?project_id=' . (int) $project->id . '&from=project_detail') ?>"
                       class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">

                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4v16m8-8H4"/>
                        </svg>

                        Tambah
                    </a>

                </div>

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

                                    <tr class="empty-row">
                                        <td colspan="3" class="px-4 py-8 text-center text-slate-500">
                                            Belum ada invoice untuk project ini.
                                        </td>
                                    </tr>

                                <?php else: ?>

                                    <?php foreach ($invoice as $i): ?>

                                        <tr class="hover:bg-slate-50 transition-colors">

                                            <td class="px-4 py-3">

                                                <a href="<?= site_url('invoice/detail/' . (int) $i['id']) ?>"
                                                   class="font-semibold text-blue-600 hover:underline">

                                                    <?= $e($i['no']) ?>

                                                </a>

                                            </td>

                                            <td class="px-4 py-3 text-slate-600">
                                                <?= $tgl($i['tempo'] ?? null) ?>
                                            </td>

                                            <td class="px-4 py-3">
                                                <?= $renderBadge($i['status'] ?? '') ?>
                                            </td>

                                        </tr>

                                    <?php endforeach; ?>

                                <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                    <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">

                        <p class="text-xs text-slate-500 pagination-info">
                            Menampilkan 0 data
                        </p>

                        <div class="pagination-controls flex items-center gap-1.5"></div>

                    </div>

                </div>

            </section>

        </div>

        <!-- KOLOM KANAN -->
        <div class="flex flex-col gap-6">

            <!-- STATUS PROJECT -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <h2 class="mb-4 text-lg font-bold text-slate-900">
                    Status Project
                </h2>

                <form method="post"
                      action="<?= site_url('project/status/' . (int) $project->id) ?>"
                      class="m-0"
                      id="formStatus">

                    <?php if ($this->config->item('csrf_protection')): ?>

                        <input type="hidden"
                               name="<?= $this->security->get_csrf_token_name() ?>"
                               value="<?= $this->security->get_csrf_hash() ?>">

                    <?php endif; ?>

                    <label class="block text-xs font-medium text-slate-700 mb-1.5">
                        Ubah Status
                    </label>

                    <div class="relative custom-dropdown mb-4" data-target="status">

                        <button type="button"
                                class="dropdown-btn w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">

                            <span class="dropdown-text truncate">
                                <?= $e($project->status) ?>
                            </span>

                            <svg class="w-4 h-4 text-slate-500 flex-shrink-0 pointer-events-none transition-transform duration-200"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">

                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M19 9l-7 7-7-7"/>

                            </svg>

                        </button>

                        <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-xl overflow-hidden origin-top transform opacity-0 scale-95 transition-all duration-150">

                            <ul class="dropdown-list py-1 text-sm text-slate-700 max-h-60 overflow-y-auto">

                                <?php foreach (['Draft', 'Berjalan', 'Selesai', 'Batal'] as $s): ?>

                                    <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $project->status === $s ? 'bg-red-50 text-[#9f2b2b] font-medium' : '' ?>"
                                        data-value="<?= $e($s) ?>">

                                        <?= $e($s) ?>

                                    </li>

                                <?php endforeach; ?>

                            </ul>

                        </div>

                        <input type="hidden"
                               name="status"
                               id="status"
                               value="<?= $e($project->status) ?>">

                    </div>

                    <button type="submit"
                            class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#9f2b2b] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#8a2525] shadow-sm">

                        Simpan status

                    </button>

                </form>

            </section>

            <!-- INFORMASI KLIEN -->
            <section class="bg-white rounded-2xl border border-slate-200 p-5">

                <h2 class="mb-4 text-lg font-bold text-slate-900">
                    Informasi Klien
                </h2>

                <dl class="space-y-4">

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Klien</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $e($project->nama_klien ?: '-') ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">PIC</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $e($project->nama_pic ?: '-') ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">No. HP</dt>

                        <dd class="text-sm font-semibold text-slate-900">
                            <?= $e($project->no_hp ?: '-') ?>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-slate-500 mb-0.5">Alamat</dt>

                        <dd class="text-sm font-semibold text-slate-900 leading-snug">
                            <?= $e($project->alamat ?: '-') ?>
                        </dd>
                    </div>

                </dl>

                <?php if ($project->klien_id): ?>

                    <div class="mt-5 pt-4 border-t border-slate-100">

                        <a href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>"
                           class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 hover:underline">

                            Lihat detail klien

                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 5l7 7-7 7"/>
                            </svg>

                        </a>

                    </div>

                <?php endif; ?>

            </section>

        </div>

    </div>

</div>

<!-- MODAL HAPUS -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">

    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
         onclick="tutupModalHapus()"></div>

    <div id="modalContent"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">

        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">

            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.667.192 3 1.732 3z"/>

            </svg>

        </div>

        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">
            Hapus Project?
        </h3>

        <p class="text-center text-gray-500 mb-6 text-sm">

            Apakah Anda yakin ingin menghapus project?

            <br>

            <strong class="text-gray-900 text-base">
                <?= $e($project->nama_project) ?>
            </strong>

            <br>

            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">
                Tindakan ini tidak dapat dibatalkan.
            </span>

        </p>

        <form method="post"
              action="<?= site_url('project/hapus') ?>"
              class="flex justify-center gap-3">

            <?php if ($this->config->item('csrf_protection')): ?>

                <input type="hidden"
                       name="<?= $this->security->get_csrf_token_name() ?>"
                       value="<?= $this->security->get_csrf_hash() ?>">

            <?php endif; ?>

            <input type="hidden" name="id" value="<?= (int) $project->id ?>">

            <button type="button"
                    onclick="tutupModalHapus()"
                    class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">

                Batal

            </button>

            <button type="submit"
                    class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm">

                Ya, Hapus

            </button>

        </form>

    </div>

</div>

<!-- TOAST -->
<div id="toastContainer"
     class="fixed top-5 right-5 z-[60] flex flex-col gap-2 pointer-events-none">
</div>

<style>
    @keyframes toastIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes toastOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }

    .toast-item {
        animation: toastIn 0.3s ease-out;
    }

    .toast-item.hide {
        animation: toastOut 0.3s ease-in forwards;
    }
</style>

<script>

// =========================================
// 1. TOAST
// =========================================

const TOAST_CFG = {
    success: {
        bg: 'bg-emerald-50 border-emerald-200 text-emerald-800',
        ic: 'text-emerald-500',
        d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
    },

    error: {
        bg: 'bg-red-50 border-red-200 text-red-800',
        ic: 'text-red-500',
        d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    },

    info: {
        bg: 'bg-blue-50 border-blue-200 text-blue-800',
        ic: 'text-blue-500',
        d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
    }
};

function showToast(msg, type = 'success') {

    const c = TOAST_CFG[type] || TOAST_CFG.info;

    const el = document.createElement('div');

    el.className = `toast-item pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg max-w-sm ${c.bg}`;

    const icon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    icon.setAttribute('class', `w-5 h-5 flex-shrink-0 ${c.ic}`);
    icon.setAttribute('fill', 'none');
    icon.setAttribute('stroke', 'currentColor');
    icon.setAttribute('viewBox', '0 0 24 24');

    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('stroke-linecap', 'round');
    path.setAttribute('stroke-linejoin', 'round');
    path.setAttribute('stroke-width', '2');
    path.setAttribute('d', c.d);

    icon.appendChild(path);

    const text = document.createElement('span');
    text.className = 'text-sm font-medium flex-1';
    text.textContent = msg;

    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'opacity-60 hover:opacity-100 flex-shrink-0';
    close.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>`;
    close.onclick = () => el.remove();

    el.appendChild(icon);
    el.appendChild(text);
    el.appendChild(close);

    document.getElementById('toastContainer').appendChild(el);

    setTimeout(() => {
        el.classList.add('hide');
        setTimeout(() => el.remove(), 300);
    }, 3000);
}

// Flashdata toast
document.addEventListener('DOMContentLoaded', () => {

    <?php if ($this->session->flashdata('success')): ?>
        showToast(<?= json_encode($this->session->flashdata('success')) ?>, 'success');
    <?php endif; ?>

    <?php if ($this->session->flashdata('error')): ?>
        showToast(<?= json_encode(strip_tags($this->session->flashdata('error'))) ?>, 'error');
    <?php endif; ?>

});

// =========================================
// 2. MODAL HAPUS
// =========================================

const modal = document.getElementById('modalHapus');
const modalContent = document.getElementById('modalContent');

function bukaModalHapus() {

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        modalContent.classList.remove('scale-95', 'opacity-0');
        modalContent.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function tutupModalHapus() {

    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }, 300);
}

document.addEventListener('keydown', (ev) => {

    if (ev.key === 'Escape' && !modal.classList.contains('hidden')) {
        tutupModalHapus();
    }

});

// =========================================
// 3. PAGINATION
// =========================================

function setupPagination(tableId, rowsPerPage = 3) {

    const table = document.getElementById(tableId);

    if (!table) return;

    const wrapper = table.closest('.table-wrapper');
    const tbody = table.querySelector('tbody');

    const rows = Array.from(tbody.querySelectorAll('tr'))
        .filter(tr => !tr.classList.contains('empty-row'));

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

        rows.forEach((r, i) => {
            r.style.display = (i >= start && i < end) ? '' : 'none';
        });

        wrapper.querySelector('.pagination-info').textContent =
            `Menampilkan ${start + 1}–${end} dari ${rows.length} data`;

        const c = wrapper.querySelector('.pagination-controls');

        c.innerHTML = '';

        // Tombol sebelumnya
        const prevBtn = document.createElement('button');

        prevBtn.type = 'button';
        prevBtn.className = 'flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';

        prevBtn.disabled = currentPage === 1;

        prevBtn.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        `;

        prevBtn.onclick = () => {
            if (currentPage > 1) {
                currentPage--;
                render();
            }
        };

        c.appendChild(prevBtn);

        // Nomor halaman
        for (let i = 1; i <= totalPages; i++) {

            const pageBtn = document.createElement('button');

            pageBtn.type = 'button';

            pageBtn.className = `flex h-7 min-w-[1.75rem] items-center justify-center rounded-md border text-xs font-semibold transition-colors ${
                i === currentPage
                    ? 'border-[#9f2b2b] bg-[#9f2b2b] text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
            }`;

            pageBtn.textContent = i;

            pageBtn.onclick = () => {
                currentPage = i;
                render();
            };

            c.appendChild(pageBtn);
        }

        // Tombol selanjutnya
        const nextBtn = document.createElement('button');

        nextBtn.type = 'button';

        nextBtn.className = 'flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';

        nextBtn.disabled = currentPage === totalPages;

        nextBtn.innerHTML = `
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        `;

        nextBtn.onclick = () => {
            if (currentPage < totalPages) {
                currentPage++;
                render();
            }
        };

        c.appendChild(nextBtn);
    }

    render();
}

// =========================================
// 4. CUSTOM DROPDOWN STATUS
// =========================================

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

    document.querySelectorAll('.custom-dropdown.open')
        .forEach(o => {
            if (o !== dd) closeDD(o);
        });

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

        dd.classList.contains('open')
            ? closeDD(dd)
            : openDD(dd);

    });

    dd.addEventListener('click', e => {

        const li = e.target.closest('li[data-value]');

        if (!li) return;

        setDropdownValue(
            dd.dataset.target,
            li.dataset.value,
            li.textContent.trim()
        );

        closeDD(dd);
        btn.focus();

    });

});

document.addEventListener('click', e => {

    document.querySelectorAll('.custom-dropdown.open')
        .forEach(dd => {
            if (!dd.contains(e.target)) closeDD(dd);
        });

});

document.addEventListener('keydown', e => {

    if (e.key === 'Escape') {
        document.querySelectorAll('.custom-dropdown.open')
            .forEach(closeDD);
    }

});

// =========================================
// 5. INISIALISASI
// =========================================

document.addEventListener('DOMContentLoaded', () => {

    setupPagination('table-dokumen', 3);
    setupPagination('table-penawaran', 3);
    setupPagination('table-invoice', 3);

});

</script>

</body>
</html>