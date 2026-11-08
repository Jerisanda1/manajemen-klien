<?php
/**
 * Form Tambah & Edit Project
 * @var CI_Controller $this
 * @var object|null   $project
 * @var array         $klienList
 * @var string        $action
 * @var string        $title
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$isEdit = !empty($project);

$val = function ($f) use ($project) {
    return set_value($f, isset($project->$f) ? $project->$f : '');
};
$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="pf-err">' . htmlspecialchars($m) . '</p>' : '';
};

$from = isset($from) ? $from : 'list';
$urlKembali = site_url($isEdit && $from === 'detail' ? 'project/detail/' . (int) $project->id : 'project');

// Path ilustrasi (relatif terhadap root web). SAMAKAN dengan gambar di form Tambah Klien.
$gambar = 'assets/img/project_form.png';
$adaGambar = file_exists(FCPATH . $gambar);
?>
<style>
    .pf { padding: 40px 24px 56px; color: #0f172a; }
    .pf-wrap { max-width: 860px; margin: 0 auto; }
    .pf a { text-decoration: none; }
    .pf-back { display:inline-flex; align-items:center; gap:6px; color:#64748b; font-size:13px; font-weight:500; margin-bottom:6px; }
    .pf-back:hover { color:#0f172a; }
    .pf-title { font-size:24px; font-weight:700; margin:0 0 16px; }

    .pf-card { display:grid; grid-template-columns:minmax(0,1.2fr) minmax(0,1fr); background:#fff; border-radius:16px; overflow:hidden;
               box-shadow:0 10px 30px rgba(15,23,42,.08), 0 1px 3px rgba(15,23,42,.06); }
    .pf-form { padding:28px 30px 24px; }
    .pf-form h2 { font-size:16px; font-weight:700; margin:0 0 18px; }

    .pf-row { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:14px; }
    .pf-field { margin-bottom:14px; min-width:0; }
    .pf-field label { display:block; font-size:12px; font-weight:500; color:#1e293b; margin-bottom:6px; }
    .pf-req { color:#dc2626; }
    .pf-input, .pf-select, .pf-textarea {
        width:100%; box-sizing:border-box; border:1px solid #e2e8f0; border-radius:8px;
        background:#f8fafc; padding:0 12px; font-size:13px; color:#0f172a; font-family:inherit;
    }
    .pf-input, .pf-select { height:36px; }
    .pf-textarea { padding:10px 12px; min-height:84px; resize:vertical; }
    .pf-input::placeholder, .pf-textarea::placeholder { color:#94a3b8; }
    .pf-input:focus, .pf-select:focus, .pf-textarea:focus { outline:none; border-color:#a62626; background:#fff; box-shadow:0 0 0 3px rgba(166,38,38,.12); }
    .pf-err { color:#dc2626; font-size:11px; margin:4px 0 0; }

    .pf-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:20px; padding-top:18px; border-top:1px solid #e2e8f0; }
    .pf-btn { display:inline-flex; align-items:center; justify-content:center; height:36px; padding:0 18px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; }
    .pf-btn-cancel { border:1px solid #cbd5e1; background:#fff; color:#334155; }
    .pf-btn-cancel:hover { background:#f8fafc; }
    .pf-btn-save { border:0; background:#a62626; color:#fff; box-shadow:0 4px 10px rgba(166,38,38,.3); }
    .pf-btn-save:hover { background:#8c1f1f; }

    .pf-side { background:linear-gradient(135deg,#4f4a49 0%,#3f3a3a 100%); display:flex; align-items:center; justify-content:center; padding:24px; }
    .pf-side img { width:100%; height:auto; max-width:300px; }

    @media (max-width: 800px) {
        .pf { padding:20px 16px 40px; }
        .pf-card { grid-template-columns:minmax(0,1fr); }
        .pf-side { display:none; }
    }
    @media (max-width: 520px) {
        .pf-row { grid-template-columns:minmax(0,1fr); gap:0; }
        .pf-form { padding:20px 16px; }
        .pf-actions { flex-direction:column-reverse; }
        .pf-btn { width:100%; }
    }
</style>

<div class="pf">
    <div class="pf-wrap">
        <a href="<?= $urlKembali ?>" class="pf-back">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        <h1 class="pf-title"><?= $e($title) ?></h1>

        <div class="pf-card">

            <!-- Form -->
            <form method="post" action="<?= $e($action) ?>" class="pf-form" novalidate>
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <?php endif; ?>

                <h2>Data Project</h2>

                <div class="pf-field">
                    <label for="nama_project">Nama project <span class="pf-req">*</span></label>
                    <input id="nama_project" type="text" name="nama_project" class="pf-input"
                           value="<?= $e($val('nama_project')) ?>" placeholder="[Nama project]">
                    <?= $err('nama_project') ?>
                </div>

                <div class="pf-row">
                    <div class="pf-field">
                        <label for="klien_id">Klien <span class="pf-req">*</span></label>
                        <select id="klien_id" name="klien_id" class="pf-select">
                            <option value="">Pilih klien</option>
                            <?php foreach ($klienList as $k): ?>
                                <option value="<?= (int) $k->id ?>" <?= (string) $val('klien_id') === (string) $k->id ? 'selected' : '' ?>><?= $e($k->nama_klien) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('klien_id') ?>
                    </div>

                    <div class="pf-field">
                        <label for="status">Status project <span class="pf-req">*</span></label>
                        <?php $st = $val('status'); ?>
                        <select id="status" name="status" class="pf-select">
                            <option value="">Pilih Status</option>
                            <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                                <option value="<?= $s ?>" <?= $st === $s ? 'selected' : '' ?>><?= $s ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $err('status') ?>
                    </div>
                </div>

                <div class="pf-row">
                    <div class="pf-field">
                        <label for="tanggal_mulai">Tanggal mulai</label>
                        <input id="tanggal_mulai" type="date" name="tanggal_mulai" class="pf-input" value="<?= $e($val('tanggal_mulai')) ?>">
                        <?= $err('tanggal_mulai') ?>
                    </div>
                    <div class="pf-field">
                        <label for="tanggal_selesai">Tanggal selesai</label>
                        <input id="tanggal_selesai" type="date" name="tanggal_selesai" class="pf-input" value="<?= $e($val('tanggal_selesai')) ?>">
                        <?= $err('tanggal_selesai') ?>
                    </div>
                </div>

                <div class="pf-field">
                    <label for="deskripsi">Deskripsi project</label>
                    <textarea id="deskripsi" name="deskripsi" class="pf-textarea" placeholder="[Ringkasan lingkup dan tujuan project]"><?= $e($val('deskripsi')) ?></textarea>
                </div>

                <div class="pf-actions">
                    <a href="<?= $urlKembali ?>" class="pf-btn pf-btn-cancel">Batal</a>
                    <button type="submit" class="pf-btn pf-btn-save"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Project' ?></button>
                </div>
            </form>

            <!-- Panel ilustrasi -->
<div class="pf-side" aria-hidden="true">
    <?php if ($adaGambar): ?>
        <img src="<?= base_url($gambar) ?>" alt="">
    <?php endif; ?>
</div>
        </div>
    </div>
</div>
