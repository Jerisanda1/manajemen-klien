<?php
/**
 * @var CI_Controller $this
 * @var object $penawaran
 * @var array $riwayat
 * @var array $statusList
 */

$e = function ($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
};

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $timestamp = $tanggal ? strtotime((string) $tanggal) : false;
    return $timestamp
        ? date('j', $timestamp) . ' ' . $bulan[(int) date('n', $timestamp)] . ' ' . date('Y', $timestamp)
        : '-';
};

$id = (int) $penawaran->id;
$status = isset($penawaran->status) ? $penawaran->status : 'Draft';

$statusBadge = [
    'Draft'     => 'bg-gray-100 text-gray-600 border-gray-200',
    'Dikirim'   => 'bg-blue-50 text-blue-600 border-blue-100',
    'Disetujui' => 'bg-emerald-50 text-emerald-600 border-emerald-100',
    'Ditolak'   => 'bg-red-50 text-red-600 border-red-100',
];
$statusBadgeClass = isset($statusBadge[$status])
    ? $statusBadge[$status]
    : 'bg-gray-100 text-gray-600 border-gray-200';

// Penawaran lama yang belum memiliki riwayat tetap menampilkan status saat ini.
$riwayat = (isset($riwayat) && is_array($riwayat)) ? $riwayat : [];
if (empty($riwayat)) {
    $riwayat[] = (object) [
        'status' => $status,
        'created_at' => isset($penawaran->created_at) ? $penawaran->created_at : $penawaran->tanggal_penawaran,
    ];
}

$unggahan = (
    isset($penawaran->dibuat_lewat)
    && $penawaran->dibuat_lewat === 'Unggahan'
    && !empty($penawaran->file_path)
);
$fileNama = !empty($penawaran->file_nama)
    ? $penawaran->file_nama
    : (!empty($penawaran->file_path) ? basename($penawaran->file_path) : 'Dokumen penawaran.pdf');
$ext = strtolower(pathinfo($fileNama, PATHINFO_EXTENSION));

if (!$unggahan) {
    $srcPratinjau = site_url('pembayaran/unduh/penawaran/' . $id . '?inline=1');
    $urlUnduh = site_url('pembayaran/unduh/penawaran/' . $id);
} else {
    $srcPratinjau = ($ext === 'pdf') ? site_url('pembayaran/berkas/' . $id) : null;
    $urlUnduh = site_url('pembayaran/berkas/' . $id . '?unduh=1');
}

$successMessage = $this->session->flashdata('success');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $e($title) : 'Detail Penawaran' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen p-4 md:p-6 lg:p-8">

    <div class="w-full flex flex-col gap-4">

        <!-- Header: susunan tetap seperti halaman Detail Penawaran sebelumnya -->
        <div class="shrink-0">
            <a href="<?= site_url('pembayaran?tab=penawaran') ?>"
               class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-3 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            <?php if ($successMessage): ?>
                <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <?= $e($successMessage) ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="flex items-center gap-3 min-w-0 flex-wrap">
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight break-words">
                        <?= $e($penawaran->no_penawaran) ?>
                    </h2>
                    <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $statusBadgeClass ?>">
                        <?= $e($status) ?>
                    </span>
                </div>

                <div class="flex flex-wrap gap-2 sm:gap-3 shrink-0">
                    <a href="<?= $urlUnduh ?>"
                       class="px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh PDF
                    </a>

                    <a href="<?= site_url('pembayaran/edit/penawaran/' . $id . '?from=detail') ?>"
                       class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Edit
                    </a>

                    <button type="button" onclick="bukaModalHapus()"
                            class="px-5 py-2.5 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Hapus
                    </button>
                </div>
            </div>
        </div>

        <!-- Layout asli dipertahankan: kolom kiri lebih lebar, kolom kanan 400px -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_400px] gap-4 items-start">

            <!-- KOLOM KIRI: Informasi Penawaran dan Pratinjau Dokumen -->
            <div class="flex flex-col gap-4">

                <!-- Informasi Penawaran -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Penawaran</h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-4">
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Nomor Penawaran</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= $e($penawaran->no_penawaran ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Klien</p>
                            <p class="text-sm font-semibold text-gray-900 break-words uppercase"><?= $e($penawaran->nama_klien ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Project</p>
                            <p class="text-sm font-semibold text-gray-900 break-words">
                                <?php if (!empty($penawaran->nama_project)): ?>
                                    <a href="<?= site_url('project/detail/' . (int) $penawaran->project_id) ?>"
                                       class="text-[#9f2b2b] hover:underline">
                                        <?= $e($penawaran->nama_project) ?>
                                    </a>
                                <?php else: ?>
                                    Mandiri
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Perihal</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= $e($penawaran->perihal ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">PIC Klien</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= $e($penawaran->nama_pic ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal Penawaran</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $tgl($penawaran->tanggal_penawaran) ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Berlaku Sampai</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $tgl($penawaran->berlaku_sampai) ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Dibuat Lewat</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $e(!empty($penawaran->dibuat_lewat) ? $penawaran->dibuat_lewat : '-') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pratinjau Dokumen: tetap berada di bawah Informasi Penawaran -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h3 class="text-lg font-bold text-gray-900">Pratinjau Dokumen</h3>
                        <span class="text-xs font-medium text-gray-500 bg-gray-100 px-2.5 py-1 rounded-md">
                            <?= $e($ext ? strtoupper($ext) : 'PDF') ?>
                        </span>
                    </div>

                    <div class="h-[460px] md:h-[640px] bg-gray-400 rounded-lg flex items-center justify-center p-3 sm:p-4 overflow-hidden">
                        <?php if ($srcPratinjau): ?>
                            <div class="w-full h-full bg-white shadow-2xl rounded overflow-hidden">
                                <iframe src="<?= $e($srcPratinjau) ?>#toolbar=0&navpanes=0&scrollbar=0&view=Fit"
                                        title="Pratinjau penawaran"
                                        class="w-full h-full border-none"
                                        loading="lazy"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="bg-white w-full max-w-sm min-h-[300px] shadow-2xl rounded-xl p-8 flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-700 font-semibold mb-1">Pratinjau tidak tersedia</p>
                                <p class="text-sm text-gray-500 mb-5">File <?= $e($fileNama) ?> tidak dapat ditampilkan langsung di browser.</p>
                                <a href="<?= $urlUnduh ?>"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                    Unduh File
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: Status Penawaran dan Invoice Terkait -->
            <div class="flex flex-col gap-4">

                <!-- Status Penawaran -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status Penawaran</h3>

                    <form method="post" action="<?= site_url('pembayaran/status/penawaran/' . $id) ?>" class="m-0">
                        <?php if ($this->config->item('csrf_protection')): ?>
                            <input type="hidden"
                                   name="<?= $e($this->security->get_csrf_token_name()) ?>"
                                   value="<?= $e($this->security->get_csrf_hash()) ?>">
                        <?php endif; ?>

                        <label for="status" class="block text-sm font-medium text-gray-600 mb-2">Ubah Status</label>
                        <select id="status" name="status"
                                class="w-full h-12 border border-gray-300 rounded-lg px-3 text-sm text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                            <?php foreach ($statusList as $statusOption): ?>
                                <option value="<?= $e($statusOption) ?>" <?= $status === $statusOption ? 'selected' : '' ?>>
                                    <?= $e($statusOption) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <button type="submit"
                                class="w-full mt-3 h-12 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                            Simpan Status
                        </button>
                    </form>

                    <div class="border-t border-gray-200 mt-5 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-800">Riwayat</h4>
                            <span class="text-xs text-gray-400"><?= count($riwayat) ?> aktivitas</span>
                        </div>

                        <div class="flex flex-col">
                            <?php foreach ($riwayat as $index => $itemRiwayat): ?>
                                <div class="flex items-center justify-between gap-3 py-2 <?= $index < count($riwayat) - 1 ? 'border-b border-gray-100' : '' ?>">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2 h-2 rounded-full shrink-0 <?= $index === 0 ? 'bg-[#9f2b2b]' : 'bg-gray-300' ?>"></span>
                                        <span class="text-sm font-medium text-gray-700 break-words"><?= $e($itemRiwayat->status) ?></span>
                                    </div>
                                    <span class="text-xs text-gray-500 shrink-0"><?= $tgl($itemRiwayat->created_at) ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Invoice Terkait -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Invoice Terkait</h3>
                    <p class="text-sm text-gray-500 mb-4">Belum ada invoice dari penawaran ini.</p>

                    <?php if ($status === 'Disetujui'): ?>
                        <a href="<?= site_url('pembayaran/tambah/invoice?penawaran_id=' . $id) ?>"
                           class="w-full min-h-12 px-4 py-2.5 inline-flex items-center justify-center gap-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Buat Invoice dari Penawaran
                        </a>
                    <?php else: ?>
                        <span class="w-full min-h-12 px-4 py-2.5 inline-flex items-center justify-center bg-gray-100 border border-gray-200 text-gray-400 text-sm font-medium rounded-lg cursor-not-allowed">
                            Buat Invoice dari Penawaran
                        </span>
                    <?php endif; ?>

                    <p class="text-xs text-gray-400 mt-3">Aktif setelah status penawaran disetujui.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal konfirmasi hapus -->
    <div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
        <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>

        <div id="modalContent" class="relative z-10 w-full max-w-sm bg-white rounded-2xl shadow-2xl p-6 transform scale-95 opacity-0 transition-all duration-300">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
            </div>

            <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Penawaran?</h3>
            <p class="text-center text-gray-500 text-sm mb-6">
                Apakah Anda yakin ingin menghapus penawaran
                <strong class="block text-gray-900 text-base mt-1"><?= $e($penawaran->no_penawaran) ?></strong>
                <span class="block text-xs text-red-500 bg-red-50 rounded-lg py-1.5 px-2 mt-3">Tindakan ini tidak dapat dibatalkan.</span>
            </p>

            <form method="post" action="<?= site_url('pembayaran/hapus') ?>" class="flex justify-center gap-3">
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden"
                           name="<?= $e($this->security->get_csrf_token_name()) ?>"
                           value="<?= $e($this->security->get_csrf_hash()) ?>">
                <?php endif; ?>
                <input type="hidden" name="tab" value="penawaran">
                <input type="hidden" name="id" value="<?= $id ?>">

                <button type="button" onclick="tutupModalHapus()"
                        class="w-full px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                    Batal
                </button>
                <button type="submit"
                        class="w-full px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white text-sm font-medium rounded-xl transition-colors shadow-md shadow-red-500/20">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

    <script>
        const modalHapus = document.getElementById('modalHapus');
        const modalHapusContent = document.getElementById('modalContent');

        function bukaModalHapus() {
            modalHapus.classList.remove('hidden');
            modalHapus.classList.add('flex');

            setTimeout(function () {
                modalHapusContent.classList.remove('scale-95', 'opacity-0');
                modalHapusContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function tutupModalHapus() {
            modalHapusContent.classList.remove('scale-100', 'opacity-100');
            modalHapusContent.classList.add('scale-95', 'opacity-0');

            setTimeout(function () {
                modalHapus.classList.add('hidden');
                modalHapus.classList.remove('flex');
            }, 300);
        }
    </script>
</body>
</html>
