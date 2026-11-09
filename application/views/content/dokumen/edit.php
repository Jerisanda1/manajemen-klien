<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/tailwind.css'); ?>">
</head>
<body class="bg-[#f4f6f9] font-sans text-gray-800 antialiased min-h-screen p-4 md:p-8">

    <div class="max-w-[1100px] mx-auto">
        
        <!-- Header -->
        <div class="mb-6">
            <a href="<?= site_url('dokumen') ?>" class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-900 mb-2 transition-colors">
                <svg class="w-5 h-5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Dokumen Kerja Sama</h2>
        </div>

        <!-- Notifikasi Error -->
        <?php if($this->session->flashdata('error')): ?>
            <div class="bg-red-50 text-red-600 p-4 rounded-xl mb-5 border border-red-200 text-sm">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('dokumen/update') ?>" method="POST" enctype="multipart/form-data" id="formDokumen">
            <input type="hidden" name="id" value="<?= $doc->id ?>">
            
            <!-- HIDDEN INPUTS UNTUK METADATA FILE LOKAL -->
            <input type="hidden" name="device_id" id="device_id" value="">
            <input type="hidden" name="file_name_local" id="fileNameLocal" value="">
            <input type="hidden" name="file_hash_local" id="file_hash_local" value="">
            <input type="hidden" name="file_size_local" id="file_size_local" value="">
            <input type="hidden" name="file_type_local" id="file_type_local" value="">

            <!-- Card 1: Pilihan Penyimpanan & Upload File -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
                <div class="flex flex-col lg:flex-row gap-6">
                    
                    <!-- Kiri: Pilihan Server / Perangkat -->
                    <div class="w-full lg:w-1/2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">Simpan File di</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            
                            <!-- Opsi Server Sistem -->
                            <label class="cursor-pointer">
                                <input type="radio" name="storage_type" value="cloud" class="peer sr-only" <?= $doc->storage_type === 'cloud' ? 'checked' : '' ?> onchange="toggleStorage('cloud')">
                                <div class="p-4 border rounded-xl peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all hover:bg-gray-50 h-full">
                                    <div class="flex items-center gap-2 mb-1">
                                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                                        <span class="font-bold text-sm text-gray-900">Server Sistem</span>
                                    </div>
                                    <p class="text-xs text-gray-500">File diunggah ke sistem. Bisa dibuka di semua perangkat.</p>
                                </div>
                            </label>

                            <!-- Opsi Perangkat Ini -->
                            <label class="cursor-pointer">
                                <input type="radio" name="storage_type" value="local" class="peer sr-only" <?= $doc->storage_type === 'local' ? 'checked' : '' ?> onchange="toggleStorage('local')">
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

                    <!-- Kanan: Area Upload File -->
                    <div class="w-full lg:w-1/2">
                        <label class="block text-sm font-bold text-gray-800 mb-3">File dokumen (Biarkan kosong jika tidak diganti)</label>
                        
                        <!-- Info File Lama -->
                        <div class="mb-3 p-3 bg-gray-50 border border-gray-200 rounded-lg text-sm text-gray-600 flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            File saat ini: <span class="font-bold text-gray-800"><?= htmlspecialchars($doc->file_name) ?></span>
                        </div>

                        <!-- Area Dropzone -->
                        <div id="dropzone" class="border-2 border-dashed border-gray-300 rounded-xl p-6 flex flex-col items-center justify-center bg-gray-50 hover:bg-gray-100 transition-colors cursor-pointer relative">
                            <input type="file" name="file_dokumen" id="fileInput" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <svg class="w-8 h-8 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            <p class="text-sm text-gray-600 font-medium text-center mb-2" id="dropzoneText">Tarik file baru ke sini atau pilih dari perangkat</p>
                            <span class="px-3 py-1.5 bg-white border border-gray-300 rounded text-xs font-medium text-gray-700 shadow-sm">Pilih file baru</span>
                        </div>

                        <!-- Preview File Baru -->
                        <div id="filePreview" class="hidden border border-gray-300 rounded-xl p-4 bg-white flex items-center justify-between mt-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <div>
                                    <p id="fileName" class="text-sm font-bold text-gray-800 truncate max-w-[200px]">Nama_File.pdf</p>
                                    <p id="fileSize" class="text-xs text-gray-500">1.2 MB • PDF</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="resetFile()" class="px-3 py-1.5 border border-red-200 bg-red-50 rounded-lg text-xs font-medium text-red-600 hover:bg-red-100">Batal Ganti</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Card 2: Data Dokumen -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
                <h3 class="text-lg font-bold text-gray-900 mb-5">Data Dokumen</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <!-- Nama Dokumen -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Nama dokumen <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_dokumen" value="<?= htmlspecialchars($doc->nama_dokumen) ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                    
                    <!-- Klien -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Klien <span class="text-red-500">*</span></label>
                        <select name="client_id" id="selectKlien" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                            <option value="">Pilih klien</option>
                            <?php foreach($klien as $k): ?>
                                <option value="<?= $k->id ?>" <?= $doc->client_id == $k->id ? 'selected' : '' ?>><?= htmlspecialchars($k->nama_klien) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Tanggal Mulai -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_mulai" value="<?= $doc->tanggal_mulai ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                    
                    <!-- Jenis Dokumen (DINAMIS DARI DATABASE) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Jenis dokumen <span class="text-red-500">*</span></label>
                        <select name="jenis_dokumen" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                            <option value="">Pilih Jenis</option>
                            <?php foreach($jenis_dokumen as $j): ?>
                                <option value="<?= htmlspecialchars($j->name) ?>" <?= $doc->jenis_dokumen == $j->name ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($j->name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <!-- Project (Cascading) -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Project <span class="text-red-500">*</span></label>
                        <select name="project_id" id="selectProject" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                            <option value="<?= $doc->project_id ?>"><?= htmlspecialchars($doc->nama_project) ?></option>
                        </select>
                    </div>
                    
                    <!-- Tanggal Berakhir -->
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1.5">Tanggal Berakhir <span class="text-red-500">*</span></label>
                        <input type="date" name="tanggal_berakhir" value="<?= $doc->tanggal_berakhir ?>" class="w-full px-3 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3">
                <a href="<?= site_url('dokumen') ?>" class="px-6 py-2.5 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">Batal</a>
                <button type="submit" id="btnSimpan" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-md shadow-[#9f2b2b]/20 disabled:opacity-50">Simpan Perubahan</button>
            </div>

        </form>
    </div>

    <script>
        // 1. UTILITY
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

        function bukaDB() {
            return new Promise((resolve, reject) => {
                const req = indexedDB.open('TridatuFileSystem', 1);
                req.onupgradeneeded = () => req.result.createObjectStore('handles');
                req.onsuccess = () => resolve(req.result);
                req.onerror = () => reject(req.error);
            });
        }
        async function simpanHandle(id, handle) {
            const db = await bukaDB();
            return new Promise((resolve, reject) => {
                const tx = db.transaction('handles', 'readwrite');
                tx.objectStore('handles').put(handle, String(id));
                tx.oncomplete = resolve;
                tx.onerror = () => reject(tx.error);
            });
        }

        // 2. PREVIEW FILE
        const fileInput = document.getElementById('fileInput');
        const dropzone = document.getElementById('dropzone');
        const filePreview = document.getElementById('filePreview');
        const fileNameText = document.getElementById('fileName');
        const fileSizeText = document.getElementById('fileSize');
        
        const fileNameLocal = document.getElementById('fileNameLocal');
        const fileHashLocal = document.getElementById('file_hash_local');
        const fileSizeLocal = document.getElementById('file_size_local');
        const fileTypeLocal = document.getElementById('file_type_local');

        fileInput.addEventListener('change', async function(e) {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const sizeInMB = (file.size / (1024 * 1024)).toFixed(2);
                const ext = file.name.split('.').pop().toUpperCase();
                
                fileNameText.textContent = file.name;
                fileSizeText.textContent = `${sizeInMB} MB • ${ext}`;

                fileNameLocal.value = file.name;
                fileSizeLocal.value = file.size;
                fileTypeLocal.value = file.type;

                fileNameText.textContent = "Menghitung keamanan file...";
                const hash = await hitungHash(file);
                fileHashLocal.value = hash;
                fileNameText.textContent = file.name;

                dropzone.classList.add('hidden');
                filePreview.classList.remove('hidden');
            }
        });

        function resetFile() {
            fileInput.value = '';
            fileNameLocal.value = '';
            fileHashLocal.value = '';
            fileSizeLocal.value = '';
            fileTypeLocal.value = '';
            dropzone.classList.remove('hidden');
            filePreview.classList.add('hidden');
        }

        // 3. CASCADING PROJECT (Agar saat ganti klien, project ter-reset)
        document.getElementById('selectKlien').addEventListener('change', function() {
            const clientId = this.value;
            const projectSelect = document.getElementById('selectProject');
            projectSelect.innerHTML = '<option value="">Memuat project...</option>';
            
            if (!clientId) {
                projectSelect.innerHTML = '<option value="">Pilih Klien Terlebih Dahulu</option>';
                return;
            }

            fetch('<?= site_url("dokumen/get_projects") ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'client_id=' + clientId
            })
            .then(response => response.json())
            .then(res => {
                projectSelect.innerHTML = '<option value="">Pilih Project</option>';
                const projects = res.data || res; 
                projects.forEach(project => {
                    projectSelect.innerHTML += `<option value="${project.id}">${project.nama_project}</option>`;
                });
            });
        });

        // 4. SUBMIT FORM
        let currentStorage = '<?= $doc->storage_type ?>';

        function toggleStorage(type) {
            currentStorage = type;
            const text = document.getElementById('dropzoneText');
            if(type === 'local') {
                text.innerHTML = "Tarik file baru ke sini atau pilih dari perangkat (Disimpan lokal)";
            } else {
                text.innerHTML = "Tarik file baru ke sini atau pilih dari perangkat";
            }
        }

        document.getElementById('formDokumen').addEventListener('submit', async function(e) {
            // Jika mode Cloud, biarkan form submit biasa
            if (currentStorage === 'cloud') {
                return; 
            }

            e.preventDefault(); // Cegat submit untuk mode local
            const btnSimpan = document.getElementById('btnSimpan');
            
            // KONDISI 1: Tetap di Local, tapi TIDAK memilih file baru
            if (!fileInput.files[0] && '<?= $doc->storage_type ?>' === 'local') {
                btnSimpan.innerHTML = 'Menyimpan Data...';
                btnSimpan.disabled = true;
                
                const formData = new FormData(this); 
                formData.delete('file_dokumen'); // Pastikan tidak ada file fisik yang terkirim
                formData.set('device_id', '<?= $doc->device_id ?>'); // Pertahankan device_id lama

                try {
                    const res = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const responseData = await res.json();

                    if (responseData.status) {
                        // PERUBAHAN: Tambah parameter URL
                        window.location.href = '<?= site_url("dokumen?edit_success=1") ?>';
                    } else {
                        alert(responseData.msg);
                        btnSimpan.innerHTML = 'Simpan Perubahan';
                        btnSimpan.disabled = false;
                    }
                } catch(err) {
                    alert('Terjadi kesalahan sistem.');
                    btnSimpan.innerHTML = 'Simpan Perubahan';
                    btnSimpan.disabled = false;
                }
                return;
            }

            // KONDISI 2: Pindah ke Local, atau Ganti File di Local (Butuh File Picker)
            if (!fileInput.files[0]) {
                return alert('Pilih file terlebih dahulu!');
            }

            const file = fileInput.files[0];
            btnSimpan.innerHTML = 'Menyimpan ke Perangkat...';
            btnSimpan.disabled = true;

            try {
                if (!window.showDirectoryPicker) {
                    throw new Error('Browser tidak mendukung Local Storage. Gunakan Chrome/Edge Desktop.');
                }

                const directoryHandle = await window.showDirectoryPicker({ mode: 'readwrite' });
                const fileHandle = await directoryHandle.getFileHandle(file.name, { create: true });
                
                const writable = await fileHandle.createWritable();
                await writable.write(file); 
                await writable.close();

                btnSimpan.innerHTML = 'Menyimpan Data...';
                const formData = new FormData(this); 
                formData.delete('file_dokumen'); 

                const res = await fetch(this.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                const responseData = await res.json();

                if (responseData.status) {
                    await simpanHandle(responseData.id, fileHandle);
                    // PERUBAHAN: Tambah parameter URL
                    window.location.href = '<?= site_url("dokumen?edit_success=1") ?>';
                } else {
                    throw new Error(responseData.msg);
                }

            } catch (error) {
                if (error.name === 'AbortError') {
                    btnSimpan.innerHTML = 'Simpan Perubahan';
                    btnSimpan.disabled = false;
                } else {
                    alert(error.message || error);
                    btnSimpan.innerHTML = 'Simpan Perubahan';
                    btnSimpan.disabled = false;
                }
            }
        });
    </script>
</body>
</html>