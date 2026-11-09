<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6">
    
    <!-- Header Halaman -->
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Pengaturan</h2>
    </div>

    <!-- Notifikasi -->
    <?php if($this->session->flashdata('success')): ?>
        <div class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('success'); ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    <?php endif; ?>
    <?php if($this->session->flashdata('error')): ?>
        <div class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-5 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
        </div>
    <?php endif; ?>

    <!-- Baris Atas: Informasi Perusahaan & Jenis Dokumen -->
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 mb-6">
        
        <!-- Kiri: Informasi Perusahaan (7 Kolom) -->
        <div class="xl:col-span-7 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-1">Informasi Perusahaan</h3>
            <p class="text-sm text-gray-500 mb-6">Identitas Tridatu yang dipakai pada dokumen penawaran dan invoice.</p>
            
            <form action="<?= site_url('pengaturan/update_info') ?>" method="POST" enctype="multipart/form-data">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Perusahaan</label>
                        <input type="text" name="nama_perusahaan" value="<?= htmlspecialchars($settings->nama_perusahaan ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($settings->email ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">No. Telepon</label>
                        <input type="text" name="no_telp" value="<?= htmlspecialchars($settings->no_telp ?? '') ?>" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Logo Perusahaan (Opsional)</label>
                        <input type="file" name="logo" accept=".jpg,.jpeg,.png" class="w-full text-sm text-gray-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 transition-all border border-gray-300 rounded-lg cursor-pointer">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Alamat</label>
                        <textarea name="alamat" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" required><?= htmlspecialchars($settings->alamat ?? '') ?></textarea>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Kanan: Jenis Dokumen (5 Kolom) -->
        <div class="xl:col-span-5 bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex flex-col">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h3 class="text-xl font-bold text-gray-900 mb-1">Jenis Dokumen</h3>
                    <p class="text-sm text-gray-500">Buat jenis dokumen</p>
                </div>
                <button type="button" onclick="bukaModalJenis()" class="flex items-center gap-1.5 px-4 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Jenis
                </button>
            </div>

            <!-- List Jenis Dokumen -->
            <div class="border border-gray-200 rounded-lg overflow-hidden flex-1 flex flex-col">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-600">Nama Jenis Dokumen</span>
                    <span class="text-sm font-semibold text-gray-600">Aksi</span>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <?php if(empty($types)): ?>
                        <div class="p-6 text-center text-gray-500 text-sm">Belum ada jenis dokumen.</div>
                    <?php else: ?>
                        <?php foreach($types as $t): ?>
                        <div class="px-4 py-3 border-b border-gray-100 last:border-0 flex justify-between items-center hover:bg-gray-50 transition-colors">
                            <span class="text-sm text-gray-800 font-medium"><?= htmlspecialchars($t->name) ?></span>
                            <div class="flex gap-1.5">
                                <button type="button" onclick="editJenis(<?= $t->id ?>, '<?= htmlspecialchars($t->name, ENT_QUOTES) ?>')" class="p-1.5 border border-gray-300 text-gray-500 hover:text-blue-600 hover:border-blue-300 rounded-lg transition-all" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                                <a href="<?= site_url('pengaturan/hapus_jenis/'.$t->id) ?>" onclick="return confirm('Hapus jenis dokumen ini?')" class="p-1.5 border border-red-200 text-red-500 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="bg-gray-50 px-4 py-2 border-t border-gray-200 text-xs text-gray-500 flex justify-between items-center">
                    <span>Menampilkan <?= count($types) ?> data</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris Bawah: Kop Surat & Tanda Tangan -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-xl font-bold text-gray-900 mb-1">Kop surat dan tanda tangan</h3>
        <p class="text-sm text-gray-500 mb-6">Dipakai otomatis pada dokumen template jika opsi kop surat atau tanda tangan dicentang.</p>

        <form action="<?= site_url('pengaturan/update_assets') ?>" method="POST" enctype="multipart/form-data">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-6">
                
                <!-- Kop Surat -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Kop surat</label>
                    <?php if(!empty($settings->kop_surat) && file_exists(FCPATH . $settings->kop_surat)): ?>
                        <div class="mb-4">
                            <img src="<?= base_url($settings->kop_surat) ?>" alt="Kop Surat" class="w-full h-48 object-contain bg-gray-50 border border-gray-200 rounded-lg p-2">
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= basename($settings->kop_surat) ?></p>
                                <p class="text-xs text-gray-500">PNG atau JPG ; maksimal 2 MB</p>
                            </div>
                            <div class="flex gap-2">
                                <label class="cursor-pointer px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Ganti
                                    <input type="file" name="kop_surat" class="hidden" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                                </label>
                                <a href="<?= site_url('pengaturan/hapus_asset/kop_surat') ?>" onclick="return confirm('Hapus kop surat?')" class="px-4 py-2 border border-red-200 text-red-600 bg-red-50 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Placeholder Kosong -->
                        <label class="w-full h-48 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-500 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
                            <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium">Pilih file Kop Surat</span>
                            <span class="text-xs mt-1">PNG atau JPG ; maksimal 2 MB</span>
                            <input type="file" name="kop_surat" class="hidden" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                        </label>
                    <?php endif; ?>
                </div>

                <!-- Tanda Tangan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Tanda Tangan</label>
                    <?php if(!empty($settings->ttd) && file_exists(FCPATH . $settings->ttd)): ?>
                        <div class="mb-4">
                            <img src="<?= base_url($settings->ttd) ?>" alt="Tanda Tangan" class="w-full h-48 object-contain bg-gray-50 border border-gray-200 rounded-lg p-2">
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-gray-800"><?= basename($settings->ttd) ?></p>
                                <p class="text-xs text-gray-500">PNG atau JPG ; maksimal 2 MB</p>
                            </div>
                            <div class="flex gap-2">
                                <label class="cursor-pointer px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    Ganti
                                    <input type="file" name="ttd" class="hidden" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                                </label>
                                <a href="<?= site_url('pengaturan/hapus_asset/ttd') ?>" onclick="return confirm('Hapus tanda tangan?')" class="px-4 py-2 border border-red-200 text-red-600 bg-red-50 text-sm font-medium rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    Hapus
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Placeholder Kosong -->
                        <label class="w-full h-48 border-2 border-dashed border-gray-300 rounded-lg flex flex-col items-center justify-center text-gray-500 bg-gray-50 cursor-pointer hover:bg-gray-100 transition-colors">
                            <svg class="w-8 h-8 mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            <span class="text-sm font-medium">Pilih file Tanda Tangan</span>
                            <span class="text-xs mt-1">PNG atau JPG ; maksimal 2 MB</span>
                            <input type="file" name="ttd" class="hidden" accept=".jpg,.jpeg,.png" onchange="this.form.submit()">
                        </label>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>

<!-- MODAL JENIS DOKUMEN -->
<div id="modalJenis" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="tutupModalJenis()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10" id="modalJenisContent">
        <h3 class="text-xl font-bold text-gray-900 mb-4" id="modalJenisTitle">Tambah Jenis Dokumen</h3>
        
        <form id="formJenis" onsubmit="simpanJenis(event)">
            <input type="hidden" id="jenisId" value="">
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Jenis Dokumen</label>
                <input type="text" id="jenisName" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b]" placeholder="Contoh: MOU, NDA, Kontrak" required>
            </div>
            
            <div class="flex justify-end gap-3">
                <button type="button" onclick="tutupModalJenis()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors text-sm">Batal</button>
                <button type="submit" class="px-5 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white font-medium rounded-lg transition-colors shadow-sm text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT UNTUK MODAL JENIS DOKUMEN -->
<script>
    const modalJenis = document.getElementById('modalJenis');
    const modalJenisContent = document.getElementById('modalJenisContent');
    const formJenis = document.getElementById('formJenis');
    const jenisId = document.getElementById('jenisId');
    const jenisName = document.getElementById('jenisName');
    const modalJenisTitle = document.getElementById('modalJenisTitle');

    function bukaModalJenis() {
        jenisId.value = '';
        jenisName.value = '';
        modalJenisTitle.innerText = 'Tambah Jenis Dokumen';
        modalJenis.classList.remove('hidden');
        modalJenis.classList.add('flex');
        setTimeout(() => {
            modalJenisContent.classList.remove('scale-95', 'opacity-0');
            modalJenisContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function editJenis(id, name) {
        jenisId.value = id;
        jenisName.value = name;
        modalJenisTitle.innerText = 'Edit Jenis Dokumen';
        modalJenis.classList.remove('hidden');
        modalJenis.classList.add('flex');
        setTimeout(() => {
            modalJenisContent.classList.remove('scale-95', 'opacity-0');
            modalJenisContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalJenis() {
        modalJenisContent.classList.remove('scale-100', 'opacity-100');
        modalJenisContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modalJenis.classList.add('hidden');
            modalJenis.classList.remove('flex');
        }, 300);
    }

    async function simpanJenis(e) {
        e.preventDefault();
        const formData = new FormData();
        formData.append('id', jenisId.value);
        formData.append('name', jenisName.value);

        try {
            const res = await fetch('<?= site_url('pengaturan/simpan_jenis') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json();
            
            if (data.status) {
                window.location.reload();
            } else {
                alert(data.msg);
            }
        } catch (err) {
            alert('Terjadi kesalahan sistem.');
        }
    }
</script>