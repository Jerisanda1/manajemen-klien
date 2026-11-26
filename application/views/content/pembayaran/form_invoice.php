<?php
/**
 * @var CI_Controller $this
 * @var object|null $inv
 * @var array $klienList, $projectList, $penawaranList
 * @var string $nomorBaru, $action, $title, $formErr, $uploadErr
 * @var int $pre
 *
 * + Smart Back Button — Kembali & Batal menuju halaman asal form dibuka
 *   (daftar pembayaran / detail invoice / detail project). $urlKembali jadi fallback.
 */
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$inv    = isset($inv) ? $inv : null;
$isEdit = !empty($inv);
$preKlien = isset($preKlien) ? $preKlien : 0;
$preProject = isset($preProject) ? $preProject : 0;

$p = function ($f, $d = '') use ($inv, $preKlien, $preProject) {
    $v = $this->input->post($f);
    if ($v !== null) return $v;
    if ($inv && isset($inv->$f) && $inv->$f !== null) return $inv->$f;
    if ($f === 'klien_id' && $preKlien) return $preKlien;
    if ($f === 'project_id' && $preProject) return $preProject;
    return $d;
};

$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="text-xs text-red-500 mt-1">' . htmlspecialchars($m) . '</p>' : '';
};

if ($isEdit) {
    $sumber = !empty($inv->penawaran_id) ? 'penawaran' : 'baru';
    $cara   = ($inv->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template';
} else {
    $sumber = $p('sumber', $pre ? 'penawaran' : 'baru');
    $cara   = $p('cara', 'template');
}

$posted = $this->input->method() === 'post';
$kop = $posted ? (bool) $this->input->post('sertakan_kop') : ($isEdit ? (bool) $inv->sertakan_kop : true);
$ttd = $posted ? (bool) $this->input->post('sertakan_ttd') : ($isEdit ? (bool) $inv->sertakan_ttd : true);
$pwPilih = $p('penawaran_id', $pre ?: '');

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

$fmt = function ($t) use ($bulan) {
    $x = $t ? strtotime($t) : false;
    return $x
        ? date('j', $x) . ' ' . $bulan[(int) date('n', $x)] . ' ' . date('Y', $x)
        : '-';
};

$nomorTampil = $isEdit ? $inv->no_invoice : $nomorBaru;
$tglTampil   = $isEdit ? $fmt($inv->tanggal_invoice) : $fmt(date('Y-m-d'));
$statusTampil = $isEdit ? $inv->status : 'Belum dibayar';

$pillStyle = $statusTampil === 'Dibayar'
    ? 'bg-green-50 text-green-700 border border-green-200'
    : 'bg-orange-50 text-orange-700 border border-orange-200';

$pwData = [];

foreach ($penawaranList as $x) {
    $pwData[$x->id] = [
        'klien_id'  => (int) $x->klien_id,
        'project_id' => (int) $x->project_id,
        'perihal'   => (string) $x->perihal,
        'isi'       => $x->dibuat_lewat === 'Unggahan' ? '' : (string) $x->isi_surat,
        'no'        => $x->no_penawaran,
    ];
}

$adaFile  = $isEdit && !empty($inv->file_path);
$fileNama = $adaFile ? $inv->file_nama : '';

$from = isset($from) ? $from : 'list';

// Fallback bila asal tidak diketahui (mis. URL dibuka langsung)
$urlKembali = ($isEdit && $from === 'detail')
    ? site_url('pembayaran/detail/invoice/' . (int) $inv->id)
    : site_url('pembayaran?tab=invoice');

// Kunci tetap agar asal tidak hilang saat validasi gagal (URL form bisa berubah)
$backKey = 'invoice-form-' . ($isEdit ? (int) $inv->id : 'new');

$labelKlien = 'Pilih Klien';

foreach ($klienList as $k) {
    if ((string) $p('klien_id') === (string) $k->id) {
        $labelKlien = $k->nama_klien;
    }
}

$labelProject = 'Pilih Project';

foreach ($projectList as $pr) {
    if ((string) $p('project_id') === (string) $pr->id) {
        $labelProject = $pr->nama_project;
    }
}

$labelPw = 'Pilih Penawaran';

foreach ($penawaranList as $x) {
    if ((string) $pwPilih === (string) $x->id) {
        $labelPw = $x->no_penawaran . ' - ' . $x->perihal;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?> - Tridatu</title>

    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">

    <style>
        .custom-dropdown.drop-up .dropdown-menu {
            top: auto;
            bottom: 100%;
            margin-top: 0;
            margin-bottom: 4px;
        }

        .dropdown-menu {
            transition: opacity 200ms cubic-bezier(0.4,0,0.2,1),
                        transform 200ms cubic-bezier(0.4,0,0.2,1);
        }

        /* Card pilihan sumber invoice */
        .invoice-option {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
            transition: all 150ms ease;
        }

        .invoice-option.selected {
            border-color: #60a5fa;
            background: #dbeafe;
        }

        .invoice-option input[type="radio"] {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 3px;
            accent-color: #2995f5;
        }

        .invoice-option:has(input:disabled) {
            cursor: not-allowed;
        }
    </style>
</head>

<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen">

    <div class="max-w-[1280px] mx-auto p-4 md:p-6 lg:p-8">

        <!-- Header -->
        <div class="mb-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <!-- SMART BACK BUTTON (href = fallback bila asal tidak diketahui) -->
                <a href="<?= $urlKembali ?>"
                   id="backBtn"
                   data-back-key="<?= $e($backKey) ?>"
                   class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 transition-colors">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>

                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">
                    <?= $e($title) ?>
                </h2>
            </div>

            <?php if ($isEdit): ?>
                <div class="bg-gray-100 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">
                    Status:
                    <span class="font-bold text-gray-900"><?= $e($statusTampil) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($formErr): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-5 border border-red-200 text-sm">
                <?= $e($formErr) ?>
            </div>
        <?php endif; ?>

        <form id="biForm"
              method="post"
              action="<?= $e($action) ?>"
              enctype="multipart/form-data"
              novalidate>

            <input type="hidden" name="pv_nomor" value="<?= $e($nomorTampil) ?>">
            <input type="hidden" name="pv_tanggal" value="<?= $e($isEdit ? $inv->tanggal_invoice : date('Y-m-d')) ?>">

            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden"
                       name="<?= $this->security->get_csrf_token_name() ?>"
                       value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-6 items-start">

                <!-- KIRI -->
                <div class="space-y-4">

                    <!-- Card 1: Sumber & Cara -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">

                        <h3 class="text-base font-bold text-gray-900 mb-4">
                            Sumber dan cara pembuatan
                        </h3>

                        <!-- Sumber Invoice -->
                        <div class="mb-4">

                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                Sumber invoice
                            </label>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">

                                <!-- Buat Baru -->
                                <label class="invoice-option <?= $sumber === 'baru' ? 'selected' : '' ?> <?= $isEdit ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' ?>">

                                    <input
                                        type="radio"
                                        name="sumber"
                                        value="baru"
                                        <?= $sumber === 'baru' ? 'checked' : '' ?>
                                        <?= $isEdit ? 'disabled' : '' ?>
                                        onchange="toggleSumber('baru')"
                                    >

                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            Buat baru
                                        </p>

                                        <p class="text-xs text-gray-500 leading-relaxed">
                                            Isi data invoice dari awal
                                        </p>
                                    </div>
                                </label>

                                <!-- Dari Penawaran -->
                                <label class="invoice-option <?= $sumber === 'penawaran' ? 'selected' : '' ?> <?= $isEdit ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' ?>">

                                    <input
                                        type="radio"
                                        name="sumber"
                                        value="penawaran"
                                        <?= $sumber === 'penawaran' ? 'checked' : '' ?>
                                        <?= $isEdit ? 'disabled' : '' ?>
                                        onchange="toggleSumber('penawaran')"
                                    >

                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-900">
                                            Dari penawaran
                                        </p>

                                        <p class="text-xs text-gray-500 leading-relaxed">
                                            Data diambil dari penawaran yang sudah disetujui
                                        </p>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <!-- Cara Pembuatan Dokumen -->
                        <div>

                            <label class="block text-xs font-medium text-gray-700 mb-2">
                                Cara pembuatan dokumen
                            </label>

                            <div class="flex flex-wrap items-center gap-x-6 gap-y-2">

                                <!-- Template -->
                                <label class="inline-flex items-center gap-2 cursor-pointer <?= ($isEdit && $cara !== 'template') ? 'opacity-60 cursor-not-allowed' : '' ?>">

                                    <input
                                        type="radio"
                                        name="cara"
                                        value="template"
                                        class="w-4 h-4 accent-[#2995f5]"
                                        <?= $cara === 'template' ? 'checked' : '' ?>
                                        <?= $isEdit ? 'disabled' : '' ?>
                                        onchange="toggleCara('template')"
                                    >

                                    <span class="text-sm text-gray-700">
                                        Template baku
                                    </span>
                                </label>

                                <!-- Unggah -->
                                <label class="inline-flex items-center gap-2 cursor-pointer <?= ($isEdit && $cara !== 'unggah') ? 'opacity-60 cursor-not-allowed' : '' ?>">

                                    <input
                                        type="radio"
                                        name="cara"
                                        value="unggah"
                                        class="w-4 h-4 accent-[#2995f5]"
                                        <?= $cara === 'unggah' ? 'checked' : '' ?>
                                        <?= $isEdit ? 'disabled' : '' ?>
                                        onchange="toggleCara('unggah')"
                                    >

                                    <span class="text-sm text-gray-700">
                                        Unggah dokumen
                                    </span>
                                </label>

                            </div>
                        </div>

                        <?php if ($isEdit): ?>
                            <p class="text-xs text-gray-500 mt-3">
                                Sumber dan cara pembuatan tidak bisa diubah setelah invoice disimpan.
                            </p>
                        <?php endif; ?>

                    </div>

                    <!-- Card 2: Data Invoice -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">

                        <h3 class="text-lg font-bold text-gray-900 mb-5">
                            Data Invoice
                        </h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">

                            <!-- Penawaran -->
                            <div class="field-sumber-penawaran md:col-span-2 <?= $sumber !== 'penawaran' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Pilih Penawaran <span class="text-red-500">*</span>
                                </label>

                                <?php if ($isEdit && $sumber === 'penawaran'): ?>

                                    <input type="text"
                                           class="w-full px-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600"
                                           readonly
                                           value="<?= $e($inv->no_penawaran . ' - ' . $inv->perihal_penawaran) ?>">

                                <?php else: ?>

                                    <div class="relative custom-dropdown" data-target="penawaran_id">

                                        <button type="button"
                                                class="dropdown-btn flex items-center justify-between w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">

                                            <span class="dropdown-text truncate <?= $pwPilih ? 'text-gray-900' : 'text-gray-400' ?>">
                                                <?= $e($labelPw) ?>
                                            </span>

                                            <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">

                                            <div class="p-2 border-b border-gray-100">
                                                <div class="relative">
                                                    <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>

                                                    <input type="text"
                                                           class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                                           placeholder="Cari penawaran..."
                                                           autocomplete="off">
                                                </div>
                                            </div>

                                            <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                                <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium"
                                                    data-value="">
                                                    Pilih penawaran
                                                </li>

                                                <?php foreach ($penawaranList as $x): ?>
                                                    <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= (string) $pwPilih === (string) $x->id ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>"
                                                        data-value="<?= $x->id ?>">
                                                        <?= $e($x->no_penawaran . ' - ' . $x->perihal) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                        <input type="hidden"
                                               name="penawaran_id"
                                               id="penawaran_id"
                                               value="<?= $e($pwPilih) ?>">
                                    </div>

                                <?php endif; ?>

                                <p class="text-xs text-gray-500 mt-1">
                                    Data Klien, Project, dan Isi Surat akan terisi otomatis dari penawaran ini.
                                </p>

                                <?= $err('penawaran_id') ?>
                            </div>

                            <!-- Klien (Baru) -->
                            <div class="field-sumber-baru <?= $sumber !== 'baru' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Klien <span class="text-red-500">*</span>
                                </label>

                                <div class="relative custom-dropdown" data-target="klien_id">

                                    <button type="button"
                                            class="dropdown-btn flex items-center justify-between w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">

                                        <span class="dropdown-text truncate <?= $p('klien_id') ? 'text-gray-900' : 'text-gray-400' ?>">
                                            <?= $e($labelKlien) ?>
                                        </span>

                                        <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">

                                        <div class="p-2 border-b border-gray-100">
                                            <div class="relative">
                                                <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>

                                                <input type="text"
                                                       class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                                       placeholder="Cari klien..."
                                                       autocomplete="off">
                                            </div>
                                        </div>

                                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                            <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium"
                                                data-value="">
                                                Pilih Klien
                                            </li>

                                            <?php foreach ($klienList as $k): ?>
                                                <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= (string) $p('klien_id') === (string) $k->id ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>"
                                                    data-value="<?= $k->id ?>">
                                                    <?= $e($k->nama_klien) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>

                                    <input type="hidden"
                                           name="klien_id"
                                           id="klien_id"
                                           value="<?= $e($p('klien_id')) ?>">
                                </div>

                                <?= $err('klien_id') ?>
                            </div>

                            <!-- Project (Baru) -->
                            <div class="field-sumber-baru <?= $sumber !== 'baru' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Project <span class="text-red-500">*</span>
                                </label>

                                <div class="relative custom-dropdown" data-target="project_id">

                                    <button type="button"
                                            class="dropdown-btn flex items-center justify-between w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">

                                        <span class="dropdown-text truncate <?= $p('project_id') ? 'text-gray-900' : 'text-gray-400' ?>">
                                            <?= $e($labelProject) ?>
                                        </span>

                                        <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200"
                                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">

                                        <div class="p-2 border-b border-gray-100">
                                            <div class="relative">
                                                <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>

                                                <input type="text"
                                                       class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                                       placeholder="Cari project..."
                                                       autocomplete="off">
                                            </div>
                                        </div>

                                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                            <li class="px-3 py-2 text-gray-400 italic cursor-default">
                                                Pilih Klien Terlebih Dahulu
                                            </li>
                                        </ul>
                                    </div>

                                    <input type="hidden"
                                           name="project_id"
                                           id="project_id"
                                           value="<?= $e($p('project_id')) ?>">
                                </div>

                                <?= $err('project_id') ?>
                            </div>

                            <!-- Perihal -->
                            <div class="md:col-span-2">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Perihal <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                       name="perihal"
                                       id="perihal"
                                       class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                       placeholder="[Perihal invoice]"
                                       value="<?= $e($p('perihal')) ?>"
                                       required>

                                <?= $err('perihal') ?>
                            </div>

                            <!-- Jatuh Tempo -->
                            <div>

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Tanggal Jatuh Tempo <span class="text-red-500">*</span>
                                </label>

                                <input type="date"
                                       name="jatuh_tempo"
                                       class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                       value="<?= $e($p('jatuh_tempo')) ?>"
                                       required>

                                <?= $err('jatuh_tempo') ?>
                            </div>

                            <!-- Nomor Invoice (Unggah) -->
                            <div class="field-cara-unggah <?= $cara !== 'unggah' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Nomor Invoice <span class="text-red-500">*</span>
                                </label>

                                <input type="text"
                                       name="no_invoice"
                                       class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                       value="<?= $e($p('no_invoice')) ?>">

                                <?= $err('no_invoice') ?>
                            </div>

                            <!-- Tanggal Invoice (Unggah) -->
                            <div class="field-cara-unggah <?= $cara !== 'unggah' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Tanggal Invoice <span class="text-red-500">*</span>
                                </label>

                                <input type="date"
                                       name="tanggal_invoice"
                                       class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]"
                                       value="<?= $e($p('tanggal_invoice', date('Y-m-d'))) ?>">

                                <?= $err('tanggal_invoice') ?>
                            </div>

                            <!-- Isi Surat (Template) -->
                            <div class="field-cara-template md:col-span-2 <?= $cara !== 'template' ? 'hidden' : '' ?>">

                                <label class="block text-xs font-medium text-gray-700 mb-1.5">
                                    Isi Surat
                                </label>

                                <textarea name="isi_surat"
                                          id="isi_surat"
                                          class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] min-h-[150px]"
                                          placeholder="[Terisi otomatis dari isi penawaran, bisa diubah sebelum disimpan]"><?= $e($p('isi_surat')) ?></textarea>
                            </div>

                            <!-- Checkboxes (Template) -->
                            <div class="field-cara-template md:col-span-2 <?= $cara !== 'template' ? 'hidden' : '' ?>">

                                <div class="flex flex-wrap gap-6 mt-2">

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               name="sertakan_kop"
                                               value="1"
                                               class="w-5 h-5 text-[#9f2b2b] rounded border-gray-300 focus:ring-[#9f2b2b]"
                                               <?= $kop ? 'checked' : '' ?>>

                                        <span class="ml-2 text-sm text-gray-700 font-medium">
                                            Sertakan kop surat
                                        </span>
                                    </label>

                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox"
                                               name="sertakan_ttd"
                                               value="1"
                                               class="w-5 h-5 text-[#9f2b2b] rounded border-gray-300 focus:ring-[#9f2b2b]"
                                               <?= $ttd ? 'checked' : '' ?>>

                                        <span class="ml-2 text-sm text-gray-700 font-medium">
                                            Sertakan tanda tangan
                                        </span>
                                    </label>

                                </div>
                            </div>

                        </div>
                    </div>

<?php
$itemsForm = $this->input->post('items');
if (!is_array($itemsForm)) {
    $itemsForm = [];
    foreach (($items ?? []) as $item) {
        $itemsForm[] = [
            'description' => $item->description,
            'quantity'    => $item->quantity,
            'unit'        => $item->unit,
            'unit_price'  => $item->unit_price,
        ];
    }
}
if (!$itemsForm) {
    $itemsForm = [[
        'description' => '',
        'quantity' => 1,
        'unit' => 'unit',
        'unit_price' => 0,
    ]];
}

$taxesForm = $this->input->post('taxes');
if (!is_array($taxesForm)) {
    $taxesForm = [];
    foreach (($taxes ?? []) as $tax) {
        $taxesForm[] = [
            'tax_name' => $tax->tax_name,
            'tax_type' => $tax->tax_type,
            'tax_rate' => $tax->tax_rate,
        ];
    }
}

// Default pajak hanya untuk invoice baru. Invoice lama tidak diubah otomatis.
if (!$posted && empty($taxesForm)) {
    $taxesForm = [
        ['tax_name' => 'PPN', 'tax_type' => 'percentage', 'tax_rate' => 11],
        ['tax_name' => 'PPh', 'tax_type' => 'percentage', 'tax_rate' => 2],
    ];
}


$settings = isset($settings) ? $settings : null;

// Susun rekening dari data Pengaturan.
$rekeningDefault = '';

if ($settings) {
    $rekeningDefault = implode("\n", array_filter([
        !empty($settings->nama_bank)
            ? 'Bank: ' . $settings->nama_bank
            : '',
        !empty($settings->nama_cabang)
            ? 'Cabang: ' . $settings->nama_cabang
            : '',
        !empty($settings->swift_code)
            ? 'SWIFT Code: ' . $settings->swift_code
            : '',
        !empty($settings->nomor_rekening)
            ? 'No. Rekening: ' . $settings->nomor_rekening
            : '',
        !empty($settings->nama_pemilik_rekening)
            ? 'Nama Pemilik: ' . $settings->nama_pemilik_rekening
            : '',
    ]));
}

?>

<div class="field-cara-template bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
    <div class="flex justify-between items-center mb-4">
        <div>
            <h3 class="text-lg font-bold text-gray-900">Rincian Proforma Invoice</h3>
            <p class="text-xs text-gray-500 mt-1">Tambahkan produk atau jasa yang ditagihkan.</p>
        </div>
        <button type="button" onclick="tambahItem()"
            class="px-3 py-2 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100">
            + Tambah Item
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b">
                    <th class="py-2 pr-2">Deskripsi</th>
                    <th class="py-2 px-2 w-20">Qty</th>
                    <th class="py-2 px-2 w-24">Satuan</th>
                    <th class="py-2 px-2 w-36">Harga</th>
                    <th class="py-2 pl-2 w-36 text-right">Jumlah</th>
                    <th class="w-8"></th>
                </tr>
            </thead>
            <tbody id="invoiceItems">
                <?php foreach ($itemsForm as $i => $item): ?>
                    <tr class="invoice-item border-b">
                        <td class="py-2 pr-2">
                            <input type="text"
                                name="items[<?= $i ?>][description]"
                                value="<?= $e($item['description'] ?? '') ?>"
                                required
                                class="w-full min-w-[150px] px-2 py-2 border rounded-lg"
                                placeholder="Nama produk/jasa">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" min="0.01" step="0.01"
                                name="items[<?= $i ?>][quantity]"
                                value="<?= $e($item['quantity'] ?? 1) ?>"
                                oninput="hitungInvoice()"
                                required class="item-qty w-full px-2 py-2 border rounded-lg">
                        </td>
                        <td class="py-2 px-2">
                            <input type="text"
                                name="items[<?= $i ?>][unit]"
                                value="<?= $e($item['unit'] ?? 'unit') ?>"
                                class="w-full px-2 py-2 border rounded-lg">
                        </td>
                        <td class="py-2 px-2">
                            <input type="number" min="0" step="1"
                                name="items[<?= $i ?>][unit_price]"
                                value="<?= $e($item['unit_price'] ?? 0) ?>"
                                oninput="hitungInvoice()"
                                required class="item-price w-full px-2 py-2 border rounded-lg">
                        </td>
                        <td class="py-2 pl-2 text-right font-medium item-total">Rp0</td>
                        <td class="text-right">
                            <button type="button" onclick="hapusItem(this)"
                                class="text-red-500 hover:text-red-700 px-1">×</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-5 grid grid-cols-1 md:grid-cols-2 gap-5">
        <div>
            <div class="flex justify-between items-center mb-3">
                <h4 class="font-semibold text-sm">Pajak / Biaya Tambahan</h4>
                <button type="button" onclick="tambahPajak()"
                    class="text-xs font-semibold text-blue-700">+ Tambah</button>
            </div>
            <div id="invoiceTaxes" class="space-y-2">
                <?php foreach ($taxesForm as $i => $tax): ?>
                    <div class="tax-row grid grid-cols-12 gap-2">
                        <input name="taxes[<?= $i ?>][tax_name]"
                            value="<?= $e($tax['tax_name'] ?? '') ?>"
                            placeholder="Nama pajak"
                            class="col-span-5 min-w-0 px-2 py-2 border rounded-lg text-sm">
                        <select name="taxes[<?= $i ?>][tax_type]"
                            class="tax-type col-span-3 min-w-0 px-1 py-2 border rounded-lg text-xs"
                            onchange="hitungInvoice()">
                            <option value="percentage" <?= ($tax['tax_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>%</option>
                            <option value="fixed" <?= ($tax['tax_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Rp</option>
                        </select>
                        <input type="number" min="0" step="0.01"
                            name="taxes[<?= $i ?>][tax_rate]"
                            value="<?= $e($tax['tax_rate'] ?? 0) ?>"
                            oninput="hitungInvoice()"
                            class="tax-rate col-span-3 min-w-0 px-2 py-2 border rounded-lg text-sm">
                        <button type="button" onclick="hapusPajak(this)"
                            class="col-span-1 text-red-500">×</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-gray-50 rounded-xl p-4 space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Subtotal</span>
                <strong id="subtotalText">Rp0</strong>
            </div>
            <div>
                <label class="block text-xs text-gray-600 mb-1">Diskon (Rp)</label>
                <input type="number" min="0" step="1" id="discountAmount"
                    name="discount_amount"
                    value="<?= $e($this->input->post('discount_amount') ?? ($inv->discount_amount ?? 0)) ?>"
                    oninput="hitungInvoice()"
                    class="w-full px-3 py-2 border rounded-lg text-sm">
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Total Pajak</span>
                <strong id="taxTotalText">Rp0</strong>
            </div>
            <div class="border-t pt-3 flex justify-between items-center">
                <span class="font-bold">TOTAL</span>
                <strong id="grandTotalText" class="text-xl text-[#9f2b2b]">Rp0</strong>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-5">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Metode Pembayaran</label>
            <input type="text" name="payment_method"
                value="<?= $e($this->input->post('payment_method') ?? ($inv->payment_method ?? 'Transfer Bank')) ?>"
                placeholder="Transfer Bank / Tunai"
                class="w-full px-3 py-2.5 border rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Rekening / Tujuan Pembayaran</label>
            
<?php
$rekeningTampil = $this->input->post('payment_account');

if ($rekeningTampil === null) {
    if ($isEdit) {
        // Invoice lama mempertahankan rekening yang tersimpan.
        $rekeningTampil = $inv->payment_account ?? '';
    } else {
        // Invoice baru mengambil rekening dari Pengaturan.
        $rekeningTampil = $rekeningDefault;
    }
}
?>

<textarea name="payment_account" rows="5"
    placeholder="Informasi rekening bank untuk ditampilkan pada PDF"
    class="w-full px-3 py-2.5 border rounded-lg text-sm"><?= $e($rekeningTampil) ?></textarea>

        </div>
        <div class="md:col-span-2">
            <label class="block text-xs font-medium text-gray-700 mb-1">Catatan</label>
            <textarea name="notes" rows="3"
                class="w-full px-3 py-2.5 border rounded-lg text-sm"
                placeholder="Catatan tambahan untuk klien"><?= $e($this->input->post('notes') ?? ($inv->notes ?? '')) ?></textarea>
        </div>
    </div>
</div>
                </div><!-- /KIRI -->

                <!-- KANAN -->
                <div class="space-y-4">

                    <!-- File Upload (Unggah) -->
                    <div class="field-cara-unggah <?= $cara !== 'unggah' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">

                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            File Invoice <span class="text-red-500">*</span>
                        </h3>

                        <div id="dropzone"
                             class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">

                            <input type="file"
                                   name="berkas"
                                   id="berkas"
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                   accept=".pdf,.doc,.docx">

                            <svg class="w-8 h-8 text-gray-400 mb-2"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                            </svg>

                            <p class="text-sm text-gray-600 font-medium text-center mb-2">
                                Tarik file ke sini atau pilih dari perangkat
                            </p>

                            <span class="px-3 py-1.5 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 shadow-sm">
                                Pilih file
                            </span>
                        </div>

                        <div id="filePreview"
                             class="<?= $adaFile ? '' : 'hidden' ?> border border-gray-300 rounded-xl p-4 bg-white flex items-center justify-between">

                            <div class="flex items-center gap-3">

                                <svg class="w-8 h-8 text-blue-500"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 00.707.293l5.414 5.414a1 1 0 00.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>

                                <div>
                                    <p id="dzNama" class="text-sm font-bold text-gray-800 truncate max-w-[200px]">
                                        <?= $e($fileNama) ?: 'Belum ada file' ?>
                                    </p>

                                    <p id="dzUkuran" class="text-xs text-gray-500">
                                        <?= $adaFile ? 'File tersimpan' : '' ?>
                                    </p>
                                </div>
                            </div>

                            <div class="flex gap-2">

                                <button type="button"
                                        onclick="document.getElementById('berkas').click()"
                                        class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">
                                    Ganti
                                </button>

                                <button type="button"
                                        onclick="resetFile()"
                                        class="px-3 py-1.5 border border-red-200 bg-red-50 rounded-lg text-xs font-medium text-red-600 hover:bg-red-100">
                                    Hapus
                                </button>
                            </div>
                        </div>

                        <p class="text-xs text-red-500 mt-2 <?= $uploadErr ? '' : 'hidden' ?>" id="dzErr">
                            <?= $e($uploadErr) ?>
                        </p>

                        <p class="text-xs text-gray-500 mt-2">
                            PDF, DOC, atau DOCX. Maksimal 5 MB.
                        </p>
                    </div>

                    <!-- Info Template -->
                    <div class="field-cara-template <?= $cara !== 'template' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">

                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            <?= $isEdit ? 'Info invoice' : 'Dibuat otomatis' ?>
                        </h3>

                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500">Nomor</span>
                            <span class="font-medium text-gray-900"><?= $e($nomorTampil) ?></span>
                        </div>

                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500">Tanggal</span>
                            <span class="font-medium text-gray-900"><?= $tglTampil ?></span>
                        </div>

                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500"><?= $isEdit ? 'Status' : 'Status awal' ?></span>
                            <span class="px-3 py-0.5 rounded-full text-xs font-semibold <?= $pillStyle ?>">
                                <?= $e($statusTampil) ?>
                            </span>
                        </div>

                        <div class="border-t border-gray-100 mt-3 pt-3 text-xs text-gray-500 leading-relaxed">
                            <?= $isEdit
                                ? 'Nomor dan tanggal terkunci. Status diubah dari halaman detail invoice.'
                                : 'Pengingat jatuh tempo dikirim dari halaman detail invoice, lewat email atau WhatsApp.' ?>
                        </div>
                    </div>

                    <!-- Info Unggah -->
                    <div class="field-cara-unggah <?= $cara !== 'unggah' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">

                        <h3 class="text-lg font-bold text-gray-900 mb-4">
                            <?= $isEdit ? 'Ringkasan' : 'Dibuat Otomatis' ?>
                        </h3>

                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500"><?= $isEdit ? 'Status' : 'Status awal' ?></span>

                            <span class="px-3 py-0.5 rounded-full text-xs font-semibold <?= $pillStyle ?>">
                                <?= $e($statusTampil) ?>
                            </span>
                        </div>

                        <div class="border-t border-gray-100 mt-3 pt-3 text-xs text-gray-500 leading-relaxed">
                            <?= $isEdit
                                ? 'Status diubah dari halaman detail invoice.'
                                : 'Pengingat jatuh tempo dikirim dari halaman detail invoice, lewat email atau WhatsApp.' ?>
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 flex flex-col gap-3">

                        <button type="submit"
                                class="w-full px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20">
                            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Invoice' ?>
                        </button>

                        <button type="submit"
                                class="field-cara-template <?= $cara !== 'template' ? 'hidden' : '' ?> w-full px-6 py-2.5 bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                formaction="<?= site_url('pembayaran/pratinjau-invoice') ?>"
                                formtarget="_blank"
                                formenctype="application/x-www-form-urlencoded"
                                formnovalidate>
                            Pratinjau PDF
                        </button>

                        <!-- Batal juga kembali ke halaman asal (data-smart-back) -->
                        <a href="<?= $urlKembali ?>"
                           data-smart-back
                           class="w-full px-6 py-2 text-center text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">
                            Batal
                        </a>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
    const EDIT = <?= $isEdit ? 'true' : 'false' ?>;
    const PW = <?= json_encode($pwData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    // --- TOGGLE VISIBILITY & CARD STYLE ---
    function toggleSumber(val) {
        document.querySelectorAll('.field-sumber-baru').forEach(el => {
            el.classList.toggle('hidden', val !== 'baru');
        });

        document.querySelectorAll('.field-sumber-penawaran').forEach(el => {
            el.classList.toggle('hidden', val !== 'penawaran');
        });

        document.querySelectorAll('.invoice-option').forEach(label => {
            const radio = label.querySelector('input[name="sumber"]');

            if (radio) {
                label.classList.toggle('selected', radio.checked);
            }
        });
    }

    function toggleCara(val) {
        document.querySelectorAll('.field-cara-template').forEach(el => {
            el.classList.toggle('hidden', val !== 'template');
        });

        document.querySelectorAll('.field-cara-unggah').forEach(el => {
            el.classList.toggle('hidden', val !== 'unggah');
        });
    }

    // --- FILE PREVIEW ---
    const fileInput = document.getElementById('berkas');
    const dropzone = document.getElementById('dropzone');
    const filePreview = document.getElementById('filePreview');
    const fName = document.getElementById('dzNama');
    const fSize = document.getElementById('dzUkuran');
    const errText = document.getElementById('dzErr');
    const MAKS = 5 * 1024 * 1024;

    function ukuran(b) {
        return b >= 1048576
            ? (b / 1048576).toFixed(1) + ' MB'
            : Math.max(1, Math.round(b / 1024)) + ' KB';
    }

    function pesan(t) {
        if (!errText) return;
        errText.textContent = t || '';
        errText.classList.toggle('hidden', !t);
    }

    function tampil() {
        if (!fileInput || !dropzone || !filePreview || !fName || !fSize) return;

        const f = fileInput.files && fileInput.files[0];

        if (f) {
            dropzone.classList.add('hidden');
            filePreview.classList.remove('hidden');
            fName.textContent = f.name;
            fSize.textContent = ukuran(f.size);
        } else if (fName.textContent !== 'Belum ada file') {
            dropzone.classList.add('hidden');
            filePreview.classList.remove('hidden');
        } else {
            dropzone.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }
    }

    function cekFile() {
        if (!fileInput) return;

        const f = fileInput.files && fileInput.files[0];

        if (!f) return tampil();

        if (!/\.(pdf|docx?)$/i.test(f.name)) {
            pesan('Format file harus PDF, DOC, atau DOCX.');
            fileInput.value = '';
            return tampil();
        }

        if (f.size > MAKS) {
            pesan('Ukuran file maksimal 5 MB.');
            fileInput.value = '';
            return tampil();
        }

        pesan('');
        tampil();
    }

    if (fileInput && dropzone) {
        fileInput.addEventListener('change', cekFile);

        ['dragenter', 'dragover'].forEach(n => {
            dropzone.addEventListener(n, e => {
                e.preventDefault();
                dropzone.classList.add('bg-gray-100');
            });
        });

        ['dragleave', 'drop'].forEach(n => {
            dropzone.addEventListener(n, e => {
                e.preventDefault();
                dropzone.classList.remove('bg-gray-100');
            });
        });

        dropzone.addEventListener('drop', e => {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                cekFile();
            }
        });
    }

    function resetFile() {
        if (fileInput) fileInput.value = '';
        pesan('');

        if (fName) fName.textContent = 'Belum ada file';
        if (fSize) fSize.textContent = '';

        if (dropzone) dropzone.classList.remove('hidden');
        if (filePreview) filePreview.classList.add('hidden');
    }

    // --- CUSTOM DROPDOWN LOGIC ---
    function setDropdownValue(targetId, value, label) {
        const dd = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);

        if (!dd) return;

        const input = dd.querySelector('input[type="hidden"]');
        const text = dd.querySelector('.dropdown-text');

        input.value = value;
        text.textContent = label;

        text.classList.toggle('text-gray-400', value === '');
        text.classList.toggle('text-gray-900', value !== '');

        dd.querySelectorAll('li').forEach(li => {
            li.classList.remove('bg-red-50', 'text-[#9f2b2b]', 'font-bold');

            if (li.dataset.value === String(value)) {
                li.classList.add('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
            }
        });

        input.dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn = dd.querySelector('.dropdown-btn');
        const menu = dd.querySelector('.dropdown-menu');
        const icon = btn.querySelector('svg');
        const search = dd.querySelector('.dropdown-search');
        const list = dd.querySelector('.dropdown-list');

        const filterList = (q) => {
            q = q.toLowerCase().trim();

            list.querySelector('.no-result')?.remove();

            let found = false;

            list.querySelectorAll('li').forEach(li => {
                if (li.dataset.value === undefined) return;

                const match = !q || li.textContent.toLowerCase().includes(q);
                li.classList.toggle('hidden', !match);
                found = found || match;
            });

            if (!found && q) {
                const li = document.createElement('li');
                li.className = 'no-result px-3 py-2 text-gray-400 italic text-center cursor-default';
                li.textContent = 'Tidak ada hasil';
                list.appendChild(li);
            }
        };

        const resetList = () => {
            if (search) search.value = '';

            list.querySelectorAll('li').forEach(li => li.classList.remove('hidden'));
            list.querySelector('.no-result')?.remove();
        };

        const close = () => {
            dd.classList.remove('open', 'drop-up');

            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');

            icon.classList.remove('rotate-180');

            setTimeout(() => menu.classList.add('hidden'), 200);
        };

        const open = () => {
            document.querySelectorAll('.custom-dropdown').forEach(d => {
                if (d !== dd && d.classList.contains('open')) {
                    d.classList.remove('open', 'drop-up');

                    const m = d.querySelector('.dropdown-menu');
                    m.classList.add('hidden', 'opacity-0', 'scale-95');
                    m.classList.remove('opacity-100', 'scale-100');

                    d.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
                }
            });

            resetList();

            menu.style.visibility = 'hidden';
            menu.classList.remove('hidden');

            const rect = btn.getBoundingClientRect();
            const h = menu.offsetHeight;
            const below = window.innerHeight - rect.bottom;

            dd.classList.toggle('drop-up', below < h + 16 && rect.top > below);

            menu.classList.add('hidden');
            menu.style.visibility = '';

            dd.classList.add('open');
            menu.classList.remove('hidden');

            void menu.offsetWidth;

            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');

            icon.classList.add('rotate-180');

            if (search) setTimeout(() => search.focus(), 50);
        };

        btn.addEventListener('click', e => {
            e.stopPropagation();
            dd.classList.contains('open') ? close() : open();
        });

        dd.addEventListener('click', e => {
            const li = e.target.closest('li');

            if (!li || li.dataset.value === undefined) return;

            setDropdownValue(dd.dataset.target, li.dataset.value, li.textContent.trim());

            // Auto-fill logic if Penawaran changes
            if (dd.dataset.target === 'penawaran_id' && li.dataset.value) {
                isiDariPenawaran(li.dataset.value);
            }

            // Trigger cascading for Klien -> Project
            if (dd.dataset.target === 'klien_id') {
                sinkronProject(li.dataset.value);
            }

            close();
        });

        if (search) {
            search.addEventListener('input', e => filterList(e.target.value));

            search.addEventListener('click', e => e.stopPropagation());

            search.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();

                    const first = [...list.querySelectorAll('li')].find(li =>
                        !li.classList.contains('hidden') && li.dataset.value
                    );

                    if (first) {
                        setDropdownValue(dd.dataset.target, first.dataset.value, first.textContent.trim());

                        if (dd.dataset.target === 'penawaran_id' && first.dataset.value) {
                            isiDariPenawaran(first.dataset.value);
                        }

                        if (dd.dataset.target === 'klien_id') {
                            sinkronProject(first.dataset.value);
                        }

                        close();
                        btn.focus();
                    }
                } else if (e.key === 'Escape') {
                    close();
                    btn.focus();
                }
            });
        }
    });

    document.addEventListener('click', e => {
        document.querySelectorAll('.custom-dropdown').forEach(dd => {
            if (dd.contains(e.target)) return;

            dd.classList.remove('open', 'drop-up');

            const m = dd.querySelector('.dropdown-menu');

            m.classList.add('opacity-0', 'scale-95');
            m.classList.remove('opacity-100', 'scale-100');

            dd.querySelector('.dropdown-btn svg').classList.remove('rotate-180');

            setTimeout(() => m.classList.add('hidden'), 200);
        });
    });

    // --- CASCADING PROJECT (Untuk sumber = baru) ---
    function sinkronProject(klienId) {
        const dd = document.querySelector('.custom-dropdown[data-target="project_id"]');

        if (!dd) return;

        const list = dd.querySelector('.dropdown-list');
        const search = dd.querySelector('.dropdown-search');

        if (search) search.value = '';

        if (!klienId) {
            list.innerHTML = '<li class="px-3 py-2 text-gray-400 italic cursor-default">Pilih Klien Terlebih Dahulu</li>';
            setDropdownValue('project_id', '', 'Pilih Klien Terlebih Dahulu');
            return;
        }

        const projects = <?= json_encode($projectList ?? []) ?>;
        const filtered = projects.filter(p => String(p.klien_id) === String(klienId));

        if (filtered.length === 0) {
            list.innerHTML = '<li class="px-3 py-2 text-gray-400 italic cursor-default">Klien ini belum punya project</li>';
            setDropdownValue('project_id', '', 'Belum ada project');
            return;
        }

        list.innerHTML = '<li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih Project</li>';

        filtered.forEach(p => {
            const li = document.createElement('li');

            li.className = 'px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors';
            li.dataset.value = p.id;
            li.textContent = p.nama_project;

            list.appendChild(li);
        });

        const currentProject = document.getElementById('project_id').value;
        const selected = filtered.find(p => String(p.id) === String(currentProject));

        if (selected) {
            setDropdownValue('project_id', selected.id, selected.nama_project);
        } else {
            setDropdownValue('project_id', '', 'Pilih Project');
        }
    }

    // --- AUTO FILL DARI PENAWARAN ---
    function isiDariPenawaran(id) {
        const d = PW[id];

        if (!d) return;

        // Set Klien
        if (d.klien_id) {
            const klienLi = document.querySelector(`.custom-dropdown[data-target="klien_id"] li[data-value="${d.klien_id}"]`);

            if (klienLi) {
                setDropdownValue('klien_id', d.klien_id, klienLi.textContent.trim());

                // Trigger cascading untuk load project
                sinkronProject(d.klien_id);

                // Set project setelah cascading selesai
                setTimeout(() => {
                    const projLi = document.querySelector(`.custom-dropdown[data-target="project_id"] li[data-value="${d.project_id}"]`);

                    if (projLi) {
                        setDropdownValue('project_id', d.project_id, projLi.textContent.trim());
                    }
                }, 100);
            }
        }

        // Set Perihal & Isi
        if (document.getElementById('perihal')) {
            document.getElementById('perihal').value = d.perihal;
        }

        if (document.getElementById('isi_surat')) {
            document.getElementById('isi_surat').value = d.isi;
        }
    }

    // Trigger on load
    document.addEventListener('DOMContentLoaded', () => {
        const klienVal = document.getElementById('klien_id')?.value;

        if (klienVal) {
            sinkronProject(klienVal);
        }
    });


let itemIndex = document.querySelectorAll('.invoice-item').length;
let taxIndex = document.querySelectorAll('.tax-row').length;

const rupiah = angka => new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0
}).format(angka || 0);

function tambahItem() {
    const index = itemIndex++;

    const row = document.createElement('tr');
    row.className = 'invoice-item border-b';

    row.innerHTML = `
        <td class="py-2 pr-2">
            <input type="text" name="items[${index}][description]"
                required placeholder="Nama produk/jasa"
                class="w-full min-w-[150px] px-2 py-2 border rounded-lg">
        </td>
        <td class="py-2 px-2">
            <input type="number" min="0.01" step="0.01"
                name="items[${index}][quantity]" value="1"
                oninput="hitungInvoice()" required
                class="item-qty w-full px-2 py-2 border rounded-lg">
        </td>
        <td class="py-2 px-2">
            <input type="text" name="items[${index}][unit]"
                value="unit" class="w-full px-2 py-2 border rounded-lg">
        </td>
        <td class="py-2 px-2">
            <input type="number" min="0" step="1"
                name="items[${index}][unit_price]" value="0"
                oninput="hitungInvoice()" required
                class="item-price w-full px-2 py-2 border rounded-lg">
        </td>
        <td class="py-2 pl-2 text-right font-medium item-total">Rp0</td>
        <td class="text-right">
            <button type="button" onclick="hapusItem(this)"
                class="text-red-500 px-1">×</button>
        </td>
    `;

    document.getElementById('invoiceItems').appendChild(row);
    hitungInvoice();
}

function hapusItem(button) {
    const rows = document.querySelectorAll('.invoice-item');

    if (rows.length <= 1) {
        alert('Minimal harus ada satu item invoice.');
        return;
    }

    button.closest('.invoice-item').remove();
    hitungInvoice();
}

function tambahPajak() {
    const index = taxIndex++;

    const row = document.createElement('div');
    row.className = 'tax-row grid grid-cols-12 gap-2';

    row.innerHTML = `
        <input name="taxes[${index}][tax_name]"
            placeholder="Nama pajak"
            class="col-span-5 min-w-0 px-2 py-2 border rounded-lg text-sm">

        <select name="taxes[${index}][tax_type]"
            class="tax-type col-span-3 min-w-0 px-1 py-2 border rounded-lg text-xs"
            onchange="hitungInvoice()">
            <option value="percentage">%</option>
            <option value="fixed">Rp</option>
        </select>

        <input type="number" min="0" step="0.01"
            name="taxes[${index}][tax_rate]" value="0"
            oninput="hitungInvoice()"
            class="tax-rate col-span-3 min-w-0 px-2 py-2 border rounded-lg text-sm">

        <button type="button" onclick="hapusPajak(this)"
            class="col-span-1 text-red-500">×</button>
    `;

    document.getElementById('invoiceTaxes').appendChild(row);
    hitungInvoice();
}

function hapusPajak(button) {
    button.closest('.tax-row').remove();
    hitungInvoice();
}

function hitungInvoice() {
    let subtotal = 0;

    document.querySelectorAll('.invoice-item').forEach(row => {
        const qty = Math.max(0, Number(row.querySelector('.item-qty').value) || 0);
        const price = Math.max(0, Number(row.querySelector('.item-price').value) || 0);
        const total = qty * price;

        subtotal += total;
        row.querySelector('.item-total').textContent = rupiah(total);
    });

    const discount = Math.min(
        subtotal,
        Math.max(0, Number(document.getElementById('discountAmount').value) || 0)
    );

    const dasarPajak = subtotal - discount;
    let totalPajak = 0;

    document.querySelectorAll('.tax-row').forEach(row => {
        const type = row.querySelector('.tax-type').value;
        const rate = Math.max(0, Number(row.querySelector('.tax-rate').value) || 0);

        totalPajak += type === 'percentage'
            ? dasarPajak * rate / 100
            : rate;
    });

    document.getElementById('subtotalText').textContent = rupiah(subtotal);
    document.getElementById('taxTotalText').textContent = rupiah(totalPajak);
    document.getElementById('grandTotalText').textContent =
        rupiah(dasarPajak + totalPajak);
}

document.addEventListener('DOMContentLoaded', hitungInvoice);
hitungInvoice();

    </script>

    <!-- SMART BACK BUTTON (script bersama) -->
    <?php $this->load->view('partials/smart_back'); ?>

</body>
</html>