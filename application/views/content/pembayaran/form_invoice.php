<?php
/**
 * @var CI_Controller $this
 * @var object|null $inv
 * @var array $klienList, $projectList, $penawaranList
 * @var string $nomorBaru, $action, $title, $formErr, $uploadErr
 * @var int $pre
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$inv    = isset($inv) ? $inv : null;
$isEdit = !empty($inv);

// Nilai form: isian terakhir (post) > data invoice (edit) > default
$p = function ($f, $d = '') use ($inv) {
    $v = $this->input->post($f);
    if ($v !== null) return $v;
    if ($inv && isset($inv->$f) && $inv->$f !== null) return $inv->$f;
    return $d;
};
$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="bi-err">' . htmlspecialchars($m) . '</p>' : '';
};

if ($isEdit) {
    $sumber = !empty($inv->penawaran_id) ? 'penawaran' : 'baru';
    $cara   = ($inv->dibuat_lewat === 'Unggahan') ? 'unggah' : 'template';
} else {
    $sumber = $p('sumber', $pre ? 'penawaran' : 'baru');
    $cara   = $p('cara', 'template');
}
$posted = $this->input->method() === 'post';
$kop = $posted ? (bool) $this->input->post('sertakan_kop') : ($isEdit ? (bool) $inv->sertakan_kop : true);
$ttd = $posted ? (bool) $this->input->post('sertakan_ttd') : ($isEdit ? (bool) $inv->sertakan_ttd : true);
$pwPilih = $p('penawaran_id', $pre ?: '');

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$fmt = function ($t) use ($bulan) {
    $x = $t ? strtotime($t) : false;
    return $x ? date('j', $x) . ' ' . $bulan[(int) date('n', $x)] . ' ' . date('Y', $x) : '-';
};
$nomorTampil = $isEdit ? $inv->no_invoice : $nomorBaru;
$tglTampil   = $isEdit ? $fmt($inv->tanggal_invoice) : $fmt(date('Y-m-d'));
$statusTampil = $isEdit ? $inv->status : 'Belum dibayar';
$pillStyle = $statusTampil === 'Dibayar' ? 'background:#bbf7d0;color:#15803d' : 'background:#fed7aa;color:#ea580c';

$pwData = [];
foreach ($penawaranList as $x) {
    $pwData[$x->id] = [
        'klien_id' => (int) $x->klien_id, 'project_id' => (int) $x->project_id,
        'perihal'  => (string) $x->perihal,
        'isi'      => $x->dibuat_lewat === 'Unggahan' ? '' : (string) $x->isi_surat,
        'no'       => $x->no_penawaran,
    ];
}

$adaFile  = $isEdit && !empty($inv->file_path);
$fileNama = $adaFile ? $inv->file_nama : '';
$from = isset($from) ? $from : 'list';
$urlKembali = ($isEdit && $from === 'detail')
    ? site_url('pembayaran/detail/invoice/' . (int) $inv->id)
    : site_url('pembayaran?tab=invoice');
?>
<style>
    [hidden] { display:none !important; }
    .bi { padding:20px 32px 40px; color:#0f172a; }
    .bi a { text-decoration:none; }
    .bi-back { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:18px; margin-bottom:6px; }
    .bi h1 { font-size:32px; font-weight:700; margin:0 0 20px; }
    .bi-grid { display:grid; grid-template-columns:minmax(0,1fr) 400px; gap:16px; align-items:start; }
    .bi-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:16px; }
    .bi-card h2 { font-size:22px; font-weight:700; margin:0 0 16px; }
    .bi-sr { position:absolute; opacity:0; pointer-events:none; }
    .bi-lbl { font-size:15px; color:#475569; margin-bottom:8px; display:block; }
    .bi-modes { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; margin-bottom:18px; }
    .bi-mode { display:flex; gap:14px; align-items:flex-start; border:1px solid #0f172a; border-radius:8px; padding:18px; cursor:pointer; }
    .bi-mode.on { background:#dbeafe; border-color:#1d8fe1; }
    .bi-mode.lock, .bi-cara label.lock { opacity:.5; cursor:not-allowed; }
    .bi-radio { width:24px; height:24px; border:1.5px solid #475569; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#fff; }
    .bi-mode.on .bi-radio { border-color:#1d8fe1; }
    .bi-mode.on .bi-radio::after { content:""; width:12px; height:12px; border-radius:50%; background:#1d8fe1; }
    .bi-mode b { display:block; font-size:16px; font-weight:500; margin-bottom:2px; }
    .bi-mode small { font-size:14px; color:#475569; line-height:1.4; }
    .bi-cara { display:flex; gap:32px; flex-wrap:wrap; }
    .bi-cara label { display:inline-flex; align-items:center; gap:10px; cursor:pointer; font-size:16px; }
    .bi-cara input { width:22px; height:22px; accent-color:#1d8fe1; }
    .bi-row { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; }
    .bi-field { margin-bottom:16px; min-width:0; }
    .bi-field label { display:block; font-size:15px; color:#475569; margin-bottom:6px; }
    .bi-input, .bi-select, .bi-text { width:100%; box-sizing:border-box; border:1px solid #94a3b8; border-radius:8px; background:#fff; padding:0 14px; font-size:15px; color:#0f172a; font-family:inherit; }
    .bi-input, .bi-select { height:48px; }
    .bi-text { padding:12px 14px; min-height:120px; resize:vertical; }
    .bi-input::placeholder, .bi-text::placeholder { color:#94a3b8; }
    .bi-input:focus, .bi-select:focus, .bi-text:focus { outline:none; border-color:#a62626; box-shadow:0 0 0 3px rgba(166,38,38,.15); }
    .bi-lock { pointer-events:none; background:#f4f4f5; }
    .bi-input[readonly] { background:#f4f4f5; color:#475569; }
    .bi-hint { font-size:12px; color:#64748b; margin:4px 0 0; }
    .bi-err { font-size:12px; color:#dc2626; margin:4px 0 0; }
    .bi-alert { border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; border-radius:8px; padding:10px 14px; font-size:14px; margin-bottom:16px; }
    .bi-checks { display:flex; gap:28px; flex-wrap:wrap; font-size:14px; color:#475569; }
    .bi-checks label { display:inline-flex; align-items:center; gap:10px; cursor:pointer; }
    .bi-checks input { width:20px; height:20px; accent-color:#c53030; }

    .bi-auto .rw { display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:15px; }
    .bi-auto .rw span:first-child { color:#64748b; }
    .bi-auto .rw span:last-child { font-weight:500; }
    .bi-pill { border-radius:999px; padding:2px 16px; font-size:14px; }
    .bi-note { border-top:1px solid #e2e8f0; margin-top:10px; padding-top:10px; font-size:12px; color:#64748b; line-height:1.5; }

    .bi-dz { border:1.5px dashed #64748b; border-radius:10px; padding:26px 16px; text-align:center; font-size:13px; color:#0f172a; }
    .bi-dz.over { background:#dbeafe; border-color:#1d8fe1; }
    .bi-dz svg { display:block; margin:0 auto 6px; }
    .bi-pick { margin-top:10px; padding:4px 14px; border:1px solid #cbd5e1; border-radius:4px; background:#f4f4f5; color:#475569; font-size:12px; cursor:pointer; font-family:inherit; }
    .bi-fname { font-weight:600; word-break:break-all; }
    .bi-fsize { color:#64748b; font-size:12px; margin-top:2px; }
    .bi-fbtns { display:flex; justify-content:flex-end; gap:12px; margin-top:16px; }
    .bi-sbtn { height:48px; padding:0 26px; border-radius:8px; font-size:16px; cursor:pointer; font-family:inherit; background:#fff; border:1px solid #cbd5e1; color:#475569; }
    .bi-sbtn.d { border-color:#f87171; color:#dc2626; display:inline-flex; align-items:center; gap:8px; }

    .bi-btn { display:block; width:100%; height:48px; border-radius:8px; font-size:16px; cursor:pointer; font-family:inherit; box-sizing:border-box; text-align:center; line-height:46px; }
    .bi-save { border:0; background:#a62626; color:#fff; font-weight:500; }
    .bi-save:hover { background:#8c1f1f; }
    .bi-prev { margin-top:12px; border:1px solid #cbd5e1; background:#f4f4f5; color:#94a3b8; cursor:not-allowed; }
    .bi-cancel { margin-top:12px; color:#475569; line-height:40px; height:40px; }
    @media (max-width:1024px) { .bi-grid { grid-template-columns:minmax(0,1fr); } .bi { padding:16px; } }
    @media (max-width:640px) { .bi-modes, .bi-row { grid-template-columns:minmax(0,1fr); } .bi h1 { font-size:26px; } }
</style>

<div class="bi">
    <a href="<?= $urlKembali ?>" class="bi-back">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <h1><?= $e($title) ?></h1>

    <form id="biForm" method="post" action="<?= $e($action) ?>" enctype="multipart/form-data" novalidate>
        <?php if ($this->config->item('csrf_protection')): ?>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <?php endif; ?>

        <div class="bi-grid">
            <!-- KIRI -->
            <div>
                <div class="bi-card">
                    <h2>Sumber dan cara pembuatan</h2>
                    <span class="bi-lbl">Sumber invoice</span>
                    <div class="bi-modes">
                        <label class="bi-mode <?= $sumber === 'baru' ? 'on' : '' ?> <?= ($isEdit && $sumber !== 'baru') ? 'lock' : '' ?>">
                            <input type="radio" name="sumber" value="baru" class="bi-sr" <?= $sumber === 'baru' ? 'checked' : '' ?> <?= $isEdit ? 'disabled' : '' ?>>
                            <span class="bi-radio"></span>
                            <span><b>Buat baru</b><small>Isi data invoice dari awal</small></span>
                        </label>
                        <label class="bi-mode <?= $sumber === 'penawaran' ? 'on' : '' ?> <?= ($isEdit && $sumber !== 'penawaran') ? 'lock' : '' ?>">
                            <input type="radio" name="sumber" value="penawaran" class="bi-sr" <?= $sumber === 'penawaran' ? 'checked' : '' ?> <?= $isEdit ? 'disabled' : '' ?>>
                            <span class="bi-radio"></span>
                            <span><b>Dari penawaran</b><small>Data diambil dari penawaran yang sudah disetujui</small></span>
                        </label>
                    </div>

                    <span class="bi-lbl">Cara pembuatan dokumen</span>
                    <div class="bi-cara">
                        <label class="<?= ($isEdit && $cara !== 'template') ? 'lock' : '' ?>"><input type="radio" name="cara" value="template" <?= $cara === 'template' ? 'checked' : '' ?> <?= $isEdit ? 'disabled' : '' ?>> Template baku</label>
                        <label class="<?= ($isEdit && $cara !== 'unggah') ? 'lock' : '' ?>"><input type="radio" name="cara" value="unggah" <?= $cara === 'unggah' ? 'checked' : '' ?> <?= $isEdit ? 'disabled' : '' ?>> Unggah dokumen</label>
                    </div>
                    <?php if ($isEdit): ?><p class="bi-hint" style="margin-top:12px">Sumber dan cara pembuatan tidak bisa diubah setelah invoice disimpan.</p><?php endif; ?>
                </div>

                <div class="bi-card">
                    <h2>Data invoice</h2>
                    <?php if ($formErr): ?><div class="bi-alert"><?= $e($formErr) ?></div><?php endif; ?>

                    <div class="bi-row">
                        <div class="bi-field">
                            <label for="klien_id">Klien</label>
                            <select id="klien_id" name="klien_id" class="bi-select">
                                <option value="">Pilih Klien</option>
                                <?php foreach ($klienList as $k): ?>
                                    <option value="<?= (int) $k->id ?>" <?= (string) $p('klien_id') === (string) $k->id ? 'selected' : '' ?>><?= $e($k->nama_klien) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('klien_id') ?>
                        </div>
                        <div class="bi-field">
                            <label for="project_id">Project</label>
                            <select id="project_id" name="project_id" class="bi-select">
                                <option value="">Pilih Project (opsional)</option>
                                <?php foreach ($projectList as $pr): ?>
                                    <option value="<?= (int) $pr->id ?>" data-klien="<?= (int) $pr->klien_id ?>" <?= (string) $p('project_id') === (string) $pr->id ? 'selected' : '' ?>><?= $e($pr->nama_project) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?= $err('project_id') ?>
                        </div>
                    </div>

                    <?php if ($isEdit && $sumber === 'penawaran'): ?>
                        <div class="bi-field">
                            <label>Penawaran</label>
                            <input type="text" class="bi-input" readonly tabindex="-1" value="<?= $e($inv->no_penawaran . ' - ' . $inv->perihal_penawaran) ?>">
                            <p class="bi-hint">Penawaran asal invoice tidak bisa diganti.</p>
                        </div>
                    <?php elseif (!$isEdit): ?>
                        <div class="bi-field" id="boxPenawaran" <?= $sumber !== 'penawaran' ? 'hidden' : '' ?>>
                            <label for="penawaran_id">Penawaran</label>
                            <select id="penawaran_id" name="penawaran_id" class="bi-select">
                                <option value="">Pilih penawaran</option>
                                <?php foreach ($penawaranList as $x): ?>
                                    <option value="<?= (int) $x->id ?>" <?= (string) $pwPilih === (string) $x->id ? 'selected' : '' ?>><?= $e($x->no_penawaran . ' - ' . $x->perihal . ' - disetujui') ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="bi-hint">Hanya penawaran berstatus Disetujui dari project ini. Isi surat hanya bisa disalin dari template, bukan unggahan.</p>
                            <?= $err('penawaran_id') ?>
                        </div>
                    <?php endif; ?>

                    <!-- Hanya cara unggah -->
                    <div class="bi-row" data-cara="unggah" <?= $cara !== 'unggah' ? 'hidden' : '' ?>>
                        <div class="bi-field">
                            <label for="no_invoice">Nomor invoice</label>
                            <input id="no_invoice" type="text" name="no_invoice" class="bi-input" maxlength="50" placeholder="[Masukkan Nomor]" value="<?= $e($p('no_invoice')) ?>">
                            <p class="bi-hint">Diisi manual sesuai nomor di dokumen.</p>
                            <?= $err('no_invoice') ?>
                        </div>
                        <div class="bi-field">
                            <label for="tanggal_invoice">Tanggal invoice</label>
                            <input id="tanggal_invoice" type="date" name="tanggal_invoice" class="bi-input" value="<?= $e($p('tanggal_invoice', date('Y-m-d'))) ?>">
                            <?= $err('tanggal_invoice') ?>
                        </div>
                    </div>

                    <div class="bi-row">
                        <div class="bi-field">
                            <label for="perihal">Perihal</label>
                            <input id="perihal" type="text" name="perihal" class="bi-input" maxlength="200" placeholder="[Perihal invoice]" value="<?= $e($p('perihal')) ?>">
                            <?= $err('perihal') ?>
                        </div>
                        <div class="bi-field">
                            <label for="jatuh_tempo">Tanggal jatuh tempo</label>
                            <input id="jatuh_tempo" type="date" name="jatuh_tempo" class="bi-input" value="<?= $e($p('jatuh_tempo')) ?>">
                            <?= $err('jatuh_tempo') ?>
                        </div>
                    </div>

                    <!-- Hanya cara template -->
                    <div data-cara="template" <?= $cara !== 'template' ? 'hidden' : '' ?>>
                        <div class="bi-field">
                            <label for="isi_surat">Isi Surat</label>
                            <textarea id="isi_surat" name="isi_surat" class="bi-text" placeholder="[Terisi otomatis dari isi penawaran, bisa diubah sebelum disimpan]"><?= $e($p('isi_surat')) ?></textarea>
                        </div>
                        <div class="bi-checks">
                            <label><input type="checkbox" name="sertakan_kop" value="1" <?= $kop ? 'checked' : '' ?>> Sertakan kop surat</label>
                            <label><input type="checkbox" name="sertakan_ttd" value="1" <?= $ttd ? 'checked' : '' ?>> Sertakan tanda tangan</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN -->
            <div>
                <!-- Cara unggah: kartu file -->
                <div class="bi-card" data-cara="unggah" <?= $cara !== 'unggah' ? 'hidden' : '' ?>>
                    <h2>File Invoice</h2>
                    <input type="file" name="berkas" id="berkas" accept=".pdf,.doc,.docx" hidden>

                    <div class="bi-dz" id="dz" data-nama="<?= $e($fileNama) ?>">
                        <div id="dzKosong">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0-12l-4 4m4-4l4 4"/></svg>
                            Tarik file ke sini atau pilih dari perangkat
                            <div><button type="button" class="bi-pick" id="btnPilih">Pilih file</button></div>
                        </div>
                        <div id="dzIsi" hidden>
                            <div class="bi-fname" id="dzNama"></div>
                            <div class="bi-fsize" id="dzUkuran"></div>
                        </div>
                    </div>
                    <p class="bi-err" id="dzErr" <?= $uploadErr ? '' : 'hidden' ?>><?= $e($uploadErr) ?></p>
                    <p class="bi-hint">PDF, DOC, atau DOCX. Maksimal 5 MB.</p>

                    <div class="bi-fbtns" id="dzAksi" hidden>
                        <button type="button" class="bi-sbtn" id="btnGanti">Ganti</button>
                        <button type="button" class="bi-sbtn d" id="btnHapus">
                            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            Hapus
                        </button>
                    </div>
                </div>

                <!-- Info: template -->
                <div class="bi-card bi-auto" data-cara="template" <?= $cara !== 'template' ? 'hidden' : '' ?>>
                    <h2><?= $isEdit ? 'Info invoice' : 'Dibuat otomatis' ?></h2>
                    <div class="rw"><span>Nomor</span><span><?= $e($nomorTampil) ?></span></div>
                    <div class="rw"><span>Tanggal</span><span><?= $tglTampil ?></span></div>
                    <div class="rw"><span><?= $isEdit ? 'Status' : 'Status awal' ?></span><span class="bi-pill" style="<?= $pillStyle ?>"><?= $e($statusTampil) ?></span></div>
                    <div class="bi-note"><?= $isEdit ? 'Nomor dan tanggal terkunci. Status diubah dari halaman detail invoice.' : 'Pengingat jatuh tempo dikirim dari halaman detail invoice, lewat email atau WhatsApp.' ?></div>
                </div>

                <!-- Info: unggah -->
                <div class="bi-card bi-auto" data-cara="unggah" <?= $cara !== 'unggah' ? 'hidden' : '' ?>>
                    <h2><?= $isEdit ? 'Ringkasan' : 'Dibuat Otomatis' ?></h2>
                    <div class="rw"><span><?= $isEdit ? 'Status' : 'Status awal' ?></span><span class="bi-pill" style="<?= $pillStyle ?>"><?= $e($statusTampil) ?></span></div>
                    <div class="bi-note"><?= $isEdit ? 'Status diubah dari halaman detail invoice.' : 'Pengingat jatuh tempo dikirim dari halaman detail invoice, lewat email atau WhatsApp.' ?></div>
                </div>

                <div class="bi-card">
                    <button type="submit" class="bi-btn bi-save"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Invoice' ?></button>
                    <button type="button" class="bi-btn bi-prev" data-cara="template" <?= $cara !== 'template' ? 'hidden' : '' ?> disabled title="Belum tersedia">Pratinjau PDF</button>
                    <a href="<?= $urlKembali ?>" class="bi-btn bi-cancel">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    var EDIT = <?= $isEdit ? 'true' : 'false' ?>;
    var PW = <?= json_encode($pwData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    var form = document.getElementById('biForm');
    var klien = document.getElementById('klien_id');
    var project = document.getElementById('project_id');
    var pw = document.getElementById('penawaran_id');       // tidak ada saat edit
    var boxPw = document.getElementById('boxPenawaran');    // tidak ada saat edit
    var perihal = document.getElementById('perihal');
    var isi = document.getElementById('isi_surat');
    var autoPerihal = '';

    function val(n) { return form.querySelector('input[name="' + n + '"]:checked').value; }

    function saringProject() {
        var id = klien.value;
        Array.prototype.forEach.call(project.options, function (o) {
            if (!o.value) return;
            var ok = !id || o.getAttribute('data-klien') === id;
            o.hidden = !ok; o.disabled = !ok;
        });
        var t = project.options[project.selectedIndex];
        if (t && t.value && t.disabled) project.value = '';
    }

    function isiDariPenawaran(timpaTeks) {
        var d = PW[pw.value];
        if (!d) { klien.value = ''; project.value = ''; return; }
        klien.value = d.klien_id;
        saringProject();
        project.value = d.project_id || '';
        if (timpaTeks && val('cara') === 'template') {
            perihal.value = d.perihal;
            isi.value = d.isi;
            autoPerihal = d.perihal;
        }
    }

    function terapkan(awal) {
        var s = val('sumber'), c = val('cara');
        var dariPw = s === 'penawaran';
        if (boxPw) boxPw.hidden = !dariPw;
        Array.prototype.forEach.call(form.querySelectorAll('[data-cara]'), function (el) {
            el.hidden = el.getAttribute('data-cara') !== c;
        });
        klien.classList.toggle('bi-lock', dariPw);
        project.classList.toggle('bi-lock', dariPw);
        klien.tabIndex = project.tabIndex = dariPw ? -1 : 0;
        if (!EDIT) {
            if (c === 'unggah' && perihal.value === autoPerihal) perihal.value = '';
            if (dariPw) isiDariPenawaran(!awal || !perihal.value);
        }
        Array.prototype.forEach.call(form.querySelectorAll('.bi-mode'), function (lb) {
            lb.classList.toggle('on', lb.querySelector('input').checked);
        });
    }

    if (!EDIT) {
        Array.prototype.forEach.call(form.querySelectorAll('input[name="sumber"], input[name="cara"]'), function (r) {
            r.addEventListener('change', function () { terapkan(false); });
        });
        if (pw) pw.addEventListener('change', function () { isiDariPenawaran(true); });
    }
    klien.addEventListener('change', saringProject);

    /* ---------- Unggah file ---------- */
    var input = document.getElementById('berkas');
    var dz = document.getElementById('dz');
    var kosong = document.getElementById('dzKosong');
    var isiBox = document.getElementById('dzIsi');
    var aksi = document.getElementById('dzAksi');
    var err = document.getElementById('dzErr');
    var namaLama = dz.getAttribute('data-nama');
    var MAKS = 5 * 1024 * 1024;

    function ukuran(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }
    function pesan(t) { err.textContent = t || ''; err.hidden = !t; }
    function tampil() {
        var f = input.files && input.files[0];
        if (f) {
            kosong.hidden = true; isiBox.hidden = false; aksi.hidden = false;
            document.getElementById('dzNama').textContent = f.name;
            document.getElementById('dzUkuran').textContent = ukuran(f.size);
        } else if (namaLama) {
            kosong.hidden = true; isiBox.hidden = false; aksi.hidden = false;
            document.getElementById('dzNama').textContent = namaLama;
            document.getElementById('dzUkuran').textContent = 'File tersimpan';
        } else {
            kosong.hidden = false; isiBox.hidden = true; aksi.hidden = true;
        }
    }
    function cekFile() {
        var f = input.files && input.files[0];
        if (!f) return tampil();
        if (!/\.(pdf|docx?)$/i.test(f.name)) { pesan('Format file harus PDF, DOC, atau DOCX.'); input.value = ''; return tampil(); }
        if (f.size > MAKS) { pesan('Ukuran file maksimal 5 MB.'); input.value = ''; return tampil(); }
        pesan(''); tampil();
    }

    document.getElementById('btnPilih').addEventListener('click', function () { input.click(); });
    document.getElementById('btnGanti').addEventListener('click', function () { input.click(); });
    document.getElementById('btnHapus').addEventListener('click', function () { input.value = ''; pesan(''); tampil(); });
    input.addEventListener('change', cekFile);

    ['dragenter', 'dragover'].forEach(function (n) {
        dz.addEventListener(n, function (e) { e.preventDefault(); dz.classList.add('over'); });
    });
    ['dragleave', 'drop'].forEach(function (n) {
        dz.addEventListener(n, function (e) { e.preventDefault(); dz.classList.remove('over'); });
    });
    dz.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files.length) { input.files = e.dataTransfer.files; cekFile(); }
    });

    saringProject();
    terapkan(true);
    tampil();
})();
</script>