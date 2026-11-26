/**
 * TridatuFS — Helper File System Access API + Fallback Downloads
 *
 * Arsitektur:
 *  Chromium (Chrome/Edge/Opera):
 *    - Folder picker per dokumen → simpan handle di IndexedDB
 *    - File disimpan di: {folder}/{klienId}_{namaKlien}/{hash8}_{fileName}
 *  Firefox / Safari:
 *    - Fallback download ke folder Downloads
 *    - Pratinjau lokal TIDAK tersedia (dikunci di UI)
 *    - Sync pakai <input type=file> manual
 *
 * Penamaan file:
 *   hash8 = 8 karakter pertama SHA-256 file_hash
 *   → mencegah tabrakan nama file, walau dua dokumen punya nama sama
 */
window.TridatuFS = (function () {
    'use strict';

    const DB_NAME = 'tridatu_fs';
    const DB_VERSION = 1;
    const STORE = 'handles';
    const FOLDER_KEY = 'docs_folder';
    const DOCUMENT_FOLDER_PREFIX = 'document_folder_';

    const SUPPORTED = typeof window.showDirectoryPicker === 'function';

    // ==========================================================
    // IndexedDB
    // ==========================================================
    let dbPromise = null;
    function getDB() {
        if (dbPromise) return dbPromise;
        dbPromise = new Promise((resolve, reject) => {
            const req = indexedDB.open(DB_NAME, DB_VERSION);
            req.onupgradeneeded = () => {
                const db = req.result;
                if (!db.objectStoreNames.contains(STORE)) {
                    db.createObjectStore(STORE);
                }
            };
            req.onsuccess = () => resolve(req.result);
            req.onerror = () => reject(req.error);
        });
        return dbPromise;
    }

    async function idbGet(key) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readonly');
            const req = tx.objectStore(STORE).get(key);
            req.onsuccess = () => resolve(req.result || null);
            req.onerror = () => reject(req.error);
        });
    }

    async function idbSet(key, value) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).put(value, key);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    async function idbDelete(key) {
        const db = await getDB();
        return new Promise((resolve, reject) => {
            const tx = db.transaction(STORE, 'readwrite');
            tx.objectStore(STORE).delete(key);
            tx.oncomplete = resolve;
            tx.onerror = () => reject(tx.error);
        });
    }

    // ==========================================================
    // Permission
    // ==========================================================
    async function ensurePermission(handle, mode = 'readwrite') {
        if (!handle) return false;
        const opts = { mode };
        try {
            if ((await handle.queryPermission(opts)) === 'granted') return true;
            if ((await handle.requestPermission(opts)) === 'granted') return true;
        } catch (e) {
            console.warn('[TridatuFS] Permission error:', e);
        }
        return false;
    }

    // ==========================================================
    // Folder handle per dokumen
    // ==========================================================
    function documentFolderKey(documentId) {
        return DOCUMENT_FOLDER_PREFIX + String(documentId);
    }

    async function getStoredHandle(documentId = null) {
        if (documentId !== null && documentId !== undefined && String(documentId) !== '') {
            const key = documentFolderKey(documentId);
            const handle = await idbGet(key);
            if (handle) return { handle, key, legacy: false };

            const legacyHandle = await idbGet(FOLDER_KEY);
            return { handle: legacyHandle, key, legacy: !!legacyHandle };
        }

        return { handle: await idbGet(FOLDER_KEY), key: FOLDER_KEY, legacy: false };
    }

    async function getFolderHandle(documentId = null, forceNew = false) {
        if (!SUPPORTED) return null;

        if (!forceNew) {
            const stored = await getStoredHandle(documentId);
            if (!stored.handle) return null;

            const ok = await ensurePermission(stored.handle, 'readwrite');
            if (!ok) return null;
            if (stored.legacy && documentId !== null && documentId !== undefined) {
                await idbSet(stored.key, stored.handle);
            }
            return stored.handle;
        }

        try {
            const handle = await window.showDirectoryPicker({ mode: 'readwrite' });
            const key = documentId !== null && documentId !== undefined
                ? documentFolderKey(documentId)
                : FOLDER_KEY;
            await idbSet(key, handle);
            return handle;
        } catch (err) {
            if (err.name === 'AbortError') return null;
            throw err;
        }
    }

    async function getStoredFolderAccess(documentId = null) {
        if (!SUPPORTED) {
            return { handle: null, permission: 'unsupported' };
        }

        const stored = await getStoredHandle(documentId);
        if (!stored.handle) {
            return { handle: null, permission: 'missing' };
        }

        const permission = await stored.handle.queryPermission({ mode: 'readwrite' });
        return { handle: stored.handle, permission };
    }

    async function requestFolderAccess(documentId = null) {
        if (!SUPPORTED) {
            return { handle: null, permission: 'unsupported' };
        }

        const stored = await getStoredHandle(documentId);
        if (!stored.handle) {
            return { handle: null, permission: 'missing' };
        }

        const granted = await ensurePermission(stored.handle, 'readwrite');
        if (granted && stored.legacy && documentId !== null && documentId !== undefined) {
            await idbSet(stored.key, stored.handle);
        }
        return {
            handle: granted ? stored.handle : null,
            permission: granted ? 'granted' : 'denied'
        };
    }

    async function selectFolder() {
        if (!SUPPORTED) return null;

        try {
            return await window.showDirectoryPicker({ mode: 'readwrite' });
        } catch (err) {
            if (err.name === 'AbortError') return null;
            throw err;
        }
    }

    async function clearFolderHandle() {
        await idbDelete(FOLDER_KEY);
    }

    async function clearDocumentFolderHandle(documentId) {
        if (documentId === null || documentId === undefined || String(documentId) === '') return;
        await idbDelete(documentFolderKey(documentId));
    }

    // ==========================================================
    // Folder klien (subfolder per klien)
    // ==========================================================
    function sanitizeFolderName(klienId, klienNama) {
        const s = String(klienNama || '')
            .replace(/[<>:"/\\|?*\x00-\x1f]/g, '_')
            .trim()
            .substring(0, 60);
        return s ? `${klienId}_${s}` : String(klienId);
    }

    /**
     * Ambil / buat folder klien. Mapping folder name disimpan di IndexedDB
     * agar tidak berubah walau nama klien berubah di kemudian hari.
     */
    async function getKlienFolder(root, klienId, klienNama, create = true) {
        const key = 'klien_folder_' + klienId;
        let folderName = await idbGet(key);
        if (!folderName) {
            folderName = sanitizeFolderName(klienId, klienNama);
            await idbSet(key, folderName);
        }
        return root.getDirectoryHandle(folderName, { create });
    }

    /**
     * Bangun nama file penyimpanan.
     * Format: {hash8}_{fileName}
     * Contoh: a1b2c3d4_kontrak.pdf
     */
    function buildSavedName(fileName, fileHash) {
        const hash8 = (fileHash || '').substring(0, 8).toLowerCase() || Date.now().toString(16);
        return `${hash8}_${fileName}`;
    }

    // ==========================================================
    // PUBLIC API
    // ==========================================================

    function isSupported() {
        return SUPPORTED;
    }

    /**
     * Simpan file dokumen.
     *
     * @param {object} opts
     * @param {number|string} opts.documentId - ID dokumen untuk menyimpan handle folder
     * @param {number} opts.klienId     - ID klien (untuk nama folder)
     * @param {string} opts.klienNama   - Nama klien (untuk nama folder)
     * @param {string} opts.fileName    - Nama file asli
     * @param {string} opts.fileHash    - SHA-256 (hex) dari file
     * @param {Blob}   opts.blob        - Isi file
     *
     * @returns {Promise<{ok:boolean, mode:'folder'|'download', savedName?:string, msg?:string}>}
     */
    async function saveFile({ documentId, root: rootHandle = null, klienId, klienNama, fileName, fileHash, blob }) {
        if (!SUPPORTED) {
            triggerDownload(blob, fileName);
            return { ok: true, mode: 'download', savedName: fileName };
        }

        const root = rootHandle || await getFolderHandle(documentId);
        if (!root) return { ok: false, mode: 'folder', msg: 'Folder tidak dipilih.' };
        if (documentId === null || documentId === undefined || String(documentId) === '') {
            return { ok: false, mode: 'folder', msg: 'ID dokumen tidak tersedia untuk menyimpan hubungan folder.' };
        }

        try {
            const klienFolder = await getKlienFolder(root, klienId, klienNama, true);
            const savedName = buildSavedName(fileName, fileHash);
            const fh = await klienFolder.getFileHandle(savedName, { create: true });
            const w = await fh.createWritable();
            await w.write(blob);
            await w.close();
            await idbSet(documentFolderKey(documentId), root);
            return { ok: true, mode: 'folder', savedName };
        } catch (err) {
            return { ok: false, mode: 'folder', msg: err.message };
        }
    }

    /**
     * Baca file dokumen (Chromium only).
     * FF/Safari: selalu null.
     *
     * @param {object} opts
     * @param {number} opts.klienId
     * @param {string} opts.klienNama
     * @param {string} opts.fileName
     * @param {string} opts.fileHash
     *
     * @returns {Promise<File|null>}
     */
    async function readFile({ documentId, klienId, klienNama, fileName, fileHash }) {
        if (!SUPPORTED) return null;
        try {
            const root = await getFolderHandle(documentId);
            if (!root) return null;
            const klienFolder = await getKlienFolder(root, klienId, klienNama, false);
            const savedName = buildSavedName(fileName, fileHash);
            const fh = await klienFolder.getFileHandle(savedName, { create: false });
            return await fh.getFile();
        } catch (err) {
            return null;
        }
    }

    /**
     * Hapus file dokumen (Chromium only).
     * FF/Safari: no-op (file di Downloads tidak bisa dihapus).
     *
     * @param {object} opts
     * @param {number} opts.klienId
     * @param {string} opts.klienNama
     * @param {string} opts.fileName
     * @param {string} opts.fileHash
     *
     * @returns {Promise<{ok:boolean, mode:'folder'|'download', msg?:string}>}
     */
    async function deleteFile({ documentId, klienId, klienNama, fileName, fileHash }) {
        if (!SUPPORTED) return { ok: true, mode: 'download' };
        const root = await getFolderHandle(documentId);
        if (!root) return { ok: false, mode: 'folder', msg: 'Folder tidak dipilih.' };
        try {
            const klienFolder = await getKlienFolder(root, klienId, klienNama, false);
            const savedName = buildSavedName(fileName, fileHash);
            await klienFolder.removeEntry(savedName);
            await clearDocumentFolderHandle(documentId);
            return { ok: true, mode: 'folder' };
        } catch (err) {
            if (err.name === 'NotFoundError') {
                await clearDocumentFolderHandle(documentId);
                return { ok: true, mode: 'folder' };
            }
            return { ok: false, mode: 'folder', msg: err.message };
        }
    }

    /**
     * Simpan blob ke disk via Save As / download.
     * Dipakai untuk unduh file cloud.
     */
    async function saveBlobToDisk(blob, fileName) {
        if (typeof window.showSaveFilePicker === 'function') {
            try {
                const handle = await window.showSaveFilePicker({ suggestedName: fileName });
                const w = await handle.createWritable();
                await w.write(blob);
                await w.close();
                return { ok: true, mode: 'savepicker' };
            } catch (err) {
                if (err.name === 'AbortError') return { ok: false, cancelled: true };
                triggerDownload(blob, fileName);
                return { ok: true, mode: 'download' };
            }
        }
        triggerDownload(blob, fileName);
        return { ok: true, mode: 'download' };
    }

    function triggerDownload(blob, fileName) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = fileName;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 2000);
    }

    /**
     * Pilih file manual via <input type=file>.
     * Dipakai untuk SYNC di FF/Safari, atau fallback sync Chromium saat file hilang.
     */
    function pickFile(accept = '.pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png') {
        return new Promise((resolve) => {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = accept;
            let resolved = false;

            input.onchange = () => {
                resolved = true;
                resolve(input.files[0] || null);
            };

            window.addEventListener('focus', function onFocus() {
                window.removeEventListener('focus', onFocus);
                setTimeout(() => { if (!resolved) resolve(null); }, 500);
            }, { once: true });

            input.click();
        });
    }

    function formatSize(bytes) {
        if (!bytes) return '-';
        const mb = bytes / 1048576;
        return mb >= 1 ? mb.toFixed(2) + ' MB' : (bytes / 1024).toFixed(0) + ' KB';
    }

    // ==========================================================
    // Expose
    // ==========================================================
    return {
        isSupported,
        getFolderHandle,
        getStoredFolderAccess,
        requestFolderAccess,
        selectFolder,
        clearFolderHandle,
        clearDocumentFolderHandle,
        saveFile,
        readFile,
        deleteFile,
        saveBlobToDisk,
        triggerDownload,
        pickFile,
        formatSize,
    };
})();