<?php
/**
 * Proforma Invoice - Tridatu Solution
 * Layout mengikuti dokumen referensi: logo kiri, metadata kanan,
 * info perusahaan/klien dua kolom, tabel gelap, ringkasan,
 * catatan/rekening kiri, tanda tangan kanan.
 */
$e = function ($v) {
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
};
$val = function ($obj, $keys, $default = '') {
    foreach ($keys as $key) {
        if (is_object($obj) && isset($obj->$key) && $obj->$key !== '') return $obj->$key;
        if (is_array($obj) && isset($obj[$key]) && $obj[$key] !== '') return $obj[$key];
    }
    return $default;
};
// Gunakan snapshot perusahaan jika tersedia.
// Fallback ke pengaturan saat ini hanya untuk invoice lama
// yang dibuat sebelum fitur snapshot diterapkan.
if (!empty($invoice->snapshot_data)) {
    $snapshot = json_decode($invoice->snapshot_data);

    if (is_object($snapshot)) {
        $settings = $snapshot;
    }
}
$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};
$rupiah = function ($angka) {
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
};

$items = isset($items) && is_array($items) ? $items : [];
$taxes = isset($taxes) && is_array($taxes) ? $taxes : [];
$kop = !isset($invoice->sertakan_kop) || (bool) $invoice->sertakan_kop;
$ttd = !isset($invoice->sertakan_ttd) || (bool) $invoice->sertakan_ttd;
$namaPerusahaan = $val($settings, ['nama_perusahaan'], 'CV. Tridatu Solution');
$alamatPerusahaan = $val($settings, ['alamat'], '-');
$telpPerusahaan = $val($settings, ['no_telp'], '');
$emailPerusahaan = $val($settings, ['email'], '');

$makeImage = function ($relativePath) {
    if (!$relativePath) return null;
    $path = FCPATH . ltrim((string) $relativePath, '/\\');
    if (!is_file($path)) return null;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mime = $ext === 'jpg' || $ext === 'jpeg' ? 'jpeg' : ($ext === 'png' ? 'png' : '');
    if ($mime === '') return null;
    return 'data:image/' . $mime . ';base64,' . base64_encode(file_get_contents($path));
};
$logoBase64 = $kop
    ? $makeImage($val($settings, ['kop_surat', 'logo'], ''))
    : null;
$ttdBase64 = $ttd ? $makeImage($val($settings, ['ttd'], '')) : null;

$subtotal = (float) $val($invoice, ['subtotal'], 0);
$diskon = (float) $val($invoice, ['discount_amount'], 0);
if ($subtotal <= 0 && $items) {
    foreach ($items as $item) $subtotal += (float) $val($item, ['subtotal', 'line_total'], 0);
}
$taxRows = [];
$totalPajak = 0;
$totalPotongan = 0;
foreach ($taxes as $tax) {
    $jenis = strtolower((string) $val($tax, ['jenis', 'tax_type'], 'pajak'));
    $nama = $val($tax, ['nama_pajak', 'tax_name'], 'Pajak');
    $persen = (float) $val($tax, ['persentase', 'tax_rate'], 0);
    $nilai = (float) $val($tax, ['nilai', 'tax_amount'], 0);
    $isPotongan = ($jenis === 'potongan' || $jenis === 'fixed_discount');
    if ($isPotongan) $totalPotongan += $nilai; else $totalPajak += $nilai;
    $label = $nama;
    if ($persen > 0) $label .= ' ' . rtrim(rtrim(number_format($persen, 2, ',', '.'), '0'), ',') . '%';
    $taxRows[] = ['label' => $label, 'nilai' => $nilai, 'potongan' => $isPotongan];
}
$total = (float) $val($invoice, ['total_amount'], 0);
$hitungTotal = $subtotal - $diskon - $totalPotongan + $totalPajak;
if ($total <= 0 && ($subtotal > 0 || $taxRows)) $total = $hitungTotal;

$metodePembayaran = trim((string) $val($invoice, ['payment_method'], ''));

$rekeningDefault = '';

if ($settings) {
    $rekeningDefault = implode("\n", array_filter([
        !empty($settings->nama_bank)
            ? 'Bank: ' . $settings->nama_bank
            : '',
        !empty($settings->nama_cabang)
            ? 'Cabang: ' . $settings->nama_cabang
            : '',
        !empty($settings->swift_code)
            ? 'SWIFT Code: ' . $settings->swift_code
            : '',
        !empty($settings->nomor_rekening)
            ? 'No. Rekening: ' . $settings->nomor_rekening
            : '',
        !empty($settings->nama_pemilik_rekening)
            ? 'Nama Pemilik: ' . $settings->nama_pemilik_rekening
            : '',
    ]));
}

$rekeningPembayaran = trim(
    (string) $val($invoice, ['payment_account'], $rekeningDefault)
);

$catatan = trim((string) $val($invoice, ['notes'], ''));
$tanggalCetak = $val($invoice, ['tanggal_invoice'], date('Y-m-d'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 30px 38px 34px; }
    body { font-family: Helvetica, Arial, sans-serif; color:#222; font-size:10px; line-height:1.42; }
    table { width:100%; border-collapse:collapse; }
    .top { margin-bottom:42px; }
    .logo-cell { width:42%; vertical-align:top; }
    .logo { max-width:150px; max-height:145px; }
    .top-meta { width:58%; vertical-align:top; text-align:right; }
    .title { font-size:24px; font-weight:normal; letter-spacing:.4px; margin:0 0 2px; }
    .number { font-size:13px; margin:0 0 27px; }
    .meta { width:auto; margin-left:auto; font-size:11px; }
    .meta td { padding:5px 0 5px 12px; text-align:left; }
    .meta td:first-child { white-space:nowrap; }
    .meta .colon { padding-left:18px; padding-right:8px; }
    .parties { margin-bottom:30px; }
    .parties td { vertical-align:top; }
    .party-left { width:49%; border-right:3px solid #777; padding-right:22px; }
    .party-right { width:51%; padding-left:42px; }
    .party-label { color:#666; font-size:11px; margin-bottom:15px; }
    .party-name { font-weight:bold; font-size:12px; margin-bottom:10px; }
    .party-line { font-size:11px; margin-bottom:7px; }
    .items { margin-top:0; }
    .items th { background:#414141; color:#fff; font-size:11px; padding:8px 6px; border:1px solid #333; text-align:center; }
    .items td { padding:6px 7px; border:1px solid #777; font-size:10px; }
    .center { text-align:center; }
    .right { text-align:right; }
    .money { white-space:nowrap; }
    .summary-label { text-align:right; font-weight:bold; }
    .summary-value { text-align:right; white-space:nowrap; font-weight:bold; }
    .summary-row td { padding:4px 7px; border-left:1px solid #777; border-right:1px solid #777; border-bottom:1px solid #777; }
    .summary-row.first td { border-top:1px solid #777; }
    .total-row td { font-weight:bold; border-top:1px solid #333; }
    .notes { margin-top:15px; font-size:10px; }
    .notes-title { color:#777; font-size:11px; margin-bottom:2px; }
    .bottom { margin-top:34px; }
    .bank { width:62%; vertical-align:top; }
    .sign { width:38%; vertical-align:top; text-align:center; padding-top:90px; }
    .bank-title { color:#777; font-size:11px; margin-bottom:8px; }
    .bank-table { width:100%; font-size:10px; }
    .bank-table td { padding:2px 0; vertical-align:top; }
    .bank-label { width:95px; padding-left:14px !important; }
    .sign-date { font-size:10px; margin-bottom:5px; }
    .sign-company { font-weight:bold; font-size:11px; }
    .sign-image-wrap { height:75px; margin:0 auto; }
    .sign-image { max-width:145px; max-height:75px; }
    .sign-name { font-weight:bold; text-decoration:underline; font-size:11px; margin-top:2px; }
    .sign-role { font-weight:bold; font-size:10px; }
    .stamp { position:fixed; top:180px; right:30px; border:3px solid #159765; color:#159765; font-size:25px; font-weight:bold; padding:5px 12px; transform:rotate(-15deg); opacity:.5; }
</style>
</head>
<body>
<?php if (isset($invoice->status) && $invoice->status === 'Dibayar'): ?><div class="stamp">LUNAS</div><?php endif; ?>

<table class="top">
    <tr>
        <td class="logo-cell">
            <?php if ($logoBase64): ?><img src="<?= $logoBase64 ?>" class="logo" alt="Logo Tridatu Solution"><?php elseif ($kop && $namaPerusahaan): ?><div style="font-size:17px;font-weight:bold;margin-top:35px;"><?= $e($namaPerusahaan) ?></div><?php endif; ?>
        </td>
        <td class="top-meta">
            <div class="title">PROFORMA INVOICE</div>
            <div class="number">#<?= $e($invoice->no_invoice ?? '-') ?></div>
            <table class="meta">
                <tr><td>Tanggal</td><td class="colon">:</td><td><?= $e($tgl($tanggalCetak)) ?></td></tr>
                <tr><td>Metode Pembayaran</td><td class="colon">:</td><td><?= $e($metodePembayaran ?: '-') ?></td></tr>
                <tr><td>Jatuh Tempo</td><td class="colon">:</td><td><?= $e($tgl($invoice->jatuh_tempo ?? null)) ?></td></tr>
            </table>
        </td>
    </tr>
</table>

<table class="parties">
    <tr>
        <td class="party-left">
            <div class="party-label">Info Perusahaan :</div>
            <div class="party-name"><?= $e($namaPerusahaan) ?></div>
            <?php if ($telpPerusahaan !== ''): ?><div class="party-line"><?= $e($telpPerusahaan) ?></div><?php endif; ?>
            <?php if ($alamatPerusahaan !== ''): ?><div class="party-line">Alamat : <?= $e($alamatPerusahaan) ?></div><?php endif; ?>
            <?php if ($emailPerusahaan !== ''): ?><div class="party-line">Email : <?= $e($emailPerusahaan) ?></div><?php endif; ?>
        </td>
        <td class="party-right">
            <div class="party-label">Tagihan Kepada :</div>
            <div class="party-name"><?= $e($invoice->nama_klien ?? '-') ?></div>
            <?php if (!empty($invoice->nama_pic)): ?><div class="party-line">PIC : <?= $e($invoice->nama_pic) ?></div><?php endif; ?>
            <?php if (!empty($invoice->alamat)): ?><div class="party-line">Alamat : <?= $e($invoice->alamat) ?></div><?php endif; ?>
            <?php if (!empty($invoice->nama_project)): ?><div class="party-line">Project : <?= $e($invoice->nama_project) ?></div><?php endif; ?>
        </td>
    </tr>
</table>

<table class="items">
    <thead>
        <tr>
            <th width="5%">No</th>
            <th width="37%">Item</th>
            <th width="9%">Qty</th>
            <th width="25%">Rate</th>
            <th width="24%">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($items): ?>
            <?php foreach ($items as $i => $item):
                $namaItem = $val($item, ['nama_item', 'description'], '-');
                $deskripsi = $val($item, ['deskripsi'], '');
                $qty = (float) $val($item, ['kuantitas', 'quantity'], 0);
                $unit = $val($item, ['satuan', 'unit'], '');
                $harga = (float) $val($item, ['harga_satuan', 'unit_price'], 0);
                $jumlah = (float) $val($item, ['subtotal', 'line_total'], $qty * $harga);
            ?>
            <tr>
                <td class="center"><?= $i + 1 ?></td>
                <td><?= $e($namaItem) ?><?php if ($deskripsi !== ''): ?><br><?= nl2br($e($deskripsi)) ?><?php endif; ?></td>
                <td class="center"><?= rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') ?><?= $unit !== '' ? ' ' . $e($unit) : '' ?></td>
                <td class="center money"><strong><?= $rupiah($harga) ?></strong></td>
                <td class="right money"><strong><?= $rupiah($jumlah) ?></strong></td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="5" class="center">Belum ada rincian item invoice.</td></tr>
        <?php endif; ?>
        <tr class="summary-row first"><td colspan="4" class="summary-label">Sub Total</td><td class="summary-value"><?= $rupiah($subtotal) ?></td></tr>
        <?php if ($diskon > 0): ?><tr class="summary-row"><td colspan="4" class="summary-label">Diskon</td><td class="summary-value">- <?= $rupiah($diskon) ?></td></tr><?php endif; ?>
        <?php foreach ($taxRows as $row): ?>
            <tr class="summary-row"><td colspan="4" class="summary-label"><?= $e($row['label']) ?></td><td class="summary-value"><?= $row['potongan'] ? '- ' : '' ?><?= $rupiah($row['nilai']) ?></td></tr>
        <?php endforeach; ?>
        <tr class="summary-row total-row"><td colspan="4" class="summary-label">TOTAL</td><td class="summary-value"><?= $rupiah($total) ?></td></tr>
    </tbody>
</table>

<?php if ($catatan !== ''): ?>
<div class="notes"><div class="notes-title">Catatan :</div><?= nl2br($e($catatan)) ?></div>
<?php endif; ?>

<table class="bottom">
    <tr>
        <td class="bank">
            <?php if ($rekeningPembayaran !== ''): ?>
                <div class="bank-title">Bank Account</div>
                <?php
                $lines = preg_split('/\\n|\r|\n/', $rekeningPembayaran);
                ?>
                <table class="bank-table">
                    <?php foreach ($lines as $line):
                        if (trim($line) === '') continue;
                        $parts = explode(':', $line, 2);
                    ?>
                    <tr>
                        <?php if (count($parts) === 2): ?>
                            <td class="bank-label"><?= $e(trim($parts[0])) ?></td><td>: <?= nl2br($e(trim($parts[1]))) ?></td>
                        <?php else: ?>
                            <td colspan="2"><?= $e(trim($line)) ?></td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </td>
        <td class="sign">
            <div class="sign-date">Denpasar, <?= $e($tgl($tanggalCetak)) ?></div>
            <div class="sign-company"><?= $e($namaPerusahaan) ?></div>
            <div class="sign-image-wrap"><?php if ($ttdBase64): ?><img src="<?= $ttdBase64 ?>" class="sign-image" alt="Tanda tangan"><?php endif; ?></div>
            <?php $namaPenanggungJawab = !empty($settings->nama_penanggung_jawab) ? $settings->nama_penanggung_jawab : 'I Putu Dony Suryambawa'; ?>
            <?php $jabatanPenanggungJawab = !empty($settings->jabatan_penanggung_jawab) ? $settings->jabatan_penanggung_jawab : 'Direktur'; ?>
            <div class="sign-name"><?= $e($namaPenanggungJawab) ?></div>
            <div class="sign-role"><?= $e($jabatanPenanggungJawab) ?></div>
        </td>
    </tr>
</table>
</body>
</html>
