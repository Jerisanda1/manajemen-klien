<?php
/** @var object $penawaran */
/** @var object|null $settings */
$e = function ($value) { return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8'); };
$val = function ($object, $keys, $default = '') {
    foreach ($keys as $key) {
        if (is_object($object) && isset($object->$key) && $object->$key !== '') return $object->$key;
        if (is_array($object) && isset($object[$key]) && $object[$key] !== '') return $object[$key];
    }
    return $default;
};
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($date) use ($bulan) {
    $time = $date ? strtotime((string) $date) : FALSE;
    return $time ? date('j', $time) . ' ' . $bulan[(int) date('n', $time)] . ' ' . date('Y', $time) : '-';
};
$rupiah = function ($amount) { return 'Rp ' . number_format((float) $amount, 0, ',', '.'); };
$items = isset($items) && is_array($items) ? $items : [];
$taxes = isset($taxes) && is_array($taxes) ? $taxes : [];
$settings = isset($settings) && is_object($settings) ? $settings : NULL;
$kop = !isset($penawaran->sertakan_kop) || (bool) $penawaran->sertakan_kop;
$ttd = !isset($penawaran->sertakan_ttd) || (bool) $penawaran->sertakan_ttd;
$kopAsset = $settings
    ? (!empty($settings->kop_surat) ? $settings->kop_surat : ($settings->logo ?? ''))
    : '';
$kopPath = $kop && $kopAsset && is_file(FCPATH . $kopAsset) ? base_url($kopAsset) : NULL;
$ttdPath = $ttd && $settings && !empty($settings->ttd) && is_file(FCPATH . $settings->ttd)
    ? base_url($settings->ttd)
    : NULL;
$subtotal = (float) $val($penawaran, ['subtotal'], 0);
if ($subtotal <= 0) foreach ($items as $item) $subtotal += (float) $val($item, ['line_total', 'subtotal'], 0);
$discount = min($subtotal, max(0, (float) $val($penawaran, ['discount_amount'], 0)));
$taxTotal = 0;
foreach ($taxes as $tax) $taxTotal += (float) $val($tax, ['tax_amount', 'nilai'], 0);
$total = (float) $val($penawaran, ['total_amount'], 0);
if ($total <= 0) $total = $subtotal - $discount + $taxTotal;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $e($penawaran->no_penawaran ?? 'Penawaran') ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; color:#1f2937; margin:0; background:#f1f5f9; }
        .bar { max-width:794px; margin:16px auto 0; text-align:right; }
        .bar button { padding:8px 18px; border:0; border-radius:6px; background:#a62626; color:#fff; cursor:pointer; }
        .sheet { max-width:794px; min-height:1000px; margin:24px auto; padding:48px; box-sizing:border-box; background:#fff; }
        .header { display:flex; justify-content:space-between; gap:24px; align-items:flex-start; border-bottom:2px solid #a62626; padding-bottom:16px; margin-bottom:24px; }
        .logo { max-width:230px; max-height:95px; object-fit:contain; }
        .company { font-size:20px; font-weight:bold; }
        .header-right { text-align:right; font-size:13px; line-height:1.7; }
        h1 { margin:0 0 4px; font-size:20px; }
        .parties { display:grid; grid-template-columns:1fr 1fr; gap:28px; margin-bottom:22px; font-size:13px; line-height:1.6; }
        .muted { color:#64748b; font-size:12px; margin-bottom:6px; }
        .party-name { font-weight:bold; font-size:15px; }
        .perihal { background:#f8fafc; border-left:3px solid #a62626; padding:10px 12px; margin-bottom:18px; font-size:14px; }
        table { width:100%; border-collapse:collapse; font-size:13px; }
        th { background:#414141; color:#fff; padding:9px 7px; text-align:left; }
        td { border:1px solid #cbd5e1; padding:8px 7px; }
        .center { text-align:center; }
        .right { text-align:right; white-space:nowrap; }
        .sum td { border-left:0; border-right:0; }
        .sum-label { text-align:right; font-weight:bold; }
        .total td { border-top:2px solid #334155; font-size:15px; font-weight:bold; }
        .notes { margin-top:16px; font-size:13px; line-height:1.6; white-space:pre-line; }
        .bottom { display:flex; justify-content:space-between; gap:24px; margin-top:36px; font-size:13px; }
        .sign { width:220px; text-align:center; }
        .signature-space { height:80px; }
        @media print { body { background:#fff; } .bar { display:none; } .sheet { margin:0; max-width:none; min-height:0; padding:0; } }
    </style>
</head>
<body>
<div class="bar"><button onclick="window.print()">Cetak / Simpan PDF</button></div>
<main class="sheet">
    <header class="header">
        <div>
            <?php if ($kopPath): ?><img src="<?= $e($kopPath) ?>" class="logo" alt="Kop Surat">
            <?php elseif ($kop): ?><div class="company"><?= $e($val($settings, ['nama_perusahaan'], 'CV. Tridatu Solution')) ?></div><?php endif; ?>
        </div>
        <div class="header-right">
            <h1>PENAWARAN HARGA</h1>
            <strong>#<?= $e($penawaran->no_penawaran ?? '-') ?></strong><br>
            Tanggal: <?= $tgl($penawaran->tanggal_penawaran ?? NULL) ?><br>
            Berlaku sampai: <?= $tgl($penawaran->berlaku_sampai ?? NULL) ?>
        </div>
    </header>
    <section class="parties">
        <div><div class="muted">Dari</div><div class="party-name"><?= $e($val($settings, ['nama_perusahaan'], 'CV. Tridatu Solution')) ?></div>
            <?= $e($val($settings, ['alamat'], '')) ?><br><?= $e($val($settings, ['no_telp'], '')) ?><br><?= $e($val($settings, ['email'], '')) ?></div>
        <div><div class="muted">Ditujukan kepada</div><div class="party-name"><?= $e($penawaran->nama_klien ?? '-') ?></div>
            <?= !empty($penawaran->nama_pic) ? 'PIC: ' . $e($penawaran->nama_pic) . '<br>' : '' ?>
            <?= !empty($penawaran->alamat) ? 'Alamat: ' . nl2br($e($penawaran->alamat)) . '<br>' : '' ?>
            <?= !empty($penawaran->nama_project) ? 'Project: ' . $e($penawaran->nama_project) : '' ?></div>
    </section>
    <div class="perihal"><strong>Perihal:</strong> <?= $e($penawaran->perihal ?: '-') ?></div>
    <table>
        <thead><tr><th class="center" style="width:5%">No</th><th>Deskripsi</th><th class="center" style="width:12%">Qty</th><th class="right" style="width:20%">Harga</th><th class="right" style="width:20%">Jumlah</th></tr></thead>
        <tbody>
        <?php if ($items): foreach ($items as $index => $item):
            $qty = (float) $val($item, ['quantity', 'kuantitas'], 0);
            $unit = $val($item, ['unit', 'satuan'], '');
            $price = (float) $val($item, ['unit_price', 'harga_satuan'], 0);
        ?>
            <tr><td class="center"><?= $index + 1 ?></td><td><?= $e($val($item, ['description', 'nama_item'], '-')) ?><?= !empty($item->deskripsi) ? '<br>' . nl2br($e($item->deskripsi)) : '' ?></td><td class="center"><?= $e($qty) ?> <?= $e($unit) ?></td><td class="right"><?= $rupiah($price) ?></td><td class="right"><?= $rupiah($val($item, ['line_total', 'subtotal'], $qty * $price)) ?></td></tr>
        <?php endforeach; else: ?>
            <tr><td colspan="5" class="center">Belum ada rincian item penawaran.</td></tr>
        <?php endif; ?>
            <tr class="sum"><td colspan="4" class="sum-label">Subtotal</td><td class="right"><?= $rupiah($subtotal) ?></td></tr>
            <?php if ($discount > 0): ?><tr class="sum"><td colspan="4" class="sum-label">Diskon</td><td class="right">- <?= $rupiah($discount) ?></td></tr><?php endif; ?>
            <?php foreach ($taxes as $tax): ?><tr class="sum"><td colspan="4" class="sum-label"><?= $e($val($tax, ['tax_name', 'nama_pajak'], 'Pajak')) ?></td><td class="right"><?= $rupiah($val($tax, ['tax_amount', 'nilai'], 0)) ?></td></tr><?php endforeach; ?>
            <tr class="sum total"><td colspan="4" class="sum-label">TOTAL</td><td class="right"><?= $rupiah($total) ?></td></tr>
        </tbody>
    </table>
    <?php if (!empty($penawaran->isi_surat)): ?><div class="notes"><strong>Keterangan / Syarat:</strong><br><?= nl2br($e($penawaran->isi_surat)) ?></div><?php endif; ?>
    <?php if (!empty($penawaran->notes)): ?><div class="notes"><strong>Catatan:</strong><br><?= nl2br($e($penawaran->notes)) ?></div><?php endif; ?>
    <?php if (!empty($penawaran->payment_account)): ?><div class="notes"><strong>Informasi Pembayaran:</strong><br><?= nl2br($e($penawaran->payment_account)) ?></div><?php endif; ?>
    <footer class="bottom">
        <div><?= !empty($penawaran->payment_method) ? '<strong>Metode Pembayaran:</strong> ' . $e($penawaran->payment_method) : '' ?></div>
        <?php if ($ttd): ?><div class="sign">Hormat kami,<br><?= $e($val($settings, ['nama_perusahaan'], 'CV. Tridatu Solution')) ?><div class="signature-space"><?php if ($ttdPath): ?><img src="<?= $e($ttdPath) ?>" alt="Tanda tangan" style="max-width:150px;max-height:75px"><?php endif; ?></div><?php if (!empty($settings->nama_penanggung_jawab)): ?><strong><u><?= $e($settings->nama_penanggung_jawab) ?></u></strong><br><?= $e($settings->jabatan_penanggung_jawab ?? '') ?><?php endif; ?></div><?php endif; ?>
    </footer>
</main>
</body>
</html>
