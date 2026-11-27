<?php
/**
 * Halaman Detail Dokumen
 * + Smart Back Button — Kembali menuju halaman asal detail dibuka
 *   (daftar dokumen / detail project / detail klien). $urlKembali jadi fallback.
 */
$urlKembali = site_url('dokumen');

$isLocal = ($doc->storage_type === 'local');
$isOwnLocal = ($isLocal && $doc->device_id === $currentDeviceId);
$isOtherLocal = ($isLocal && !$isOwnLocal);
$isCloud = ($doc->storage_type === 'cloud');
$ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));

$tgl_akhir_raw = !empty($doc->tanggal_berakhir) ? $doc->tanggal_berakhir : '2099-12-31';
$today = new DateTime(date('Y-m-d'));
$end   = new DateTime($tgl_akhir_raw);
$diff  = (int)$today->diff($end)->format('%r%a');

if ($diff < 0) { $status_class = 'bg-red-50 text-red-600 border-red-100'; $status_text = 'Expired'; }
elseif ($diff <= 30) { $status_class = 'bg-orange-50 text-orange-600 border-orange-100'; $status_text = 'Akan Berakhir'; }
else { $status_class = 'bg-blue-50 text-blue-600 border-blue-100'; $status_text = 'Aktif'; }

$iconColor = 'text-gray-400';
if ($ext === 'pdf') { $iconColor = 'text-red-500'; }
elseif (in_array($ext, ['doc', 'docx'])) { $iconColor = 'text-blue-500'; }
elseif (in_array($ext, ['xls', 'xlsx'])) { $iconColor = 'text-emerald-500'; }
elseif (in_array($ext, ['jpg', 'jpeg', 'png'])) { $iconColor = 'text-purple-500'; }

$fileHash8 = substr($doc->file_hash ?? '', 0, 8);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen lg:h-screen lg:overflow-hidden p-4 md:p-6 lg:p-8">

    <div class="w-full h-full flex flex-col gap-4">

        <!-- Header -->
        <div class="shrink-0">
            <!-- SMART BACK BUTTON (href = fallback bila asal tidak diketahui) -->
            <a href="<?= $urlKembali ?>" id="backBtn" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-3 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight truncate"><?= htmlspecialchars($doc->nama_dokumen) ?></h2>
                    <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>"><?= $status_text ?></span>
                </div>

                <div class="flex gap-3 shrink-0">
                    <?php if (!$isOtherLocal): ?>
                    <a href="<?= site_url('dokumen/edit/'.$doc->id) ?>" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit
                    </a>
                    <?php endif; ?>
                    <button type="button" onclick="bukaModalHapus()" class="px-5 py-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:flex-1 lg:min-h-0">

            <!-- Kolom kiri -->
            <div class="flex flex-col gap-4 lg:min-h-0 lg:overflow-y-auto">
                <!-- Info Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Dokumen</h3>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-4">
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Nama Dokumen</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= htmlspecialchars($doc->nama_dokumen) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Klien</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= htmlspecialchars($doc->nama_klien) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Project</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= htmlspecialchars($doc->nama_project) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Jenis</p>
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($doc->jenis_dokumen) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Mulai Berlaku</p>
                            <p class="text-sm font-semibold text-gray-900"><?= !empty($doc->tanggal_mulai) ? date('d M Y', strtotime($doc->tanggal_mulai)) : '-' ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Berakhir</p>
                            <p class="text-sm font-semibold text-gray-900"><?= !empty($doc->tanggal_berakhir) ? date('d M Y', strtotime($doc->tanggal_berakhir)) : '-' ?></p>
                        </div>
                    </div>
                </div>

                <!-- File Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">File Dokumen</h3>

                    <?php if($isCloud): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            <div><p class="text-sm font-semibold text-blue-700">File tersimpan di Server Sistem</p><p class="text-xs text-blue-600 mt-0.5">Bisa dibuka di semua perangkat.</p></div>
                        </div>
                    <?php elseif($isOwnLocal): ?>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 mb-4 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <div><p class="text-sm font-semibold text-emerald-700">File tersimpan di Perangkat Ini</p><p class="text-xs text-emerald-600 mt-0.5">Setelah restart, browser mungkin meminta izin folder lagi. Izinkan akses atau pilih ulang folder yang sama.</p></div>
                        </div>
                    <?php endif; ?>

                    <div class="border border-gray-300 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="<?= $iconColor ?>">
                                <svg class="w-8 h-8 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-bold text-gray-900 truncate" title="<?= htmlspecialchars($doc->file_name) ?>"><?= htmlspecialchars($doc->file_name) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= $doc->file_size ? round($doc->file_size / (1024*1024), 2) . ' MB' : 'Ukuran tidak diketahui' ?>
                                    • <?= strtoupper($ext) ?>
                                </p>
                            </div>
                        </div>

                        <?php if($isCloud): ?>
                            <a href="<?= site_url('dokumen/unduh/'.$doc->id) ?>" class="shrink-0 px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh File
                            </a>
                        <?php elseif($isOwnLocal): ?>
                            <button type="button" id="btnSyncDetail" class="shrink-0 px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                <span id="btnSyncDetailText">Sync ke Server</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Kolom kanan: Pratinjau -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col lg:min-h-0">
                <h3 class="text-lg font-bold text-gray-900 mb-4 shrink-0">Pratinjau Dokumen</h3>
                <div class="flex-1 bg-gray-400 rounded-lg flex items-center justify-center p-3 sm:p-4 overflow-hidden min-h-[400px] lg:min-h-0" id="previewArea">
                    <?php if($isCloud): ?>
                        <?php if(in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                            <img src="<?= site_url('dokumen/preview/'.$doc->id) ?>" alt="Preview" class="max-w-full max-h-full object-contain bg-white shadow-2xl rounded">
                        <?php elseif($ext === 'pdf'): ?>
                            <div class="w-full h-full max-w-3xl mx-auto bg-white shadow-2xl rounded overflow-hidden">
                                <iframe src="<?= site_url('dokumen/preview/'.$doc->id) ?>#toolbar=0&navpanes=0&scrollbar=0&view=Fit" class="w-full h-full border-none"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 font-medium">Pratinjau tidak tersedia untuk format <?= $ext ?>.</p>
                            </div>
                        <?php endif; ?>
                    <?php elseif($isOwnLocal): ?>
                        <!-- Loading state (awal) -->
                        <div id="localPreviewLoading" class="text-white font-medium flex flex-col items-center">
                            <svg class="w-8 h-8 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Memuat Pratinjau...
                        </div>

                        <!-- Content (diisi JS) -->
                        <div id="localPreviewContent" class="hidden w-full h-full flex items-center justify-center"></div>

                        <!-- Folder lokal mungkin memerlukan izin ulang setelah Chrome dibuka kembali -->
                        <div id="localPreviewError" class="hidden bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex-col items-center justify-center text-center">
                            <svg class="w-16 h-16 text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-gray-600 font-bold">Folder atau File Belum Dapat Diakses</p>
                            <p class="text-sm text-gray-500 mt-2 mb-4">Izinkan akses folder yang tersimpan. Jika folder dipindah atau tidak ditemukan, pilih kembali folder utama yang sama. File lokal tidak otomatis tersimpan di server.</p>
                            <div class="flex flex-wrap justify-center gap-2">
                                <button type="button" id="btnIzinFolder" class="px-4 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors">
                                    Izinkan Akses Folder
                                </button>
                                <button type="button" id="btnPilihFolder" class="px-4 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                                    Pilih Folder Utama
                                </button>
                            </div>
                        </div>

                        <!-- Locked (FF/Safari) -->
                        <div id="localPreviewLocked" class="hidden bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex-col items-center justify-center text-center">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            <p class="text-gray-600 font-bold">Pratinjau Tidak Tersedia</p>
                            <p class="text-sm text-gray-500 mt-2">Browser Anda belum mendukung pratinjau lokal. Buka file langsung dari folder Downloads.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL KONFIRMASI HAPUS -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>
        <div id="modalContent" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Dokumen?</h3>
            <p class="text-center text-gray-500 mb-6 text-sm">
                Apakah Anda yakin ingin menghapus dokumen <br>
                <strong class="text-gray-900 text-base"><?= htmlspecialchars($doc->nama_dokumen) ?></strong>?<br>
                <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
            </p>
            <div class="flex justify-center gap-3">
                <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
                <button type="button" onclick="eksekusiHapus()" id="btnHapusConfirm" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm disabled:opacity-50">Ya, Hapus</button>
            </div>
        </div>
    </div>

    <!-- Container Toast -->
    <div id="toastContainer" class="fixed top-5 right-5 z-[60] flex flex-col gap-2 pointer-events-none"></div>

    <style>
        @keyframes toastIn  { from { opacity:0; transform:translateX(100%); } to { opacity:1; transform:translateX(0); } }
        @keyframes toastOut { from { opacity:1; transform:translateX(0); }    to { opacity:0; transform:translateX(100%); } }
        .toast-item { animation: toastIn 0.3s ease-out; }
        .toast-item.hide { animation: toastOut 0.3s ease-in forwards; }
    </style>

    <script src="<?= base_url('assets/js/tridatu-fs.js?v=' . filemtime(FCPATH . 'assets/js/tridatu-fs.js')) ?>"></script>
    <script>
    // =====================================================
    // DATA DARI SERVER
    // =====================================================
    const DOCUMENT_CSRF_TOKEN = <?= json_encode($documentCsrfToken) ?>;
    const DOC = {
        id:         <?= (int) $doc->id ?>,
        storage:    <?= json_encode($doc->storage_type) ?>,
        fileName:   <?= json_encode($doc->file_name) ?>,
        fileHash:   <?= json_encode($doc->file_hash) ?>,
        fileSize:   <?= (int) ($doc->file_size ?? 0) ?>,
        mimeType:   <?= json_encode($doc->mime_type) ?>,
        klienId:    <?= (int) $doc->client_id ?>,
        klienNama:  <?= json_encode($doc->nama_klien) ?>,
        deviceId:   <?= json_encode($doc->device_id) ?>,
        nama:       <?= json_encode($doc->nama_dokumen) ?>,
        ext:        <?= json_encode($ext) ?>,
    };
    const IS_OWN_LOCAL = <?= $isOwnLocal ? 'true' : 'false' ?>;
    const IS_CLOUD     = <?= $isCloud ? 'true' : 'false' ?>;

    // =====================================================
    // DEVICE ID
    // =====================================================
    const DEVICE_ID = (() => {
        const serverId = <?= json_encode($currentDeviceId) ?>;
        let id = serverId || localStorage.getItem('tridatu_device_id');
        if (!id) id = (crypto.randomUUID && crypto.randomUUID()) || (Date.now().toString(36) + Math.random().toString(36).slice(2));
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
    })();

    // =====================================================
    // TOAST
    // =====================================================
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
        setTimeout(() => { el.classList.add('hide'); setTimeout(() => el.remove(), 300); }, 4000);
    }

    // =====================================================
    // HASH
    // =====================================================
    async function hitungHash(blob) {
        const buf = await crypto.subtle.digest('SHA-256', await blob.arrayBuffer());
        return [...new Uint8Array(buf)].map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // =====================================================
    // PRATINJAU LOCAL (OWN LOCAL)
    // =====================================================
    let currentBlobUrl = null;

    function revokeBlobUrl() {
        if (currentBlobUrl) {
            try { URL.revokeObjectURL(currentBlobUrl); } catch (e) {}
            currentBlobUrl = null;
        }
    }

    function showPreviewState(state) {
        document.getElementById('localPreviewLoading').classList.add('hidden');
        document.getElementById('localPreviewContent').classList.add('hidden');
        const errEl = document.getElementById('localPreviewError');
        errEl.classList.add('hidden');
        errEl.classList.remove('flex');
        const lockEl = document.getElementById('localPreviewLocked');
        lockEl.classList.add('hidden');
        lockEl.classList.remove('flex');

        if (state === 'loading') {
            document.getElementById('localPreviewLoading').classList.remove('hidden');
        } else if (state === 'error') {
            errEl.classList.remove('hidden');
            errEl.classList.add('flex');
        } else if (state === 'locked') {
            lockEl.classList.remove('hidden');
            lockEl.classList.add('flex');
        } else if (state === 'content') {
            document.getElementById('localPreviewContent').classList.remove('hidden');
        }
    }

    async function loadLocalPreview() {
        if (!IS_OWN_LOCAL) return false;

        // FF/Safari: dikunci
        if (!TridatuFS.isSupported()) {
            showPreviewState('locked');
            return false;
        }

        showPreviewState('loading');
        try {
            const file = await TridatuFS.readFile({
                documentId: DOC.id,
                klienId:   DOC.klienId,
                klienNama: DOC.klienNama,
                fileName:  DOC.fileName,
                fileHash:  DOC.fileHash
            });

            if (!file) {
                showPreviewState('error');
                return false;
            }

            revokeBlobUrl();
            currentBlobUrl = URL.createObjectURL(file);

            const contentDiv = document.getElementById('localPreviewContent');
            contentDiv.innerHTML = '';

            if (['jpg', 'jpeg', 'png'].includes(DOC.ext)) {
                const img = document.createElement('img');
                img.src = currentBlobUrl;
                img.className = 'max-w-full max-h-full object-contain bg-white shadow-2xl rounded';
                contentDiv.appendChild(img);
            } else if (DOC.ext === 'pdf') {
                const wrapper = document.createElement('div');
                wrapper.className = 'w-full h-full max-w-3xl mx-auto bg-white shadow-2xl rounded overflow-hidden';
                const iframe = document.createElement('iframe');
                iframe.src = currentBlobUrl + '#toolbar=0&navpanes=0&scrollbar=0&view=Fit';
                iframe.className = 'w-full h-full border-none';
                wrapper.appendChild(iframe);
                contentDiv.appendChild(wrapper);
            } else {
                contentDiv.innerHTML = `<div class="bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex flex-col items-center justify-center text-center"><p class="text-gray-500 font-medium">Pratinjau tidak tersedia untuk format ${DOC.ext.toUpperCase()}.</p></div>`;
            }
            showPreviewState('content');
            return true;
        } catch (err) {
            console.error('Preview error:', err);
            showPreviewState('error');
            return false;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadLocalPreview();

        // Tombol pemulihan akses folder
        const btnPilihFolder = document.getElementById('btnPilihFolder');
        const btnIzinFolder = document.getElementById('btnIzinFolder');
        if (btnIzinFolder) {
            btnIzinFolder.addEventListener('click', async () => {
                btnIzinFolder.disabled = true;
                btnIzinFolder.textContent = 'Meminta izin...';
                try {
                    const access = await TridatuFS.requestFolderAccess(DOC.id, 'read');
                    if (access.permission !== 'granted') {
                        const message = access.permission === 'missing'
                            ? 'Folder belum terhubung. Pilih folder utama untuk menghubungkan kembali.'
                            : 'Izin folder belum diberikan. Coba izinkan akses atau pilih ulang folder.';
                        showToast(message, 'info');
                        return;
                    }

                    showPreviewState('loading');
                    const found = await loadLocalPreview();
                    if (!found) {
                        showToast('Izin berhasil, tetapi file belum ditemukan. Pastikan folder utama benar atau pilih ulang folder.', 'error');
                    }
                } catch (err) {
                    console.error('Gagal meminta izin folder:', err);
                    showToast('Izin folder gagal diminta: ' + (err.message || 'terjadi kesalahan.'), 'error');
                } finally {
                    btnIzinFolder.disabled = false;
                    btnIzinFolder.textContent = 'Izinkan Akses Folder';
                }
            });
        }

        if (btnPilihFolder) {
            btnPilihFolder.addEventListener('click', async () => {
                btnPilihFolder.disabled = true;
                btnPilihFolder.textContent = 'Memeriksa folder...';
                try {
                    const h = await TridatuFS.getFolderHandle(DOC.id, true, 'read');
                    if (h) {
                        showPreviewState('loading');
                        await loadLocalPreview();
                    } else {
                        showPreviewState('error');
                    }
                } catch (err) {
                    console.error('Gagal menghubungkan ulang folder:', err);
                    showToast('Folder gagal dihubungkan: ' + (err.message || 'terjadi kesalahan.'), 'error');
                } finally {
                    btnPilihFolder.disabled = false;
                    btnPilihFolder.textContent = 'Pilih Folder Utama';
                }
            });
        }
    });

    window.addEventListener('beforeunload', revokeBlobUrl);

    // =====================================================
    // SYNC (OWN LOCAL → CLOUD)
    // =====================================================
    const btnSyncDetail = document.getElementById('btnSyncDetail');
    if (btnSyncDetail) {
        btnSyncDetail.addEventListener('click', async () => {
            const btnText = document.getElementById('btnSyncDetailText');
            const originalText = btnText.textContent;
            btnSyncDetail.disabled = true;
            btnText.textContent = 'Memeriksa folder...';

            try {
                let file = null;

                if (TridatuFS.isSupported()) {
                    let access = { permission: 'missing' };
                    try {
                        access = await TridatuFS.requestFolderAccess(DOC.id, 'read');
                    } catch (permissionError) {
                        console.warn('Izin folder lokal tidak dapat dipulihkan:', permissionError);
                    }
                    if (access.permission === 'granted') {
                        file = await TridatuFS.readFile({
                            documentId: DOC.id,
                            klienId: DOC.klienId,
                            klienNama: DOC.klienNama,
                            fileName: DOC.fileName,
                            fileHash: DOC.fileHash
                        });
                    }

                    if (!file) {
                        showToast('Akses folder perlu dipulihkan. Pilih folder utama tempat dokumen ini disimpan.', 'info');
                        try {
                            const root = await TridatuFS.getFolderHandle(DOC.id, true, 'read');
                            if (root) {
                                file = await TridatuFS.readFile({
                                    documentId: DOC.id,
                                    klienId: DOC.klienId,
                                    klienNama: DOC.klienNama,
                                    fileName: DOC.fileName,
                                    fileHash: DOC.fileHash
                                });
                            }
                        } catch (folderError) {
                            console.warn('Folder lokal belum dapat dihubungkan:', folderError);
                        }
                    }
                }

                if (!file) {
                    showToast('Pilih file dokumen untuk diunggah secara manual.', 'info');
                    file = await TridatuFS.pickFile();
                }
                if (!file) return;

                btnText.textContent = 'Menghitung hash...';
                const fileHash = await hitungHash(file);
                if (fileHash !== DOC.fileHash) {
                    throw new Error('File berbeda dari aslinya. Gunakan Edit Dokumen jika ingin mengganti file.');
                }

                btnText.textContent = 'Mengunggah...';
                const fd = new FormData();
                fd.append('id', DOC.id);
                fd.append('device_id', DEVICE_ID);
                fd.append('dokumen_csrf_token', DOCUMENT_CSRF_TOKEN);
                fd.append('file_hash', fileHash);
                fd.append('file', file, DOC.fileName);

                const res = await fetch('<?= site_url("dokumen/sync_to_cloud") ?>', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (!res.ok || !data.status) throw new Error(data.msg || 'Gagal mengunggah.');

                showToast('Berhasil diunggah ke Server Sistem!', 'success');
                setTimeout(() => window.location.reload(), 1200);
            } catch (err) {
                showToast(err.message || 'Terjadi kesalahan.', 'error');
            } finally {
                btnSyncDetail.disabled = false;
                btnText.textContent = originalText;
            }
        });
    }

    // =====================================================
    // HAPUS
    // =====================================================
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
        setTimeout(() => { modal.classList.add('hidden'); modal.classList.remove('flex'); }, 300);
    }

    async function eksekusiHapus() {
        const btn = document.getElementById('btnHapusConfirm');
        btn.disabled = true;
        btn.textContent = 'Menghapus...';

        try {
            const fd = new FormData();
            fd.append('dokumen_csrf_token', DOCUMENT_CSRF_TOKEN);

            const response = await fetch('<?= site_url('dokumen/hapus/') ?>' + DOC.id, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            if (!response.ok || !result.status) throw new Error(result.msg || 'Dokumen gagal dihapus.');

            // Hapus file fisik (Chromium only)
            if (IS_OWN_LOCAL) {
                if (TridatuFS.isSupported()) {
                    const del = await TridatuFS.deleteFile({
                        documentId: DOC.id,
                        klienId:   DOC.klienId,
                        klienNama: DOC.klienNama,
                        fileName:  DOC.fileName,
                        fileHash:  DOC.fileHash
                    });
                    if (!del.ok) {
                        console.warn('Gagal hapus file fisik:', del.msg);
                    }
                } else {
                    showToast('Data terhapus. File di folder Downloads masih ada — hapus manual jika perlu.', 'info');
                    await new Promise(r => setTimeout(r, 1500));
                }
            }

            window.location.href = result.redirect || '<?= site_url('dokumen') ?>';
        } catch (err) {
            btn.disabled = false;
            btn.textContent = 'Ya, Hapus';
            tutupModalHapus();
            showToast(err.message || 'Gagal menghapus.', 'error');
        }
    }
    </script>

    <!-- SMART BACK BUTTON (script bersama) -->
    <?php $this->load->view('partials/smart_back'); ?>

</body>
</html>