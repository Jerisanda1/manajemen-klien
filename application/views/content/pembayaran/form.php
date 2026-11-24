<?php
/**
 * @var CI_Controller $this
 * @var object|null $penawaran
 * @var array $klienList, $projectList
 * @var string $nomorBaru, $from, $action, $title, $mode, $uploadErr
 *
 * + Smart Back Button — Kembali & Batal menuju halaman asal form dibuka
 *   (daftar pembayaran / detail penawaran / detail project). $urlKembali jadi fallback.
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$isEdit = !empty($penawaran);

$preKlien = isset($preKlien) ? $preKlien : 0;
$preProject = isset($preProject) ? $preProject : 0;
$val = function ($f, $default = '') use ($penawaran, $preKlien, $preProject) {
    $context = ['klien_id' => $preKlien, 'project_id' => $preProject];
    $fallback = isset($penawaran->$f) ? $penawaran->$f : (isset($context[$f]) ? $context[$f] : $default);
    return set_value($f, $fallback);
};
$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="text-xs text-red-500 mt-1">' . htmlspecialchars($m) . '</p>' : '';
};

$mode      = isset($mode) ? $mode : 'template';
$uploadErr = isset($uploadErr) ? $uploadErr : '';
$from      = isset($from) ? $from : 'list';
// Fallback bila asal tidak diketahui (mis. URL dibuka langsung)
$urlKembali = $isEdit && $from === 'detail'
    ? site_url('pembayaran/detail/penawaran/' . (int) $penawaran->id)
    : site_url('pembayaran?tab=penawaran');

// Kunci tetap agar asal tidak hilang saat validasi gagal (URL form bisa berubah)
$backKey = 'penawaran-form-' . ($isEdit ? (int) $penawaran->id : 'new');

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($t) use ($bulan) {
    $x = $t ? strtotime($t) : false;
    return $x ? date('j', $x) . ' ' . $bulan[(int) date('n', $x)] . ' ' . date('Y', $x) : '-';
};

$nomor   = $isEdit ? $penawaran->no_penawaran : $nomorBaru;
$tanggal = $isEdit ? $penawaran->tanggal_penawaran : date('Y-m-d');
$status  = $isEdit ? $penawaran->status : 'Draft';

$posted = $this->input->method() === 'post';
$kop = $posted ? (bool) $this->input->post('sertakan_kop') : ($isEdit ? (bool) $penawaran->sertakan_kop : true);
$ttd = $posted ? (bool) $this->input->post('sertakan_ttd') : ($isEdit ? (bool) $penawaran->sertakan_ttd : true);

$picAwal = '';
$labelKlien = 'Pilih Klien';
foreach ($klienList as $k) {
    if ((string) $val('klien_id') === (string) $k->id) {
        $picAwal = $k->nama_pic;
        $labelKlien = $k->nama_klien;
    }
}
$labelProject = 'Pilih Project';
foreach ($projectList as $p) {
    if ((string) $val('project_id') === (string) $p->id) {
        $labelProject = $p->nama_project;
    }
}

$adaFile  = $isEdit && !empty($penawaran->file_path);
$fileNama = $adaFile ? $penawaran->file_nama : '';
$fileUrl  = $adaFile ? site_url('pembayaran/berkas/' . (int) $penawaran->id) : '';
$kunci = $isEdit;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?> - Tridatu</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
    <style>
        .custom-dropdown.drop-up .dropdown-menu { top: auto; bottom: 100%; margin-top: 0; margin-bottom: 4px; }
        .dropdown-menu { transition: opacity 200ms cubic-bezier(0.4,0,0.2,1), transform 200ms cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen">

    <div class="max-w-[1280px] mx-auto p-4 md:p-6 lg:p-8">
        
        <!-- Header -->
        <div class="mb-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <!-- SMART BACK BUTTON (href = fallback bila asal tidak diketahui) -->
                <a href="<?= $urlKembali ?>" id="backBtn" data-back-key="<?= $e($backKey) ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 transition-colors">
                    <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali
                </a>
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight"><?= $e($title) ?></h2>
            </div>
            <?php if($isEdit): ?>
                <div class="bg-gray-100 px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">
                    Status: <span class="font-bold text-gray-900"><?= $e($status) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <form id="bpForm" method="post" action="<?= $e($action) ?>" enctype="multipart/form-data" novalidate>
            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>
            <input type="hidden" name="pv_nomor" value="<?= $e($nomor) ?>">
            <input type="hidden" name="pv_tanggal" value="<?= $e($tanggal) ?>">
            <input type="hidden" name="pv_status" value="<?= $e($status) ?>">

            <div class="grid grid-cols-1 lg:grid-cols-[1fr_400px] gap-6 items-start">
                
                <!-- KIRI -->
                <div class="space-y-4">
                    
                    <!-- Card 1: Cara Pembuatan -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Cara Pembuatan</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="mode" value="template" class="peer sr-only" <?= $mode === 'template' ? 'checked' : '' ?> <?= $kunci ? 'disabled' : '' ?> onchange="toggleMode('template')">
                                <div class="p-4 border rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-gray-50 h-full <?= $kunci && $mode !== 'template' ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span class="font-bold text-sm text-gray-900">Template Baku</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed">Nomor, tanggal, dan format dibuat otomatis. Admin cukup mengisi perihal dan isi.</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="mode" value="unggah" class="peer sr-only" <?= $mode === 'unggah' ? 'checked' : '' ?> <?= $kunci ? 'disabled' : '' ?> onchange="toggleMode('unggah')">
                                <div class="p-4 border rounded-xl peer-checked:border-[#9f2b2b] peer-checked:bg-red-50 transition-all hover:bg-gray-50 h-full <?= $kunci && $mode !== 'unggah' ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                        <span class="font-bold text-sm text-gray-900">Unggah Manual</span>
                                    </div>
                                    <p class="text-xs text-gray-500 leading-relaxed">Pakai file penawaran yang sudah dibuat sendiri (PDF atau Word).</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Card 2: Data Penawaran -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-5">Data penawaran</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-5">
                            
                            <!-- Klien -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Klien <span class="text-red-500">*</span></label>
                                <div class="relative custom-dropdown" data-target="klien_id">
                                    <button type="button" class="dropdown-btn flex items-center justify-between w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                        <span class="dropdown-text truncate <?= $val('klien_id') ? 'text-gray-900' : 'text-gray-400' ?>"><?= $e($labelKlien) ?></span>
                                        <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                        <div class="p-2 border-b border-gray-100">
                                            <div class="relative">
                                                <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari klien..." autocomplete="off">
                                            </div>
                                        </div>
                                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                            <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih Klien</li>
                                            <?php foreach ($klienList as $k): ?>
                                                <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= (string) $val('klien_id') === (string) $k->id ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>" data-value="<?= (int) $k->id ?>" data-pic="<?= $e($k->nama_pic) ?>"><?= $e($k->nama_klien) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="klien_id" id="klien_id" value="<?= $e($val('klien_id')) ?>">
                                </div>
                                <?= $err('klien_id') ?>
                            </div>

                            <!-- Project -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Project <span class="text-red-500">*</span></label>
                                <div class="relative custom-dropdown" data-target="project_id">
                                    <button type="button" class="dropdown-btn flex items-center justify-between w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                        <span class="dropdown-text truncate <?= $val('project_id') ? 'text-gray-900' : 'text-gray-400' ?>"><?= $e($labelProject) ?></span>
                                        <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                    <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                        <div class="p-2 border-b border-gray-100">
                                            <div class="relative">
                                                <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                                <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari project..." autocomplete="off">
                                            </div>
                                        </div>
                                        <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                            <li class="px-3 py-2 text-gray-400 italic cursor-default">Pilih Klien Terlebih Dahulu</li>
                                        </ul>
                                    </div>
                                    <input type="hidden" name="project_id" id="project_id" value="<?= $e($val('project_id')) ?>">
                                </div>
                                <?= $err('project_id') ?>
                            </div>

                            <!-- Hanya mode unggah: No & Tgl -->
                            <div class="field-unggah <?= $mode !== 'unggah' ? 'hidden' : '' ?>">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Nomor Penawaran <span class="text-red-500">*</span></label>
                                <input id="no_penawaran" type="text" name="no_penawaran" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" value="<?= $e($val('no_penawaran', $nomorBaru)) ?>">
                                <?= $err('no_penawaran') ?>
                            </div>
                            <div class="field-unggah <?= $mode !== 'unggah' ? 'hidden' : '' ?>">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Penawaran <span class="text-red-500">*</span></label>
                                <input id="tanggal_penawaran" type="date" name="tanggal_penawaran" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" value="<?= $e($val('tanggal_penawaran', date('Y-m-d'))) ?>">
                                <?= $err('tanggal_penawaran') ?>
                            </div>

                            <!-- Perihal -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Perihal <span class="text-red-500">*</span></label>
                                <input id="perihal" type="text" name="perihal" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="[Perihal Penawaran]" value="<?= $e($val('perihal')) ?>">
                                <?= $err('perihal') ?>
                            </div>

                            <!-- Berlaku Sampai & PIC -->
                            <div class="field-template <?= $mode !== 'template' ? 'hidden' : '' ?>">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Berlaku Sampai</label>
                                <input id="berlaku_sampai" type="date" name="berlaku_sampai" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" value="<?= $e($val('berlaku_sampai')) ?>">
                                <?= $err('berlaku_sampai') ?>
                            </div>
                            <div class="field-template <?= $mode !== 'template' ? 'hidden' : '' ?>">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">PIC Klien</label>
                                <input id="pic" type="text" class="w-full px-3 py-2.5 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-500 cursor-not-allowed" readonly tabindex="-1" value="<?= $e($picAwal) ?>">
                            </div>

                            <!-- Isi Surat -->
                            <div class="field-template md:col-span-2 <?= $mode !== 'template' ? 'hidden' : '' ?>">
                                <label class="block text-xs font-medium text-gray-700 mb-1.5">Isi Surat</label>
                                <textarea id="isi_surat" name="isi_surat" class="w-full px-3 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] min-h-[150px]"><?= $e($val('isi_surat')) ?></textarea>
                            </div>

                            <!-- Checkboxes -->
                            <div class="field-template md:col-span-2 <?= $mode !== 'template' ? 'hidden' : '' ?>">
                                <div class="flex gap-6 mt-2">
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="sertakan_kop" value="1" class="w-5 h-5 text-[#9f2b2b] rounded border-gray-300 focus:ring-[#9f2b2b]" <?= $kop ? 'checked' : '' ?>>
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Sertakan kop surat</span>
                                    </label>
                                    <label class="inline-flex items-center cursor-pointer">
                                        <input type="checkbox" name="sertakan_ttd" value="1" class="w-5 h-5 text-[#9f2b2b] rounded border-gray-300 focus:ring-[#9f2b2b]" <?= $ttd ? 'checked' : '' ?>>
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Sertakan tanda tangan</span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- KANAN -->
                <div class="space-y-4">
                    
                    <!-- Info Template -->
                    <div class="field-template <?= $mode !== 'template' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4"><?= $isEdit ? 'Info penawaran' : 'Dibuat otomatis' ?></h3>
                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500">Nomor</span>
                            <span class="font-medium text-gray-900"><?= $e($nomor) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500">Tanggal</span>
                            <span class="font-medium text-gray-900"><?= $tgl($tanggal) ?></span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500"><?= $isEdit ? 'Status' : 'Status awal' ?></span>
                            <span class="bg-gray-100 text-gray-600 border border-gray-200 px-3 py-0.5 rounded-full text-xs font-semibold"><?= $e($status) ?></span>
                        </div>
                        <div class="border-t border-gray-100 mt-3 pt-3 text-xs text-gray-500 leading-relaxed">
                            Nomor dan tanggal terkunci saat disimpan. Status bisa diubah di halaman detail.
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="field-unggah <?= $mode !== 'unggah' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">File Penawaran <span class="text-red-500">*</span></h3>
                        
                        <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <input type="file" name="berkas" id="berkas" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <p class="text-sm text-gray-600 font-medium text-center mb-2">Tarik file ke sini atau pilih dari perangkat</p>
                            <span class="px-3 py-1.5 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 shadow-sm">Pilih file</span>
                        </div>
                        
                        <div id="filePreview" class="<?= $adaFile ? '' : 'hidden' ?> border border-gray-300 rounded-xl p-4 bg-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div>
                                    <p id="dzNama" class="text-sm font-bold text-gray-800 truncate max-w-[200px]"><?= $e($fileNama) ?: 'Belum ada file' ?></p>
                                    <p id="dzUkuran" class="text-xs text-gray-500"><?= $adaFile ? 'File tersimpan' : '' ?></p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.getElementById('berkas').click()" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">Ganti</button>
                                <button type="button" onclick="resetFile()" class="px-3 py-1.5 border border-red-200 bg-red-50 rounded-lg text-xs font-medium text-red-600 hover:bg-red-100">Hapus</button>
                            </div>
                        </div>
                        <p class="text-xs text-red-500 mt-2 <?= $uploadErr ? '' : 'hidden' ?>" id="dzErr"><?= $e($uploadErr) ?></p>
                        <p class="text-xs text-gray-500 mt-2">PDF, DOC, atau DOCX. Maksimal 5 MB.</p>
                    </div>

                    <!-- Info Unggah (Ringkasan) -->
                    <div class="field-unggah <?= $mode !== 'unggah' ? 'hidden' : '' ?> bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">Ringkasan</h3>
                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500">Asal dokumen</span>
                            <span class="font-medium text-gray-900">Unggahan</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 text-sm">
                            <span class="text-gray-500"><?= $isEdit ? 'Status' : 'Status awal' ?></span>
                            <span class="bg-gray-100 text-gray-600 border border-gray-200 px-3 py-0.5 rounded-full text-xs font-semibold"><?= $e($status) ?></span>
                        </div>
                        <div class="border-t border-gray-100 mt-3 pt-3 text-xs text-gray-500 leading-relaxed">
                            Kop surat dan tanda tangan otomatis tidak dipakai, karena dokumen sudah jadi. Status bisa diubah di halaman detail.
                        </div>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 flex flex-col gap-3">
                        <button type="submit" class="w-full px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20">
                            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Penawaran' ?>
                        </button>

                        <button type="submit" class="field-template <?= $mode !== 'template' ? 'hidden' : '' ?> w-full px-6 py-2.5 bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                formaction="<?= site_url('pembayaran/pratinjau') ?>" formtarget="_blank"
                                formenctype="application/x-www-form-urlencoded" formnovalidate>
                            Pratinjau PDF
                        </button>
                        <button type="button" class="field-unggah <?= $mode !== 'unggah' ? 'hidden' : '' ?> w-full px-6 py-2.5 bg-gray-100 border border-gray-300 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors" id="btnPrevFile">
                            Pratinjau PDF
                        </button>

                        <!-- Batal juga kembali ke halaman asal (data-smart-back) -->
                        <a href="<?= $urlKembali ?>" data-smart-back class="w-full px-6 py-2 text-center text-sm font-medium text-gray-500 hover:text-gray-900 transition-colors">Batal</a>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
    // --- GANTI MODE ---
    function toggleMode(mode) {
        document.querySelectorAll('.field-template').forEach(el => el.classList.toggle('hidden', mode !== 'template'));
        document.querySelectorAll('.field-unggah').forEach(el => el.classList.toggle('hidden', mode !== 'unggah'));
    }

    // --- FILE PREVIEW ---
    const fileInput = document.getElementById('berkas');
    const dropzone = document.getElementById('dropzone');
    const filePreview = document.getElementById('filePreview');
    const fName = document.getElementById('dzNama');
    const fSize = document.getElementById('dzUkuran');
    const errText = document.getElementById('dzErr');
    const MAKS = 5 * 1024 * 1024;

    function ukuran(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }
    function pesan(t) { errText.textContent = t || ''; errText.classList.toggle('hidden', !t); }

    function tampil() {
        const f = fileInput.files && fileInput.files[0];
        if (f) {
            dropzone.classList.add('hidden');
            filePreview.classList.remove('hidden');
            fName.textContent = f.name;
            fSize.textContent = ukuran(f.size);
        } else if (fName.textContent !== 'Belum ada file') {
            // ada file lama
            dropzone.classList.add('hidden');
            filePreview.classList.remove('hidden');
        } else {
            dropzone.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }
    }

    function cekFile() {
        const f = fileInput.files && fileInput.files[0];
        if (!f) return tampil();
        if (!/\.(pdf|docx?)$/i.test(f.name)) { pesan('Format file harus PDF, DOC, atau DOCX.'); fileInput.value = ''; return tampil(); }
        if (f.size > MAKS) { pesan('Ukuran file maksimal 5 MB.'); fileInput.value = ''; return tampil(); }
        pesan('');
        tampil();
    }

    if(fileInput) {
        fileInput.addEventListener('change', cekFile);
        ['dragenter', 'dragover'].forEach(n => dropzone.addEventListener(n, e => { e.preventDefault(); dropzone.classList.add('bg-gray-100'); }));
        ['dragleave', 'drop'].forEach(n => dropzone.addEventListener(n, e => { e.preventDefault(); dropzone.classList.remove('bg-gray-100'); }));
        dropzone.addEventListener('drop', e => {
            if (e.dataTransfer && e.dataTransfer.files.length) {
                fileInput.files = e.dataTransfer.files;
                cekFile();
            }
        });
    }

    function resetFile() {
        if(fileInput) fileInput.value = '';
        pesan('');
        fName.textContent = 'Belum ada file';
        fSize.textContent = '';
        dropzone.classList.remove('hidden');
        filePreview.classList.add('hidden');
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
            if (li.dataset.value === String(value)) li.classList.add('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
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
            
            // Auto fill PIC if Klien changes
            if (dd.dataset.target === 'klien_id') {
                document.getElementById('pic').value = li.dataset.pic || '';
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
                    const first = [...list.querySelectorAll('li')].find(li => !li.classList.contains('hidden') && li.dataset.value);
                    if (first) {
                        setDropdownValue(dd.dataset.target, first.dataset.value, first.textContent.trim());
                        if (dd.dataset.target === 'klien_id') {
                            document.getElementById('pic').value = first.dataset.pic || '';
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

    // --- CASCADING PROJECT ---
    function sinkronProject(klienId) {
        const dd = document.querySelector('.custom-dropdown[data-target="project_id"]');
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
        
        // maintain previous selected value if possible
        const currentProj = document.getElementById('project_id').value;
        const label = document.querySelector('.custom-dropdown[data-target="project_id"] .dropdown-text');
        if (currentProj) {
            const match = filtered.find(p => String(p.id) === String(currentProj));
            if (match) {
                setDropdownValue('project_id', match.id, match.nama_project);
                return;
            }
        }
        setDropdownValue('project_id', '', 'Pilih Project');
    }

    // init on load
    document.addEventListener('DOMContentLoaded', () => {
        const klienVal = document.getElementById('klien_id').value;
        if (klienVal) sinkronProject(klienVal);
    });
    </script>

    <!-- SMART BACK BUTTON (script bersama) -->
    <?php $this->load->view('partials/smart_back'); ?>
</body>
</html>