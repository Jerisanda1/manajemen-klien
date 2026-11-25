<?php
/**
 * Detail Project — Halaman scroll (min-h-screen).
 * Pagination di tiap tabel (client-side).
 * Custom dropdown untuk Status Project.
 * + Notifikasi Toast & Modal Hapus Kustom (project, dokumen, penawaran, invoice).
 * + Smart Back Button — kembali ke halaman asal (lihat partials/smart_back.php).
 * Data dokumen, penawaran, dan invoice diambil dari database.
 *
 * Tata letak:
 *  - Baris 1 : Informasi Project (kiri) + Status Project (kanan)
 *  - Baris 2 : Dokumen kerja sama (lebar penuh)
 *  - Baris 3 : Penawaran (kiri) + Invoice (kanan)
 *
 * Tabel Dokumen Kerja Sama:
 *  - Ikon lokasi penyimpanan (Server Sistem / Perangkat Ini / Perangkat Lain)
 *  - Nama dokumen = pratinjau file; ikon mata di Aksi = halaman detail
 *  - Kolom Status dihitung dari tanggal berakhir (Aktif / Akan Berakhir / Expired)
 *  - Sinkron ke server hanya untuk dokumen yang masih di perangkat ini
 *
 * Field tambahan yang dibutuhkan di setiap baris $dokumen:
 *   storage_type, device_id, file_name, file_hash
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

$dokumen   = isset($dokumen) ? $dokumen : [];
$penawaran = isset($penawaran) ? $penawaran : [];
$invoice   = isset($invoice) ? $invoice : [];

// Token CSRF & identitas perangkat untuk modul dokumen (sama seperti halaman Dokumen).
// Dipakai bila controller belum mengirimkannya.
if (empty($documentCsrfToken)) {
    $documentCsrfToken = $this->session->userdata('dokumen_csrf_token');
    if (!$documentCsrfToken) {
        $documentCsrfToken = bin2hex(random_bytes(32));
        $this->session->set_userdata('dokumen_csrf_token', $documentCsrfToken);
    }
}
if (!isset($currentDeviceId)) {
    $currentDeviceId = (string) $this->session->userdata('dokumen_device_id');
    if ($currentDeviceId === '') {
        $cookieDeviceId = strtolower((string) $this->input->cookie('tridatu_device_id', TRUE));
        if (preg_match('/\A[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}\z/', $cookieDeviceId)) {
            $currentDeviceId = $cookieDeviceId;
            $this->session->set_userdata('dokumen_device_id', $currentDeviceId);
        }
    }
}

$badge = [
    'Draft'         => ['bg-slate-100 text-slate-700 ring-slate-200',       'bg-slate-500'],
    'Berjalan'      => ['bg-sky-50 text-sky-700 ring-sky-200',              'bg-sky-500'],
    'Selesai'       => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Batal'         => ['bg-rose-50 text-rose-700 ring-rose-200',           'bg-rose-500'],
    'Disetujui'     => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Belum Dibayar' => ['bg-orange-50 text-orange-700 ring-orange-200',     'bg-orange-500'],
    'Lunas'         => ['bg-emerald-50 text-emerald-700 ring-emerald-200',  'bg-emerald-500'],
    'Dikirim'       => ['bg-sky-50 text-sky-700 ring-sky-200',              'bg-sky-500'],

    // Status dokumen kerja sama
    'Aktif'         => ['bg-sky-50 text-sky-700 ring-sky-200',              'bg-sky-500'],
    'Akan Berakhir' => ['bg-orange-50 text-orange-700 ring-orange-200',     'bg-orange-500'],
    'Expired'       => ['bg-rose-50 text-rose-700 ring-rose-200',           'bg-rose-500'],
];

$renderBadge = function ($status) use ($badge) {
    $status = trim((string) $status);
    if ($status === '') {
        return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset bg-slate-100 text-slate-500 ring-slate-200"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>-</span>';
    }
    $key = ucwords(strtolower($status));
    [$kelas, $dot] = $badge[$key] ?? ['bg-slate-200 text-slate-700 ring-slate-300', 'bg-slate-500'];
    return '<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ' . $kelas . '">'
        . '<span class="h-1.5 w-1.5 rounded-full ' . $dot . '"></span>'
        . htmlspecialchars($key, ENT_QUOTES, 'UTF-8')
        . '</span>';
};

// Status dokumen kerja sama dari tanggal berakhir
// (aturan sama dengan halaman detail dokumen)
$statusDokumen = function ($tanggalBerakhir) {
    if (empty($tanggalBerakhir)) return 'Aktif';
    $akhir = strtotime($tanggalBerakhir);
    if (!$akhir) return 'Aktif';

    $selisihHari = (int) floor(($akhir - strtotime(date('Y-m-d'))) / 86400);
    if ($selisihHari < 0)   return 'Expired';
    if ($selisihHari <= 30) return 'Akan Berakhir';
    return 'Aktif';
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
            <!-- SMART BACK BUTTON (href = fallback bila asal tidak diketahui) -->
            <a href="<?= site_url('project') ?>"
               id="backBtn"
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

    <!-- BARIS 1: INFORMASI PROJECT + STATUS PROJECT -->
    <div class="dp-grid-top mb-6">

        <!-- INFORMASI PROJECT -->
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Informasi Project</h2>

            <dl class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                <div class="min-w-0">
                    <dt class="text-xs text-slate-500 mb-0.5">Nama project</dt>
                    <dd class="text-sm font-semibold text-slate-900 break-words"><?= $e($project->nama_project) ?></dd>
                </div>

                <div class="min-w-0">
                    <dt class="text-xs text-slate-500 mb-0.5">Klien</dt>
                    <dd class="text-sm font-semibold text-slate-900 break-words">
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

                <div class="min-w-0">
                    <dt class="text-xs text-slate-500 mb-0.5">Tanggal mulai</dt>
                    <dd class="text-sm font-semibold text-slate-900"><?= $tgl($project->tanggal_mulai) ?></dd>
                </div>

                <div class="min-w-0">
                    <dt class="text-xs text-slate-500 mb-0.5">Tanggal selesai</dt>
                    <dd class="text-sm font-semibold text-slate-900"><?= $tgl($project->tanggal_selesai) ?></dd>
                </div>
            </dl>

            <div class="pt-4 border-t border-slate-100">
                <dt class="text-xs text-slate-500 mb-1">Deskripsi</dt>
                <dd class="text-sm text-slate-700 leading-relaxed whitespace-pre-line break-words">
                    <?= $project->deskripsi
                        ? $e($project->deskripsi)
                        : '<span class="text-slate-400 italic">-</span>' ?>
                </dd>
            </div>
        </section>

        <!-- STATUS PROJECT -->
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <h2 class="mb-4 text-lg font-bold text-slate-900">Status Project</h2>

            <div class="mb-4 flex items-center justify-between rounded-lg bg-slate-50 px-3 py-2.5">
                <span class="text-xs text-slate-500">Status saat ini</span>
                <?= $renderBadge($project->status) ?>
            </div>

            <form method="post"
                  action="<?= site_url('project/status/' . (int) $project->id) ?>"
                  class="m-0"
                  id="formStatus">

                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden"
                           name="<?= $this->security->get_csrf_token_name() ?>"
                           value="<?= $this->security->get_csrf_hash() ?>">
                <?php endif; ?>

                <label class="block text-xs font-medium text-slate-700 mb-1.5">Ubah Status</label>

                <div class="relative custom-dropdown mb-4" data-target="status">
                    <button type="button"
                            class="dropdown-btn w-full px-3 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                        <span class="dropdown-text truncate"><?= $e($project->status) ?></span>
                        <svg class="w-4 h-4 text-slate-500 flex-shrink-0 pointer-events-none transition-transform duration-200"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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

                    <input type="hidden" name="status" id="status" value="<?= $e($project->status) ?>">
                </div>

                <button type="submit"
                        class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-[#9f2b2b] px-4 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-[#8a2525] shadow-sm">
                    Simpan status
                </button>
            </form>
        </section>

    </div>

    <!-- BARIS 2: DOKUMEN KERJA SAMA (lebar penuh) -->
    <section class="bg-white rounded-2xl border border-slate-200 p-5 mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-bold text-slate-900">Dokumen kerja sama</h2>
            <a href="<?= site_url('dokumen/tambah?project_id=' . (int) $project->id) ?>"
               class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah
            </a>
        </div>

        <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
            <div class="overflow-x-auto">
                <table class="tbl-det w-full text-left text-[13px]" id="table-dokumen" style="min-width: 720px;">
                    <colgroup>
                        <col>                          <!-- Nama dokumen (sisa ruang) -->
                        <col style="width: 150px;">    <!-- Jenis -->
                        <col style="width: 120px;">    <!-- Berlaku sampai -->
                        <col style="width: 140px;">    <!-- Status -->
                        <col style="width: 134px;">    <!-- Aksi (tetap) -->
                    </colgroup>
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr class="text-xs font-medium text-slate-500">
                            <th>Nama dokumen</th>
                            <th>Jenis</th>
                            <th>Berlaku sampai</th>
                            <th>Status</th>
                            <th class="text-center col-aksi">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($dokumen)): ?>
                            <tr class="empty-row">
                                <td colspan="5" class="text-center text-slate-500">
                                    Belum ada dokumen untuk project ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($dokumen as $d): ?>
                                <?php
                                    $storageType  = $d['storage_type'] ?? 'cloud';
                                    $rowDeviceId  = $d['device_id'] ?? '';
                                    $isCloud      = ($storageType === 'cloud');
                                    $isOwnLocal   = (!$isCloud && $rowDeviceId !== '' && $rowDeviceId === $currentDeviceId);
                                    $isOtherLocal = (!$isCloud && !$isOwnLocal);

                                    if ($isCloud)         { $lokasiLabel = 'Tersimpan di Server Sistem'; }
                                    elseif ($isOwnLocal)  { $lokasiLabel = 'Tersimpan di Perangkat Ini'; }
                                    else                  { $lokasiLabel = 'Tersimpan di Perangkat Lain'; }
                                ?>
                                <tr class="hover:bg-slate-50 transition-colors">

                                    <!-- Nama dokumen + ikon lokasi penyimpanan -->
                                    <td>
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="flex-shrink-0 <?= $isOtherLocal ? 'text-slate-400' : 'text-blue-500' ?>" title="<?= $e($lokasiLabel) ?>">
                                                <?php if ($isCloud): ?>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                                <?php else: ?>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                                <?php endif; ?>
                                            </span>

                                            <?php if ($isOtherLocal): ?>
                                                <span class="min-w-0 truncate cell-ellipsis font-semibold text-slate-400 cursor-not-allowed" title="Terkunci di Perangkat Lain"><?= $e($d['nama']) ?></span>

                                            <?php elseif ($isCloud): ?>
                                                <!-- Dokumen cloud: pratinjau langsung dari server di tab baru -->
                                                <a href="<?= site_url('dokumen/preview/' . (int) $d['id']) ?>" target="_blank" rel="noopener"
                                                   class="min-w-0 truncate cell-ellipsis font-semibold text-blue-600 hover:underline"
                                                   title="Pratinjau Dokumen"><?= $e($d['nama']) ?></a>

                                            <?php else: ?>
                                                <!-- Dokumen lokal (perangkat ini): pratinjau dibaca dari folder lokal via TridatuFS -->
                                                <button type="button"
                                                    class="btn-preview-dok min-w-0 truncate cell-ellipsis text-left font-semibold text-blue-600 hover:underline"
                                                    data-klien-id="<?= (int) ($project->klien_id ?? 0) ?>"
                                                    data-klien-nama="<?= $e($project->nama_klien ?? '') ?>"
                                                    data-file-name="<?= $e($d['file_name'] ?? '') ?>"
                                                    data-file-hash="<?= $e($d['file_hash'] ?? '') ?>"
                                                    title="Pratinjau Dokumen"><?= $e($d['nama']) ?></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>

                                    <td class="cell-ellipsis text-slate-700 font-medium" title="<?= $e($d['jenis'] ?? '-') ?>"><?= $e($d['jenis'] ?? '-') ?></td>
                                    <td class="whitespace-nowrap text-slate-600"><?= $tgl($d['sampai'] ?? null) ?></td>

                                    <!-- Status dokumen (berdasarkan tanggal berakhir) -->
                                    <td class="whitespace-nowrap"><?= $renderBadge($statusDokumen($d['sampai'] ?? null)) ?></td>

                                    <!-- Aksi -->
                                    <td class="whitespace-nowrap text-center col-aksi">
                                        <div class="flex flex-nowrap justify-center items-center gap-1">

                                            <!-- Detail (ikon mata) -->
                                            <?php if (!$isOtherLocal): ?>
                                                <a href="<?= site_url('dokumen/detail/' . (int) $d['id']) ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Lihat Detail">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </a>
                                            <?php else: ?>
                                                <span class="aksi-btn bg-white border border-slate-200 text-slate-300 rounded-lg cursor-not-allowed" title="Terkunci di Perangkat Lain">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </span>
                                            <?php endif; ?>

                                            <!-- Sinkron ke server (hanya dokumen yang masih di perangkat ini) -->
                                            <?php if ($isOwnLocal): ?>
                                                <button
                                                    type="button"
                                                    data-sync-id="<?= (int) $d['id'] ?>"
                                                    data-sync-name="<?= $e($d['file_name'] ?? '') ?>"
                                                    data-klien-id="<?= (int) ($project->klien_id ?? 0) ?>"
                                                    data-klien-nama="<?= $e($project->nama_klien ?? '') ?>"
                                                    data-file-hash="<?= $e($d['file_hash'] ?? '') ?>"
                                                    class="btn-sync-dok aksi-btn bg-white border border-blue-300 text-blue-600 hover:bg-blue-50 hover:border-blue-400 rounded-lg transition-all shadow-sm"
                                                    title="Unggah ke Cloud">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                </button>
                                            <?php elseif ($isOtherLocal): ?>
                                                <span class="aksi-btn bg-white border border-slate-200 text-slate-300 rounded-lg cursor-not-allowed" title="Terkunci di Perangkat Lain">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </span>
                                            <?php endif; ?>

                                            <!-- Edit -->
                                            <?php if (!$isOtherLocal): ?>
                                                <a href="<?= site_url('dokumen/edit/' . (int) $d['id']) ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </a>
                                            <?php else: ?>
                                                <span class="aksi-btn bg-white border border-slate-200 text-slate-300 rounded-lg cursor-not-allowed" title="Tidak dapat diedit dari perangkat ini">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                                </span>
                                            <?php endif; ?>

                                            <!-- Hapus -->
                                            <button
                                                type="button"
                                                class="btn-hapus-dok aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                                data-id="<?= (int) $d['id'] ?>"
                                                data-nama="<?= $e($d['nama'] ?? '') ?>"
                                                data-is-own-local="<?= $isOwnLocal ? '1' : '0' ?>"
                                                data-klien-id="<?= (int) ($project->klien_id ?? 0) ?>"
                                                data-klien-nama="<?= $e($project->nama_klien ?? '') ?>"
                                                data-file-name="<?= $e($d['file_name'] ?? '') ?>"
                                                data-file-hash="<?= $e($d['file_hash'] ?? '') ?>"
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

            <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                <div class="pagination-controls flex items-center gap-1.5"></div>
            </div>
        </div>

        <!-- Keterangan ikon lokasi penyimpanan -->
        <div class="mt-3 flex flex-wrap items-center gap-x-5 gap-y-1.5 text-xs text-slate-500">
            <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>Server Sistem</div>
            <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Ini</div>
            <div class="flex items-center gap-1.5"><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>Perangkat Lain</div>
        </div>
    </section>

    <!-- BARIS 3: PENAWARAN + INVOICE (berdampingan) -->
    <div class="dp-grid-pair">

        <!-- PENAWARAN -->
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-slate-900">Penawaran</h2>
                <a href="<?= site_url('pembayaran/tambah/penawaran?project_id=' . (int) $project->id . '&from=project_detail') ?>"
                   class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah
                </a>
            </div>

            <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="tbl-det w-full text-left text-[13px]" id="table-penawaran" style="min-width: 440px;">
                        <colgroup>
                            <col>                          <!-- No. Penawaran (sisa ruang) -->
                            <col style="width: 110px;">    <!-- Tanggal -->
                            <col style="width: 145px;">    <!-- Status -->
                            <col style="width: 104px;">    <!-- Aksi (tetap) -->
                        </colgroup>
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-xs font-medium text-slate-500">
                                <th>No. Penawaran</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th class="text-center col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($penawaran)): ?>
                                <tr class="empty-row">
                                    <td colspan="4" class="text-center text-slate-500">
                                        Belum ada penawaran untuk project ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($penawaran as $p): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="cell-ellipsis" title="<?= $e($p['no']) ?>">
                                            <a href="<?= site_url('pembayaran/detail/penawaran/' . (int) $p['id']) ?>"
                                               class="font-semibold text-blue-600 hover:underline">
                                                <?= $e($p['no']) ?>
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap text-slate-600"><?= $tgl($p['tanggal'] ?? null) ?></td>
                                        <td class="whitespace-nowrap"><?= $renderBadge($p['status'] ?? '') ?></td>
                                        <td class="whitespace-nowrap text-center col-aksi">
                                            <div class="flex flex-nowrap justify-center items-center gap-1">
                                                <a href="<?= site_url('pembayaran/detail/penawaran/' . (int) $p['id']) ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Lihat Detail">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </a>
                                                <a href="<?= site_url('pembayaran/edit/penawaran/' . (int) $p['id'] . '?from=detail') ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </a>
                                                <!-- Hapus (modal kustom) -->
                                                <button type="button"
                                                        class="btn-hapus-pembayaran aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                                        data-id="<?= (int) $p['id'] ?>"
                                                        data-nama="<?= $e($p['no']) ?>"
                                                        data-tab="penawaran"
                                                        data-label="Penawaran"
                                                        title="Hapus Penawaran">
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

                <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                    <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                    <div class="pagination-controls flex items-center gap-1.5"></div>
                </div>
            </div>
        </section>

        <!-- INVOICE -->
        <section class="bg-white rounded-2xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-bold text-slate-900">Invoice</h2>
                <a href="<?= site_url('pembayaran/tambah/invoice?project_id=' . (int) $project->id . '&from=project_detail') ?>"
                   class="text-sm font-semibold text-[#9f2b2b] hover:underline flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah
                </a>
            </div>

            <div class="table-wrapper flex flex-col overflow-hidden rounded-xl border border-slate-200">
                <div class="overflow-x-auto">
                    <table class="tbl-det w-full text-left text-[13px]" id="table-invoice" style="min-width: 440px;">
                        <colgroup>
                            <col>                          <!-- No. Invoice (sisa ruang) -->
                            <col style="width: 110px;">    <!-- Jatuh tempo -->
                            <col style="width: 145px;">    <!-- Status -->
                            <col style="width: 104px;">    <!-- Aksi (tetap) -->
                        </colgroup>
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr class="text-xs font-medium text-slate-500">
                                <th>No. Invoice</th>
                                <th>Jatuh tempo</th>
                                <th>Status</th>
                                <th class="text-center col-aksi">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (empty($invoice)): ?>
                                <tr class="empty-row">
                                    <td colspan="4" class="text-center text-slate-500">
                                        Belum ada invoice untuk project ini.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($invoice as $i): ?>
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="cell-ellipsis" title="<?= $e($i['no']) ?>">
                                            <a href="<?= site_url('pembayaran/detail/invoice/' . (int) $i['id']) ?>"
                                               class="font-semibold text-blue-600 hover:underline">
                                                <?= $e($i['no']) ?>
                                            </a>
                                        </td>
                                        <td class="whitespace-nowrap text-slate-600"><?= $tgl($i['tempo'] ?? null) ?></td>
                                        <td class="whitespace-nowrap"><?= $renderBadge($i['status'] ?? '') ?></td>
                                        <td class="whitespace-nowrap text-center col-aksi">
                                            <div class="flex flex-nowrap justify-center items-center gap-1">
                                                <a href="<?= site_url('pembayaran/detail/invoice/' . (int) $i['id']) ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Lihat Detail">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                </a>
                                                <a href="<?= site_url('pembayaran/edit/invoice/' . (int) $i['id'] . '?from=detail') ?>"
                                                   class="aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                                </a>
                                                <!-- Hapus (modal kustom) -->
                                                <button type="button"
                                                        class="btn-hapus-pembayaran aksi-btn bg-white border border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm"
                                                        data-id="<?= (int) $i['id'] ?>"
                                                        data-nama="<?= $e($i['no']) ?>"
                                                        data-tab="invoice"
                                                        data-label="Invoice"
                                                        title="Hapus Invoice">
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

                <div class="pagination-footer flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-2">
                    <p class="text-xs text-slate-500 pagination-info">Menampilkan 0 data</p>
                    <div class="pagination-controls flex items-center gap-1.5"></div>
                </div>
            </div>
        </section>

    </div>

</div>

<!-- MODAL HAPUS PROJECT -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>

    <div id="modalContent"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">

        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.667.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Project?</h3>

        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus project?
            <br>
            <strong class="text-gray-900 text-base"><?= $e($project->nama_project) ?></strong>
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

<!-- MODAL HAPUS DOKUMEN -->
<div id="modalHapusDok" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHapusDok()"></div>

    <div id="modalHapusDokContent"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">

        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.667.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Dokumen?</h3>

        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus dokumen <br>
            <strong id="namaDokumenHapus" class="text-gray-900 text-base"></strong>?<br>
            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
        </p>

        <div class="flex justify-center gap-3">
            <button type="button" onclick="tutupModalHapusDok()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
            <button type="button" onclick="eksekusiHapusDok()" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- MODAL HAPUS PENAWARAN / INVOICE -->
<div id="modalHapusPembayaran" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHapusPembayaran()"></div>

    <div id="modalHapusPembayaranContent"
         class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">

        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.667.192 3 1.732 3z"/>
            </svg>
        </div>

        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus <span id="labelHapusPembayaran">Penawaran</span>?</h3>

        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus <span id="labelHapusPembayaran2" class="lowercase">penawaran</span> <br>
            <strong id="namaHapusPembayaran" class="text-gray-900 text-base"></strong>?<br>
            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
        </p>

        <form method="post" action="<?= site_url('pembayaran/hapus') ?>" id="formHapusPembayaran" class="flex justify-center gap-3" onsubmit="disableHapusPembayaran()">
            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>
            <input type="hidden" name="id" id="inputHapusPembayaranId">
            <input type="hidden" name="tab" id="inputHapusPembayaranTab">

            <button type="button" onclick="tutupModalHapusPembayaran()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
            <button type="submit" id="btnHapusPembayaran" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm disabled:opacity-50">Ya, Hapus</button>
        </form>
    </div>
</div>

<!-- TOAST -->
<div id="toastContainer" class="fixed top-5 right-5 z-[60] flex flex-col gap-2 pointer-events-none"></div>

<style>
    @keyframes toastIn { from { opacity: 0; transform: translateX(100%); } to { opacity: 1; transform: translateX(0); } }
    @keyframes toastOut { from { opacity: 1; transform: translateX(0); } to { opacity: 0; transform: translateX(100%); } }
    .toast-item { animation: toastIn 0.3s ease-out; }
    .toast-item.hide { animation: toastOut 0.3s ease-in forwards; }

    /* Tata letak halaman: kolom diatur di sini agar tidak bergantung pada class Tailwind yang ter-build */
    .dp-grid-top  { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; align-items: stretch; }
    .dp-grid-pair { display: grid; grid-template-columns: minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    @media (min-width: 1024px) {
        .dp-grid-top { grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); }
    }
    @media (min-width: 1100px) {
        .dp-grid-pair { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); }
    }

    /* Tabel detail (dokumen, penawaran, invoice): lebar kolom diatur colgroup,
       teks panjang dipotong dengan titik-titik (...) */
    .tbl-det { table-layout: fixed; }
    .tbl-det th,
    .tbl-det td { padding: 0.75rem; vertical-align: middle; }
    .tbl-det tbody td[colspan] { padding: 2rem 1rem; }
    .tbl-det th.col-aksi,
    .tbl-det td.col-aksi { padding-left: 0.5rem; padding-right: 0.5rem; }
    .tbl-det .cell-ellipsis { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

    /* Tombol aksi: ukuran seragam, tidak ikut menyusut */
    .aksi-btn { display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; width: 26px; height: 26px; padding: 0; }
    .aksi-btn svg { width: 14px; height: 14px; }
</style>

<script src="<?= base_url('assets/js/tridatu-fs.js') ?>"></script>
<script>
// =========================================
// 1. TOAST
// =========================================
const TOAST_CFG = {
    success: { bg: 'bg-emerald-50 border-emerald-200 text-emerald-800', ic: 'text-emerald-500', d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    error:   { bg: 'bg-red-50 border-red-200 text-red-800',             ic: 'text-red-500',     d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' },
    info:    { bg: 'bg-blue-50 border-blue-200 text-blue-800',          ic: 'text-blue-500',    d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z' }
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

document.addEventListener('DOMContentLoaded', () => {
    <?php if ($this->session->flashdata('success')): ?>
        showToast(<?= json_encode($this->session->flashdata('success')) ?>, 'success');
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        showToast(<?= json_encode(strip_tags($this->session->flashdata('error'))) ?>, 'error');
    <?php endif; ?>
});

// =========================================
// 2. MODAL HAPUS PROJECT
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
    const rows = Array.from(tbody.querySelectorAll('tr')).filter(tr => !tr.classList.contains('empty-row'));

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

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'flex h-7 w-7 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
        prevBtn.disabled = currentPage === 1;
        prevBtn.innerHTML = `<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>`;
        prevBtn.onclick = () => { if (currentPage > 1) { currentPage--; render(); } };
        c.appendChild(prevBtn);

        for (let i = 1; i <= totalPages; i++) {
            const pageBtn = document.createElement('button');
            pageBtn.type = 'button';
            pageBtn.className = `flex h-7 min-w-[1.75rem] items-center justify-center rounded-md border text-xs font-semibold transition-colors ${
                i === currentPage
                    ? 'border-[#9f2b2b] bg-[#9f2b2b] text-white'
                    : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'
            }`;
            pageBtn.textContent = i;
            pageBtn.onclick = () => { currentPage = i; render(); };
            c.appendChild(pageBtn);
        }

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
    document.querySelectorAll('.custom-dropdown.open').forEach(o => {
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

// =========================================
// 5. DOKUMEN KERJA SAMA (perangkat, pratinjau, sinkron, hapus)
// =========================================
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

// --- PRATINJAU DOKUMEN LOKAL (PERANGKAT INI) ---
document.querySelectorAll('.btn-preview-dok').forEach(btn => {
    btn.addEventListener('click', async function () {
        const klienId      = Number(this.dataset.klienId || 0);
        const klienNama    = this.dataset.klienNama || '';
        const fileName     = this.dataset.fileName || '';
        const expectedHash = (this.dataset.fileHash || '').toLowerCase();

        // Buka tab dulu (sinkron dengan klik) agar tidak diblokir pop-up blocker
        const win = window.open('', '_blank');
        if (!win) {
            showToast('Pop-up diblokir browser. Izinkan pop-up untuk situs ini.', 'error');
            return;
        }
        win.document.title = 'Memuat dokumen...';

        try {
            if (typeof TridatuFS === 'undefined') {
                throw new Error('Helper TridatuFS belum berhasil dimuat.');
            }
            if (!fileName) {
                throw new Error('Informasi file tidak lengkap.');
            }

            let file = null;

            // 1. Coba baca dari folder lokal yang sudah diizinkan
            if (TridatuFS.isSupported()) {
                try {
                    file = await TridatuFS.readFile({
                        klienId: klienId,
                        klienNama: klienNama,
                        fileName: fileName,
                        fileHash: expectedHash
                    });
                } catch (readError) {
                    console.warn('Tidak dapat membaca folder lokal:', readError);
                }
            }

            // 2. Fallback: pilih file secara manual
            if (!file) {
                showToast('File tidak ditemukan di folder. Pilih file secara manual.', 'info');
                file = await TridatuFS.pickFile();
            }

            // Pengguna membatalkan pemilihan file
            if (!file) { win.close(); return; }

            // 3. Pastikan file sama dengan yang tercatat
            if (expectedHash) {
                const actualHash = (await hitungHash(file)).toLowerCase();
                if (actualHash !== expectedHash) {
                    throw new Error('File berbeda dari aslinya. Pastikan memilih file yang benar.');
                }
            }

            // 4. Tampilkan di viewer bawaan browser (PDF/gambar), selain itu diunduh
            const mimeMap = { pdf: 'application/pdf', jpg: 'image/jpeg', jpeg: 'image/jpeg', png: 'image/png' };
            const ext  = fileName.split('.').pop().toLowerCase();
            const mime = mimeMap[ext];
            const url  = URL.createObjectURL(new Blob([file], { type: mime || 'application/octet-stream' }));

            if (mime) {
                win.location.href = url;
            } else {
                win.close();
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                a.remove();
                showToast('Format ini tidak bisa dipratinjau, file diunduh.', 'info');
            }
        } catch (err) {
            win.close();
            console.error('Pratinjau dokumen gagal:', err);
            showToast(err.message || 'Gagal membuka pratinjau.', 'error');
        }
    });
});

// --- SYNC TO CLOUD (FILE SYSTEM ACCESS API) ---
document.querySelectorAll('.btn-sync-dok').forEach(btn => {
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
                    console.warn('Tidak dapat membaca folder lokal:', readError);
                }
            }

            // 2. Fallback: pilih file secara manual
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
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
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

// --- HAPUS DOKUMEN ---
let hapusDokId = null;
let hapusDokMeta = null;

const modalDok = document.getElementById('modalHapusDok');
const modalDokContent = document.getElementById('modalHapusDokContent');

document.querySelectorAll('.btn-hapus-dok').forEach(btn => {
    btn.addEventListener('click', function () {
        hapusDokId = this.dataset.id;
        hapusDokMeta = {
            isOwnLocal: this.dataset.isOwnLocal === '1',
            klienId: Number(this.dataset.klienId || 0),
            klienNama: this.dataset.klienNama || '',
            fileName: this.dataset.fileName || '',
            fileHash: this.dataset.fileHash || ''
        };
        document.getElementById('namaDokumenHapus').innerText = this.dataset.nama;

        modalDok.classList.remove('hidden');
        modalDok.classList.add('flex');

        setTimeout(() => {
            modalDokContent.classList.remove('scale-95', 'opacity-0');
            modalDokContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    });
});

function tutupModalHapusDok() {
    hapusDokId = null;
    hapusDokMeta = null;

    modalDokContent.classList.remove('scale-100', 'opacity-100');
    modalDokContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => {
        modalDok.classList.add('hidden');
        modalDok.classList.remove('flex');
    }, 300);
}

document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape' && !modalDok.classList.contains('hidden')) {
        tutupModalHapusDok();
    }
});

async function eksekusiHapusDok() {
    if (!hapusDokId) return;

    try {
        const formData = new FormData();
        formData.append('dokumen_csrf_token', DOCUMENT_CSRF_TOKEN);

        const response = await fetch('<?= site_url('dokumen/hapus/') ?>' + hapusDokId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();

        if (!response.ok || !result.status) {
            throw new Error(result.msg || 'Dokumen gagal dihapus.');
        }

        // Hapus file fisik hanya jika file lokal tersebut memang milik perangkat ini.
        // Data dokumen di server sudah berhasil dihapus sebelum bagian ini dijalankan.
        if (hapusDokMeta && hapusDokMeta.isOwnLocal && hapusDokMeta.fileName && hapusDokMeta.fileHash) {
            try {
                if (typeof TridatuFS !== 'undefined' && TridatuFS.isSupported()) {
                    const deleteResult = await TridatuFS.deleteFile({
                        klienId: hapusDokMeta.klienId,
                        klienNama: hapusDokMeta.klienNama,
                        fileName: hapusDokMeta.fileName,
                        fileHash: hapusDokMeta.fileHash
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

        // Tetap di halaman detail project (notifikasi sukses muncul lewat flashdata)
        window.location.reload();

    } catch (err) {
        showToast(err.message || 'Dokumen gagal dihapus.', 'error');
    }
}

// =========================================
// 5b. HAPUS PENAWARAN / INVOICE
// =========================================
const modalPembayaran        = document.getElementById('modalHapusPembayaran');
const modalPembayaranContent = document.getElementById('modalHapusPembayaranContent');

document.querySelectorAll('.btn-hapus-pembayaran').forEach(btn => {
    btn.addEventListener('click', function () {
        document.getElementById('inputHapusPembayaranId').value  = this.dataset.id;
        document.getElementById('inputHapusPembayaranTab').value = this.dataset.tab;
        document.getElementById('labelHapusPembayaran').textContent  = this.dataset.label;
        document.getElementById('labelHapusPembayaran2').textContent = this.dataset.label;
        document.getElementById('namaHapusPembayaran').textContent   = this.dataset.nama;

        const submitBtn = document.getElementById('btnHapusPembayaran');
        submitBtn.disabled = false;
        submitBtn.textContent = 'Ya, Hapus';

        modalPembayaran.classList.remove('hidden');
        modalPembayaran.classList.add('flex');
        setTimeout(() => {
            modalPembayaranContent.classList.remove('scale-95', 'opacity-0');
            modalPembayaranContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    });
});

function tutupModalHapusPembayaran() {
    modalPembayaranContent.classList.remove('scale-100', 'opacity-100');
    modalPembayaranContent.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modalPembayaran.classList.add('hidden');
        modalPembayaran.classList.remove('flex');
    }, 300);
}

// Cegah klik ganda saat menghapus
function disableHapusPembayaran() {
    const submitBtn = document.getElementById('btnHapusPembayaran');
    submitBtn.textContent = 'Menghapus...';
    submitBtn.disabled = true;
    return true;
}

document.addEventListener('keydown', (ev) => {
    if (ev.key === 'Escape' && !modalPembayaran.classList.contains('hidden')) {
        tutupModalHapusPembayaran();
    }
});

// =========================================
// 6. INISIALISASI
// =========================================
document.addEventListener('DOMContentLoaded', () => {
    setupPagination('table-dokumen', 3);
    setupPagination('table-penawaran', 3);
    setupPagination('table-invoice', 3);
});
</script>

<!-- SMART BACK BUTTON (script bersama) -->
<?php $this->load->view('partials/smart_back'); ?>

</body>
</html>