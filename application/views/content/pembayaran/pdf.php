<?php
/** @var object $penawaran */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};
$kop = !isset($penawaran->sertakan_kop) || $penawaran->sertakan_kop;
$ttd = !isset($penawaran->sertakan_ttd) || $penawaran->sertakan_ttd;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 40px 50px; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 12px; color: #0f172a; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; }
        .kop td { padding-bottom: 12px; border-bottom: 2px solid #a62626; vertical-align: top; }
        .kop h1 { margin: 0; font-size: 20px; }
        .kop small { color: #64748b; }
        .right { text-align: right; color: #475569; }
        .meta { margin: 22px 0; }
        .meta td { padding: 2px 0; vertical-align: top; }
        .meta td.l { width: 110px; color: #64748b; }
        .isi { margin-top: 14px; }
        .ttd { margin-top: 70px; text-align: right; }
    </style>
</head>
<body>
    <?php if ($kop): ?>
        <table class="kop">
            <tr>
                <td><h1>Tridatu Solution</h1><small>Jl. Alamat perusahaan, Bali</small></td>
                <td class="right">No. <?= $e($penawaran->no_penawaran) ?><br><?= $tgl($penawaran->tanggal_penawaran) ?></td>
            </tr>
        </table>
    <?php else: ?>
        <div class="right">No. <?= $e($penawaran->no_penawaran) ?><br><?= $tgl($penawaran->tanggal_penawaran) ?></div>
    <?php endif; ?>

    <table class="meta">
        <tr><td class="l">Kepada</td><td><strong><?= $e($penawaran->nama_klien ?: '-') ?></strong></td></tr>
        <tr><td class="l">PIC</td><td><?= $e($penawaran->nama_pic ?: '-') ?></td></tr>
        <tr><td class="l">Alamat</td><td><?= $e($penawaran->alamat ?: '-') ?></td></tr>
        <tr><td class="l">Project</td><td><?= $e($penawaran->nama_project ?: 'Mandiri') ?></td></tr>
        <tr><td class="l">Berlaku sampai</td><td><?= $tgl($penawaran->berlaku_sampai) ?></td></tr>
    </table>

    <div><strong>Perihal:</strong> <?= $e($penawaran->perihal ?: '-') ?></div>
    <div class="isi"><?= nl2br($e($penawaran->isi_surat)) ?></div>

    <?php if ($ttd): ?>
        <div class="ttd">Hormat kami,<br><br><br><br><strong>Tridatu Solution</strong></div>
    <?php endif; ?>
</body>
</html>