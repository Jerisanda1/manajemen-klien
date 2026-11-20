<?php
/** @var object $invoice */
/** @var object $settings */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

$kop = !isset($invoice->sertakan_kop) || $invoice->sertakan_kop;
$ttd = !isset($invoice->sertakan_ttd) || $invoice->sertakan_ttd;

$kopBase64 = null;
if ($kop && !empty($settings->kop_surat) && file_exists(FCPATH . $settings->kop_surat)) {
    $type = pathinfo(FCPATH . $settings->kop_surat, PATHINFO_EXTENSION);
    $data = file_get_contents(FCPATH . $settings->kop_surat);
    $kopBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}

$ttdBase64 = null;
if ($ttd && !empty($settings->ttd) && file_exists(FCPATH . $settings->ttd)) {
    $type = pathinfo(FCPATH . $settings->ttd, PATHINFO_EXTENSION);
    $data = file_get_contents(FCPATH . $settings->ttd);
    $ttdBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 40px 50px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #333; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; }
        
        /* Kop Surat */
        .kop-container { text-align: center; margin-bottom: 20px; }
        .kop-img { max-height: 120px; max-width: 100%; object-fit: contain; }
        
        .company-name { font-size: 20px; font-weight: bold; margin: 0 0 5px 0; color: #111; text-align: center; }
        .company-info { font-size: 10px; color: #666; margin: 0; line-height: 1.4; text-align: center; }

        /* Judul Dokumen */
        .doc-title { text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 2px; margin: 25px 0 30px 0; color: #111; text-transform: uppercase; }
        
        /* Layout Kiri-Kanan */
        .meta-wrapper { margin-bottom: 25px; }
        .meta-table td { vertical-align: top; padding-bottom: 5px; }
        .meta-label { width: 80px; color: #666; }
        .meta-value { width: 10px; text-align: center; color: #666; }
        
        /* Isi Surat */
        .perihal-box { margin-bottom: 20px; padding: 12px; background: #f8fafc; border-left: 3px solid #a62626; font-size: 13px; }
        .isi-text { text-align: justify; white-space: pre-line; margin-bottom: 50px; font-size: 13px; }

        /* Tanda Tangan */
        .ttd-box { width: 220px; float: right; text-align: center; }
        .ttd-space { height: 80px; margin: 10px 0; }
        .ttd-img { max-height: 80px; max-width: 180px; object-fit: contain; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 5px; font-size: 13px; }

        /* Stempel Lunas */
        .stamp-lunas { position: absolute; top: 150px; right: 50px; border: 4px solid #10b981; color: #10b981; font-size: 36px; font-weight: bold; padding: 10px 20px; transform: rotate(-15deg); opacity: 0.6; }
    </style>
</head>
<body>
    <?php if (isset($invoice->status) && $invoice->status === 'Dibayar'): ?>
        <div class="stamp-lunas">LUNAS</div>
    <?php endif; ?>

    <!-- 1. KOP SURAT (Tengah) -->
    <div class="kop-container">
        <?php if ($kopBase64): ?>
            <img src="<?= $kopBase64 ?>" class="kop-img" alt="Kop Surat">
        <?php elseif ($kop): ?>
            <h1 class="company-name"><?= $e($settings->nama_perusahaan ?? 'Tridatu Solution') ?></h1>
            <p class="company-info">
                <?= $e($settings->alamat ?? '-') ?><br>
                <?= !empty($settings->no_telp) ? 'Telp: ' . $e($settings->no_telp) : '' ?> 
                <?= !empty($settings->email) ? ' | Email: ' . $e($settings->email) : '' ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- 2. JUDUL DOKUMEN -->
    <div class="doc-title">INVOICE</div>

    <!-- 3. METADATA (Kiri & Kanan) -->
    <div class="meta-wrapper">
        <table>
            <tr>
                <td width="55%">
                    <table class="meta-table">
                        <tr><td class="meta-label">Kepada</td><td class="meta-value">:</td><td><strong><?= $e($invoice->nama_klien ?: '-') ?></strong></td></tr>
                        <tr><td class="meta-label">PIC</td><td class="meta-value">:</td><td><?= $e($invoice->nama_pic ?: '-') ?></td></tr>
                        <tr><td class="meta-label">Alamat</td><td class="meta-value">:</td><td><?= nl2br($e($invoice->alamat ?: '-')) ?></td></tr>
                        <tr><td class="meta-label">Project</td><td class="meta-value">:</td><td><?= $e($invoice->nama_project ?: 'Mandiri') ?></td></tr>
                    </table>
                </td>
                <td width="45%" align="right">
                    <table class="meta-table" style="width: auto; margin-left: auto;">
                        <tr><td style="color: #666; padding-right: 10px;">No. Invoice</td><td>: <strong><?= $e($invoice->no_invoice) ?></strong></td></tr>
                        <tr><td style="color: #666; padding-right: 10px;">Tanggal</td><td>: <?= $tgl($invoice->tanggal_invoice) ?></td></tr>
                        <tr><td style="color: #dc2626; font-weight: bold; padding-right: 10px;">Jatuh Tempo</td><td>: <strong style="color: #dc2626;"><?= $tgl($invoice->jatuh_tempo) ?></strong></td></tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>

    <!-- 4. PERIHAL & ISI -->
    <div class="perihal-box">
        <strong>Perihal:</strong> <?= $e($invoice->perihal ?: '-') ?>
    </div>
    
    <div class="isi-text"><?= nl2br($e($invoice->isi_surat)) ?></div>

    <!-- 5. TANDA TANGAN -->
    <?php if ($ttd): ?>
        <div class="ttd-box">
            <p style="margin-bottom: 5px;">Hormat kami,</p>
            <?php if ($ttdBase64): ?>
                <div class="ttd-space"><img src="<?= $ttdBase64 ?>" class="ttd-img" alt="Tanda Tangan"></div>
            <?php else: ?>
                <div class="ttd-space"></div>
            <?php endif; ?>
            <div class="ttd-name"><?= $e($settings->nama_perusahaan ?? 'Tridatu Solution') ?></div>
        </div>
        <div style="clear: both;"></div>
    <?php endif; ?>
</body>
</html>