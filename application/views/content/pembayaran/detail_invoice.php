<?php
/**
 * @var CI_Controller $this
 * @var object $inv
 * @var array  $pengiriman  (saluran => waktu terakhir)
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };
$bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tgl = function ($t) use ($bulan) {
    $x = $t ? strtotime((string) $t) : false;
    return $x ? date('j', $x) . ' ' . $bulan[(int) date('n', $x)] . ' ' . date('Y', $x) : '-';
};
$id = (int) $inv->id;
$dibayar = ($inv->status === 'Dibayar');
$pillStatus = $dibayar ? 'background:#bbf7d0;color:#15803d' : 'background:#fde68a;color:#d97706';

// Hitung mundur jatuh tempo (hanya jika belum dibayar)
$badgeTempo = null;
if (!$dibayar && $inv->jatuh_tempo) {
    $hari = (int) round((strtotime($inv->jatuh_tempo) - strtotime(date('Y-m-d'))) / 86400);
    if ($hari > 0)      $teks = 'Jatuh tempo ' . $hari . ' hari lagi';
    elseif ($hari === 0) $teks = 'Jatuh tempo hari ini';
    else                 $teks = 'Terlambat ' . abs($hari) . ' hari';
    $badgeTempo = [$teks, $hari <= 7 ? 'background:#fecaca;color:#dc2626' : 'background:#e2e8f0;color:#475569'];
}

$adaEmail = filter_var($inv->email_pic, FILTER_VALIDATE_EMAIL);
$adaHp    = preg_replace('/\D/', '', (string) $inv->hp_pic) !== '';
$csrf = $this->config->item('csrf_protection')
    ? '<input type="hidden" name="' . $this->security->get_csrf_token_name() . '" value="' . $this->security->get_csrf_hash() . '">'
    : '';
?>
<style>
    .iv { padding:20px 32px 40px; color:#0f172a; }
    .iv a { text-decoration:none; }
    .iv-back { display:inline-flex; align-items:center; gap:8px; color:#64748b; font-size:18px; margin-bottom:6px; }
    .iv-head { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-bottom:20px; }
    .iv-title { display:flex; align-items:center; gap:12px; flex-wrap:wrap; }
    .iv-title h1 { font-size:32px; font-weight:700; margin:0; }
    .iv-pill { border-radius:999px; padding:4px 14px; font-size:12px; font-weight:600; white-space:nowrap; }
    .iv-sub { margin:6px 0 0; font-size:14px; color:#64748b; }
    .iv-btns { display:flex; gap:10px; flex-wrap:wrap; }
    .iv-btn { display:inline-flex; align-items:center; gap:8px; height:46px; padding:0 20px; border-radius:8px; font-size:15px; background:#fff; cursor:pointer; font-family:inherit; box-sizing:border-box; }
    .iv-btn.dark { background:#524d4c; color:#fff; border:0; }
    .iv-btn.dark:hover { background:#3f3a3a; }
    .iv-btn.n { border:1px solid #cbd5e1; color:#475569; }
    .iv-btn.d { border:1px solid #f87171; color:#dc2626; }

    .iv-grid { display:grid; grid-template-columns:minmax(0,1fr) 400px; gap:16px; align-items:start; }
    .iv-card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:24px; margin-bottom:16px; }
    .iv-card h2 { font-size:20px; font-weight:700; margin:0 0 18px; }
    .iv-info { display:grid; grid-template-columns:repeat(3, minmax(0,1fr)); gap:22px 20px; }
    .iv-lbl { font-size:14px; color:#64748b; margin-bottom:2px; }
    .iv-val { font-size:15px; font-weight:500; word-break:break-word; }
    .iv-link { color:#1d8fe1; text-decoration:underline !important; }
    .iv-dot { color:#64748b; font-size:13px; font-weight:400; margin-left:6px; }

    .iv-prev { height:330px; background:#f4f4f5; border:1px solid #cbd5e1; border-radius:8px; display:flex; align-items:center; justify-content:center; color:#64748b; font-size:15px; }

    .iv-rw { display:flex; justify-content:space-between; align-items:center; gap:12px; font-size:15px; padding:6px 0; }
    .iv-rw span:first-child { color:#64748b; }
    .iv-pay { display:block; width:100%; height:48px; margin-top:14px; border:0; border-radius:8px; background:#15803d; color:#fff; font-size:17px; font-weight:500; cursor:pointer; font-family:inherit; }
    .iv-pay:hover { background:#166534; }
    .iv-note { margin:8px 0 0; font-size:13px; color:#64748b; line-height:1.5; }
    .iv-undo { margin-top:10px; background:none; border:0; padding:0; color:#64748b; font-size:13px; text-decoration:underline; cursor:pointer; font-family:inherit; }

    .iv-pic { font-size:15px; font-weight:500; line-height:1.6; margin-bottom:16px; }
    .iv-send { display:block; width:100%; height:48px; margin-bottom:12px; border:1px solid #cbd5e1; border-radius:8px; background:#fff; color:#475569; font-size:15px; cursor:pointer; font-family:inherit; }
    .iv-send:hover:not(:disabled) { background:#f8fafc; }
    .iv-send:disabled { opacity:.5; cursor:not-allowed; }
    .iv-log { margin-top:18px; }
    .iv-flash { margin-bottom:16px; border-radius:8px; padding:10px 16px; font-size:14px; }
    .iv-flash.ok { border:1px solid #a7f3d0; background:#ecfdf5; color:#047857; }
    .iv-flash.er { border:1px solid #fecaca; background:#fef2f2; color:#b91c1c; }

    @media (max-width:1024px) { .iv-grid { grid-template-columns:minmax(0,1fr); } .iv { padding:16px; } }
    @media (max-width:640px) { .iv-info { grid-template-columns:repeat(2, minmax(0,1fr)); } .iv-title h1 { font-size:26px; } }
</style>

<div class="iv">
    <a href="<?= site_url('pembayaran?tab=invoice') ?>" class="iv-back">
        <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Kembali
    </a>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="iv-flash ok"><?= $e($this->session->flashdata('success')) ?></div>
    <?php endif; ?>
    <?php if ($this->session->flashdata('error')): ?>
        <div class="iv-flash er"><?= $e($this->session->flashdata('error')) ?></div>
    <?php endif; ?>

    <div class="iv-head">
        <div>
            <div class="iv-title">
                <h1><?= $e($inv->no_invoice) ?></h1>
                <span class="iv-pill" style="<?= $pillStatus ?>"><?= $e($inv->status) ?></span>
                <?php if ($badgeTempo): ?>
                    <span class="iv-pill" style="<?= $badgeTempo[1] ?>"><?= $e($badgeTempo[0]) ?></span>
                <?php endif; ?>
            </div>
            <?php if (!empty($inv->perihal)): ?><p class="iv-sub"><?= $e($inv->perihal) ?></p><?php endif; ?>
        </div>

        <div class="iv-btns">
            <a href="<?= site_url('pembayaran/unduh/invoice/' . $id) ?>" class="iv-btn dark">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 4v12m0 0l-4-4m4 4l4-4"/></svg>
                Unduh PDF
            </a>
            <a href="<?= site_url('pembayaran/edit/invoice/' . $id . '?from=detail') ?>" class="iv-btn n">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit
            </a>
            <form method="post" action="<?= site_url('pembayaran/hapus') ?>" onsubmit="return confirm('Hapus invoice ini secara permanen?');" style="margin:0">
                <?= $csrf ?>
                <input type="hidden" name="tab" value="invoice">
                <input type="hidden" name="id" value="<?= $id ?>">
                <button type="submit" class="iv-btn d">
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="iv-grid">

        <!-- KIRI -->
        <div>
            <div class="iv-card">
                <h2>Informasi Invoice</h2>
                <div class="iv-info">
                    <div><div class="iv-lbl">Klien</div><div class="iv-val"><?= $e($inv->nama_klien ?: '-') ?></div></div>
                    <div>
                        <div class="iv-lbl">Project</div>
                        <div class="iv-val">
                            <?php if ($inv->nama_project): ?>
                                <a class="iv-link" href="<?= site_url('project/detail/' . (int) $inv->project_id) ?>"><?= $e($inv->nama_project) ?></a>
                            <?php else: ?>Mandiri<?php endif; ?>
                        </div>
                    </div>
                    <div><div class="iv-lbl">Perihal</div><div class="iv-val"><?= $e($inv->perihal ?: '-') ?></div></div>

                    <div>
                        <div class="iv-lbl">Penawaran</div>
                        <div class="iv-val">
                            <?php if ($inv->penawaran_id): ?>
                                <a class="iv-link" href="<?= site_url('pembayaran/detail/penawaran/' . (int) $inv->penawaran_id) ?>"><?= $e($inv->no_penawaran) ?></a>
                                <span class="iv-dot">&bull; <?= $e($inv->status_penawaran) ?></span>
                            <?php else: ?>-<?php endif; ?>
                        </div>
                    </div>
                    <div><div class="iv-lbl">Tanggal invoice</div><div class="iv-val"><?= $tgl($inv->tanggal_invoice) ?></div></div>
                    <div><div class="iv-lbl">Jatuh tempo</div><div class="iv-val"><?= $tgl($inv->jatuh_tempo) ?></div></div>

                    <div><div class="iv-lbl">Dibuat lewat</div><div class="iv-val"><?= $e($inv->dibuat_lewat ?: '-') ?></div></div>
                    <?php if ($dibayar): ?>
                        <div><div class="iv-lbl">Tanggal pembayaran</div><div class="iv-val"><?= $tgl($inv->tanggal_bayar) ?></div></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="iv-card">
                <h2>Pratinjau dokumen</h2>
                <div class="iv-prev">[Pratinjau dokumen]</div>
            </div>
        </div>

        <!-- KANAN -->
        <div>
            <div class="iv-card">
                <h2>Status Pembayaran</h2>
                <div class="iv-rw"><span>Status</span><span class="iv-pill" style="<?= $pillStatus ?>"><?= $e($inv->status) ?></span></div>

                <?php if (!$dibayar): ?>
                    <form method="post" action="<?= site_url('pembayaran/status/invoice/' . $id) ?>" onsubmit="return confirm('Tandai invoice ini sudah dibayar?');" style="margin:0">
                        <?= $csrf ?>
                        <input type="hidden" name="status" value="Dibayar">
                        <button type="submit" class="iv-pay">Tandai Sudah Bayar</button>
                    </form>
                    <p class="iv-note">Tanggal pembayaran dicatat saat status diubah.</p>
                <?php else: ?>
                    <div class="iv-rw"><span>Dibayar pada</span><span style="font-weight:500"><?= $tgl($inv->tanggal_bayar) ?></span></div>
                    <form method="post" action="<?= site_url('pembayaran/status/invoice/' . $id) ?>" onsubmit="return confirm('Kembalikan status menjadi Belum dibayar?');" style="margin:0">
                        <?= $csrf ?>
                        <input type="hidden" name="status" value="Belum dibayar">
                        <button type="submit" class="iv-undo">Kembalikan ke Belum dibayar</button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="iv-card">
                <h2>Pengingat jatuh tempo</h2>
                <div class="iv-lbl" style="margin-bottom:4px">Kontak PIC klien</div>
                <div class="iv-pic">
                    <?= $e($inv->nama_pic ?: '-') ?><br>
                    <?= $e($inv->email_pic ?: '-') ?> &nbsp; <?= $e($inv->hp_pic ?: '-') ?>
                </div>

                <?php foreach ([['email', 'Kirim lewat email', $adaEmail], ['whatsapp', 'Kirim lewat whatsApp', $adaHp]] as $s): ?>
                    <form method="post" action="<?= site_url('pembayaran/kirim/invoice/' . $id) ?>" target="_blank"
                          onsubmit="setTimeout(function(){location.reload()},1800)" style="margin:0">
                        <?= $csrf ?>
                        <input type="hidden" name="saluran" value="<?= $s[0] ?>">
                        <button type="submit" class="iv-send" <?= ($dibayar || !$s[2]) ? 'disabled' : '' ?>
                                title="<?= $dibayar ? 'Invoice sudah dibayar' : (!$s[2] ? 'Kontak PIC belum diisi' : '') ?>"><?= $s[1] ?></button>
                    </form>
                <?php endforeach; ?>
                <?php if ($dibayar): ?><p class="iv-note">Invoice sudah dibayar, pengingat tidak diperlukan.</p><?php endif; ?>

                <div class="iv-log">
                    <div class="iv-lbl" style="font-size:15px;margin-bottom:6px">Riwayat pengiriman</div>
                    <div class="iv-rw">
                        <span style="color:#0f172a">Email<?= isset($pengiriman['email']) ? ' - Terkirim' : '' ?></span>
                        <span><?= isset($pengiriman['email']) ? $tgl($pengiriman['email']) : 'Belum dikirim' ?></span>
                    </div>
                    <div class="iv-rw">
                        <span style="color:#0f172a">WhatsApp<?= isset($pengiriman['whatsapp']) ? ' - Terkirim' : '' ?></span>
                        <span><?= isset($pengiriman['whatsapp']) ? $tgl($pengiriman['whatsapp']) : 'Belum dikirim' ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>