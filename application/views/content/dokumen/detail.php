<?php
// Pastikan Cookie terbaca
$currentDeviceId = $_COOKIE['tridatu_device_id'] ?? '';
$isLocal = ($doc->storage_type === 'local');
$isOwnLocal = ($isLocal && $doc->device_id === $currentDeviceId);
$isCloud = ($doc->storage_type === 'cloud');
$ext = strtolower(pathinfo($doc->file_name, PATHINFO_EXTENSION));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-[1200px] mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="<?= site_url('dokumen') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-4 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="text-3xl font-bold text-gray-900 tracking-tight"><?= htmlspecialchars($doc->nama_dokumen) ?></h2>
                    <?php
                        $today = new DateTime(date('Y-m-d'));
                        $end   = new DateTime($doc->tanggal_berakhir);
                        $diff  = (int)$today->diff($end)->format('%r%a');
                        if ($diff < 0) {
                            $status_class = 'bg-red-50 text-red-600 border-red-100';
                            $status_text  = 'Expired';
                        } elseif ($diff <= 30) {
                            $status_class = 'bg-orange-50 text-orange-600 border-orange-100';
                            $status_text  = 'Akan Berakhir';
                        } else {
                            $status_class = 'bg-blue-50 text-blue-600 border-blue-100';
                            $status_text  = 'Aktif';
                        }
                    ?>
                    <span class="inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>"><?= $status_text ?></span>
                </div>
                
                <div class="flex gap-3">
                    <a href="<?= site_url('dokumen/edit/'.$doc->id) ?>" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Edit
                    </a>
                    <a href="<?= site_url('dokumen/hapus/'.$doc->id) ?>" onclick="return confirm('Yakin ingin menghapus dokumen ini?')" class="px-5 py-2 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        Hapus
                    </a>
                </div>
            </div>
        </div>

        <!-- Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- KOLOM KIRI -->
            <div class="flex flex-col gap-6">
                
                <!-- Card 1: Informasi Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Informasi Dokumen</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-6 gap-x-4">
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Nama Dokumen</p>
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($doc->nama_dokumen) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Klien</p>
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($doc->nama_klien) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Project</p>
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($doc->nama_project) ?></p>
                        </div>
                        
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Jenis</p>
                            <p class="text-sm font-semibold text-gray-900"><?= htmlspecialchars($doc->jenis_dokumen) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Mulai Berlaku</p>
                            <p class="text-sm font-semibold text-gray-900"><?= date('d M Y', strtotime($doc->tanggal_mulai)) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-sm text-gray-500 mb-1">Berakhir</p>
                            <p class="text-sm font-semibold text-gray-900"><?= date('d M Y', strtotime($doc->tanggal_berakhir)) ?></p>
                        </div>
                    </div>

                    <div class="mt-8 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500">
                            <span class="font-bold text-gray-700">Pengingat :</span> Email dikirim ke admin saat dokumen mendekati tanggal berakhir supaya admin bisa menghubungi PIC klien.
                        </p>
                    </div>
                </div>

                <!-- Card 2: File Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">File Dokumen</h3>
                    
                    <?php if($isCloud): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 mb-5 flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-700">File tersimpan di Server Sistem</p>
                                <p class="text-xs text-blue-600 mt-0.5">Bisa dibuka di semua perangkat.</p>
                            </div>
                        </div>
                    <?php elseif($isOwnLocal): ?>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4 mb-5 flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-700">File tersimpan di Perangkat Ini</p>
                                <p class="text-xs text-emerald-600 mt-0.5">File aman tersimpan di perangkat Anda.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Box File -->
                    <div class="border border-gray-300 rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <div>
                                <p class="text-sm font-bold text-gray-900"><?= htmlspecialchars($doc->file_name) ?></p>
                                <p class="text-xs text-gray-500">
                                    <?= $doc->file_size ? round($doc->file_size / (1024*1024), 2) . ' MB' : 'Ukuran tidak diketahui' ?> 
                                    • <?= strtoupper($ext) ?>
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex gap-2 w-full sm:w-auto">
                            <?php if($isCloud): ?>
                                <a href="<?= site_url('dokumen/unduh/'.$doc->id) ?>" class="flex-1 sm:flex-none justify-center px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh File
                                </a>
                            <?php elseif($isOwnLocal): ?>
                                <button type="button" id="btnDownloadLocal" class="flex-1 sm:flex-none justify-center px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Unduh File
                                </button>
                            <?php endif; ?>
                            <a href="<?= site_url('dokumen') ?>" class="flex-1 sm:flex-none text-center px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                                Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Pratinjau Dokumen -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-full min-h-[500px] flex flex-col">
                <h3 class="text-xl font-bold text-gray-900 mb-6">Pratinjau Dokumen</h3>
                
                <div class="flex-1 bg-gray-400 rounded-lg flex items-center justify-center p-4 sm:p-8 overflow-hidden relative" id="previewArea">
                    
                    <?php if($isCloud): ?>
                        <!-- Preview untuk Cloud / Server Sistem -->
                        <?php if(in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                            <img src="<?= site_url('dokumen/preview/'.$doc->id) ?>" alt="Preview" class="max-w-full max-h-full object-contain bg-white shadow-2xl rounded">
                        <?php elseif($ext === 'pdf'): ?>
                            <iframe src="<?= site_url('dokumen/preview/'.$doc->id) ?>" class="w-full h-full min-h-[600px] bg-white shadow-2xl rounded border-none"></iframe>
                        <?php else: ?>
                            <div class="bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 font-medium">Pratinjau tidak tersedia untuk format <?= $ext ?>.</p>
                                <p class="text-sm text-gray-400 mt-2">Silakan unduh file untuk melihat isinya.</p>
                            </div>
                        <?php endif; ?>

                    <?php elseif($isOwnLocal): ?>
                        <!-- Preview untuk Perangkat Ini (Dimuat via JavaScript) -->
                        <div id="localPreviewLoading" class="text-white font-medium flex flex-col items-center">
                            <svg class="w-8 h-8 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Memuat Pratinjau...
                        </div>
                        <div id="localPreviewContent" class="hidden w-full h-full flex items-center justify-center"></div>
                        <div id="localPreviewError" class="hidden bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex-col items-center justify-center text-center">
                            <svg class="w-16 h-16 text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-gray-600 font-bold">Akses File Gagal</p>
                            <p class="text-sm text-gray-500 mt-2">File fisik tidak ditemukan di perangkat ini. Mungkin sudah dihapus atau dipindah.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </div>

    <!-- SCRIPT UNTUK MEMBACA FILE LOKAL (PERANGKAT INI) -->
    <?php if($isOwnLocal): ?>
    <script>
        // IndexedDB Helpers
        function bukaDB() {
            return new Promise((resolve, reject) => {
                const req = indexedDB.open('TridatuFileSystem', 1);
                req.onupgradeneeded = () => req.result.createObjectStore('handles');
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => reject(req.error);
            });
        }
        async function ambilHandle(id) {
            const db = await bukaDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('handles', 'readonly');
                const req = tx.objectStore('handles').get(String(id));
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => reject(req.error);
            });
        }

        let localFileObject = null;

        document.addEventListener('DOMContentLoaded', async () => {
            const docId = "<?= $doc->id ?>";
            const ext = "<?= $ext ?>";
            const loadingDiv = document.getElementById('localPreviewLoading');
            const contentDiv = document.getElementById('localPreviewContent');
            const errorDiv = document.getElementById('localPreviewError');

            try {
                const handle = await ambilHandle(docId);
                if (!handle) throw new Error('No handle');

                const permission = await handle.requestPermission({ mode: 'read' });
                if (permission !== 'granted') throw new Error('No permission');

                const file = await handle.getFile();
                localFileObject = file;

                // ========================================================
                // PERBAIKAN: Paksa MIME Type agar browser mengenali sebagai PDF
                // ========================================================
                let mimeType = file.type;
                if (!mimeType || mimeType === '') {
                    if (ext === 'pdf') mimeType = 'application/pdf';
                    else if (['jpg', 'jpeg', 'png'].includes(ext)) mimeType = `image/${ext}`;
                }

                const blob = new Blob([file], { type: mimeType });
                const fileUrl = URL.createObjectURL(blob);

                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    contentDiv.innerHTML = `<img src="${fileUrl}" alt="Preview" class="max-w-full max-h-full object-contain bg-white shadow-2xl rounded">`;
                } else if (ext === 'pdf') {
                    // Gunakan iframe untuk PDF lokal yang sudah dibungkus Blob MIME Type
                    contentDiv.innerHTML = `<iframe src="${fileUrl}" class="w-full h-full min-h-[600px] bg-white shadow-2xl rounded border-none"></iframe>`;
                } else {
                    contentDiv.innerHTML = `
                        <div class="bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex flex-col items-center justify-center text-center">
                            <p class="text-gray-500 font-medium">Pratinjau tidak tersedia untuk format ${ext.toUpperCase()}.</p>
                        </div>
                    `;
                }

                loadingDiv.classList.add('hidden');
                contentDiv.classList.remove('hidden');

            } catch (err) {
                console.error(err);
                loadingDiv.classList.add('hidden');
                errorDiv.classList.remove('hidden');
                errorDiv.classList.add('flex');
            }
        });

        // Tombol Download Lokal
        document.getElementById('btnDownloadLocal').addEventListener('click', () => {
            if(localFileObject) {
                const blob = new Blob([localFileObject], { type: 'application/octet-stream' });
                const fileUrl = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = fileUrl;
                a.download = "<?= htmlspecialchars($doc->file_name) ?>";
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                URL.revokeObjectURL(fileUrl);
            } else {
                alert('File tidak siap. Silakan refresh halaman.');
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>