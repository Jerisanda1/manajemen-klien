<?php
/**
 * @var CI_Controller $this
 * @var object|null $penawaran
 * @var array $klienList, $projectList
 * @var string $nomorBaru, $from, $action, $title, $mode, $uploadErr
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$isEdit = !empty($penawaran);

$val = function ($f, $default = '') use ($penawaran) {
    return set_value($f, isset($penawaran->$f) ? $penawaran->$f : $default);
};
$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="bp-err">' . htmlspecialchars($m) . '</p>' : '';
};

$mode      = isset($mode) ? $mode : 'template';
$uploadErr = isset($uploadErr) ? $uploadErr : '';
$from      = isset($from) ? $from : 'list';
$urlKembali = $isEdit && $from === 'detail'
    ? site_url('pembayaran/detail/penawaran/' . (int) $penawaran->id)
    : site_url('pembayaran?tab=penawaran');

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($t) use ($bulan) {
    $x = $t ? strtotime($t) : false;
    return $x ? date('j', $x) . ' ' . $bulan[(int) date('n', $x)] . ' ' . date('Y', $x) : '-';
};

$nomor   = $isEdit ? $penawaran->no_penawaran : $nomorBaru;
$tanggal = $isEdit ? $penawaran->tanggal_penawaran : date('Y-m-d');
$status  = $isEdit ? $penawaran->status : 'Draft';

$posted = $this->input->method() === 'post';
$kop = $posted ? (bool) $this->input->post('sertakan_kop') : ($isEdit ? (bool) $penawaran->sertakan_kop : true);
$ttd = $posted ? (bool) $this->input->post('sertakan_ttd') : ($isEdit ? (bool) $penawaran->sertakan_ttd : true);

$picAwal = '';
foreach ($klienList as $k) {
    if ((string) $val('klien_id') === (string) $k->id) $picAwal = $k->nama_pic;
}

// File yang sudah tersimpan (saat edit penawaran unggahan)
$adaFile  = $isEdit && !empty($penawaran->file_path);
$fileNama = $adaFile ? $penawaran->file_nama : '';
$fileUrl  = $adaFile ? site_url('pembayaran/berkas/' . (int) $penawaran->id) : '';

// Saat edit, mode tidak bisa diganti
$kunci = $isEdit;
?>
<style>
    [hidden] { display:none !important; }
    .bp { padding: 20px 32px 40px; color:#0f172a; }
    .bp a { text-decoration:none; }
    .bp-back { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:18px; margin-bottom:6px; }
    .bp h1 { font-size:32px; font-weight:700; margin:0 0 20px; }
    .bp-grid { display:grid; grid-template-columns:minmax(0,1fr) 400px; gap:16px; align-items:start; }
    .bp-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:16px; }
    .bp-card h2 { font-size:22px; font-weight:700; margin:0 0 18px; }
    .bp-sr { position:absolute; opacity:0; pointer-events:none; }

    .bp-modes { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; }
    .bp-mode { display:flex; gap:14px; align-items:flex-start; border:1px solid #0f172a; border-radius:8px; padding:20px; cursor:pointer; position:relative; }
    .bp-mode.on { background:#dbeafe; border-color:#1d8fe1; }
    .bp-mode.lock { opacity:.5; cursor:not-allowed; }
    .bp-radio { width:24px; height:24px; border:1.5px solid #475569; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#fff; }
    .bp-mode.on .bp-radio { border-color:#1d8fe1; }
    .bp-mode.on .bp-radio::after { content:""; width:12px; height:12px; border-radius:50%; background:#1d8fe1; }
    .bp-mode b { display:block; font-size:16px; font-weight:500; margin-bottom:4px; }
    .bp-mode span.d { font-size:12px; color:#475569; line-height:1.4; display:block; }

    .bp-fields { display:flex; flex-direction:column; }
    .f-klien { order:1; }
    .f-perihal { order:2; }
    .f-nomor { order:2; }
    .f-akhir { order:3; }
    .f-isi { order:4; }
    .f-checks { order:5; }
    .m-unggah .f-perihal { order:4; }

    .bp-row { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:16px; }
    .bp-field { margin-bottom:16px; min-width:0; }
    .bp-field label { display:block; font-size:15px; color:#475569; margin-bottom:6px; }
    .bp-input, .bp-select, .bp-text { width:100%; box-sizing:border-box; border:1px solid #94a3b8; border-radius:8px; background:#fff; padding:0 14px; font-size:15px; color:#0f172a; font-family:inherit; }
    .bp-input, .bp-select { height:48px; }
    .bp-text { padding:12px 14px; min-height:120px; resize:vertical; }
    .bp-input::placeholder { color:#94a3b8; }
    .bp-input:focus, .bp-select:focus, .bp-text:focus { outline:none; border-color:#a62626; box-shadow:0 0 0 3px rgba(166,38,38,.15); }
    .bp-input[readonly] { background:#d4d4d8; color:#475569; border-color:#d4d4d8; }
    .bp-hint { font-size:12px; color:#64748b; margin:4px 0 0; }
    .bp-err { font-size:12px; color:#dc2626; margin:4px 0 0; }
    .bp-checks { display:flex; gap:28px; flex-wrap:wrap; font-size:14px; color:#475569; }
    .bp-checks label { display:inline-flex; align-items:center; gap:10px; cursor:pointer; }
    .bp-checks input { width:20px; height:20px; accent-color:#c53030; }

    .bp-auto .rw { display:flex; justify-content:space-between; align-items:center; padding:6px 0; font-size:15px; }
    .bp-auto .rw span:first-child { color:#64748b; }
    .bp-auto .rw span:last-child { font-weight:500; }
    .bp-draft { background:#e4e4e7; color:#52525b; border-radius:999px; padding:2px 18px; font-size:15px; }
    .bp-note { border-top:1px solid #e2e8f0; margin-top:10px; padding-top:10px; font-size:12px; color:#64748b; line-height:1.4; }
    .bp-note.plain { border-top:0; margin-top:18px; padding-top:0; }

    .bp-dz { border:1.5px dashed #64748b; border-radius:10px; padding:26px 16px; text-align:center; font-size:13px; color:#0f172a; }
    .bp-dz.over { background:#dbeafe; border-color:#1d8fe1; }
    .bp-dz svg { display:block; margin:0 auto 6px; }
    .bp-pick { margin-top:10px; padding:4px 14px; border:1px solid #cbd5e1; border-radius:4px; background:#f4f4f5; color:#475569; font-size:12px; cursor:pointer; font-family:inherit; }
    .bp-fname { font-weight:600; word-break:break-all; }
    .bp-fsize { color:#64748b; font-size:12px; margin-top:2px; }
    .bp-fbtns { display:flex; justify-content:flex-end; gap:12px; margin-top:16px; }
    .bp-sbtn { height:48px; padding:0 26px; border-radius:8px; font-size:16px; cursor:pointer; font-family:inherit; background:#fff; border:1px solid #cbd5e1; color:#475569; }
    .bp-sbtn.d { border-color:#f87171; color:#dc2626; display:inline-flex; align-items:center; gap:8px; }

    .bp-btn { display:block; width:100%; height:48px; border-radius:8px; font-size:16px; cursor:pointer; font-family:inherit; box-sizing:border-box; text-align:center; line-height:46px; }
    .bp-save { border:0; background:#a62626; color:#fff; font-weight:500; }
    .bp-save:hover { background:#8c1f1f; }
    .bp-prev { margin-top:12px; border:1px solid #cbd5e1; background:#f4f4f5; color:#475569; }
    .bp-prev:hover { background:#e4e4e7; }
    .bp-cancel { margin-top:12px; color:#475569; font-size:16px; line-height:40px; height:40px; }

    @media (max-width: 1024px) { .bp-grid { grid-template-columns:minmax(0,1fr); } .bp { padding:16px; } }
    @media (max-width: 640px) { .bp-modes, .bp-row { grid-template-columns:minmax(0,1fr); } .bp h1 { font-size:26px; } }
</style>

<div class="bp">
    <a href="<?= $urlKembali ?>" class="bp-back">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>
    <h1><?= $e($title) ?></h1>

    <form id="bpForm" method="post" action="<?= $e($action) ?>" enctype="multipart/form-data" novalidate>
        <?php if ($this->config->item('csrf_protection')): ?>
            <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
        <?php endif; ?>
        <input type="hidden" name="pv_nomor" value="<?= $e($nomor) ?>">
        <input type="hidden" name="pv_tanggal" value="<?= $e($tanggal) ?>">
        <input type="hidden" name="pv_status" value="<?= $e($status) ?>">

        <div class="bp-grid">

            <!-- KIRI -->
            <div>
                <div class="bp-card">
                    <h2>Cara Pembuatan</h2>
                    <div class="bp-modes">
                        <label class="bp-mode <?= $mode === 'template' ? 'on' : '' ?> <?= ($kunci && $mode !== 'template') ? 'lock' : '' ?>">
                            <input type="radio" name="mode" value="template" class="bp-sr" <?= $mode === 'template' ? 'checked' : '' ?> <?= ($kunci && $mode !== 'template') ? 'disabled' : '' ?>>
                            <span class="bp-radio"></span>
                            <span>
                                <b>Template Baku</b>
                                <span class="d">Nomor, tanggal, dan format dibuat otomatis. Admin cukup mengisi perihal dan isi.</span>
                            </span>
                        </label>
                        <label class="bp-mode <?= $mode === 'unggah' ? 'on' : '' ?> <?= ($kunci && $mode !== 'unggah') ? 'lock' : '' ?>">
                            <input type="radio" name="mode" value="unggah" class="bp-sr" <?= $mode === 'unggah' ? 'checked' : '' ?> <?= ($kunci && $mode !== 'unggah') ? 'disabled' : '' ?>>
                            <span class="bp-radio"></span>
                            <span>
                                <b>Unggah Manual</b>
                                <span class="d">Pakai file penawaran yang sudah dibuat sendiri (PDF atau Word).</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="bp-card">
                    <h2>Data penawaran</h2>

                    <div class="bp-fields <?= $mode === 'unggah' ? 'm-unggah' : 'm-template' ?>" id="bpFields">

                        <div class="bp-row f-klien">
                            <div class="bp-field">
                                <label for="klien_id">Klien</label>
                                <select id="klien_id" name="klien_id" class="bp-select">
                                    <option value="">Pilih Klien</option>
                                    <?php foreach ($klienList as $k): ?>
                                        <option value="<?= (int) $k->id ?>" data-pic="<?= $e($k->nama_pic) ?>" <?= (string) $val('klien_id') === (string) $k->id ? 'selected' : '' ?>><?= $e($k->nama_klien) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?= $err('klien_id') ?>
                            </div>
                            <div class="bp-field">
                                <label for="project_id">Project</label>
                                <select id="project_id" name="project_id" class="bp-select">
                                    <option value="">Pilih Project (opsional)</option>
                                    <?php foreach ($projectList as $p): ?>
                                        <option value="<?= (int) $p->id ?>" data-klien="<?= (int) $p->klien_id ?>" <?= (string) $val('project_id') === (string) $p->id ? 'selected' : '' ?>><?= $e($p->nama_project) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="bp-hint">Daftar mengikuti klien yang dipilih.</p>
                                <?= $err('project_id') ?>
                            </div>
                        </div>

                        <!-- Hanya mode unggah -->
                        <div class="bp-row f-nomor" data-only="unggah" <?= $mode !== 'unggah' ? 'hidden' : '' ?>>
                            <div class="bp-field">
                                <label for="no_penawaran">Nomor Penawaran</label>
                                <input id="no_penawaran" type="text" name="no_penawaran" class="bp-input" maxlength="50" value="<?= $e($val('no_penawaran', $nomorBaru)) ?>">
                                <?= $err('no_penawaran') ?>
                            </div>
                            <div class="bp-field">
                                <label for="tanggal_penawaran">Tanggal Penawaran</label>
                                <input id="tanggal_penawaran" type="date" name="tanggal_penawaran" class="bp-input" value="<?= $e($val('tanggal_penawaran', date('Y-m-d'))) ?>">
                                <?= $err('tanggal_penawaran') ?>
                            </div>
                        </div>

                        <div class="bp-field f-perihal">
                            <label for="perihal">Perihal</label>
                            <input id="perihal" type="text" name="perihal" class="bp-input" maxlength="200" placeholder="[Perihal Penawaran]" value="<?= $e($val('perihal')) ?>">
                            <?= $err('perihal') ?>
                        </div>

                        <div class="bp-row f-akhir">
                            <div class="bp-field">
                                <label for="berlaku_sampai">Berlaku Sampai</label>
                                <input id="berlaku_sampai" type="date" name="berlaku_sampai" class="bp-input" value="<?= $e($val('berlaku_sampai')) ?>">
                                <?= $err('berlaku_sampai') ?>
                            </div>
                            <div class="bp-field">
                                <label for="pic">PIC Klien</label>
                                <input id="pic" type="text" class="bp-input" readonly tabindex="-1" value="<?= $e($picAwal) ?>">
                                <p class="bp-hint">Diambil dari data klien</p>
                            </div>
                        </div>

                        <!-- Hanya mode template -->
                        <div class="bp-field f-isi" data-only="template" <?= $mode !== 'template' ? 'hidden' : '' ?>>
                            <label for="isi_surat">Isi Surat</label>
                            <textarea id="isi_surat" name="isi_surat" class="bp-text"><?= $e($val('isi_surat')) ?></textarea>
                        </div>

                        <div class="bp-checks f-checks" data-only="template" <?= $mode !== 'template' ? 'hidden' : '' ?>>
                            <label><input type="checkbox" name="sertakan_kop" value="1" <?= $kop ? 'checked' : '' ?>> Sertakan kop surat</label>
                            <label><input type="checkbox" name="sertakan_ttd" value="1" <?= $ttd ? 'checked' : '' ?>> Sertakan tanda tangan</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KANAN -->
            <div>
                <!-- Mode template -->
                <div class="bp-card bp-auto" data-only="template" <?= $mode !== 'template' ? 'hidden' : '' ?>>
                    <h2><?= $isEdit ? 'Info penawaran' : 'Dibuat otomatis' ?></h2>
                    <div class="rw"><span>Nomor</span><span><?= $e($nomor) ?></span></div>
                    <div class="rw"><span>Tanggal</span><span><?= $tgl($tanggal) ?></span></div>
                    <div class="rw"><span><?= $isEdit ? 'Status' : 'Status awal' ?></span><span class="bp-draft"><?= $e($status) ?></span></div>
                    <div class="bp-note">Nomor dan tanggal terkunci saat disimpan. Status bisa diubah di halaman detail.</div>
                </div>

                <!-- Mode unggah -->
                <div data-only="unggah" <?= $mode !== 'unggah' ? 'hidden' : '' ?>>
                    <div class="bp-card">
                        <h2>File Penawaran</h2>
                        <input type="file" name="berkas" id="berkas" accept=".pdf,.doc,.docx" hidden>

                        <div class="bp-dz" id="dz" data-nama="<?= $e($fileNama) ?>" data-url="<?= $e($fileUrl) ?>">
                            <div id="dzKosong">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0-12l-4 4m4-4l4 4"/></svg>
                                Tarik file ke sini atau pilih dari perangkat
                                <div><button type="button" class="bp-pick" id="btnPilih">Pilih file</button></div>
                            </div>
                            <div id="dzIsi" hidden>
                                <div class="bp-fname" id="dzNama"></div>
                                <div class="bp-fsize" id="dzUkuran"></div>
                            </div>
                        </div>
                        <p class="bp-err" id="dzErr" <?= $uploadErr ? '' : 'hidden' ?>><?= $e($uploadErr) ?></p>
                        <p class="bp-hint">PDF, DOC, atau DOCX. Maksimal 5 MB.</p>

                        <div class="bp-fbtns" id="dzAksi" hidden>
                            <button type="button" class="bp-sbtn" id="btnGanti">Ganti</button>
                            <button type="button" class="bp-sbtn d" id="btnHapus">
                                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </div>
                    </div>

                    <div class="bp-card bp-auto">
                        <h2>Ringkasan</h2>
                        <div class="rw"><span>Asal dokumen</span><span>Unggahan</span></div>
                        <div class="rw"><span><?= $isEdit ? 'Status' : 'Status awal' ?></span><span class="bp-draft"><?= $e($status) ?></span></div>
                        <div class="bp-note plain">Kop surat dan tanda tangan otomatis tidak dipakai, karena dokumen sudah jadi. Status bisa diubah di halaman detail.</div>
                    </div>
                </div>

                <div class="bp-card">
                    <button type="submit" class="bp-btn bp-save"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Penawaran' ?></button>

                    <button type="submit" class="bp-btn bp-prev" data-only="template" <?= $mode !== 'template' ? 'hidden' : '' ?>
                            formaction="<?= site_url('pembayaran/pratinjau') ?>" formtarget="_blank"
                            formenctype="application/x-www-form-urlencoded" formnovalidate>Pratinjau PDF</button>
                    <button type="button" class="bp-btn bp-prev" id="btnPrevFile" data-only="unggah" <?= $mode !== 'unggah' ? 'hidden' : '' ?>>Pratinjau PDF</button>

                    <a href="<?= $urlKembali ?>" class="bp-btn bp-cancel">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
(function () {
    var form = document.getElementById('bpForm');
    var fields = document.getElementById('bpFields');

    /* ---------- Ganti mode ---------- */
    function terapkan(mode) {
        fields.className = 'bp-fields ' + (mode === 'unggah' ? 'm-unggah' : 'm-template');
        Array.prototype.forEach.call(form.querySelectorAll('[data-only]'), function (el) {
            el.hidden = el.getAttribute('data-only') !== mode;
        });
        Array.prototype.forEach.call(form.querySelectorAll('.bp-mode'), function (lb) {
            var r = lb.querySelector('input');
            lb.classList.toggle('on', r.checked);
        });
    }
    Array.prototype.forEach.call(form.querySelectorAll('input[name="mode"]'), function (r) {
        r.addEventListener('change', function () { terapkan(r.value); });
    });

    /* ---------- Klien -> PIC & Project ---------- */
    var klien = document.getElementById('klien_id');
    var project = document.getElementById('project_id');
    var pic = document.getElementById('pic');
    function sinkron() {
        var id = klien.value;
        var opt = klien.options[klien.selectedIndex];
        pic.value = (opt && opt.value) ? (opt.getAttribute('data-pic') || '') : '';
        Array.prototype.forEach.call(project.options, function (o) {
            if (!o.value) return;
            var cocok = !id || o.getAttribute('data-klien') === id;
            o.hidden = !cocok;
            o.disabled = !cocok;
        });
        var t = project.options[project.selectedIndex];
        if (t && t.value && t.disabled) project.value = '';
    }
    klien.addEventListener('change', sinkron);
    sinkron();

    /* ---------- Unggah file ---------- */
    var input = document.getElementById('berkas');
    var dz = document.getElementById('dz');
    var kosong = document.getElementById('dzKosong');
    var isi = document.getElementById('dzIsi');
    var aksi = document.getElementById('dzAksi');
    var err = document.getElementById('dzErr');
    var namaLama = dz.getAttribute('data-nama');
    var urlLama = dz.getAttribute('data-url');
    var MAKS = 5 * 1024 * 1024;

    function ukuran(b) { return b >= 1048576 ? (b / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(b / 1024)) + ' KB'; }
    function pesan(t) { err.textContent = t || ''; err.hidden = !t; }

    function tampil() {
        var f = input.files && input.files[0];
        if (f) {
            kosong.hidden = true; isi.hidden = false; aksi.hidden = false;
            document.getElementById('dzNama').textContent = f.name;
            document.getElementById('dzUkuran').textContent = ukuran(f.size);
        } else if (namaLama) {
            kosong.hidden = true; isi.hidden = false; aksi.hidden = false;
            document.getElementById('dzNama').textContent = namaLama;
            document.getElementById('dzUkuran').textContent = 'File tersimpan';
        } else {
            kosong.hidden = false; isi.hidden = true; aksi.hidden = true;
        }
    }

    function cekFile() {
        var f = input.files && input.files[0];
        if (!f) return tampil();
        var ok = /\.(pdf|docx?)$/i.test(f.name);
        if (!ok) { pesan('Format file harus PDF, DOC, atau DOCX.'); input.value = ''; return tampil(); }
        if (f.size > MAKS) { pesan('Ukuran file maksimal 5 MB.'); input.value = ''; return tampil(); }
        pesan('');
        tampil();
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
        if (e.dataTransfer && e.dataTransfer.files.length) {
            input.files = e.dataTransfer.files;
            cekFile();
        }
    });

    /* ---------- Pratinjau file unggahan ---------- */
    document.getElementById('btnPrevFile').addEventListener('click', function () {
        var f = input.files && input.files[0];
        if (f) {
            if (!/\.pdf$/i.test(f.name)) { alert('Pratinjau hanya tersedia untuk file PDF.'); return; }
            window.open(URL.createObjectURL(f), '_blank');
        } else if (urlLama) {
            window.open(urlLama, '_blank');
        } else {
            alert('Pilih file penawaran terlebih dahulu.');
        }
    });

    tampil();
})();
</script>