<?php
/**
 * @var CI_Controller $this
 * @var object $project
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

$statusStyle = [
    'Berjalan' => 'background:#dbeafe;color:#2563eb',
    'Selesai'  => 'background:#d1fae5;color:#059669',
    'Ditunda'  => 'background:#fef3c7;color:#d97706',
];

// ---- DATA DUMMY: ganti dengan data dari database saat modulnya jadi ----
$dokumen = [
    ['nama' => 'Kerja Sistem ERP',        'jenis' => 'MOU',               'sampai' => '2026-11-15'],
    ['nama' => 'Persetujuan Sistem ERP',  'jenis' => 'Kontrak Kerja sama', 'sampai' => '2026-11-15'],
];
$penawaran = [
    ['no' => 'PNW/2026/018', 'tanggal' => '2026-05-01', 'status' => 'Disetujui'],
];
$invoice = [
    ['no' => 'INV/2026/014', 'tempo' => '2026-11-01', 'status' => 'Belum dibayar'],
];
$pill = [
    'Disetujui'     => 'background:#bbf7d0;color:#15803d',
    'Belum dibayar' => 'background:#fed7aa;color:#ea580c',
    'Lunas'         => 'background:#bbf7d0;color:#15803d',
];
?>
<style>
    .pd { padding: 20px 32px; color: #0f172a; }
    .pd a { text-decoration: none; }
    .pd-back { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:18px; margin-bottom:6px; }
    .pd-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .pd-title { display:flex; align-items:center; gap:12px; }
    .pd-title h1 { font-size:32px; font-weight:700; margin:0; }
    .pd-badge { border-radius:999px; padding:3px 12px; font-size:12px; font-weight:600; white-space:nowrap; }
    .pd-btn { display:inline-flex; align-items:center; gap:8px; padding:10px 22px; border-radius:8px; font-size:15px; background:#fff; cursor:pointer; }
    .pd-btn-edit { border:1px solid #cbd5e1; color:#475569; }
    .pd-btn-del  { border:1px solid #f87171; color:#dc2626; }
    .pd-grid { display:grid; grid-template-columns:minmax(0,1fr) 380px; gap:16px; align-items:start; }
    @media (max-width: 1024px) { .pd-grid { grid-template-columns:minmax(0,1fr); } .pd { padding:16px; } }
    .pd-card { background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:16px; }
    .pd-card h2 { font-size:18px; font-weight:600; margin:0 0 16px; }
    .pd-info { display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:16px; }
    @media (max-width: 700px) { .pd-info { grid-template-columns:repeat(2, minmax(0,1fr)); } }
    .pd-lbl { font-size:13px; color:#64748b; margin-bottom:2px; }
    .pd-val { font-size:14px; font-weight:500; }
    .pd-link { color:#1d8fe1; text-decoration:underline !important; }
    .pd-sec { display:flex; align-items:center; justify-content:space-between; margin:0 0 10px; }
    .pd-sec h2 { margin:0; }
    .pd-add { color:#1d8fe1; font-size:15px; font-weight:500; text-decoration:underline !important; }
    .pd-table { width:100%; border:1px solid #cbd5e1; border-radius:8px; border-collapse:separate; border-spacing:0; overflow:hidden; font-size:13px; margin-bottom:28px; }
    .pd-table th { background:#f1f5f9; color:#475569; font-weight:600; text-align:left; padding:14px 20px; border-bottom:1px solid #cbd5e1; }
    .pd-table td { padding:14px 20px; border-bottom:1px solid #e2e8f0; }
    .pd-table tr:last-child td { border-bottom:0; }
    .pd-empty { text-align:center; color:#94a3b8; padding:18px; }
    .pd-pill { display:inline-block; border-radius:999px; padding:3px 12px; font-size:12px; font-weight:600; }
    .pd-select { width:100%; height:42px; border:1px solid #94a3b8; border-radius:8px; padding:0 12px; font-size:15px; background:#fff; }
    .pd-save { width:100%; margin-top:16px; height:42px; border:0; border-radius:8px; background:#0b5f9e; color:#fff; font-size:15px; font-weight:500; cursor:pointer; }
    .pd-save:hover { background:#094d82; }
    .pd-flash { margin-bottom:16px; border:1px solid #a7f3d0; background:#ecfdf5; color:#047857; border-radius:8px; padding:10px 16px; font-size:14px; }
    .pd-side-item { margin-bottom:14px; }
</style>

<div class="pd">
    <a href="<?= site_url('project') ?>" class="pd-back">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="pd-flash"><?= $e($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <div class="pd-head">
        <div class="pd-title">
            <h1><?= $e($project->nama_project) ?></h1>
            <span class="pd-badge" style="<?= $statusStyle[$project->status] ?? 'background:#f1f5f9;color:#475569' ?>"><?= $e($project->status) ?></span>
        </div>
        <div style="display:flex;gap:10px">
            <a href="<?= site_url('project/edit/' . (int) $project->id . '?from=detail') ?>" class="pd-btn pd-btn-edit">
    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
    Edit
</a>
            <form method="post" action="<?= site_url('project/hapus') ?>" onsubmit="return confirm('Hapus project ini secara permanen?');" style="margin:0">
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <?php endif; ?>
                <input type="hidden" name="id" value="<?= (int) $project->id ?>">
                <button type="submit" class="pd-btn pd-btn-del">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="pd-grid">

        <!-- KOLOM KIRI -->
        <div>
            <!-- Informasi klien (info project) -->
            <div class="pd-card">
                <h2>Informasi Klien</h2>
                <div class="pd-info">
                    <div><div class="pd-lbl">Nama project</div><div class="pd-val"><?= $e($project->nama_project) ?></div></div>
                    <div>
                        <div class="pd-lbl">Klien</div>
                        <div class="pd-val">
                            <?php if ($project->nama_klien): ?>
                                <a class="pd-link" href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>"><?= $e($project->nama_klien) ?></a>
                            <?php else: ?>-<?php endif; ?>
                        </div>
                    </div>
                    <div><div class="pd-lbl">Tanggal mulai</div><div class="pd-val"><?= $tgl($project->tanggal_mulai) ?></div></div>
                    <div><div class="pd-lbl">Tanggal selesai</div><div class="pd-val"><?= $tgl($project->tanggal_selesai) ?></div></div>
                </div>
                <div style="margin-top:20px">
                    <div class="pd-lbl">Deskripsi</div>
                    <div class="pd-val" style="font-weight:400;white-space:pre-line"><?= $project->deskripsi ? $e($project->deskripsi) : '-' ?></div>
                </div>
            </div>

            <!-- Dokumen, penawaran, invoice -->
            <div class="pd-card">
                <div class="pd-sec">
                    <h2>Dokumen kerja sama</h2>
                    <a class="pd-add" href="<?= site_url('dokumen/tambah?project_id=' . (int) $project->id) ?>">+ Tambah dokumen</a>
                </div>
                <table class="pd-table">
                    <thead><tr><th>Nama dokumen</th><th>Jenis</th><th>Berlaku sampai</th></tr></thead>
                    <tbody>
                    <?php if (empty($dokumen)): ?>
                        <tr><td colspan="3" class="pd-empty">Belum ada dokumen.</td></tr>
                    <?php else: foreach ($dokumen as $d): ?>
                        <tr>
                            <td style="font-weight:500"><?= $e($d['nama']) ?></td>
                            <td><?= $e($d['jenis']) ?></td>
                            <td><?= $tgl($d['sampai']) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <div class="pd-sec">
                    <h2>Penawaran</h2>
                    <a class="pd-add" href="<?= site_url('penawaran/tambah?project_id=' . (int) $project->id) ?>">+ Tambah penawaran</a>
                </div>
                <table class="pd-table">
                    <thead><tr><th>No. Penawaran</th><th>Tanggal</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($penawaran)): ?>
                        <tr><td colspan="3" class="pd-empty">Belum ada penawaran.</td></tr>
                    <?php else: foreach ($penawaran as $p): ?>
                        <tr>
                            <td><a class="pd-link" href="<?= site_url('penawaran') ?>"><?= $e($p['no']) ?></a></td>
                            <td><?= $tgl($p['tanggal']) ?></td>
                            <td><span class="pd-pill" style="<?= $pill[$p['status']] ?? 'background:#f1f5f9;color:#475569' ?>"><?= $e($p['status']) ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>

                <div class="pd-sec">
                    <h2>Invoice</h2>
                    <a class="pd-add" href="<?= site_url('invoice/tambah?project_id=' . (int) $project->id) ?>">+ Tambah invoice</a>
                </div>
                <table class="pd-table" style="margin-bottom:0">
                    <thead><tr><th>No. Invoice</th><th>Jatuh tempo</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php if (empty($invoice)): ?>
                        <tr><td colspan="3" class="pd-empty">Belum ada invoice.</td></tr>
                    <?php else: foreach ($invoice as $i): ?>
                        <tr>
                            <td><a class="pd-link" href="<?= site_url('invoice') ?>"><?= $e($i['no']) ?></a></td>
                            <td><?= $tgl($i['tempo']) ?></td>
                            <td><span class="pd-pill" style="<?= $pill[$i['status']] ?? 'background:#f1f5f9;color:#475569' ?>"><?= $e($i['status']) ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- KOLOM KANAN -->
        <div>
            <div class="pd-card">
                <h2>Status Project</h2>
                <form method="post" action="<?= site_url('project/status/' . (int) $project->id) ?>" style="margin:0">
                    <?php if ($this->config->item('csrf_protection')): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <?php endif; ?>
                    <label class="pd-lbl" for="status" style="display:block;margin-bottom:6px;font-size:15px">Ubah Status</label>
                    <select id="status" name="status" class="pd-select">
                        <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                            <option value="<?= $s ?>" <?= $project->status === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="pd-save">Simpan status</button>
                </form>
            </div>

            <div class="pd-card">
                <h2>Informasi Klien</h2>
                <div class="pd-side-item"><div class="pd-lbl">Klien</div><div class="pd-val"><?= $e($project->nama_klien ?: '-') ?></div></div>
                <div class="pd-side-item"><div class="pd-lbl">PIC</div><div class="pd-val"><?= $e($project->nama_pic ?: '-') ?></div></div>
                <div class="pd-side-item"><div class="pd-lbl">No. HP</div><div class="pd-val"><?= $e($project->no_hp ?: '-') ?></div></div>
                <div class="pd-side-item"><div class="pd-lbl">Alamat</div><div class="pd-val"><?= $e($project->alamat ?: '-') ?></div></div>
                <?php if ($project->klien_id): ?>
                    <a class="pd-link" style="font-size:16px;font-weight:500" href="<?= site_url('klien/detail/' . (int) $project->klien_id) ?>">Lihat detail klien</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>