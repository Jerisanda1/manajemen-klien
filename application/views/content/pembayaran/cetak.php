<?php
/** @var object $penawaran */
/** @var object $settings */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};
$kop = !isset($penawaran->sertakan_kop) || $penawaran->sertakan_kop;
$ttd = !isset($penawaran->sertakan_ttd) || $penawaran->sertakan_ttd;

$kopUrl = $kop && !empty($settings->kop_surat) ? base_url($settings->kop_surat) : null;
$ttdUrl = $ttd && !empty($settings->ttd) ? base_url($settings->ttd) : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($penawaran->no_penawaran) ?></title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #333; line-height: 1.6; margin: 0; background: #f1f5f9; }
        .sheet { max-width: 800px; margin: 20px auto; background: #fff; padding: 50px 60px; box-sizing: border-box; min-height: 1000px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; }
        
        .bar { max-width: 800px; margin: 20px auto 0; text-align: right; }
        .bar button { padding: 10px 20px; border: 0; border-radius: 8px; background: #a62626; color: #fff; font-size: 14px; font-weight: bold; cursor: pointer; transition: 0.2s; }
        .bar button:hover { background: #8a1f1f; }

        .kop-container { text-align: center; margin-bottom: 25px; }
        .kop-img { max-height: 120px; max-width: 100%; object-fit: contain; display: block; margin: 0 auto; }
        
        .company-name { font-size: 22px; font-weight: bold; margin: 0 0 5px 0; color: #111; text-align: center; }
        .company-info { font-size: 11px; color: #666; margin: 0; line-height: 1.4; text-align: center; }

        .doc-title { text-align: center; font-size: 20px; font-weight: bold; letter-spacing: 2px; margin: 30px 0; color: #111; text-transform: uppercase; }

        .meta-wrapper { margin-bottom: 25px; }
        .meta-table td { vertical-align: top; padding-bottom: 5px; }
        .meta-label { width: 80px; color: #666; }
        .meta-value { width: 10px; text-align: center; color: #666; }
        
        .perihal-box { margin-bottom: 20px; padding: 12px; background: #f8fafc; border-left: 3px solid #a62626; font-size: 14px; }
        .isi-text { text-align: justify; white-space: pre-line; margin-bottom: 50px; font-size: 14px; }

        .ttd-box { width: 220px; float: right; text-align: center; }
        .ttd-space { height: 80px; margin: 10px 0; }
        .ttd-img { max-height: 80px; max-width: 180px; object-fit: contain; display: block; margin: 0 auto; }
        .ttd-name { font-weight: bold; text-decoration: underline; margin-top: 5px; font-size: 14px; }

        @media print {
            body { background: #fff; }
            .bar { display: none; }
            .sheet { margin: 0; max-width: none; padding: 0; box-shadow: none; min-height: 0; }
        }
    </style>
</head>
<body>
    <div class="bar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    <div class="sheet">
        
        <!-- 1. KOP SURAT (Tengah) -->
        <div class="kop-container">
            <?php if ($kopUrl): ?>
                <img src="<?= $kopUrl ?>" class="kop-img" alt="Kop Surat">
            <?php elseif ($kop): ?>
                <h1 class="company-name"><?= $e($settings->nama_perusahaan ?? 'Tridatu Solution') ?></h1>
                <p class="company-info">
                    <?= $e($settings->alamat ?? '-') ?><br>
                    <?= !empty($settings->no_telp) ? 'Telp: ' . $e($settings->no_telp) : '' ?> 
                    <?= !empty($settings->email) ? ' | Email: ' . $e($settings->email) : '' ?>
                </p>
            <?php endif; ?>
        </div>

        <!-- 2. JUDUL -->
        <div class="doc-title">Penawaran Harga</div>

        <!-- 3. METADATA -->
        <div class="meta-wrapper">
            <table>
                <tr>
                    <td width="55%">
                        <table class="meta-table">
                            <tr><td class="meta-label">Kepada</td><td class="meta-value">:</td><td><strong><?= $e($penawaran->nama_klien ?: '-') ?></strong></td></tr>
                            <tr><td class="meta-label">PIC</td><td class="meta-value">:</td><td><?= $e($penawaran->nama_pic ?: '-') ?></td></tr>
                            <tr><td class="meta-label">Alamat</td><td class="meta-value">:</td><td><?= nl2br($e($penawaran->alamat ?: '-')) ?></td></tr>
                            <tr><td class="meta-label">Project</td><td class="meta-value">:</td><td><?= $e($penawaran->nama_project ?: 'Mandiri') ?></td></tr>
                        </table>
                    </td>
                    <td width="45%" align="right">
                        <table class="meta-table" style="width: auto; margin-left: auto;">
                            <tr><td style="color: #666; padding-right: 10px;">No. Penawaran</td><td>: <strong><?= $e($penawaran->no_penawaran) ?></strong></td></tr>
                            <tr><td style="color: #666; padding-right: 10px;">Tanggal</td><td>: <?= $tgl($penawaran->tanggal_penawaran) ?></td></tr>
                            <tr><td style="color: #666; padding-right: 10px;">Berlaku s/d</td><td>: <?= $tgl($penawaran->berlaku_sampai) ?></td></tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <!-- 4. PERIHAL & ISI -->
        <div class="perihal-box">
            <strong>Perihal:</strong> <?= $e($penawaran->perihal ?: '-') ?>
        </div>
        
        <div class="isi-text"><?= nl2br($e($penawaran->isi_surat)) ?></div>

        <!-- 5. TANDA TANGAN -->
        <?php if ($ttd): ?>
            <div class="ttd-box">
                <p style="margin-bottom: 5px;">Hormat kami,</p>
                <?php if ($ttdUrl): ?>
                    <div class="ttd-space"><img src="<?= $ttdUrl ?>" class="ttd-img" alt="Tanda Tangan"></div>
                <?php else: ?>
                    <div class="ttd-space"></div>
                <?php endif; ?>
                <div class="ttd-name"><?= $e($settings->nama_perusahaan ?? 'Tridatu Solution') ?></div>
            </div>
            <div style="clear: both;"></div>
        <?php endif; ?>
    </div>
</body>
</html>