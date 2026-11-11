<?php
/**
 * @var CI_Controller $this
 * @var string $tab
 * @var array  $rows, $klienList, $statusList
 * @var int    $total, $page, $perPage, $totalPages
 * @var string $q, $fKlien, $fSumber, $fStatus
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

$isInvoice = ($tab === 'invoice');
$label = $isInvoice
    ? ['judul' => 'Invoice', 'no' => 'No. Invoice', 'tgl' => 'Tanggal Invoice', 'akhir' => 'Jatuh Tempo', 'tombol' => 'Buat Invoice', 'cari' => 'Cari nomor invoice, klien, project....', 'status' => 'Status invoice']
    : ['judul' => 'Penawaran', 'no' => 'No. Penawaran', 'tgl' => 'Tanggal Penawaran', 'akhir' => 'Berlaku Sampai', 'tombol' => 'Buat Penawaran', 'cari' => 'Cari nomor penawaran, klien, project....', 'status' => 'Status penawaran'];

$badge = [
    'Draft'         => 'background:#e2e8f0;color:#475569',
    'Dikirim'       => 'background:#dbeafe;color:#1d8fe1',
    'Disetujui'     => 'background:#bbf7d0;color:#15803d',
    'Ditolak'       => 'background:#fecaca;color:#dc2626',
    'Belum dibayar' => 'background:#fed7aa;color:#ea580c',
    'Dibayar'       => 'background:#bbf7d0;color:#15803d',
];

$urlHalaman = function ($p) use ($tab, $q, $fKlien, $fSumber, $fStatus) {
    return site_url('pembayaran') . '?' . http_build_query([
        'tab' => $tab, 'q' => $q, 'klien' => $fKlien, 'sumber' => $fSumber, 'status' => $fStatus, 'page' => $p,
    ]);
};

$awal  = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$akhir = $total > 0 ? min($page * $perPage, $total) : 0;
?>
<style>
    .pb { padding: 20px 32px 40px; color: #0f172a; }
    .pb a { text-decoration: none; }
    .pb-head { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:12px; }
    .pb-head h1 { font-size:32px; font-weight:700; margin:0; }
    .pb-head p { margin:4px 0 0; font-size:16px; color:#64748b; }
    .pb-add { display:inline-flex; align-items:center; gap:8px; background:#a62626; color:#fff; padding:12px 22px; border-radius:12px; font-size:16px; font-weight:500; box-shadow:0 1px 2px rgba(0,0,0,.1); align-self:flex-start; }
    .pb-add:hover { background:#8c1f1f; }

    .pb-tabs { display:flex; gap:28px; margin-bottom:14px; }
    .pb-tab { font-size:18px; color:#0f172a; padding-bottom:4px; border-bottom:2px solid transparent; }
    .pb-tab.on { color:#1d8fe1; border-bottom-color:#1d8fe1; }

    .pb-card { background:#fff; border:1px solid #cbd5e1; border-radius:16px; padding:20px; }
    .pb-flash { margin-bottom:14px; border:1px solid #a7f3d0; background:#ecfdf5; color:#047857; border-radius:8px; padding:10px 16px; font-size:14px; }

    .pb-filter { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:20px; }
    .pb-search { position:relative; flex:1 1 260px; min-width:0; }
    .pb-search svg { position:absolute; left:14px; top:50%; transform:translateY(-50%); width:20px; height:20px; color:#64748b; pointer-events:none; }
    .pb-input, .pb-select { height:48px; box-sizing:border-box; border:1px solid #cbd5e1; border-radius:8px; background:#fff; font-size:14px; color:#0f172a; font-family:inherit; }
    .pb-input { width:100%; padding:0 16px 0 44px; background:#f8fafc; }
    .pb-select { padding:0 12px; flex:0 1 160px; min-width:130px; }
    .pb-input:focus, .pb-select:focus { outline:none; border-color:#a62626; box-shadow:0 0 0 3px rgba(166,38,38,.12); }

    .pb-box { border:1px solid #cbd5e1; border-radius:8px; overflow:hidden; }
    .pb-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
    .pb-table { width:100%; min-width:900px; border-collapse:collapse; font-size:14px; text-align:left; }
    .pb-table th { background:#f1f5f9; color:#475569; font-weight:600; padding:16px 14px; border-bottom:1px solid #cbd5e1; }
    .pb-table td { padding:16px 14px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
    .pb-table th:first-child, .pb-table td:first-child { padding-left:24px; }
    .pb-table tbody tr:hover { background:#f8fafc; }
    .pb-no { color:#1d8fe1; font-weight:600; font-size:16px; text-decoration:underline !important; white-space:nowrap; }
    .pb-klien { font-weight:700; text-transform:uppercase; }
    .pb-proj { font-weight:600; }
    .pb-nowrap { white-space:nowrap; color:#475569; }
    .pb-pill { display:inline-block; border-radius:999px; padding:4px 14px; font-size:12px; font-weight:600; white-space:nowrap; }
    .pb-empty { text-align:center; color:#64748b; padding:40px 16px !important; }

    .pb-acts { display:flex; align-items:center; gap:10px; }
    .pb-ico { width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center; border-radius:6px; background:#fff; border:1px solid #cbd5e1; color:#475569; cursor:pointer; padding:0; }
    .pb-ico:hover { background:#f1f5f9; }
    .pb-ico.del { border-color:#f87171; color:#dc2626; }
    .pb-ico.del:hover { background:#fef2f2; }

    .pb-foot { display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; padding:18px 24px; background:#f1f5f9; border-top:1px solid #cbd5e1; font-size:14px; color:#475569; }
    .pb-pager { display:flex; align-items:center; gap:6px; }
    .pb-pg { min-width:32px; height:32px; padding:0 8px; box-sizing:border-box; display:inline-flex; align-items:center; justify-content:center; border:1px solid #cbd5e1; border-radius:6px; background:#fff; color:#475569; font-size:14px; }
    .pb-pg:hover { background:#f8fafc; }
    .pb-pg.on { border-color:#fca5a5; background:#fef2f2; color:#b91c1c; font-weight:600; }
    .pb-pg.off { color:#cbd5e1; border-color:#e2e8f0; cursor:not-allowed; }

    .pb-note { display:inline-flex; align-items:center; gap:8px; margin-top:16px; padding:6px 12px; border:1px solid #cbd5e1; border-radius:4px; background:#f8fafc; font-size:11px; color:#475569; }

    .pb-modal { position:fixed; inset:0; z-index:50; display:none; align-items:center; justify-content:center; padding:16px; background:rgba(15,23,42,.5); }
    .pb-modal.open { display:flex; }
    .pb-dialog { width:100%; max-width:420px; background:#fff; border-radius:16px; padding:24px; box-shadow:0 20px 40px rgba(0,0,0,.2); }
    .pb-dialog h3 { margin:0 0 8px; font-size:18px; }
    .pb-dialog p { margin:0; font-size:14px; line-height:1.5; color:#64748b; }
    .pb-dialog-act { display:flex; justify-content:flex-end; gap:8px; margin-top:20px; }
    .pb-btn { height:38px; padding:0 16px; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; font-family:inherit; }
    .pb-btn-c { border:1px solid #cbd5e1; background:#fff; color:#334155; }
    .pb-btn-d { border:0; background:#dc2626; color:#fff; }
    .pb-btn-d:hover { background:#b91c1c; }

    @media (max-width: 800px) {
        .pb { padding:16px; }
        .pb-head h1 { font-size:26px; }
        .pb-select { flex:1 1 140px; }
        .pb-foot { padding:14px 16px; }
    }
</style>

<div class="pb">

    <div class="pb-head">
        <div>
            <h1>Pembayaran</h1>
            <p>Kelola Penawaran dan Invoice</p>
        </div>
        <a href="<?= site_url('pembayaran/tambah/' . $tab) ?>" class="pb-add">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            <?= $e($label['tombol']) ?>
        </a>
    </div>

    <div class="pb-tabs">
        <a href="<?= site_url('pembayaran') ?>?tab=penawaran" class="pb-tab <?= !$isInvoice ? 'on' : '' ?>">Penawaran</a>
        <a href="<?= site_url('pembayaran') ?>?tab=invoice" class="pb-tab <?= $isInvoice ? 'on' : '' ?>">Invoice</a>
    </div>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="pb-flash"><?= $e($this->session->flashdata('success')) ?></div>
    <?php endif; ?>

    <section class="pb-card">

        <!-- Filter -->
        <form method="get" action="<?= site_url('pembayaran') ?>" class="pb-filter">
            <input type="hidden" name="tab" value="<?= $e($tab) ?>">

            <div class="pb-search">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/></svg>
                <input type="text" name="q" value="<?= $e($q) ?>" placeholder="<?= $e($label['cari']) ?>" class="pb-input">
            </div>

            <select name="klien" class="pb-select" onchange="this.form.submit()">
                <option value="">Semua Klien</option>
                <?php foreach ($klienList as $k): ?>
                    <option value="<?= (int) $k->id ?>" <?= (string) $fKlien === (string) $k->id ? 'selected' : '' ?>><?= $e($k->nama_klien) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="sumber" class="pb-select" onchange="this.form.submit()">
                <option value="">Semua sumber</option>
                <option value="Project" <?= $fSumber === 'Project' ? 'selected' : '' ?>>Dari project</option>
                <option value="Mandiri" <?= $fSumber === 'Mandiri' ? 'selected' : '' ?>>Mandiri</option>
            </select>

            <select name="status" class="pb-select" onchange="this.form.submit()">
                <option value=""><?= $e($label['status']) ?></option>
                <?php foreach ($statusList as $s): ?>
                    <option value="<?= $e($s) ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= $e($s) ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Tabel -->
        <div class="pb-box">
            <div class="pb-scroll">
                <table class="pb-table">
                    <thead>
                        <tr>
                            <th><?= $e($label['no']) ?></th>
                            <th>Klien</th>
                            <th>Project</th>
                            <th><?= $e($label['tgl']) ?></th>
                            <th><?= $e($label['akhir']) ?></th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="7" class="pb-empty">Belum ada data <?= $isInvoice ? 'invoice' : 'penawaran' ?>.</td></tr>
                    <?php else: foreach ($rows as $r): ?>
                        <tr>
                            <td><a href="<?= site_url('pembayaran/detail/' . $tab . '/' . (int) $r->id) ?>" class="pb-no"><?= $e($r->nomor) ?></a></td>
                            <td class="pb-klien"><?= $e($r->nama_klien ?: '-') ?></td>
                            <td class="pb-proj"><?= $e($r->nama_project ?: '-') ?></td>
                            <td class="pb-nowrap"><?= $tgl($r->tanggal) ?></td>
                            <td class="pb-nowrap"><?= $tgl($r->akhir) ?></td>
                            <td><span class="pb-pill" style="<?= $badge[$r->status] ?? 'background:#f1f5f9;color:#475569' ?>"><?= $e($r->status) ?></span></td>
                            <td>
                                <div class="pb-acts">
                                   <a href="<?= site_url('pembayaran/unduh/' . $tab . '/' . (int) $r->id) ?>" class="pb-ico" aria-label="Unduh">
                                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
                                    </a>
                                    <a href="<?= site_url('pembayaran/edit/' . $tab . '/' . (int) $r->id) ?>" class="pb-ico" aria-label="Edit">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <button type="button" class="pb-ico del" aria-label="Hapus"
                                            data-id="<?= (int) $r->id ?>" data-nama="<?= $e($r->nomor) ?>"
                                            onclick="bukaHapus(this.dataset.id, this.dataset.nama)">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="pb-foot">
                <span>Menampilkan <?= $awal ?>-<?= $akhir ?> dari <?= (int) $total ?> data</span>
                <div class="pb-pager">
                    <?php if ($page > 1): ?>
                        <a class="pb-pg" href="<?= $urlHalaman($page - 1) ?>" aria-label="Sebelumnya">&lsaquo;</a>
                    <?php else: ?>
                        <span class="pb-pg off">&lsaquo;</span>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a class="pb-pg <?= $i === $page ? 'on' : '' ?>" href="<?= $urlHalaman($i) ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a class="pb-pg" href="<?= $urlHalaman($page + 1) ?>" aria-label="Berikutnya">&rsaquo;</a>
                    <?php else: ?>
                        <span class="pb-pg off">&rsaquo;</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="pb-note">
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            Reminder otomatis dikirim ke klien saat mendekati jatuh tempo tanggal pembayaran.
        </div>
    </section>
</div>

<!-- Modal hapus -->
<div id="pbModal" class="pb-modal" role="dialog" aria-modal="true" onclick="if(event.target===this)tutupHapus()">
    <div class="pb-dialog">
        <h3>Hapus <?= $isInvoice ? 'invoice' : 'penawaran' ?>?</h3>
        <p><strong id="pbNama" style="color:#0f172a"></strong> akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.</p>
        <form method="post" action="<?= site_url('pembayaran/hapus') ?>" class="pb-dialog-act">
            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>
            <input type="hidden" name="tab" value="<?= $e($tab) ?>">
            <input type="hidden" name="id" id="pbId">
            <button type="button" class="pb-btn pb-btn-c" onclick="tutupHapus()">Batal</button>
            <button type="submit" class="pb-btn pb-btn-d">Hapus</button>
        </form>
    </div>
</div>

<script>
    function bukaHapus(id, nama) {
        document.getElementById('pbId').value = id;
        document.getElementById('pbNama').textContent = nama;
        document.getElementById('pbModal').classList.add('open');
    }
    function tutupHapus() {
        document.getElementById('pbModal').classList.remove('open');
    }
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') tutupHapus();
    });
</script>