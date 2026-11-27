<?php
/** @var object $penawaran */
/** @var object|null $settings */
$e = function ($value) {
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
};
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
$rupiah = function ($amount) {
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
};
$makeImage = function ($relativePath) {
    if (!$relativePath) return NULL;
    $path = FCPATH . ltrim((string) $relativePath, '/\\');
    if (!is_file($path)) return NULL;
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = in_array($extension, ['jpg', 'jpeg'], TRUE) ? 'jpeg' : ($extension === 'png' ? 'png' : '');
    if ($mime === '') return NULL;
    $data = file_get_contents($path);
    return $data === FALSE ? NULL : 'data:image/' . $mime . ';base64,' . base64_encode($data);
};
$items = isset($items) && is_array($items) ? $items : [];
$taxes = isset($taxes) && is_array($taxes) ? $taxes : [];
$kop = !isset($penawaran->sertakan_kop) || (bool) $penawaran->sertakan_kop;
$ttd = !isset($penawaran->sertakan_ttd) || (bool) $penawaran->sertakan_ttd;
$namaPerusahaan = $val($settings, ['nama_perusahaan'], 'CV. Tridatu Solution');
$alamatPerusahaan = $val($settings, ['alamat'], '');
$telpPerusahaan = $val($settings, ['no_telp'], '');
$emailPerusahaan = $val($settings, ['email'], '');
$kopImage = $kop ? $makeImage($val($settings, ['kop_surat', 'logo'], '')) : NULL;
$ttdImage = $ttd ? $makeImage($val($settings, ['ttd'], '')) : NULL;
$subtotal = (float) $val($penawaran, ['subtotal'], 0);
if ($subtotal <= 0 && $items) {
    foreach ($items as $item) $subtotal += (float) $val($item, ['subtotal', 'line_total'], 0);
}
$discount = min($subtotal, max(0, (float) $val($penawaran, ['discount_amount'], 0)));
$taxRows = [];
$taxTotal = 0;
foreach ($taxes as $tax) {
    $taxName = $val($tax, ['tax_name', 'nama_pajak'], 'Pajak');
    $rate = (float) $val($tax, ['tax_rate', 'persentase'], 0);
    $amount = (float) $val($tax, ['tax_amount', 'nilai'], 0);
    $taxTotal += $amount;
    $taxRows[] = [
        'name' => $taxName . ($rate > 0 ? ' ' . rtrim(rtrim(number_format($rate, 2, ',', '.'), '0'), ',') . '%' : ''),
        'amount' => $amount,
        'deduction' => $val($tax, ['jenis'], 'pajak') === 'potongan',
    ];
}
$total = (float) $val($penawaran, ['total_amount'], 0);
if ($total <= 0) $total = $subtotal - $discount + $taxTotal;
$rekening = trim((string) $val($penawaran, ['payment_account'], ''));
if ($rekening === '' && $settings) {
    $rekening = implode("\n", array_filter([
        !empty($settings->nama_bank) ? 'Bank: ' . $settings->nama_bank : '',
        !empty($settings->nama_cabang) ? 'Cabang: ' . $settings->nama_cabang : '',
        !empty($settings->nomor_rekening) ? 'No. Rekening: ' . $settings->nomor_rekening : '',
        !empty($settings->nama_pemilik_rekening) ? 'Nama Pemilik: ' . $settings->nama_pemilik_rekening : '',
    ]));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 30px 38px 34px; }
    body { font-family: Helvetica, Arial, sans-serif; color:#222; font-size:10px; line-height:1.45; }
    table { width:100%; border-collapse:collapse; }
    .top { margin-bottom:30px; }
    .brand { width:50%; vertical-align:top; }
    .brand img { max-width:210px; max-height:90px; }
    .company-name { font-size:17px; font-weight:bold; }
    .doc { width:50%; vertical-align:top; text-align:right; }
    .title { font-size:22px; font-weight:bold; margin:0 0 5px; }
    .number { font-size:12px; margin-bottom:10px; }
    .meta td { padding:2px 0 2px 10px; text-align:left; }
    .meta td:first-child { white-space:nowrap; }
    .parties { margin-bottom:24px; }
    .parties td { vertical-align:top; width:50%; }
    .party-label { color:#666; font-size:10px; margin-bottom:6px; }
    .party-name { font-weight:bold; font-size:12px; margin-bottom:5px; }
    .line { margin-bottom:3px; }
    .items th { background:#414141; color:#fff; font-size:10px; padding:7px 6px; border:1px solid #333; text-align:center; }
    .items td { padding:6px; border:1px solid #777; }
    .center { text-align:center; }
    .right { text-align:right; white-space:nowrap; }
    .summary td { padding:4px 7px; border:1px solid #777; }
    .summary-label { text-align:right; font-weight:bold; }
    .summary-value { text-align:right; white-space:nowrap; font-weight:bold; }
    .total td { border-top:2px solid #333; font-size:11px; }
    .description { margin-top:16px; white-space:pre-line; }
    .bottom { margin-top:30px; }
    .bank { width:60%; vertical-align:top; }
    .sign { width:40%; vertical-align:top; text-align:center; padding-top:10px; }
    .sign-image-wrap { height:70px; margin:8px auto 0; }
    .sign-image { max-width:140px; max-height:70px; }
    .sign-name { font-weight:bold; text-decoration:underline; margin-top:4px; }
</style>
</head>
<body>
<table class="top">
    <tr>
        <td class="brand">
            <?php if ($kopImage): ?>
                <img src="<?= $kopImage ?>" alt="Kop Surat">
            <?php elseif ($kop): ?>
                <div class="company-name"><?= $e($namaPerusahaan) ?></div>
                <?= $alamatPerusahaan ? '<div>' . $e($alamatPerusahaan) . '</div>' : '' ?>
                <?= $telpPerusahaan ? '<div>' . $e($telpPerusahaan) . '</div>' : '' ?>
                <?= $emailPerusahaan ? '<div>' . $e($emailPerusahaan) . '</div>' : '' ?>
            <?php endif; ?>
        </td>
        <td class="doc">
            <div class="title">PENAWARAN HARGA</div>
            <div class="number">#<?= $e($penawaran->no_penawaran ?? '-') ?></div>
            <table class="meta">
                <tr><td>Tanggal</td><td>:</td><td><?= $e($tgl($penawaran->tanggal_penawaran ?? NULL)) ?></td></tr>
                <tr><td>Berlaku sampai</td><td>:</td><td><?= $e($tgl($penawaran->berlaku_sampai ?? NULL)) ?></td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="parties">
    <tr>
        <td>
            <div class="party-label">Dari :</div>
            <div class="party-name"><?= $e($namaPerusahaan) ?></div>
            <?php if ($alamatPerusahaan): ?><div class="line"><?= $e($alamatPerusahaan) ?></div><?php endif; ?>
            <?php if ($telpPerusahaan): ?><div class="line"><?= $e($telpPerusahaan) ?></div><?php endif; ?>
            <?php if ($emailPerusahaan): ?><div class="line"><?= $e($emailPerusahaan) ?></div><?php endif; ?>
        </td>
        <td>
            <div class="party-label">Ditujukan kepada :</div>
            <div class="party-name"><?= $e($penawaran->nama_klien ?? '-') ?></div>
            <?php if (!empty($penawaran->nama_pic)): ?><div class="line">PIC: <?= $e($penawaran->nama_pic) ?></div><?php endif; ?>
            <?php if (!empty($penawaran->alamat)): ?><div class="line">Alamat: <?= $e($penawaran->alamat) ?></div><?php endif; ?>
            <?php if (!empty($penawaran->nama_project)): ?><div class="line">Project: <?= $e($penawaran->nama_project) ?></div><?php endif; ?>
        </td>
    </tr>
</table>

<div style="margin-bottom:10px"><strong>Perihal:</strong> <?= $e($penawaran->perihal ?: '-') ?></div>
<table class="items">
    <thead><tr>
        <th width="5%">No</th><th width="41%">Deskripsi</th><th width="10%">Qty</th><th width="22%">Harga</th><th width="22%">Jumlah</th>
    </tr></thead>
    <tbody>
    <?php if ($items): foreach ($items as $index => $item):
        $quantity = (float) $val($item, ['quantity', 'kuantitas'], 0);
        $unit = $val($item, ['unit', 'satuan'], '');
        $price = (float) $val($item, ['unit_price', 'harga_satuan'], 0);
        $amount = (float) $val($item, ['line_total', 'subtotal'], $quantity * $price);
    ?>
        <tr>
            <td class="center"><?= $index + 1 ?></td>
            <td><?= $e($val($item, ['description', 'nama_item'], '-')) ?><?php if (!empty($item->deskripsi)): ?><br><?= nl2br($e($item->deskripsi)) ?><?php endif; ?></td>
            <td class="center"><?= rtrim(rtrim(number_format($quantity, 2, ',', '.'), '0'), ',') ?><?= $unit !== '' ? ' ' . $e($unit) : '' ?></td>
            <td class="right"><?= $rupiah($price) ?></td>
            <td class="right"><?= $rupiah($amount) ?></td>
        </tr>
    <?php endforeach; else: ?>
        <tr><td colspan="5" class="center">Belum ada rincian item penawaran.</td></tr>
    <?php endif; ?>
        <tr class="summary"><td colspan="4" class="summary-label">Subtotal</td><td class="summary-value"><?= $rupiah($subtotal) ?></td></tr>
        <?php if ($discount > 0): ?><tr class="summary"><td colspan="4" class="summary-label">Diskon</td><td class="summary-value">- <?= $rupiah($discount) ?></td></tr><?php endif; ?>
        <?php foreach ($taxRows as $row): ?><tr class="summary"><td colspan="4" class="summary-label"><?= $e($row['name']) ?></td><td class="summary-value"><?= $row['deduction'] ? '- ' : '' ?><?= $rupiah($row['amount']) ?></td></tr><?php endforeach; ?>
        <tr class="summary total"><td colspan="4" class="summary-label">TOTAL</td><td class="summary-value"><?= $rupiah($total) ?></td></tr>
    </tbody>
</table>

<?php if (!empty($penawaran->isi_surat)): ?><div class="description"><?= nl2br($e($penawaran->isi_surat)) ?></div><?php endif; ?>
<?php if (!empty($penawaran->notes)): ?><div class="description"><strong>Catatan:</strong><br><?= nl2br($e($penawaran->notes)) ?></div><?php endif; ?>
<?php if ($rekening !== ''): ?><div class="description"><strong>Informasi Pembayaran:</strong><br><?= nl2br($e($rekening)) ?></div><?php endif; ?>

<?php if ($ttd): ?>
<table class="bottom"><tr>
    <td class="bank"><?= !empty($penawaran->payment_method) ? '<strong>Metode Pembayaran:</strong> ' . $e($penawaran->payment_method) : '' ?></td>
    <td class="sign">
        <div><?= $e($tgl($penawaran->tanggal_penawaran ?? NULL)) ?></div>
        <div><?= $e($namaPerusahaan) ?></div>
        <div class="sign-image-wrap"><?php if ($ttdImage): ?><img src="<?= $ttdImage ?>" class="sign-image" alt="Tanda tangan"><?php endif; ?></div>
        <?php if (!empty($settings->nama_penanggung_jawab)): ?><div class="sign-name"><?= $e($settings->nama_penanggung_jawab) ?></div><?php endif; ?>
        <?php if (!empty($settings->jabatan_penanggung_jawab)): ?><div><?= $e($settings->jabatan_penanggung_jawab) ?></div><?php endif; ?>
    </td>
</tr></table>
<?php endif; ?>
</body>
</html>
