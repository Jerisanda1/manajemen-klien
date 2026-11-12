<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6 lg:p-8">
    
    <!-- Header Halaman -->
    <div class="mb-8 max-w-[1000px] mx-auto">
        <h2 class="text-3xl font-bold text-gray-900 tracking-tight">Pengaturan Sistem</h2>
        <p class="text-sm text-gray-500 mt-1">Kelola profil akun, identitas perusahaan, dan aset dokumen Anda di sini.</p>
    </div>

    <!-- Notifikasi (Auto-fade) -->
    <div class="max-w-[1000px] mx-auto">
        <?php if($this->session->flashdata('success')): ?>
            <div id="alert-success" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-6 shadow-sm transition-all duration-300">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium text-sm"><?= $this->session->flashdata('success'); ?></span>
                </div>
                <button type="button" onclick="document.getElementById('alert-success').remove()" class="text-emerald-500 hover:text-emerald-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>
        <?php if($this->session->flashdata('error')): ?>
            <div id="alert-error" class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-6 shadow-sm transition-all duration-300">
                <div class="flex items-center gap-3">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
                </div>
                <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Layout Pengaturan: Top Bar Tabs & Konten -->
    <div class="max-w-[1000px] mx-auto">
        
        <!-- Top Bar Navigation -->
        <div class="mb-6 border-b border-slate-200">
            <nav class="flex gap-6 sm:gap-8 overflow-x-auto hide-scrollbar" aria-label="Tabs">
                <button onclick="switchTab('akun')" id="btn-tab-akun" class="tab-btn flex items-center gap-2 py-4 text-sm border-b-2 border-[#9f2b2b] text-[#9f2b2b] font-semibold transition-colors whitespace-nowrap focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profil & Akun
                </button>
                <button onclick="switchTab('perusahaan')" id="btn-tab-perusahaan" class="tab-btn flex items-center gap-2 py-4 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-800 font-medium transition-colors whitespace-nowrap focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    Info Perusahaan
                </button>
                <button onclick="switchTab('aset')" id="btn-tab-aset" class="tab-btn flex items-center gap-2 py-4 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-800 font-medium transition-colors whitespace-nowrap focus:outline-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    Aset & Dokumen
                </button>
            </nav>
        </div>

        <!-- Konten Area -->
        <div>
            
            <!-- TAB 1: PROFIL & AKUN -->
            <div id="tab-akun" class="tab-content block animate-fade-in">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                    <div class="mb-6 border-b border-slate-100 pb-5">
                        <h3 class="text-xl font-bold text-gray-900">Profil & Keamanan</h3>
                        <p class="text-sm text-gray-500 mt-1">Perbarui username dan password untuk login ke sistem.</p>
                    </div>

                    <form action="<?= site_url('pengaturan/update_account') ?>" method="POST" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                            <input type="text" name="username" value="<?= htmlspecialchars($user->username ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                        </div>
                        
                        <div class="border-t border-slate-100 pt-6 mt-6">
                            <h4 class="text-sm font-semibold text-gray-900 mb-4">Ubah Password <span class="text-gray-400 font-normal">(Opsional)</span></h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                    <input type="password" name="password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="Biarkan kosong jika tidak diubah">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                                    <input type="password" name="confirm_password" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="Ketik ulang password baru">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4 border-t border-slate-100 mt-6">
                            <button type="submit" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                                Simpan Akun
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 2: INFORMASI PERUSAHAAN -->
            <div id="tab-perusahaan" class="tab-content hidden animate-fade-in">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                    <div class="mb-6 border-b border-slate-100 pb-5">
                        <h3 class="text-xl font-bold text-gray-900">Informasi Perusahaan</h3>
                        <p class="text-sm text-gray-500 mt-1">Identitas Tridatu yang dipakai pada dokumen penawaran dan invoice.</p>
                    </div>

                    <form action="<?= site_url('pengaturan/update_info') ?>" method="POST" enctype="multipart/form-data">
                        
                        <!-- Logo Preview Section -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 mb-8 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                            <div class="w-24 h-24 shrink-0 bg-white border border-slate-200 rounded-xl flex items-center justify-center overflow-hidden shadow-sm relative group">
                                <?php if(!empty($settings->logo) && file_exists(FCPATH . $settings->logo)): ?>
                                    <img id="logo-preview" src="<?= base_url($settings->logo) ?>" alt="Logo" class="w-full h-full object-contain p-2">
                                <?php else: ?>
                                    <img id="logo-preview" src="" alt="Logo" class="w-full h-full object-contain p-2 hidden">
                                    <div id="logo-placeholder" class="text-slate-300 flex flex-col items-center">
                                        <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-gray-900 mb-1">Logo Perusahaan</label>
                                <p class="text-xs text-gray-500 mb-3">Format PNG atau JPG. Maksimal 2MB.</p>
                                <label class="cursor-pointer inline-flex items-center gap-2 px-4 py-2 border border-slate-300 bg-white text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    Pilih File Logo
                                    <input type="file" name="logo" id="logo-input" accept=".jpg,.jpeg,.png" class="hidden" onchange="previewLogo(this)">
                                </label>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Perusahaan</label>
                                <input type="text" name="nama_perusahaan" value="<?= htmlspecialchars($settings->nama_perusahaan ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($settings->email ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                <input type="text" name="no_telp" value="<?= htmlspecialchars($settings->no_telp ?? '') ?>" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" required>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alamat Lengkap</label>
                                <textarea name="alamat" rows="3" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all resize-none" required><?= htmlspecialchars($settings->alamat ?? '') ?></textarea>
                            </div>
                        </div>
                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                                Simpan Informasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TAB 3: ASET & DOKUMEN -->
            <div id="tab-aset" class="tab-content hidden animate-fade-in space-y-8">
                
                <!-- Card Kop & TTD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                    <div class="mb-6 border-b border-slate-100 pb-5">
                        <h3 class="text-xl font-bold text-gray-900">Kop Surat & Tanda Tangan</h3>
                        <p class="text-sm text-gray-500 mt-1">Aset ini akan dipakai secara otomatis pada template dokumen.</p>
                    </div>

                    <form action="<?= site_url('pengaturan/update_assets') ?>" method="POST" enctype="multipart/form-data">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            
                            <!-- Kop Surat -->
                            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50 relative group">
                                <p class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#9f2b2b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    Kop Surat
                                </p>
                                <div class="w-full h-32 bg-white border border-slate-200 rounded-xl mb-4 flex items-center justify-center overflow-hidden relative">
                                    <?php if(!empty($settings->kop_surat) && file_exists(FCPATH . $settings->kop_surat)): ?>
                                        <img id="kop-preview" src="<?= base_url($settings->kop_surat) ?>" class="w-full h-full object-contain p-2">
                                        <img id="kop-preview-new" class="w-full h-full object-contain p-2 hidden">
                                    <?php else: ?>
                                        <div id="kop-placeholder" class="text-slate-300 flex flex-col items-center">
                                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            <span class="text-xs font-medium text-slate-400">Belum ada file</span>
                                        </div>
                                        <img id="kop-preview-new" class="w-full h-full object-contain p-2 hidden">
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2">
                                    <label class="flex-1 text-center cursor-pointer px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                                        Pilih File
                                        <input type="file" name="kop_surat" accept=".jpg,.jpeg,.png" class="hidden" onchange="previewAsset(this, 'kop-preview-new')">
                                    </label>
                                    <?php if(!empty($settings->kop_surat)): ?>
                                        <a href="<?= site_url('pengaturan/hapus_asset/kop_surat') ?>" onclick="return confirm('Hapus kop surat?')" class="px-3 py-2 border border-red-200 text-red-600 bg-white text-xs font-semibold rounded-lg hover:bg-red-50 transition-colors shadow-sm text-center">Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Tanda Tangan -->
                            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50 relative group">
                                <p class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#9f2b2b]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    Tanda Tangan
                                </p>
                                <div class="w-full h-32 bg-white border border-slate-200 rounded-xl mb-4 flex items-center justify-center overflow-hidden relative">
                                    <?php if(!empty($settings->ttd) && file_exists(FCPATH . $settings->ttd)): ?>
                                        <img id="ttd-preview" src="<?= base_url($settings->ttd) ?>" class="w-full h-full object-contain p-2">
                                        <img id="ttd-preview-new" class="w-full h-full object-contain p-2 hidden">
                                    <?php else: ?>
                                        <div id="ttd-placeholder" class="text-slate-300 flex flex-col items-center">
                                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            <span class="text-xs font-medium text-slate-400">Belum ada file</span>
                                        </div>
                                        <img id="ttd-preview-new" class="w-full h-full object-contain p-2 hidden">
                                    <?php endif; ?>
                                </div>
                                <div class="flex gap-2">
                                    <label class="flex-1 text-center cursor-pointer px-3 py-2 bg-white border border-slate-300 text-slate-700 text-xs font-semibold rounded-lg hover:bg-slate-50 transition-colors shadow-sm">
                                        Pilih File
                                        <input type="file" name="ttd" accept=".jpg,.jpeg,.png" class="hidden" onchange="previewAsset(this, 'ttd-preview-new')">
                                    </label>
                                    <?php if(!empty($settings->ttd)): ?>
                                        <a href="<?= site_url('pengaturan/hapus_asset/ttd') ?>" onclick="return confirm('Hapus tanda tangan?')" class="px-3 py-2 border border-red-200 text-red-600 bg-white text-xs font-semibold rounded-lg hover:bg-red-50 transition-colors shadow-sm text-center">Hapus</a>
                                    <?php endif; ?>
                                </div>
                            </div>

                        </div>
                        <div class="flex justify-end pt-4 border-t border-slate-100">
                            <button type="submit" class="px-6 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                                Simpan Aset
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Card Jenis Dokumen -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
                    <div class="flex justify-between items-center mb-6 border-b border-slate-100 pb-5">
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">Jenis Dokumen</h3>
                            <p class="text-sm text-gray-500 mt-1">Master data untuk kategori dokumen kerja sama.</p>
                        </div>
                        <button type="button" onclick="bukaModalJenis()" class="flex items-center gap-1.5 px-4 py-2 bg-[#9f2b2b] hover:bg-[#8a2525] text-white text-sm font-semibold rounded-xl transition-colors shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Tambah Jenis
                        </button>
                    </div>

                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <div class="bg-slate-50 px-4 py-3 border-b border-slate-200 grid grid-cols-12 gap-4 text-xs font-semibold text-slate-500 uppercase tracking-wider">
                            <div class="col-span-10">Nama Jenis</div>
                            <div class="col-span-2 text-right">Aksi</div>
                        </div>
                        <div class="divide-y divide-slate-100 max-h-[400px] overflow-y-auto">
                            <?php if(empty($types)): ?>
                                <div class="p-8 text-center text-slate-500 text-sm">Belum ada jenis dokumen.</div>
                            <?php else: ?>
                                <?php foreach($types as $t): ?>
                                    <div class="px-4 py-3 grid grid-cols-12 gap-4 items-center hover:bg-slate-50 transition-colors">
                                        <div class="col-span-10 text-sm text-slate-800 font-medium truncate"><?= htmlspecialchars($t->name) ?></div>
                                        <div class="col-span-2 flex justify-end gap-1.5">
                                            <button type="button" onclick="editJenis(<?= $t->id ?>, '<?= htmlspecialchars($t->name, ENT_QUOTES) ?>')" class="p-1.5 border border-slate-200 text-slate-500 hover:text-blue-600 hover:border-blue-300 hover:bg-blue-50 rounded-lg transition-all" title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                            </button>
                                            <a href="<?= site_url('pengaturan/hapus_jenis/'.$t->id) ?>" onclick="return confirm('Hapus jenis dokumen ini?')" class="p-1.5 border border-slate-200 text-slate-500 hover:text-red-600 hover:border-red-300 hover:bg-red-50 rounded-lg transition-all" title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</main>

<!-- MODAL JENIS DOKUMEN -->
<div id="modalJenis" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="tutupModalJenis()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10" id="modalJenisContent">
        <h3 class="text-xl font-bold text-gray-900 mb-4" id="modalJenisTitle">Tambah Jenis Dokumen</h3>
        
        <form id="formJenis" onsubmit="simpanJenis(event)">
            <input type="hidden" id="jenisId" value="">
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Jenis Dokumen</label>
                <input type="text" id="jenisName" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:bg-white focus:border-[#9f2b2b] focus:ring-1 focus:ring-[#9f2b2b] transition-all" placeholder="Contoh: MOU, NDA, Kontrak" required>
            </div>
            
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="tutupModalJenis()" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition-colors text-sm">Batal</button>
                <button type="submit" class="px-5 py-2.5 bg-[#9f2b2b] hover:bg-[#8a2525] text-white font-semibold rounded-xl transition-colors shadow-sm text-sm">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- STYLE & SCRIPT TAMBAHAN -->
<style>
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    
    /* Sembunyikan scrollbar di tab nav jika kepanjangan di layar kecil */
    .hide-scrollbar::-webkit-scrollbar { display: none; }
    .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
    // --- 1. TAB LOGIC & PERSISTENCE ---
    function switchTab(tabId) {
        // Sembunyikan semua konten
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        
        // Matikan semua gaya aktif tombol tab (ubah ke warna abu-abu)
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('border-[#9f2b2b]', 'text-[#9f2b2b]', 'font-semibold');
            el.classList.add('border-transparent', 'text-gray-500', 'font-medium', 'hover:text-gray-800');
        });

        // Tampilkan konten yang dipilih
        document.getElementById('tab-' + tabId).classList.remove('hidden');
        
        // Beri gaya aktif ke tombol yang dipilih (garis bawah marun, teks marun)
        const btn = document.getElementById('btn-tab-' + tabId);
        btn.classList.remove('border-transparent', 'text-gray-500', 'font-medium', 'hover:text-gray-800');
        btn.classList.add('border-[#9f2b2b]', 'text-[#9f2b2b]', 'font-semibold');

        // Simpan tab aktif di localStorage agar tidak balik ke awal saat reload
        localStorage.setItem('active_settings_tab', tabId);
    }

    // --- 2. LOGO PREVIEW LOGIC ---
    function previewLogo(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logo-preview');
                const placeholder = document.getElementById('logo-placeholder');
                
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                if(placeholder) placeholder.classList.add('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- 3. ASET (KOP/TTD) PREVIEW LOGIC ---
    function previewAsset(input, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById(imgId);
                // Sembunyikan gambar lama / placeholder
                const oldImg = input.name === 'kop_surat' ? document.getElementById('kop-preview') : document.getElementById('ttd-preview');
                const placeholder = input.name === 'kop_surat' ? document.getElementById('kop-placeholder') : document.getElementById('ttd-placeholder');
                
                if(oldImg) oldImg.classList.add('hidden');
                if(placeholder) placeholder.classList.add('hidden');

                img.src = e.target.result;
                img.classList.remove('hidden');
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // --- 4. MODAL JENIS DOKUMEN ---
    const modalJenis = document.getElementById('modalJenis');
    const modalJenisContent = document.getElementById('modalJenisContent');
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

    // --- 5. INISIALISASI SAAT HALAMAN DI-LOAD ---
    document.addEventListener('DOMContentLoaded', () => {
        // Kembalikan tab yang terakhir dibuka
        const activeTab = localStorage.getItem('active_settings_tab') || 'akun';
        switchTab(activeTab);

        // Auto-hide alert notifikasi setelah 4 detik
        setTimeout(() => {
            ['alert-success', 'alert-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 300);
                }
            });
        }, 4000);
    });
</script>