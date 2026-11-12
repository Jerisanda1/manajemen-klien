<?php
/**
 * Form Tambah & Edit Project — halaman mandiri
 * Konsep visual mengikuti form Klien: card 55/45 dengan ilustrasi.
 * Custom dropdown Klien & Status dipertahankan (konsisten dengan form Dokumen).
 */
$e = function ($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); };

$isEdit = !empty($project);

$val = function ($f) use ($project) {
    return set_value($f, isset($project->$f) ? $project->$f : '');
};
$err = function ($f) {
    $m = form_error($f);
    return $m ? '<p class="text-[11px] text-red-600 mt-1">' . htmlspecialchars($m) . '</p>' : '';
};

$from = isset($from) ? $from : 'list';
$urlKembali = site_url($isEdit && $from === 'detail' ? 'project/detail/' . (int) $project->id : 'project');

$gambar = 'assets/img/project_form.png';
$adaGambar = file_exists(FCPATH . $gambar);

$curKlienId    = (string) $val('klien_id');
$curStatus     = (string) $val('status');
$curKlienLabel = 'Pilih klien';
foreach ($klienList as $k) {
    if ((string) $k->id === $curKlienId) { $curKlienLabel = $k->nama_klien; break; }
}
$curStatusLabel = $curStatus !== '' ? $curStatus : 'Pilih Status';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $e($title) ?> - Tridatu</title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-gradient-to-br from-[#f4f6f9] to-[#e6e9ef] font-sans text-gray-800 antialiased min-h-screen flex items-center justify-center p-4 md:p-6">

    <!-- Container Utama -->
    <div class="w-full max-w-[950px]">

        <!-- Header (di luar kotak) -->
        <div class="mb-4 pl-2">
            <a href="<?= $urlKembali ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-1 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight"><?= $e($title) ?></h2>
        </div>

        <!-- Kotak Form -->
        <div class="bg-white rounded-2xl shadow-xl border border-white overflow-hidden flex flex-col md:flex-row">

            <!-- KIRI: Form Input -->
            <div class="w-full md:w-[55%] p-7 md:p-8 bg-white flex flex-col justify-center relative z-10">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Project</h3>

                <form action="<?= $e($action) ?>" method="POST" id="formProject" onsubmit="disableButton(this)">
                    <?php if ($this->config->item('csrf_protection')): ?>
                        <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
                    <?php endif; ?>

                    <!-- Nama Project -->
                    <div class="mb-4">
                        <label for="nama_project" class="block text-xs font-medium text-gray-700 mb-1.5">
                            Nama Project <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="nama_project" name="nama_project" required
                               value="<?= $e($val('nama_project')) ?>"
                               class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all"
                               placeholder="Contoh: Website Company Profile">
                        <?= $err('nama_project') ?>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 mb-4">

                        <!-- Klien: custom dropdown + search -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Klien <span class="text-red-500">*</span></label>
                            <div class="relative custom-dropdown" data-target="klien_id">
                                <button type="button" class="dropdown-btn w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                    <span class="dropdown-text truncate <?= $curKlienId === '' ? 'text-gray-400' : 'text-gray-800' ?>"><?= $e($curKlienLabel) ?></span>
                                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden origin-top transform opacity-0 scale-95 transition-all duration-150">
                                    <div class="p-2 border-b border-gray-100 relative">
                                        <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari klien..." autocomplete="off">
                                    </div>
                                    <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                        <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium <?= $curKlienId === '' ? 'bg-red-50 text-[#9f2b2b]' : '' ?>" data-value="">Pilih klien</li>
                                        <?php foreach ($klienList as $k): ?>
                                            <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $curKlienId === (string) $k->id ? 'bg-red-50 text-[#9f2b2b] font-medium' : '' ?>" data-value="<?= (int) $k->id ?>"><?= $e($k->nama_klien) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <input type="hidden" name="klien_id" id="klien_id" value="<?= $e($curKlienId) ?>">
                            </div>
                            <?= $err('klien_id') ?>
                        </div>

                        <!-- Status: custom dropdown tanpa search -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1.5">Status Project <span class="text-red-500">*</span></label>
                            <div class="relative custom-dropdown" data-target="status">
                                <button type="button" class="dropdown-btn w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-left flex items-center justify-between gap-2 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                    <span class="dropdown-text truncate <?= $curStatus === '' ? 'text-gray-400' : 'text-gray-800' ?>"><?= $e($curStatusLabel) ?></span>
                                    <svg class="w-4 h-4 text-gray-500 flex-shrink-0 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>
                                <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-xl overflow-hidden origin-top transform opacity-0 scale-95 transition-all duration-150">
                                    <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                        <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium <?= $curStatus === '' ? 'bg-red-50 text-[#9f2b2b]' : '' ?>" data-value="">Pilih Status</li>
                                        <?php foreach (['Berjalan', 'Selesai', 'Ditunda'] as $s): ?>
                                            <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $curStatus === $s ? 'bg-red-50 text-[#9f2b2b] font-medium' : '' ?>" data-value="<?= $s ?>"><?= $s ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <input type="hidden" name="status" id="status" value="<?= $e($curStatus) ?>">
                            </div>
                            <?= $err('status') ?>
                        </div>
                    </div>

                    <!-- Tanggal -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4 mb-4">
                        <div>
                            <label for="tanggal_mulai" class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Mulai</label>
                            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= $e($val('tanggal_mulai')) ?>"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all">
                            <?= $err('tanggal_mulai') ?>
                        </div>
                        <div>
                            <label for="tanggal_selesai" class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Selesai</label>
                            <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= $e($val('tanggal_selesai')) ?>"
                                   class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all">
                            <?= $err('tanggal_selesai') ?>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-5">
                        <label for="deskripsi" class="block text-xs font-medium text-gray-700 mb-1.5">Deskripsi Project</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3"
                                  class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all resize-none"
                                  placeholder="Ringkasan lingkup dan tujuan project..."><?= $e($val('deskripsi')) ?></textarea>
                        <?= $err('deskripsi') ?>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <a href="<?= $urlKembali ?>" class="px-5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                        <button type="submit" id="btnSimpan"
                                class="px-5 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed">
                            <?= $isEdit ? 'Simpan Perubahan' : 'Simpan Project' ?>
                        </button>
                    </div>
                </form>
            </div>

            <!-- KANAN: Ilustrasi -->
            <div class="hidden md:flex w-[45%] bg-[#4A4545] items-center justify-center p-6 relative">
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full mix-blend-overlay filter blur-3xl transform translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#9f2b2b]/20 rounded-full mix-blend-overlay filter blur-3xl transform -translate-x-1/2 translate-y-1/2 pointer-events-none"></div>
                <?php if ($adaGambar): ?>
                    <img src="<?= base_url($gambar) ?>" alt="Ilustrasi Project" class="w-full max-h-[280px] object-contain drop-shadow-2xl relative z-10">
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script>
    // --- CUSTOM DROPDOWN ---
    function closeDD(dd) {
        dd.classList.remove('open', 'drop-up');
        const menu  = dd.querySelector('.dropdown-menu');
        const arrow = dd.querySelector('.dropdown-btn svg');
        if (!menu) return;
        menu.classList.remove('opacity-100', 'scale-100');
        menu.classList.add('opacity-0', 'scale-95');
        if (arrow) arrow.classList.remove('rotate-180');
        setTimeout(() => menu.classList.add('hidden'), 150);
    }

    function openDD(dd) {
        document.querySelectorAll('.custom-dropdown.open').forEach(o => { if (o !== dd) closeDD(o); });
        const menu   = dd.querySelector('.dropdown-menu');
        const btn    = dd.querySelector('.dropdown-btn');
        const arrow  = dd.querySelector('.dropdown-btn svg');
        const search = dd.querySelector('.dropdown-search');
        if (!menu || !btn) return;

        if (search) { search.value = ''; filterList(dd, ''); }
        const list = dd.querySelector('.dropdown-list');
        if (list) list.scrollTop = 0;

        // Deteksi drop-up
        menu.style.visibility = 'hidden';
        menu.classList.remove('hidden');
        const rect = btn.getBoundingClientRect();
        const h = menu.offsetHeight;
        const spaceBelow = window.innerHeight - rect.bottom;
        dd.classList.toggle('drop-up', spaceBelow < h + 16 && rect.top > spaceBelow);
        menu.classList.add('hidden');
        menu.style.visibility = '';

        dd.classList.add('open');
        menu.classList.remove('hidden');
        void menu.offsetWidth;
        menu.classList.remove('opacity-0', 'scale-95');
        menu.classList.add('opacity-100', 'scale-100');
        if (arrow) arrow.classList.add('rotate-180');

        if (search) setTimeout(() => search.focus(), 50);
    }

    function filterList(dd, q) {
        const list = dd.querySelector('.dropdown-list');
        if (!list) return;
        q = q.toLowerCase().trim();
        list.querySelector('.empty')?.remove();
        let found = false;
        list.querySelectorAll('li[data-value]').forEach(li => {
            const match = !q || li.textContent.toLowerCase().includes(q);
            li.style.display = match ? '' : 'none';
            found = found || match;
        });
        if (!found && q) {
            const empty = document.createElement('li');
            empty.className = 'empty px-3 py-2 text-gray-400 italic text-center cursor-default text-xs';
            empty.textContent = 'Tidak ada hasil';
            list.appendChild(empty);
        }
    }

    function setDropdownValue(targetId, value, label) {
        const dd = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);
        if (!dd) return;
        const hidden = dd.querySelector('input[type="hidden"]');
        const text   = dd.querySelector('.dropdown-text');
        hidden.value = value;
        text.textContent = label;
        text.classList.toggle('text-gray-400', value === '');
        text.classList.toggle('text-gray-800', value !== '');
        dd.querySelectorAll('li[data-value]').forEach(li => {
            const isActive = li.dataset.value === String(value);
            li.classList.toggle('bg-red-50', isActive);
            li.classList.toggle('text-[#9f2b2b]', isActive);
            li.classList.toggle('font-medium', isActive);
        });
        hidden.dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn = dd.querySelector('.dropdown-btn');
        if (!btn) return;

        btn.addEventListener('click', e => {
            e.stopPropagation();
            dd.classList.contains('open') ? closeDD(dd) : openDD(dd);
        });

        dd.addEventListener('click', e => {
            const li = e.target.closest('li[data-value]');
            if (!li) return;
            setDropdownValue(dd.dataset.target, li.dataset.value, li.textContent.trim());
            closeDD(dd);
            btn.focus();
        });

        const search = dd.querySelector('.dropdown-search');
        if (search) {
            search.addEventListener('input', e => filterList(dd, e.target.value));
            search.addEventListener('click', e => e.stopPropagation());
            search.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = [...dd.querySelectorAll('li[data-value]')].find(li => li.style.display !== 'none');
                    if (first) {
                        setDropdownValue(dd.dataset.target, first.dataset.value, first.textContent.trim());
                        closeDD(dd);
                        btn.focus();
                    }
                } else if (e.key === 'Escape') {
                    closeDD(dd);
                    btn.focus();
                }
            });
        }
    });

    document.addEventListener('click', e => {
        document.querySelectorAll('.custom-dropdown.open').forEach(dd => {
            if (!dd.contains(e.target)) closeDD(dd);
        });
    });

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.custom-dropdown.open').forEach(closeDD);
        }
    });

    // --- DOUBLE SUBMIT PREVENTION (pola sama dengan form Klien) ---
    function disableButton(form) {
        const btn = form.querySelector('#btnSimpan');
        if (!btn) return true;
        btn.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Menyimpan...
        `;
        btn.disabled = true;
        return true;
    }
    </script>

</body>
</html>