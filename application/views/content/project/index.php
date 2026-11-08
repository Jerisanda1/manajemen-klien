<?php

/**
 * Manajemen Project
 * Variabel: $projects, $klienList, $jmlProject, $jmlBerjalan, $total,
 *           $page, $perPage, $totalPages, $q, $fKlien, $fStatus
 */
$e = function ($v) {
    return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
};

$bulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tgl = function ($tanggal) use ($bulan) {
    $t = $tanggal ? strtotime((string) $tanggal) : false;
    return $t ? date('d', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t) : '-';
};

$badge = [
    'Berjalan' => 'bg-sky-100 text-sky-600',
    'Selesai'  => 'bg-emerald-100 text-emerald-600',
    'Ditunda'  => 'bg-amber-100 text-amber-600',
];

// Link pagination: pertahankan filter
$urlHalaman = function ($p) use ($q, $fKlien, $fStatus) {
    return site_url('project') . '?' . http_build_query(['q' => $q, 'klien' => $fKlien, 'status' => $fStatus, 'page' => $p]);
};

$awal  = $total > 0 ? (($page - 1) * $perPage) + 1 : 0;
$akhir = $total > 0 ? min($page * $perPage, $total) : 0;
?> 

<div class="flex w-full shrink-0 flex-col px-4 py-5 md:px-8">

    <!-- Header -->
    <header class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-slate-900">Manajemen Project</h1>
            <p class="mt-1 text-base text-slate-500"><?= (int) $jmlProject ?> Project, <?= (int) $jmlBerjalan ?> sedang berjalan</p>
        </div>
         <a href="<?= site_url('project/tambah') ?>"
       style="display:inline-flex;align-items:center;gap:8px;background:#a62626;color:#fff;padding:12px 20px;border-radius:12px;font-size:16px;font-weight:500;text-decoration:none;box-shadow:0 1px 2px rgba(0,0,0,.1)"
       onmouseover="this.style.background='#8c1f1f'" onmouseout="this.style.background='#a62626'">
        <svg style="width:20px;height:20px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Tambah Project
    </a>
    </header>

    <?php if ($this->session->flashdata('success')): ?>
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <?= $e($this->session->flashdata('success')) ?>
        </div>
    <?php endif; ?>

    <!-- Kartu utama -->
    <section class="rounded-2xl border border-slate-300 bg-white p-5">

        <!-- Filter -->
        <form method="get" action="<?= site_url('project') ?>" class="mb-5 flex flex-col gap-3 md:flex-row">
            <div class="relative flex-1">
    <svg style="position:absolute;left:14px;top:50%;transform:translateY(-50%);width:20px;height:20px;pointer-events:none;color:#64748b"
         fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11a6 6 0 11-12 0 6 6 0 0112 0z"/>
    </svg>
    <input type="text" name="q" value="<?= $e($q) ?>" placeholder="Cari nama project atau klien...."
           style="padding-left:44px"
           class="h-12 w-full rounded-lg border border-slate-300 bg-slate-50 pr-4 text-sm text-slate-800 placeholder-slate-500 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200">
</div>

            <select name="klien" onchange="this.form.submit()"
                    class="h-12 rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-44">
                <option value="">Semua Project</option>
                <?php foreach ($klienList as $k): ?>
                    <option value="<?= (int) $k->id ?>" <?= (string) $fKlien === (string) $k->id ? 'selected' : '' ?>><?= $e($k->nama_klien) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status" onchange="this.form.submit()"
                    class="h-12 rounded-lg border border-slate-300 bg-white px-4 text-sm font-medium text-slate-800 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-200 md:w-44">
                <option value="">Semua Status</option>
                <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                    <option value="<?= $s ?>" <?= $fStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Tabel -->
        <div class="overflow-hidden rounded-lg border border-slate-300">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[900px] text-left text-sm">
                    <thead class="bg-slate-100">
                        <tr class="border-b border-slate-300 text-sm font-medium text-slate-600">
                            <th class="px-6 py-4">Nama Project</th>
                            <th class="px-3 py-4">Klien</th>
                            <th class="px-3 py-4">Deskripsi</th>
                            <th class="px-3 py-4">Mulai</th>
                            <th class="px-3 py-4">Selesai</th>
                            <th class="px-3 py-4">Status</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        <?php if (empty($projects)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-500">Belum ada data project.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                                <tr class="transition-colors hover:bg-slate-50">
                                    <td class="px-6 py-4 text-base font-semibold text-slate-900"><?= $e($p->nama_project) ?></td>
                                    <td class="px-3 py-4 text-slate-800"><?= $e($p->nama_klien ?: '-') ?></td>
                                    <td class="max-w-[220px] px-3 py-4 text-slate-600">
                                        <p class="line-clamp-2" title="<?= $e($p->deskripsi) ?>"><?= $e($p->deskripsi ?: '-') ?></p>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 font-medium text-slate-600"><?= $tgl($p->tanggal_mulai) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4 font-medium text-slate-600"><?= $tgl($p->tanggal_selesai) ?></td>
                                    <td class="whitespace-nowrap px-3 py-4">
                                        <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold <?= $badge[$p->status] ?? 'bg-slate-100 text-slate-600' ?>">
                                            <?= $e($p->status) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <a href="<?= site_url('project/detail/' . (int) $p->id) ?>" aria-label="Lihat detail"
                                               class="flex h-8 w-8 items-center justify-center rounded-full border border-slate-400 text-slate-600 transition-colors hover:bg-slate-100">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <a href="<?= site_url('project/edit/' . (int) $p->id) ?>" aria-label="Edit"
                                               class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-300 text-slate-600 transition-colors hover:bg-slate-100">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                            </a>

                                            <a href="<?= site_url('project/edit/' . (int) $p->id) ?>" ...>
                                            </a>
                                            <button type="button" aria-label="Hapus"
                                                    data-id="<?= (int) $p->id ?>"
                                                    data-nama="<?= $e($p->nama_project) ?>"
                                                    onclick="bukaModalHapus(this.dataset.id, this.dataset.nama)"
                                                    class="flex h-8 w-8 items-center justify-center rounded-md border border-red-400 text-red-600 transition-colors hover:bg-red-50">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer + pagination -->
            <div class="flex flex-col gap-3 border-t border-slate-300 bg-slate-100 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-600">Menampilkan <?= $awal ?>-<?= $akhir ?> dari <?= (int) $total ?> Project</p>
                <div class="flex items-center gap-1.5">
                    <?php if ($page > 1): ?>
                        <a href="<?= $urlHalaman($page - 1) ?>" aria-label="Sebelumnya" class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-600 hover:bg-slate-50">
                    <?php else: ?>
                        <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-white text-slate-300">
                    <?php endif; ?>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <?= $page > 1 ? '</a>' : '</span>' ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?= $urlHalaman($i) ?>"
                           class="flex h-8 min-w-[2rem] items-center justify-center rounded-md border px-2 text-sm <?= $i === $page ? 'border-red-300 bg-red-50 font-semibold text-red-700' : 'border-slate-300 bg-white text-slate-600 hover:bg-slate-50' ?>"><?= $i ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= $urlHalaman($page + 1) ?>" aria-label="Berikutnya" class="flex h-8 w-8 items-center justify-center rounded-md border border-slate-300 bg-white text-slate-600 hover:bg-slate-50">
                    <?php else: ?>
                        <span class="flex h-8 w-8 cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-white text-slate-300">
                    <?php endif; ?>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <?= $page < $totalPages ? '</a>' : '</span>' ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal hapus -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="judulModalHapus">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" onclick="tutupModalHapus()"></div>
    <div id="modalContent" class="relative z-10 w-full max-w-md scale-95 transform rounded-2xl bg-white p-6 opacity-0 shadow-xl transition-all duration-200">
        <h3 id="judulModalHapus" class="text-lg font-semibold text-slate-900">Hapus project?</h3>
        <p class="mt-2 text-sm leading-relaxed text-slate-500">
            <strong id="namaProjectHapus" class="font-semibold text-slate-900"></strong>
            akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
        </p>

        <form method="post" action="<?= site_url('project/hapus') ?>" class="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <?php if ($this->config->item('csrf_protection')): ?>
                <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
            <?php endif; ?>
            <input type="hidden" name="id" id="inputHapusId">
            <button type="button" onclick="tutupModalHapus()" class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Batal</button>
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Hapus project</button>
        </form>
    </div>
</div>

<script>
    const modalHapus = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    function bukaModalHapus(id, nama) {
        document.getElementById('inputHapusId').value = id;
        document.getElementById('namaProjectHapus').textContent = nama;
        modalHapus.classList.remove('hidden');
        modalHapus.classList.add('flex');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalHapus() {
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalHapus.classList.add('hidden');
            modalHapus.classList.remove('flex');
        }, 200);
    }

    document.addEventListener('keydown', (ev) => {
        if (ev.key === 'Escape' && !modalHapus.classList.contains('hidden')) tutupModalHapus();
    });
</script>