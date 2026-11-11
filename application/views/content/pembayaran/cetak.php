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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($penawaran->no_penawaran) ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color:#0f172a; margin:0; background:#f1f5f9; }
        .sheet { max-width:794px; margin:24px auto; background:#fff; padding:48px; box-sizing:border-box; min-height:1000px; }
        .bar { max-width:794px; margin:16px auto 0; text-align:right; }
        .bar button { padding:8px 18px; border:0; border-radius:6px; background:#a62626; color:#fff; font-size:14px; cursor:pointer; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; border-bottom:2px solid #a62626; padding-bottom:16px; margin-bottom:28px; }
        .top h1 { margin:0; font-size:22px; }
        .top small { color:#64748b; }
        .meta { font-size:14px; line-height:1.7; margin-bottom:24px; }
        .meta .r { display:flex; }
        .meta .l { width:110px; color:#64748b; }
        .perihal { font-size:15px; margin-bottom:20px; }
        .isi { font-size:14px; line-height:1.7; white-space:pre-line; }
        .ttd { margin-top:80px; text-align:right; font-size:14px; }
        @media print { body { background:#fff; } .bar { display:none; } .sheet { margin:0; max-width:none; padding:0; min-height:0; } }
    </style>
</head>
<body>
    <div class="bar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
    <div class="sheet">
        <?php if ($kop): ?>
            <div class="top">
                <div><h1>Tridatu Solution</h1><small>Jl. Alamat perusahaan, Bali</small></div>
                <div style="text-align:right;font-size:14px;color:#475569">No. <?= $e($penawaran->no_penawaran) ?><br><?= $tgl($penawaran->tanggal_penawaran) ?></div>
            </div>
        <?php else: ?>
            <div style="text-align:right;font-size:14px;color:#475569;margin-bottom:24px">No. <?= $e($penawaran->no_penawaran) ?><br><?= $tgl($penawaran->tanggal_penawaran) ?></div>
        <?php endif; ?>

        <div class="meta">
            <div class="r"><span class="l">Kepada</span><span><strong><?= $e($penawaran->nama_klien ?: '-') ?></strong></span></div>
            <div class="r"><span class="l">PIC</span><span><?= $e($penawaran->nama_pic ?: '-') ?></span></div>
            <div class="r"><span class="l">Alamat</span><span><?= $e($penawaran->alamat ?: '-') ?></span></div>
            <div class="r"><span class="l">Project</span><span><?= $e($penawaran->nama_project ?: 'Mandiri') ?></span></div>
            <div class="r"><span class="l">Berlaku sampai</span><span><?= $tgl($penawaran->berlaku_sampai) ?></span></div>
        </div>

        <div class="perihal"><strong>Perihal:</strong> <?= $e($penawaran->perihal ?: '-') ?></div>
        <div class="isi"><?= $e($penawaran->isi_surat) ?></div>

        <?php if ($ttd): ?>
            <div class="ttd">Hormat kami,<br><br><br><br><strong>Tridatu Solution</strong></div>
        <?php endif; ?>
    </div>
</body>
</html>