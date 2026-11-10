<?php
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
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen lg:h-screen lg:overflow-hidden p-4 md:p-6 lg:p-8">

    <div class="w-full h-full flex flex-col gap-4">

        <!-- Header -->
        <div class="shrink-0">
            <a href="<?= site_url('dokumen') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-3 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight truncate"><?= htmlspecialchars($doc->nama_dokumen) ?></h2>
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
                    <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $status_class ?>"><?= $status_text ?></span>
                </div>

                <div class="flex gap-3 shrink-0">
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
                            <p class="text-sm font-semibold text-gray-900"><?= date('d M Y', strtotime($doc->tanggal_mulai)) ?></p>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Berakhir</p>
                            <p class="text-sm font-semibold text-gray-900"><?= date('d M Y', strtotime($doc->tanggal_berakhir)) ?></p>
                        </div>
                    </div>

                    <div class="mt-5 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 leading-relaxed">
                            <span class="font-bold text-gray-700">Pengingat:</span> Email dikirim ke admin saat dokumen mendekati tanggal berakhir supaya admin bisa menghubungi PIC klien.
                        </p>
                    </div>
                </div>

                <!-- File Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">File Dokumen</h3>

                    <?php if($isCloud): ?>
                        <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 mb-4 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-blue-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            <div>
                                <p class="text-sm font-semibold text-blue-700">File tersimpan di Server Sistem</p>
                                <p class="text-xs text-blue-600 mt-0.5">Bisa dibuka di semua perangkat.</p>
                            </div>
                        </div>
                    <?php elseif($isOwnLocal): ?>
                        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-3 mb-4 flex items-start gap-2.5">
                            <svg class="w-5 h-5 text-emerald-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                            <div>
                                <p class="text-sm font-semibold text-emerald-700">File tersimpan di Perangkat Ini</p>
                                <p class="text-xs text-emerald-600 mt-0.5">File aman tersimpan di perangkat Anda.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Box File -->
                    <div class="border border-gray-300 rounded-xl p-4 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <svg class="w-8 h-8 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
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
                            <!-- Tombol Unduh diarahkan ke Local Service -->
                            <a href="http://localhost:8080/download?filename=<?= urlencode($doc->file_name) ?>" class="shrink-0 px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh File
                            </a>
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
                                <p class="text-sm text-gray-400 mt-2">Silakan unduh file untuk melihat isinya.</p>
                            </div>
                        <?php endif; ?>

                    <?php elseif($isOwnLocal): ?>
                        <div id="localPreviewLoading" class="text-white font-medium flex flex-col items-center">
                            <svg class="w-8 h-8 animate-spin mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                            Memuat Pratinjau...
                        </div>
                        <div id="localPreviewContent" class="hidden w-full h-full flex items-center justify-center"></div>
                        <div id="localPreviewError" class="hidden bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex-col items-center justify-center text-center">
                            <svg class="w-16 h-16 text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <p class="text-gray-600 font-bold">Aplikasi Belum Terbuka</p>
                            <p class="text-sm text-gray-500 mt-2">Buka TridatuAgent.exe di laptop Anda untuk melihat pratinjau.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT BARU: MENGGUNAKAN LOCAL SERVICE AGENT -->
    <?php if($isOwnLocal): ?>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            const ext = "<?= $ext ?>";
            const fileName = "<?= htmlspecialchars($doc->file_name) ?>";
            const loadingDiv = document.getElementById('localPreviewLoading');
            const contentDiv = document.getElementById('localPreviewContent');
            const errorDiv = document.getElementById('localPreviewError');

            try {
                // 1. Cek koneksi ke TridatuAgent (Local Service)
                const ping = await fetch('http://localhost:8080/ping', { mode: 'cors' });
                if (!ping.ok) throw new Error('Agent mati');

                // 2. Ambil URL dari Local Service
                const fileUrl = `http://localhost:8080/file?filename=${encodeURIComponent(fileName)}`;

                // 3. Render persis seperti kode lama Anda
                if (['jpg', 'jpeg', 'png'].includes(ext)) {
                    contentDiv.innerHTML = `<img src="${fileUrl}" alt="Preview" class="max-w-full max-h-full object-contain bg-white shadow-2xl rounded">`;
                } else if (ext === 'pdf') {
                    contentDiv.innerHTML = `
                        <div class="w-full h-full max-w-3xl mx-auto bg-white shadow-2xl rounded overflow-hidden">
                            <iframe src="${fileUrl}#toolbar=0&navpanes=0&scrollbar=0&view=Fit" class="w-full h-full border-none"></iframe>
                        </div>`;
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
                loadingDiv.classList.add('hidden');
                errorDiv.classList.remove('hidden');
                errorDiv.classList.add('flex');
            }
        });
    </script>
    <?php endif; ?>

</body>
</html>