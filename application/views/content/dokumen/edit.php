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
        .scroll-hide { scrollbar-width: none; -ms-overflow-style: none; }
        .scroll-hide::-webkit-scrollbar { display: none; }
    </style>
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen lg:h-screen lg:overflow-hidden p-4 md:p-5 lg:p-6">

    <div class="max-w-[1280px] mx-auto w-full h-full flex flex-col">

        <!-- Header -->
        <div class="shrink-0 mb-4">
            <a href="<?= site_url('dokumen') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-1.5 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Edit Dokumen Kerja Sama</h2>
        </div>

        <!-- Notifikasi Error -->
        <?php if($this->session->flashdata('error')): ?>
            <div class="shrink-0 bg-red-50 text-red-600 p-4 rounded-xl mb-4 border border-red-200 text-sm">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('dokumen/update') ?>" method="POST" enctype="multipart/form-data" id="formDokumen" class="flex-1 flex flex-col min-h-0">

            <input type="hidden" name="id" value="<?= $doc->id ?>">
            <input type="hidden" name="device_id" id="device_id" value="">
            <input type="hidden" name="file_name_local" id="fileNameLocal" value="">
            <input type="hidden" name="file_hash_local" id="file_hash_local" value="">
            <input type="hidden" name="file_size_local" id="file_size_local" value="">
            <input type="hidden" name="file_type_local" id="file_type_local" value="">

            <!-- Area Isi: Grid 2 Baris -->
            <div class="flex-1 min-h-0 overflow-y-auto lg:overflow-visible scroll-hide pr-1 flex flex-col gap-4 md:gap-5 pb-2 lg:grid lg:grid-rows-2">

                <!-- Card 1: Penyimpanan & Upload -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 flex flex-col justify-center min-h-0">
                    <div class="flex flex-col lg:flex-row gap-5 md:gap-6 items-start">

                        <!-- Kiri: Penyimpanan -->
                        <div class="w-full lg:w-1/2">
                            <label class="block text-base font-bold text-gray-800 mb-3">Simpan File di</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                                <label class="cursor-pointer">
                                    <input type="radio" name="storage_type" value="cloud" class="peer sr-only" <?= $doc->storage_type === 'cloud' ? 'checked' : '' ?> onchange="toggleStorage('cloud')">
                                    <div class="p-4 border rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-gray-50 h-full">
                                        <div class="flex items-center gap-2.5 mb-1.5">
                                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                                            <span class="font-bold text-sm text-gray-900">Server Sistem</span>
                                        </div>
                                        <p class="text-xs text-gray-500 leading-snug">File diunggah ke sistem. Bisa dibuka di semua perangkat.</p>
                                    </div>
                                </label>

                                <label class="cursor-pointer">
                                    <input type="radio" name="storage_type" value="local" class="peer sr-only" <?= $doc->storage_type === 'local' ? 'checked' : '' ?> onchange="toggleStorage('local')">
                                    <div class="p-4 border rounded-xl peer-checked:border-[#9f2b2b] peer-checked:bg-red-50 transition-all hover:bg-gray-50 h-full">
                                        <div class="flex items-center gap-2.5 mb-1.5">
                                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            <span class="font-bold text-sm text-gray-900">Perangkat Ini</span>
                                        </div>
                                        <p class="text-xs text-gray-500 leading-snug">File tetap di laptop/PC ini. Hanya bisa dibuka dari sini.</p>
                                    </div>
                                </label>

                            </div>
                        </div>

                        <!-- Kanan: Upload File -->
                        <div class="w-full lg:w-1/2">
                            <label class="block text-base font-bold text-gray-800 mb-3">File dokumen</label>
                            <p class="text-sm text-gray-500 mb-2.5 truncate">
                                File saat ini: <span class="font-semibold text-gray-700"><?= htmlspecialchars($doc->file_name) ?></span>
                                <span class="text-gray-400">· Biarkan kosong jika tidak diganti</span>
                            </p>

                            <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-5 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative min-h-[140px]">
                                <input type="file" name="file_dokumen" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <p class="text-sm text-gray-600 font-medium text-center mb-2.5" id="dropzoneText">Tarik file baru ke sini atau pilih dari perangkat</p>
                                <span class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 shadow-sm">Pilih file baru</span>
                            </div>

                            <div id="filePreview" class="hidden border border-gray-300 rounded-xl p-4 bg-white flex items-center justify-between mt-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <svg class="w-8 h-8 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <div class="min-w-0">
                                        <p id="fileName" class="text-sm font-bold text-gray-800 truncate">Nama_File.pdf</p>
                                        <p id="fileSize" class="text-xs text-gray-500">1.2 MB • PDF</p>
                                    </div>
                                </div>
                                <button type="button" onclick="resetFile()" class="shrink-0 px-4 py-2 border border-red-200 bg-red-50 rounded-lg text-sm font-medium text-red-600 hover:bg-red-100">Batal Ganti</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Data Dokumen -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 md:p-6 flex flex-col justify-center min-h-0">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Data Dokumen</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama dokumen <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_dokumen" value="<?= htmlspecialchars($doc->nama_dokumen) ?>" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                        </div>

                        <!-- Klien -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Klien <span class="text-red-500">*</span></label>
                            <div class="relative custom-dropdown" data-target="client_id">
                                <button type="button" class="dropdown-btn flex items-center justify-between w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                    <span class="dropdown-text truncate text-gray-900"><?= htmlspecialchars($doc->nama_klien) ?></span>
                                    <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                    <div class="p-2 border-b border-gray-100">
                                        <div class="relative">
                                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            <input type="text" class="dropdown-search w-full pl-9 pr-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari klien..." autocomplete="off">
                                        </div>
                                    </div>
                                    <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-56 overflow-y-auto">
                                        <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih klien</li>
                                        <?php foreach($klien as $k): ?>
                                            <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $doc->client_id == $k->id ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>" data-value="<?= $k->id ?>"><?= htmlspecialchars($k->nama_klien) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <input type="hidden" name="client_id" id="client_id" value="<?= $doc->client_id ?>">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_mulai" value="<?= $doc->tanggal_mulai ?>" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                        </div>

                        <!-- Jenis Dokumen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis dokumen <span class="text-red-500">*</span></label>
                            <div class="relative custom-dropdown" data-target="jenis_dokumen">
                                <button type="button" class="dropdown-btn flex items-center justify-between w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                    <span class="dropdown-text truncate text-gray-900"><?= htmlspecialchars($doc->jenis_dokumen) ?></span>
                                    <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                    <div class="p-2 border-b border-gray-100">
                                        <div class="relative">
                                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            <input type="text" class="dropdown-search w-full pl-9 pr-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari jenis..." autocomplete="off">
                                        </div>
                                    </div>
                                    <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-56 overflow-y-auto">
                                        <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih jenis</li>
                                        <?php foreach($jenis_dokumen as $j): ?>
                                            <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors <?= $doc->jenis_dokumen == $j->name ? 'bg-red-50 text-[#9f2b2b] font-bold' : '' ?>" data-value="<?= htmlspecialchars($j->name) ?>"><?= htmlspecialchars($j->name) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <input type="hidden" name="jenis_dokumen" id="jenis_dokumen" value="<?= htmlspecialchars($doc->jenis_dokumen) ?>">
                            </div>
                        </div>

                        <!-- Project -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Project <span class="text-red-500">*</span></label>
                            <div class="relative custom-dropdown" data-target="project_id">
                                <button type="button" class="dropdown-btn flex items-center justify-between w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm hover:border-gray-400 focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all cursor-pointer">
                                    <span class="dropdown-text truncate text-gray-900"><?= htmlspecialchars($doc->nama_project) ?></span>
                                    <svg class="w-4 h-4 text-gray-500 pointer-events-none transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div class="dropdown-menu absolute z-50 hidden mt-1 w-full bg-white border border-gray-100 rounded-lg shadow-xl overflow-hidden transform opacity-0 scale-95">
                                    <div class="p-2 border-b border-gray-100">
                                        <div class="relative">
                                            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                            <input type="text" class="dropdown-search w-full pl-9 pr-2 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Cari project..." autocomplete="off">
                                        </div>
                                    </div>
                                    <ul class="dropdown-list py-1 text-sm text-gray-700 max-h-56 overflow-y-auto">
                                        <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih project</li>
                                        <li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors bg-red-50 text-[#9f2b2b] font-bold" data-value="<?= $doc->project_id ?>"><?= htmlspecialchars($doc->nama_project) ?></li>
                                    </ul>
                                </div>
                                <input type="hidden" name="project_id" id="project_id" value="<?= $doc->project_id ?>">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Berakhir <span class="text-red-500">*</span></label>
                            <input type="date" name="tanggal_berakhir" value="<?= $doc->tanggal_berakhir ?>" class="w-full px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tombol Aksi -->
            <div class="shrink-0 flex justify-end gap-3 pt-3 md:pt-4 mt-1 border-t border-gray-200">
                <a href="<?= site_url('dokumen') ?>" class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                <button type="submit" id="btnSimpan" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 disabled:opacity-50">Simpan Perubahan</button>
            </div>

        </form>
    </div>

    <script>
        function getDeviceId() {
            let id = localStorage.getItem('tridatu_device_id');
            if (!id) {
                id = crypto.randomUUID();
                localStorage.setItem('tridatu_device_id', id);
                document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;
            }
            return id;
        }
        document.getElementById('device_id').value = getDeviceId();

        async function hitungHash(file) {
            const arrayBuffer = await file.arrayBuffer();
            const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
            return Array.from(new Uint8Array(hashBuffer)).map(b => b.toString(16).padStart(2, '0')).join('');
        }

        const fileInput = document.getElementById('fileInput');
        const dropzone = document.getElementById('dropzone');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileName');
        const fileSizeText = document.getElementById('fileSize');

        const fileNameLocal = document.getElementById('fileNameLocal');
        const fileHashLocal = document.getElementById('file_hash_local');
        const fileSizeLocal = document.getElementById('file_size_local');
        const fileTypeLocal = document.getElementById('file_type_local');

        fileInput.addEventListener('change', async function() {
            const file = this.files[0];
            if (!file) return;

            const mb = (file.size / 1048576).toFixed(2);
            const ext = file.name.split('.').pop().toUpperCase();

            fileNameText.textContent = 'Menghitung keamanan file...';
            fileSizeText.textContent = `${mb} MB • ${ext}`;
            fileNameLocal.value = file.name;
            fileSizeLocal.value = file.size;
            fileTypeLocal.value = file.type;

            fileHashLocal.value = await hitungHash(file);
            fileNameText.textContent = file.name;

            dropzone.classList.add('hidden');
            filePreview.classList.remove('hidden');
        });

        function resetFile() {
            fileInput.value = '';
            [fileNameLocal, fileHashLocal, fileSizeLocal, fileTypeLocal].forEach(el => el.value = '');
            dropzone.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }

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

        function closeDropdown(el) {
            const menu = el.querySelector('.dropdown-menu');
            const icon = el.querySelector('.dropdown-btn svg');
            if (!menu) return;

            el.classList.remove('open', 'drop-up');
            menu.classList.add('opacity-0', 'scale-95');
            menu.classList.remove('opacity-100', 'scale-100');
            if (icon) icon.classList.remove('rotate-180');

            clearTimeout(menu._hideTimer);
            menu._hideTimer = setTimeout(() => {
                if (!el.classList.contains('open')) menu.classList.add('hidden');
            }, 200);
        }

        function openDropdown(el) {
            document.querySelectorAll('.custom-dropdown').forEach(other => {
                if (other !== el) closeDropdown(other);
            });

            const menu = el.querySelector('.dropdown-menu');
            const icon = el.querySelector('.dropdown-btn svg');
            const search = el.querySelector('.dropdown-search');
            const list = el.querySelector('.dropdown-list');
            const btn = el.querySelector('.dropdown-btn');
            if (!menu) return;

            if (search) search.value = '';
            list.querySelectorAll('li').forEach(li => li.classList.remove('hidden'));
            list.querySelector('.no-result')?.remove();

            menu.style.visibility = 'hidden';
            menu.classList.remove('hidden');
            const rect = btn.getBoundingClientRect();
            const h = menu.offsetHeight;
            const below = window.innerHeight - rect.bottom;
            el.classList.toggle('drop-up', below < h + 16 && rect.top > below);
            menu.classList.add('hidden');
            menu.style.visibility = '';

            clearTimeout(menu._hideTimer);
            el.classList.add('open');
            menu.classList.remove('hidden');
            void menu.offsetWidth;
            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
            if (icon) icon.classList.add('rotate-180');

            if (search) search.focus({ preventScroll: true });
        }

        document.querySelectorAll('.custom-dropdown').forEach(dd => {
            const btn = dd.querySelector('.dropdown-btn');
            const search = dd.querySelector('.dropdown-search');
            const list = dd.querySelector('.dropdown-list');

            btn.addEventListener('click', e => {
                e.stopPropagation();
                if (dd.classList.contains('open')) closeDropdown(dd);
                else openDropdown(dd);
            });

            dd.addEventListener('click', e => {
                const li = e.target.closest('li');
                if (!li || li.dataset.value === undefined) return;
                setDropdownValue(dd.dataset.target, li.dataset.value, li.textContent.trim());
                closeDropdown(dd);
            });

            if (search) {
                search.addEventListener('input', e => {
                    const q = e.target.value.toLowerCase().trim();
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
                        li.className = 'no-result px-4 py-2.5 text-gray-400 italic text-center cursor-default';
                        li.textContent = 'Tidak ada hasil';
                        list.appendChild(li);
                    }
                });
                search.addEventListener('click', e => e.stopPropagation());
                search.addEventListener('keydown', e => {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const first = [...list.querySelectorAll('li')].find(li => !li.classList.contains('hidden') && li.dataset.value);
                        if (first) {
                            setDropdownValue(dd.dataset.target, first.dataset.value, first.textContent.trim());
                            closeDropdown(dd);
                            btn.focus();
                        }
                    } else if (e.key === 'Escape') {
                        closeDropdown(dd);
                        btn.focus();
                    }
                });
            }
        });

        document.addEventListener('click', e => {
            document.querySelectorAll('.custom-dropdown').forEach(dd => {
                if (dd.contains(e.target)) return;
                closeDropdown(dd);
            });
        });

        document.getElementById('client_id').addEventListener('change', async function() {
            const clientId = this.value;
            const dd = document.querySelector('.custom-dropdown[data-target="project_id"]');
            const list = dd.querySelector('.dropdown-list');
            const search = dd.querySelector('.dropdown-search');

            if (clientId == '<?= $doc->client_id ?>') return;

            setDropdownValue('project_id', '', 'Memuat project...');
            list.innerHTML = '<li class="px-4 py-2.5 text-gray-400 italic cursor-default">Memuat...</li>';
            if (search) search.value = '';

            if (!clientId) {
                setDropdownValue('project_id', '', 'Pilih Klien Terlebih Dahulu');
                list.innerHTML = '<li class="px-4 py-2.5 text-gray-400 italic cursor-default">Pilih Klien Terlebih Dahulu</li>';
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
                    list.innerHTML = '<li class="px-4 py-2.5 text-gray-400 italic cursor-default">Klien ini belum punya project</li>';
                    return;
                }

                setDropdownValue('project_id', '', 'Pilih project');
                list.innerHTML = '<li class="px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors font-medium" data-value="">Pilih project</li>';
                projects.forEach(p => {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-2.5 hover:bg-red-50 hover:text-[#9f2b2b] cursor-pointer transition-colors';
                    li.dataset.value = p.id;
                    li.textContent = p.nama_project;
                    list.appendChild(li);
                });
            } catch (err) {
                console.error(err);
                setDropdownValue('project_id', '', 'Gagal memuat project');
                list.innerHTML = '<li class="px-4 py-2.5 text-red-500 italic cursor-default">Gagal memuat project</li>';
            }
        });

        let currentStorage = '<?= $doc->storage_type ?>';

        function toggleStorage(type) {
            currentStorage = type;
            const text = document.getElementById('dropzoneText');
            text.innerHTML = type === 'local'
                ? "Tarik file baru ke sini atau pilih dari perangkat (Disimpan lokal)"
                : "Tarik file baru ke sini atau pilih dari perangkat";
        }

        // --- FORM SUBMIT (INTEGRASI LOCAL SERVICE) ---
        document.getElementById('formDokumen').addEventListener('submit', async function(e) {
            if (currentStorage === 'cloud') return;

            e.preventDefault();
            const btnSimpan = document.getElementById('btnSimpan');

            // KONDISI 1: Tetap di Local, tapi TIDAK memilih file baru (Hanya edit data)
            if (!fileInput.files[0] && '<?= $doc->storage_type ?>' === 'local') {
                btnSimpan.textContent = 'Menyimpan Data...';
                btnSimpan.disabled = true;

                const formData = new FormData(this);
                formData.delete('file_dokumen');
                formData.set('device_id', '<?= $doc->device_id ?>');

                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const data = await res.json();

                    if (data.status) {
                        window.location.href = data.redirect || '<?= site_url("dokumen") ?>';
                    } else {
                        alert(data.msg);
                        btnSimpan.textContent = 'Simpan Perubahan';
                        btnSimpan.disabled = false;
                    }
                } catch(err) {
                    alert('Terjadi kesalahan sistem.');
                    btnSimpan.textContent = 'Simpan Perubahan';
                    btnSimpan.disabled = false;
                }
                return;
            }

            // KONDISI 2: Pindah ke Local, atau Ganti File (Kirim ke Local Agent)
            if (!fileInput.files[0]) {
                return alert('Pilih file terlebih dahulu!');
            }

            const file = fileInput.files[0];
            btnSimpan.textContent = 'Menghubungi Local Agent...';
            btnSimpan.disabled = true;

            try {
                // 1. Cek apakah Local Agent menyala
                try {
                    await fetch('http://localhost:8080/ping', { mode: 'cors' });
                } catch (err) {
                    throw new Error('Aplikasi TridatuAgent.exe belum dibuka. Silakan buka aplikasinya terlebih dahulu di laptop Anda.');
                }

                // 2. Kirim file ke Local Agent untuk disimpan di hardisk laptop
                btnSimpan.textContent = 'Menyimpan ke Folder...';
                const resAgent = await fetch(`http://localhost:8080/save?filename=${encodeURIComponent(file.name)}`, {
                    method: 'POST',
                    body: file 
                });

                const dataAgent = await resAgent.json();
                if (!dataAgent.success) throw new Error(dataAgent.error || 'Gagal menyimpan file di laptop.');

                // 3. Jika sukses, simpan metadatanya ke Database
                btnSimpan.textContent = 'Menyimpan Data...';
                const formData = new FormData(this);
                formData.delete('file_dokumen'); 
                formData.set('storage_type', 'local'); 

                const res = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const data = await res.json();

                if (data.status) {
                    window.location.href = data.redirect || '<?= site_url("dokumen") ?>';
                } else {
                    throw new Error(data.msg || 'Gagal menyimpan data ke database.');
                }

            } catch (error) {
                alert(error.message || error);
                btnSimpan.textContent = 'Simpan Perubahan';
                btnSimpan.disabled = false;
            }
        });
    </script>
</body>
</html>