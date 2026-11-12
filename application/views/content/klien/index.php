<main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f4f6f9] p-6">
    
    <!-- Header Halaman -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Klien</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data klien, kontak, project dan dokumen kerja sama.</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden md:flex items-center gap-2 bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <span class="font-bold text-gray-800"><?= count($data_klien) ?></span> <span class="text-gray-500 text-sm">Klien</span>
            </div>
            <a href="<?= site_url('klien/tambah') ?>" class="bg-[#9f2b2b] hover:bg-[#8a2525] text-white px-5 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 transition-all shadow-md shadow-[#9f2b2b]/20">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Klien
            </a>
        </div>
    </div>

    <!-- Notifikasi Sukses / Error -->
    <?php if($this->session->flashdata('success')): ?>
        <div id="alert-success" class="flex items-center justify-between bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
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
        <div id="alert-error" class="flex items-center justify-between bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl mb-5 shadow-sm transition-all duration-300">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="font-medium text-sm"><?= $this->session->flashdata('error'); ?></span>
            </div>
            <button type="button" onclick="document.getElementById('alert-error').remove()" class="text-red-500 hover:text-red-700 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
    <?php endif; ?>

    <!-- Card Tabel -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
        
        <!-- Toolbar & Search -->
        <div class="p-4 sm:p-5 border-b border-gray-100 bg-white flex flex-col xl:flex-row gap-3 items-center justify-between">
            <div class="relative w-full xl:w-72">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" id="searchInput" class="bg-gray-50 border border-gray-200 text-gray-900 text-sm rounded-xl block w-full pl-10 p-2.5 outline-none focus:bg-white focus:border-[#9f2b2b] focus:ring-4 focus:ring-[#9f2b2b]/10 transition-all" placeholder="Cari nama klien atau PIC...">
            </div>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse" id="klienTable">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50/50">
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Klien</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">PIC / Kontak</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Mulai Kerja Sama</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Project</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Dokumen</th>
                        <th class="px-5 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php if(empty($data_klien)): ?>
                        <tr id="empty-db-row">
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <p class="text-sm font-medium">Belum ada data klien.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($data_klien as $k): ?>
                        <tr class="data-row border-b border-gray-100 hover:bg-gray-50/60 transition-colors group">
                            
                            <!-- Nama Klien -->
                            <td class="px-5 py-4">
                                <div class="text-sm font-bold text-gray-900 group-hover:text-[#9f2b2b] transition-colors"><?= $k->nama_klien ?></div>
                            </td>
                            
                            <!-- PIC & Kontak -->
                            <td class="px-5 py-4">
                                <div class="text-sm text-gray-800 font-medium flex items-center gap-1.5 mb-1">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <?= $k->nama_pic ?>
                                </div>
                                <div class="text-xs text-gray-500 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    <?= $k->no_hp ?>
                                </div>
                            </td>
                            
                            <!-- Tanggal Mulai -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-600 flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <?= date('d M Y', strtotime($k->tanggal_mulai)) ?>
                                </div>
                            </td>
                            
                            <!-- Project (DINAMIS) -->
                            <td class="px-5 py-4 text-center">
                                <?php if ((int) $k->total_project > 0): ?>
                                    <a href="<?= site_url('klien/detail/' . $k->id) ?>"
                                       class="inline-flex items-center justify-center bg-purple-50 text-purple-600 hover:bg-purple-100 hover:border-purple-200 text-xs font-semibold px-3 py-1 rounded-full border border-purple-100 transition-colors"
                                       title="Lihat detail klien">
                                        <?= (int) $k->total_project ?> Project
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center bg-gray-50 text-gray-400 text-xs font-semibold px-3 py-1 rounded-full border border-gray-100">
                                        0 Project
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Dokumen (DINAMIS) -->
                            <td class="px-5 py-4 text-center">
                                <?php if ((int) $k->total_dokumen > 0): ?>
                                    <a href="<?= site_url('klien/detail/' . $k->id) ?>"
                                       class="inline-flex items-center justify-center bg-orange-50 text-orange-600 hover:bg-orange-100 hover:border-orange-200 text-xs font-semibold px-3 py-1 rounded-full border border-orange-100 transition-colors"
                                       title="Lihat detail klien">
                                        <?= (int) $k->total_dokumen ?> Dokumen
                                    </a>
                                <?php else: ?>
                                    <span class="inline-flex items-center justify-center bg-gray-50 text-gray-400 text-xs font-semibold px-3 py-1 rounded-full border border-gray-100">
                                        0 Dokumen
                                    </span>
                                <?php endif; ?>
                            </td>
                            
                            <!-- Aksi -->
                            <td class="px-5 py-4 text-center">
                                <div class="flex justify-center items-center gap-1.5">
                                    <a href="<?= site_url('klien/detail/' . $k->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 rounded-lg transition-all shadow-sm" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>
                                    <a href="<?= site_url('klien/edit/' . $k->id) ?>" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-yellow-600 hover:bg-yellow-50 hover:border-yellow-200 rounded-lg transition-all shadow-sm" title="Edit Data">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </a>
                                    <button type="button" onclick="bukaModalHapus(<?= $k->id ?>, '<?= htmlspecialchars($k->nama_klien, ENT_QUOTES) ?>')" class="p-1.5 bg-white border border-gray-200 text-gray-600 hover:text-red-600 hover:bg-red-50 hover:border-red-200 rounded-lg transition-all shadow-sm" title="Hapus Klien">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION FOOTER -->
        <div class="flex flex-col border-t border-gray-200 bg-gray-50/30 px-5 py-4 gap-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-1">
                <span id="pagination-info" class="text-sm text-gray-500">
                    Menampilkan 0-0 dari 0 Klien
                </span>
                <div id="pagination-controls" class="flex items-center gap-1.5">
                    <!-- Tombol digenerate via JavaScript -->
                </div>
            </div>
        </div>

    </div>
</main>

<!-- MODAL KONFIRMASI HAPUS -->
<div id="modalHapus" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" onclick="tutupModalHapus()"></div>
    
    <div id="modalContent" class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 transform scale-95 opacity-0 transition-all duration-300 z-10">
        <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-50 rounded-full mb-5">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        
        <h3 class="text-xl font-bold text-center text-gray-900 mb-2">Hapus Klien?</h3>
        <p class="text-center text-gray-500 mb-6 text-sm">
            Apakah Anda yakin ingin menghapus klien <br>
            <strong id="namaKlienHapus" class="text-gray-900 text-base"></strong>?<br>
            <span class="text-xs text-red-500 mt-3 block bg-red-50 py-1.5 px-2 rounded-lg">Tindakan ini tidak dapat dibatalkan.</span>
        </p>
        
        <div class="flex justify-center gap-3">
            <button type="button" onclick="tutupModalHapus()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors w-full text-sm">Batal</button>
            <button type="button" onclick="eksekusiHapus()" class="px-5 py-2.5 bg-red-500 hover:bg-red-600 text-white font-medium rounded-xl transition-all shadow-md shadow-red-500/30 w-full text-sm">Ya, Hapus</button>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    // --- LOGIKA PAGINATION & LIVE SEARCH ---
    const rowsPerPage = 5;
    let currentPage = 1;
    let allRows = [];
    let filteredRows = [];

    document.addEventListener('DOMContentLoaded', function() {
        const tableBody = document.getElementById('tableBody');
        allRows = Array.from(tableBody.querySelectorAll('.data-row'));
        filteredRows = [...allRows];

        updateTablePagination();

        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                const filter = this.value.toLowerCase();
                filteredRows = allRows.filter(row => {
                    const text = row.textContent.toLowerCase();
                    return text.includes(filter);
                });
                currentPage = 1;
                updateTablePagination();
            });
        }

        setTimeout(() => {
            const successAlert = document.getElementById('alert-success');
            const errorAlert = document.getElementById('alert-error');
            if (successAlert) { successAlert.style.opacity = '0'; setTimeout(() => successAlert.remove(), 300); }
            if (errorAlert) { errorAlert.style.opacity = '0'; setTimeout(() => errorAlert.remove(), 300); }
        }, 4000);
    });

    function updateTablePagination() {
        const totalRows = filteredRows.length;
        const totalPages = Math.ceil(totalRows / rowsPerPage);
        const tableBody = document.getElementById('tableBody');

        const emptyRow = document.getElementById('search-empty-row');
        if (emptyRow) emptyRow.remove();

        if (totalRows === 0 && allRows.length > 0) {
            const tr = document.createElement('tr');
            tr.id = 'search-empty-row';
            tr.innerHTML = `<td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <p class="text-sm font-medium">Data tidak ditemukan.</p>
                <p class="text-xs text-gray-400 mt-1">Coba gunakan kata kunci lain.</p>
            </td>`;
            tableBody.appendChild(tr);
        }

        if (currentPage > totalPages && totalPages > 0) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;

        allRows.forEach(row => row.style.display = 'none');

        if (totalRows > 0) {
            const startIndex = (currentPage - 1) * rowsPerPage;
            const endIndex = Math.min(startIndex + rowsPerPage, totalRows);

            for (let i = startIndex; i < endIndex; i++) {
                filteredRows[i].style.display = '';
            }

            document.getElementById('pagination-info').innerText = `Menampilkan ${startIndex + 1}–${endIndex} dari ${totalRows} Klien`;
        } else {
            document.getElementById('pagination-info').innerText = `Menampilkan 0 dari 0 Klien`;
        }

        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        const container = document.getElementById('pagination-controls');
        container.innerHTML = '';

        if (totalPages <= 1) return;

        const NAV = 'flex h-8 w-8 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors';
        const numCls = on => on
            ? 'flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-[#9f2b2b] bg-[#9f2b2b] text-white text-sm font-medium shadow-sm'
            : 'flex h-8 min-w-[2rem] items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 text-sm font-medium transition-colors';

        const mkNav = (d, disabled, fn) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = NAV;
            b.disabled = disabled;
            b.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${d}"/></svg>`;
            b.onclick = fn;
            return b;
        };

        container.appendChild(mkNav('M15 19l-7-7 7-7', currentPage === 1, () => { currentPage--; updateTablePagination(); }));

        for (let i = 1; i <= totalPages; i++) {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = numCls(i === currentPage);
            b.innerText = i;
            b.onclick = () => { currentPage = i; updateTablePagination(); };
            container.appendChild(b);
        }

        container.appendChild(mkNav('M9 5l7 7-7 7', currentPage === totalPages, () => { currentPage++; updateTablePagination(); }));
    }

    // --- LOGIKA MODAL HAPUS ---
    let hapusId = null;
    const modal = document.getElementById('modalHapus');
    const modalContent = document.getElementById('modalContent');

    function bukaModalHapus(id, nama) {
        hapusId = id;
        document.getElementById('namaKlienHapus').innerText = nama;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            modalContent.classList.remove('scale-95', 'opacity-0');
            modalContent.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function tutupModalHapus() {
        hapusId = null;
        modalContent.classList.remove('scale-100', 'opacity-100');
        modalContent.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    function eksekusiHapus() {
        if (hapusId) {
            window.location.href = '<?= site_url('klien/hapus/') ?>' + hapusId;
        }
    }
</script>