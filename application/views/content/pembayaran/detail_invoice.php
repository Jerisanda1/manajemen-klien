<?php
/**
 * @var CI_Controller $this
 * @var object $inv
 * @var array $pengiriman (saluran => waktu terakhir)
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

$id = (int) $inv->id;
$dibayar = ($inv->status === 'Dibayar');

$statusBadgeClass = $dibayar
    ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
    : 'bg-amber-50 text-amber-700 border-amber-100';

$unggahan = ($inv->dibuat_lewat === 'Unggahan' && !empty($inv->file_path));
$ext = $unggahan ? strtolower(pathinfo($inv->file_path, PATHINFO_EXTENSION)) : '';

$urlUnduh = site_url('pembayaran/unduh/invoice/' . $id);

if (!$unggahan) {
    $srcPratinjau = site_url('pembayaran/unduh/invoice/' . $id . '?inline=1');
} elseif ($ext === 'pdf') {
    $srcPratinjau = site_url('pembayaran/berkas-invoice/' . $id);
} else {
    // File selain PDF tetap tidak dipratinjau di browser, seperti perilaku sebelumnya.
    $srcPratinjau = null;
}

// Hitung mundur jatuh tempo hanya untuk invoice yang belum dibayar.
$badgeTempo = null;
if (!$dibayar && !empty($inv->jatuh_tempo)) {
    $hari = (int) round((strtotime($inv->jatuh_tempo) - strtotime(date('Y-m-d'))) / 86400);

    if ($hari > 0) {
        $teksTempo = 'Jatuh tempo ' . $hari . ' hari lagi';
    } elseif ($hari === 0) {
        $teksTempo = 'Jatuh tempo hari ini';
    } else {
        $teksTempo = 'Terlambat ' . abs($hari) . ' hari';
    }

    $kelasTempo = ($hari <= 7)
        ? 'bg-red-50 text-red-600 border-red-100'
        : 'bg-gray-100 text-gray-600 border-gray-200';

    $badgeTempo = [$teksTempo, $kelasTempo];
}

$csrf = $this->config->item('csrf_protection')
    ? '<input type="hidden" name="' . $e($this->security->get_csrf_token_name()) . '" value="' . $e($this->security->get_csrf_hash()) . '">'
    : '';

$successMessage = $this->session->flashdata('success');
$errorMessage = $this->session->flashdata('error');
$pengiriman = (isset($pengiriman) && is_array($pengiriman)) ? $pengiriman : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? $e($title) : 'Detail Invoice' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen p-4 md:p-6 lg:p-8">

    <div class="w-full flex flex-col gap-4">

        <!-- Header: susunan tetap seperti halaman Detail Invoice sebelumnya -->
        <div class="shrink-0">
            <a href="<?= site_url('pembayaran?tab=invoice') ?>"
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

            <?php if ($errorMessage): ?>
                <div class="mb-4 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <?= $e($errorMessage) ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-3 min-w-0 flex-wrap">
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight break-words">
                            <?= $e($inv->no_invoice) ?>
                        </h2>
                        <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $statusBadgeClass ?>">
                            <?= $e($inv->status) ?>
                        </span>
                        <?php if ($badgeTempo): ?>
                            <span class="shrink-0 inline-flex items-center justify-center px-3 py-1 rounded-full text-xs font-semibold border <?= $badgeTempo[1] ?>">
                                <?= $e($badgeTempo[0]) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($inv->perihal)): ?>
                        <p class="text-sm text-gray-500 mt-2"><?= $e($inv->perihal) ?></p>
                    <?php endif; ?>
                </div>

                <div class="flex flex-wrap gap-2 sm:gap-3 shrink-0">
                    <a href="<?= $urlUnduh ?>"
                       class="px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Unduh PDF
                    </a>

                    <a href="<?= site_url('pembayaran/edit/invoice/' . $id . '?from=detail') ?>"
                       class="px-5 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                        Edit
                    </a>

                    <form method="post" action="<?= site_url('pembayaran/hapus') ?>"
                          onsubmit="return confirm('Hapus invoice ini secara permanen?');" class="m-0">
                        <?= $csrf ?>
                        <input type="hidden" name="tab" value="invoice">
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="submit"
                                class="px-5 py-2.5 bg-white border border-red-200 hover:bg-red-50 text-red-600 text-sm font-medium rounded-lg transition-colors flex items-center gap-2 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Hapus
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- Layout asli dipertahankan: kolom kiri lebih lebar, kolom kanan 400px -->
        <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_400px] gap-4 items-start">

            <!-- KOLOM KIRI: Informasi Invoice dan Pratinjau Dokumen -->
            <div class="flex flex-col gap-4">

                <!-- Informasi Invoice -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Informasi Invoice</h3>

                    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-4">
                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Klien</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= $e($inv->nama_klien ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Project</p>
                            <p class="text-sm font-semibold text-gray-900 break-words">
                                <?php if (!empty($inv->nama_project)): ?>
                                    <a href="<?= site_url('project/detail/' . (int) $inv->project_id) ?>"
                                       class="text-[#9f2b2b] hover:underline">
                                        <?= $e($inv->nama_project) ?>
                                    </a>
                                <?php else: ?>
                                    Mandiri
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Perihal</p>
                            <p class="text-sm font-semibold text-gray-900 break-words"><?= $e($inv->perihal ?: '-') ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Penawaran</p>
                            <p class="text-sm font-semibold text-gray-900 break-words">
                                <?php if (!empty($inv->penawaran_id)): ?>
                                    <a href="<?= site_url('pembayaran/detail/penawaran/' . (int) $inv->penawaran_id) ?>"
                                       class="text-[#9f2b2b] hover:underline">
                                        <?= $e($inv->no_penawaran) ?>
                                    </a>
                                    <span class="text-xs font-normal text-gray-500 ml-1">&bull; <?= $e($inv->status_penawaran) ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Tanggal invoice</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $tgl($inv->tanggal_invoice) ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Jatuh tempo</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $tgl($inv->jatuh_tempo) ?></p>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <p class="text-xs text-gray-500 mb-0.5">Dibuat lewat</p>
                            <p class="text-sm font-semibold text-gray-900"><?= $e($inv->dibuat_lewat ?: '-') ?></p>
                        </div>

                        <?php if ($dibayar): ?>
                            <div class="col-span-2 md:col-span-1">
                                <p class="text-xs text-gray-500 mb-0.5">Tanggal pembayaran</p>
                                <p class="text-sm font-semibold text-gray-900"><?= $tgl($inv->tanggal_bayar) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pratinjau Dokumen: gaya area pratinjau disamakan dengan halaman Detail Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 shrink-0">Pratinjau Dokumen</h3>

                    <div class="h-[460px] min-[641px]:h-[640px] bg-gray-400 rounded-lg flex items-center justify-center p-3 sm:p-4 overflow-hidden min-h-[400px]">
                        <?php if ($srcPratinjau): ?>
                            <div class="w-full h-full max-w-3xl mx-auto bg-white shadow-2xl rounded overflow-hidden">
                                <iframe
                                    src="<?= $e($srcPratinjau) ?>#toolbar=0&navpanes=0&scrollbar=0&view=Fit"
                                    title="Pratinjau Invoice"
                                    loading="lazy"
                                    class="w-full h-full border-none bg-white"></iframe>
                            </div>
                        <?php else: ?>
                            <div class="bg-white w-full max-w-sm aspect-[1/1.4] shadow-2xl rounded p-8 flex flex-col items-center justify-center text-center">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <p class="text-gray-500 font-medium">Pratinjau tidak tersedia untuk format <?= $e($ext ?: 'file') ?>.</p>
                                <p class="text-xs text-gray-500 mt-2 mb-4 break-words">
                                    <?= $e(!empty($inv->file_nama) ? $inv->file_nama : 'File invoice') ?>
                                </p>
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

            <!-- KOLOM KANAN: Status Pembayaran dan Pengingat Jatuh Tempo -->
            <div class="flex flex-col gap-4">

                <!-- Status Pembayaran -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status Pembayaran</h3>

                    <div class="flex items-center justify-between gap-3 py-2">
                        <span class="text-sm text-gray-500">Status</span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border <?= $statusBadgeClass ?>">
                            <?= $e($inv->status) ?>
                        </span>
                    </div>

                    <?php if (!$dibayar): ?>
                        <form method="post" action="<?= site_url('pembayaran/status/invoice/' . $id) ?>"
                              onsubmit="return confirm('Tandai invoice ini sudah dibayar?');" class="m-0">
                            <?= $csrf ?>
                            <input type="hidden" name="status" value="Dibayar">
                            <button type="submit"
                                    class="w-full mt-3 h-12 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                                Tandai Sudah Bayar
                            </button>
                        </form>
                        <p class="text-xs text-gray-500 leading-relaxed mt-2">Tanggal pembayaran dicatat saat status diubah.</p>
                    <?php else: ?>
                        <div class="flex items-center justify-between gap-3 py-2 mt-1 border-t border-gray-100">
                            <span class="text-sm text-gray-500">Dibayar pada</span>
                            <span class="text-sm font-medium text-gray-800"><?= $tgl($inv->tanggal_bayar) ?></span>
                        </div>
                        <form method="post" action="<?= site_url('pembayaran/status/invoice/' . $id) ?>"
                              onsubmit="return confirm('Kembalikan status menjadi Belum dibayar?');" class="m-0">
                            <?= $csrf ?>
                            <input type="hidden" name="status" value="Belum dibayar">
                            <button type="submit" class="mt-3 text-sm text-gray-500 hover:text-gray-800 underline underline-offset-2">
                                Kembalikan ke Belum dibayar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Pengingat Jatuh Tempo -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Pengingat otomatis</h3>

                    <div class="mb-4">
                        <p class="text-xs text-gray-500 mb-1">Kontak PIC klien</p>
                        <div class="text-sm font-medium text-gray-800 leading-relaxed break-words">
                            <?= $e($inv->nama_pic ?: '-') ?><br>
                            <span class="font-normal text-gray-600"><?= $e($inv->email_pic ?: '-') ?> &nbsp; <?= $e($inv->hp_pic ?: '-') ?></span>
                        </div>
                    </div>

                    <p class="text-xs text-gray-500 leading-relaxed">
                        Pengingat dikirim otomatis melalui email dan WhatsApp pada H-30 dan hari jatuh tempo.
                    </p>

                    <?php if ($dibayar): ?>
                        <p class="text-xs text-gray-500 leading-relaxed mt-2">Invoice sudah dibayar, pengingat otomatis tidak dikirim lagi.</p>
                    <?php endif; ?>

                    <!-- Riwayat pengiriman tetap menampilkan status Email dan WhatsApp -->
                    <div class="border-t border-gray-200 mt-5 pt-4">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-800">Riwayat pengiriman</h4>
                        </div>

                        <?php foreach (['email' => 'Email', 'whatsapp' => 'WhatsApp'] as $keySaluran => $namaSaluran): ?>
                            <?php $riwayatKirim = isset($pengiriman[$keySaluran]) ? $pengiriman[$keySaluran] : null; ?>
                            <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 last:border-b-0">
                                <span class="text-sm font-medium text-gray-700">
                                    <?= $e($namaSaluran) ?><?= $riwayatKirim ? ($riwayatKirim->berhasil ? ' - Terkirim' : ' - Gagal') : ' - Belum dikirim' ?>
                                </span>
                                <span class="text-xs text-gray-500 shrink-0">
                                    <?php if ($riwayatKirim): ?>
                                        <?= $tgl($riwayatKirim->created_at) ?>
                                        <?php if (!empty($riwayatKirim->otomatis)): ?>
                                            &middot; Otomatis <?= (int) $riwayatKirim->offset_hari === 0 ? 'hari H' : 'H-' . (int) $riwayatKirim->offset_hari ?>
                                        <?php else: ?>
                                            &middot; Manual
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Belum dikirim
                                    <?php endif; ?>
                                </span>
                            </div>
                            <?php if ($riwayatKirim && !$riwayatKirim->berhasil && !empty($riwayatKirim->keterangan)): ?>
                                <p class="text-xs text-red-600 leading-relaxed mt-1 mb-2">
                                    <?= $e($riwayatKirim->keterangan) ?>
                                </p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
