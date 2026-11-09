<?php
/**
 * @var CI_Controller $this
 * @var object $penawaran
 * @var array  $riwayat, $statusList
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};
$badge = [
    'Draft'     => 'background:#e2e8f0;color:#475569',
    'Dikirim'   => 'background:#dbeafe;color:#1d8fe1',
    'Disetujui' => 'background:#bbf7d0;color:#15803d',
    'Ditolak'   => 'background:#fecaca;color:#dc2626',
];
$id = (int) $penawaran->id;

// Penawaran lama yang belum punya riwayat: tampilkan status saat ini
if (empty($riwayat)) {
    $r = new stdClass();
    $r->status = $penawaran->status;
    $r->created_at = isset($penawaran->created_at) ? $penawaran->created_at : $penawaran->tanggal_penawaran;
    $riwayat = [$r];
}
?>
<style>
    .pn { padding: 20px 32px 40px; color:#0f172a; }
    .pn a { text-decoration:none; }
    .pn-back { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:18px; margin-bottom:6px; }
    .pn-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .pn-title { display:flex; align-items:center; gap:14px; flex-wrap:wrap; }
    .pn-title h1 { font-size:32px; font-weight:700; margin:0; }
    .pn-pill { border-radius:999px; padding:4px 14px; font-size:12px; font-weight:600; }
    .pn-sub { margin:6px 0 0; font-size:14px; color:#64748b; }
    .pn-btns { display:flex; gap:10px; flex-wrap:wrap; }
    .pn-btn { display:inline-flex; align-items:center; gap:8px; height:46px; padding:0 20px; border-radius:8px; font-size:15px; background:#fff; cursor:pointer; font-family:inherit; box-sizing:border-box; }
    .pn-btn.dark { background:#524d4c; color:#fff; border:0; }
    .pn-btn.dark:hover { background:#3f3a3a; }
    .pn-btn.n { border:1px solid #cbd5e1; color:#475569; }
    .pn-btn.d { border:1px solid #f87171; color:#dc2626; }

    .pn-grid { display:grid; grid-template-columns:minmax(0,1fr) 400px; gap:16px; align-items:start; }
    .pn-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:16px; }
    .pn-card h2 { font-size:20px; font-weight:700; margin:0 0 18px; }
    .pn-info { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:22px 20px; }
    .pn-lbl { font-size:14px; color:#64748b; margin-bottom:2px; }
    .pn-val { font-size:14px; font-weight:500; word-break:break-word; }
    .pn-link { color:#9333ea; text-decoration:underline !important; }

    .pn-prev { height:330px; background:#f4f4f5; border:1px solid #cbd5e1; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:15px; }

    .pn-select { width:100%; height:48px; border:1px solid #94a3b8; border-radius:8px; padding:0 12px; font-size:15px; background:#fff; font-family:inherit; box-sizing:border-box; }
    .pn-save { width:100%; height:48px; margin-top:14px; border:0; border-radius:8px; background:#a62626; color:#fff; font-size:16px; font-weight:500; cursor:pointer; font-family:inherit; }
    .pn-save:hover { background:#8c1f1f; }
    .pn-hr { border:0; border-top:1px solid #e2e8f0; margin:18px 0 14px; }
    .pn-rw { display:flex; justify-content:space-between; font-size:15px; padding:5px 0; }
    .pn-rw span:last-child { color:#64748b; }

    .pn-inv-btn { display:block; width:100%; height:48px; line-height:46px; text-align:center; border:1px solid #cbd5e1; border-radius:8px; background:#f4f4f5; color:#475569; font-size:15px; box-sizing:border-box; }
    .pn-inv-btn.off { cursor:not-allowed; opacity:.8; }
    .pn-note { margin:14px 0 0; font-size:14px; color:#64748b; }
    .pn-flash { margin-bottom:16px; border:1px solid #a7f3d0; background:#ecfdf5; color:#047857; border-radius:8px; padding:10px 16px; font-size:14px; }

    @media (max-width: 1024px) { .pn-grid { grid-template-columns:minmax(0,1fr); } .pn { padding:16px; } }
    @media (max-width: 640px) { .pn-info { grid-template-columns:repeat(2, minmax(0,1fr)); } .pn-title h1 { font-size:26px; } }
</style>

<div class="pn">
    <a href="<?= site_url('pembayaran?tab=penawaran') ?>" class="pn-back">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="pn-flash"><?= $e($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <div class="pn-head">
        <div>
            <div class="pn-title">
                <h1><?= $e($penawaran->no_penawaran) ?></h1>
                <span class="pn-pill" style="<?= $badge[$penawaran->status] ?? 'background:#f1f5f9;color:#475569' ?>"><?= $e($penawaran->status) ?></span>
            </div>
            <?php if (!empty($penawaran->perihal)): ?>
                <p class="pn-sub"><?= $e($penawaran->perihal) ?></p>
            <?php endif; ?>
        </div>

        <div class="pn-btns">
            <a href="<?= site_url('pembayaran/unduh/penawaran/' . $id . '?print=1') ?>" target="_blank" class="pn-btn dark">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4"/></svg>
                Unduh PDF
            </a>
            <a href="<?= site_url('pembayaran/edit/penawaran/' . $id . '?from=detail') ?>" class="pn-btn n">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit
            </a>
            <form method="post" action="<?= site_url('pembayaran/hapus') ?>" onsubmit="return confirm('Hapus penawaran ini secara permanen?');" style="margin:0">
                <?php if ($this->config->item('csrf_protection')): ?>
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                <?php endif; ?>
                <input type="hidden" name="tab" value="penawaran">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button type="submit" class="pn-btn d">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="pn-grid">

        <!-- KIRI -->
        <div>
            <div class="pn-card">
                <h2>Informasi penawaran</h2>
                <div class="pn-info">
                    <div>
                        <div class="pn-lbl">Klien</div>
                        <div class="pn-val" style="text-transform:uppercase"><?= $e($penawaran->nama_klien ?: '-') ?></div>
                    </div>
                    <div>
                        <div class="pn-lbl">Project</div>
                        <div class="pn-val">
                            <?php if ($penawaran->nama_project): ?>
                                <a class="pn-link" href="<?= site_url('project/detail/' . (int) $penawaran->project_id) ?>"><?= $e($penawaran->nama_project) ?></a>
                            <?php else: ?>Mandiri<?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <div class="pn-lbl">Perihal</div>
                        <div class="pn-val"><?= $e($penawaran->perihal ?: '-') ?></div>
                    </div>
                    <div>
                        <div class="pn-lbl">PIC klien</div>
                        <div class="pn-val"><?= $e($penawaran->nama_pic ?: '-') ?></div>
                    </div>
                    <div>
                        <div class="pn-lbl">Tanggal Penawaran</div>
                        <div class="pn-val"><?= $tgl($penawaran->tanggal_penawaran) ?></div>
                    </div>
                    <div>
                        <div class="pn-lbl">Berlaku Sampai</div>
                        <div class="pn-val"><?= $tgl($penawaran->berlaku_sampai) ?></div>
                    </div>
                    <div>
                        <div class="pn-lbl">Dibuat Lewat</div>
                        <div class="pn-val"><?= $e($penawaran->dibuat_lewat ?: '-') ?></div>
                    </div>
                </div>
            </div>

            <div class="pn-card">
                <h2>Pratinjau dokumen</h2>
                <div class="pn-prev">[Pratinjau dokumen]</div>
            </div>
        </div>

        <!-- KANAN -->
        <div>
            <div class="pn-card">
                <h2>Status Penawaran</h2>
                <form method="post" action="<?= site_url('pembayaran/status/penawaran/' . $id) ?>" style="margin:0">
                    <?php if ($this->config->item('csrf_protection')): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <?php endif; ?>
                    <label class="pn-lbl" for="status" style="display:block;margin-bottom:8px;font-size:15px">Ubah Status</label>
                    <select id="status" name="status" class="pn-select">
                        <?php foreach ($statusList as $s): ?>
                            <option value="<?= $e($s) ?>" <?= $penawaran->status === $s ? 'selected' : '' ?>><?= $e($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="pn-save">Simpan Status</button>
                </form>

                <hr class="pn-hr">
                <div class="pn-lbl" style="font-size:15px;margin-bottom:6px">Riwayat</div>
                <?php foreach ($riwayat as $r): ?>
                    <div class="pn-rw">
                        <span><?= $e($r->status) ?></span>
                        <span><?= $tgl($r->created_at) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pn-card">
                <h2 style="margin-bottom:4px">Invoice terkait</h2>
                <p class="pn-lbl" style="font-size:15px;margin:0 0 12px">Belum ada invoice dari penawaran ini</p>

                <?php if ($penawaran->status === 'Disetujui'): ?>
                    <a href="<?= site_url('pembayaran/tambah/invoice?penawaran_id=' . $id) ?>" class="pn-inv-btn">Buat Invoice dari penawaran</a>
                <?php else: ?>
                    <span class="pn-inv-btn off">Buat Invoice dari penawaran</span>
                <?php endif; ?>

                <p class="pn-note">Aktif setelah status penawaran disetujui.</p>
            </div>
        </div>
    </div>
</div>