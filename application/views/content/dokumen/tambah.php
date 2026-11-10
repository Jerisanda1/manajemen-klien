<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
    <style>
        .custom-dropdown.drop-up .dropdown-menu { top: auto; bottom: 100%; margin-top: 0; margin-bottom: 4px; }
        .dropdown-menu { transition: opacity 200ms cubic-bezier(0.4,0,0.2,1), transform 200ms cubic-bezier(0.4,0,0.2,1); }
    </style>
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen">

    <div class="max-w-[1280px] mx-auto p-4 md:p-6 lg:p-8">

        <div class="mb-5">
            <a href="<?= site_url('dokumen') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Tambah Dokumen Kerja Sama</h2>
        </div>

        <?php if($this->session->flashdata('error')): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-5 border border-red-200 text-sm"><?= $this->session->flashdata('error'); ?></div>
        <?php endif; ?>

        <form action="<?= site_url('dokumen/simpan') ?>" method="POST" enctype="multipart/form-data" id="formDokumen">
            <input type="hidden" name="device_id" id="device_id" value="">
            <input type="hidden" name="file_name_local" id="fileNameLocal" value="">
            <input type="hidden" name="file_hash_local" id="file_hash_local" value="">
            <input type="hidden" name="file_size_local" id="file_size_local" value="">
            <input type="hidden" name="file_type_local" id="file_type_local" value="">

            <!-- Card 1: Storage & Upload -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 mb-4">
                <div class="flex flex-col lg:flex-row gap-5 lg:gap-6">
                    <div class="w-full lg:w-1/2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Simpan File di</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer">
                                <input type="radio" name="storage_type" value="cloud" class="peer sr-only" checked onchange="toggleStorage('cloud')">
                                <div class="p-4 border rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-gray-50 h-full">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                        <span class="font-bold text-sm text-gray-900">Server Sistem</span>
                                    </div>
                                    <p class="text-xs text-gray-500">File diunggah ke sistem. Bisa dibuka di semua perangkat.</p>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="storage_type" value="local" class="peer sr-only" onchange="toggleStorage('local')">
                                <div class="p-4 border rounded-xl peer-checked:border-[#9f2b2b] peer-checked:bg-red-50 transition-all hover:bg-gray-50 h-full">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                        <span class="font-bold text-sm text-gray-900">Perangkat Ini</span>
                                    </div>
                                    <p class="text-xs text-gray-500">File tetap di laptop/PC ini. Hanya bisa dibuka dari sini.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="w-full lg:w-1/2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">File dokumen</label>
                        <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <input type="file" name="file_dokumen" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <p class="text-sm text-gray-600 font-medium text-center mb-2" id="dropzoneText">Tarik file ke sini atau pilih dari perangkat</p>
                            <span class="px-3 py-1.5 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 shadow-sm">Pilih file</span>
                        </div>
                        <div id="filePreview" class="hidden border border-gray-300 rounded-xl p-4 bg-white flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div>
                                    <p id="fileName" class="text-sm font-bold text-gray-800 truncate max-w-[200px]">Nama_File.pdf</p>
                                    <p id="fileSize" class="text-xs text-gray-500">1.2 MB • PDF</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="document.getElementById('fileInput').click()" class="px-3 py-1.5 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 hover:bg-gray-50">Ganti</button>
                                <button type="button" onclick="resetFile()" class="px-3 py-1.5 border border-red-200 bg-red-50 rounded-lg text-xs font-medium text-red-600 hover:bg-red-100">Hapus</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Data Dokumen -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 mb-4">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Dokumen</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-5">

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_dokumen" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="[Masukkan nama dokumen]" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Klien <span class="text-red-500">*</span></label>
                        <div class="relative custom-dropdown" data-target="client_id">
                            <button type="button" class="dropdown-btn flex items-center justify-between w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                <span class="dropdown-text truncate text-gray-400">Pilih klien</span>
                                <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                <div class="p-2 border-b border-gray-100">
                                    <div class="relative">
                                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari klien..." autocomplete="off">
                                    </div>
                                </div>
                                <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                    <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih klien</li>
                                    <?php foreach($klien as $k): ?>
                                        <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors" data-value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_klien) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <input type="hidden" name="client_id" id="client_id" value="">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Jenis dokumen <span class="text-red-500">*</span></label>
                        <div class="relative custom-dropdown" data-target="jenis_dokumen">
                            <button type="button" class="dropdown-btn flex items-center justify-between w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                <span class="dropdown-text truncate text-gray-400">Pilih jenis</span>
                                <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                <div class="p-2 border-b border-gray-100">
                                    <div class="relative">
                                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari jenis..." autocomplete="off">
                                    </div>
                                </div>
                                <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                    <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih jenis</li>
                                    <?php foreach($jenis_dokumen as $j): ?>
                                        <li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors" data-value="<?= htmlspecialchars($j->name) ?>"><?= htmlspecialchars($j->name) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <input type="hidden" name="jenis_dokumen" id="jenis_dokumen" value="">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Project <span class="text-red-500">*</span></label>
                        <div class="relative custom-dropdown" data-target="project_id">
                            <button type="button" class="dropdown-btn flex items-center justify-between w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                <span class="dropdown-text truncate text-gray-400">Pilih Klien Terlebih Dahulu</span>
                                <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                <div class="p-2 border-b border-gray-100">
                                    <div class="relative">
                                        <svg class="absolute left-2 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                        <input type="text" class="dropdown-search w-full pl-7 pr-2 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari project..." autocomplete="off">
                                    </div>
                                </div>
                                <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-60 overflow-y-auto">
                                    <li class="px-3 py-2 text-gray-400 italic cursor-default">Pilih Klien Terlebih Dahulu</li>
                                </ul>
                            </div>
                            <input type="hidden" name="project_id" id="project_id" value="">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_berakhir" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="<?= site_url('dokumen') ?>" class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                <button type="submit" id="btnSimpan" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 disabled:opacity-50">Simpan Dokumen</button>
            </div>
        </form>
    </div>

    <script>
    // --- UTILITY ---
    const DEVICE_ID = (() => {
        let id = localStorage.getItem('tridatu_device_id');
        if (!id) {
            id = crypto.randomUUID();
            localStorage.setItem('tridatu_device_id', id);
            document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;
        }
        return id;
    })();
    document.getElementById('device_id').value = DEVICE_ID;

    async function hitungHash(file) {
        const buf = await crypto.subtle.digest('SHA-256', await file.arrayBuffer());
        return [...new Uint8Array(buf)].map(b => b.toString(16).padStart(2, '0')).join('');
    }

    // --- FILE PREVIEW ---
    const fileInput = document.getElementById('fileInput');
    const dropzone = document.getElementById('dropzone');
    const filePreview = document.getElementById('filePreview');
    const fName = document.getElementById('fileName');
    const fSize = document.getElementById('fileSize');
    const fNameLocal = document.getElementById('fileNameLocal');
    const fHashLocal = document.getElementById('file_hash_local');
    const fSizeLocal = document.getElementById('file_size_local');
    const fTypeLocal = document.getElementById('file_type_local');

    fileInput.addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;
        const mb = (file.size / 1048576).toFixed(2);
        const ext = file.name.split('.').pop().toUpperCase();

        fName.textContent = 'Menghitung keamanan file...';
        fSize.textContent = `${mb} MB • ${ext}`;
        fNameLocal.value = file.name;
        fSizeLocal.value = file.size;
        fTypeLocal.value = file.type;
        fHashLocal.value = await hitungHash(file);
        fName.textContent = file.name;

        dropzone.classList.add('hidden');
        filePreview.classList.remove('hidden');
    });

    function resetFile() {
        fileInput.value = '';
        [fNameLocal, fHashLocal, fSizeLocal, fTypeLocal].forEach(el => el.value = '');
        dropzone.classList.remove('hidden');
        filePreview.classList.add('hidden');
    }

    // --- DROPDOWN ---
    function setDropdownValue(targetId, value, label) {
        const dd = document.querySelector(`.custom-dropdown[data-target="${targetId}"]`);
        if (!dd) return;
        const input = dd.querySelector('input[type="hidden"]');
        const text = dd.querySelector('.dropdown-text');

        input.value = value;
        text.textContent = label;
        text.classList.toggle('text-gray-400', value === '');
        text.classList.toggle('text-gray-900', value !== '');

        dd.querySelectorAll('li').forEach(li => {
            li.classList.remove('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
            if (li.dataset.value === String(value)) li.classList.add('bg-red-50', 'text-[#9f2b2b]', 'font-bold');
        });

        input.dispatchEvent(new Event('change'));
    }

    document.querySelectorAll('.custom-dropdown').forEach(dd => {
        const btn = dd.querySelector('.dropdown-btn');
        const menu = dd.querySelector('.dropdown-menu');
        const icon = btn.querySelector('svg');
        const search = dd.querySelector('.dropdown-search');
        const list = dd.querySelector('.dropdown-list');

        const filterList = (q) => {
            q = q.toLowerCase().trim();
            list.querySelector('.no-result')?.remove();
            let found = false;
            list.querySelectorAll('li').forEach(li => {
                if (li.dataset.value === undefined) return;
                const match = !q || li.textContent.toLowerCase().includes(q);
                li.classList.toggle('hidden', !match);
                found = found || match;
            });
            if (!found && q) {
                const li = document.createElement('li');
                li.className = 'no-result px-3 py-2 text-gray-400 italic text-center cursor-default';
                li.textContent = 'Tidak ada hasil';
                list.appendChild(li);
            }
        };

        const resetList = () => {
            if (search) search.value = '';
            list.querySelectorAll('li').forEach(li => li.classList.remove('hidden'));
            list.querySelector('.no-result')?.remove();
        };

        const close = () => {
            dd.classList.remove('open', 'drop-up');
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            icon.classList.remove('rotate-180');
            setTimeout(() => menu.classList.add('hidden'), 200);
        };

        const open = () => {
            document.querySelectorAll('.custom-dropdown').forEach(d => {
                if (d !== dd && d.classList.contains('open')) {
                    d.classList.remove('open', 'drop-up');
                    const m = d.querySelector('.dropdown-menu');
                    m.classList.add('hidden', 'opacity-0', 'scale-95');
                    m.classList.remove('opacity-100', 'scale-100');
                    d.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
                }
            });
            resetList();

            menu.style.visibility = 'hidden';
            menu.classList.remove('hidden');
            const rect = btn.getBoundingClientRect();
            const h = menu.offsetHeight;
            const below = window.innerHeight - rect.bottom;
            dd.classList.toggle('drop-up', below < h + 16 && rect.top > below);
            menu.classList.add('hidden');
            menu.style.visibility = '';

            dd.classList.add('open');
            menu.classList.remove('hidden');
            void menu.offsetWidth;
            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
            icon.classList.add('rotate-180');

            if (search) setTimeout(() => search.focus(), 50);
        };

        btn.addEventListener('click', e => {
            e.stopPropagation();
            dd.classList.contains('open') ? close() : open();
        });

        dd.addEventListener('click', e => {
            const li = e.target.closest('li');
            if (!li || li.dataset.value === undefined) return;
            setDropdownValue(dd.dataset.target, li.dataset.value, li.textContent.trim());
            close();
        });

        if (search) {
            search.addEventListener('input', e => filterList(e.target.value));
            search.addEventListener('click', e => e.stopPropagation());
            search.addEventListener('keydown', e => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const first = [...list.querySelectorAll('li')].find(li => !li.classList.contains('hidden') && li.dataset.value);
                    if (first) {
                        setDropdownValue(dd.dataset.target, first.dataset.value, first.textContent.trim());
                        close();
                        btn.focus();
                    }
                } else if (e.key === 'Escape') {
                    close();
                    btn.focus();
                }
            });
        }
    });

    document.addEventListener('click', e => {
        document.querySelectorAll('.custom-dropdown').forEach(dd => {
            if (dd.contains(e.target)) return;
            dd.classList.remove('open', 'drop-up');
            const m = dd.querySelector('.dropdown-menu');
            m.classList.add('opacity-0', 'scale-95');
            m.classList.remove('opacity-100', 'scale-100');
            dd.querySelector('.dropdown-btn svg').classList.remove('rotate-180');
            setTimeout(() => m.classList.add('hidden'), 200);
        });
    });

    // --- CASCADING PROJECT ---
    document.getElementById('client_id').addEventListener('change', async function() {
        const clientId = this.value;
        const dd = document.querySelector('.custom-dropdown[data-target="project_id"]');
        const list = dd.querySelector('.dropdown-list');
        const search = dd.querySelector('.dropdown-search');

        setDropdownValue('project_id', '', 'Memuat project...');
        list.innerHTML = '<li class="px-3 py-2 text-gray-400 italic cursor-default">Memuat...</li>';
        if (search) search.value = '';

        if (!clientId) {
            setDropdownValue('project_id', '', 'Pilih Klien Terlebih Dahulu');
            list.innerHTML = '<li class="px-3 py-2 text-gray-400 italic cursor-default">Pilih Klien Terlebih Dahulu</li>';
            return;
        }

        try {
            const res = await fetch('<?= site_url("dokumen/get_projects") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'client_id=' + clientId
            });
            const data = await res.json();
            const projects = data.data || data || [];

            if (projects.length === 0) {
                setDropdownValue('project_id', '', 'Klien ini belum punya project');
                list.innerHTML = '<li class="px-3 py-2 text-gray-400 italic cursor-default">Klien ini belum punya project</li>';
                return;
            }

            setDropdownValue('project_id', '', 'Pilih project');
            list.innerHTML = '<li class="px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih project</li>';
            projects.forEach(p => {
                const li = document.createElement('li');
                li.className = 'px-3 py-2 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors';
                li.dataset.value = p.id;
                li.textContent = p.nama_project;
                list.appendChild(li);
            });
        } catch (err) {
            console.error(err);
            setDropdownValue('project_id', '', 'Gagal memuat project');
            list.innerHTML = '<li class="px-3 py-2 text-red-500 italic cursor-default">Gagal memuat project</li>';
        }
    });

    // --- STORAGE TOGGLE ---
    let currentStorage = 'cloud';
    function toggleStorage(type) {
        currentStorage = type;
        document.getElementById('dropzoneText').textContent = type === 'local'
            ? 'Tarik file ke sini atau pilih dari perangkat (Disimpan lokal)'
            : 'Tarik file ke sini atau pilih dari perangkat';
    }

    // --- FORM SUBMIT DENGAN LOCAL SERVICE ---
    document.getElementById('formDokumen').addEventListener('submit', async function(e) {
        const fields = [
            { id: 'client_id', label: 'Klien' },
            { id: 'jenis_dokumen', label: 'Jenis dokumen' },
            { id: 'project_id', label: 'Project' }
        ];
        for (const f of fields) {
            if (!document.getElementById(f.id).value) {
                e.preventDefault();
                alert(`${f.label} wajib dipilih.`);
                return;
            }
        }

        // Mode Cloud: biarkan submit standar
        if (currentStorage === 'cloud') return;

        e.preventDefault();
        const file = fileInput.files[0];
        if (!file) return alert('Pilih file terlebih dahulu!');

        const btn = document.getElementById('btnSimpan');
        btn.textContent = 'Menghubungi Local Agent...';
        btn.disabled = true;

        try {
            // 1. Cek apakah Local Agent aktif
            try {
                await fetch('http://localhost:8080/ping', { mode: 'cors' });
            } catch (err) {
                throw new Error('Aplikasi TridatuAgent.exe belum dibuka. Silakan buka aplikasinya terlebih dahulu di laptop Anda.');
            }

            // 2. Kirim file ke Local Agent untuk disimpan di hardisk
            btn.textContent = 'Menyimpan ke Folder...';
            const resAgent = await fetch(`http://localhost:8080/save?filename=${encodeURIComponent(file.name)}`, {
                method: 'POST',
                body: file // Kirim raw file
            });

            const dataAgent = await resAgent.json();
            if (!dataAgent.success) throw new Error(dataAgent.error || 'Gagal menyimpan file di laptop.');

            // 3. Kirim metadata ke server CodeIgniter (tanpa file fisik)
            btn.textContent = 'Menyimpan Data...';
            const fd = new FormData(this);
            fd.delete('file_dokumen'); // Pastikan file tidak di-upload ke hosting
            fd.set('storage_type', 'local'); // Set tipe local

            const res = await fetch(this.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();

            if (data.status) {
                window.location.href = data.redirect || '<?= site_url("dokumen") ?>';
            } else {
                throw new Error(data.msg || 'Gagal menyimpan data ke database.');
            }
        } catch (err) {
            alert(err.message || err);
            btn.textContent = 'Simpan Dokumen';
            btn.disabled = false;
        }
    });
    </script>
</body>
</html>