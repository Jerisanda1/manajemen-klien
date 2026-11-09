// ==========================================
// 1. DEVICE ID GENERATOR
// ==========================================
function getDeviceId() {
    let id = localStorage.getItem('tridatu_device_id');
    if (!id) {
        // Gunakan crypto.randomUUID() untuk ID unik perangkat
        id = crypto.randomUUID();
        localStorage.setItem('tridatu_device_id', id);
        document.cookie = `tridatu_device_id=${id}; path=/; max-age=31536000; SameSite=Lax`;
    }
    return id;
}

// ==========================================
// 2. HELPER: HITUNG HASH SHA-256
// ==========================================
async function hitungHash(file) {
    const arrayBuffer = await file.arrayBuffer();
    const hashBuffer = await crypto.subtle.digest('SHA-256', arrayBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// ==========================================
// 3. HELPER: INDEXEDDB UNTUK SIMPAN FILE HANDLE
// ==========================================
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

async function ambilHandle(id) {
    const db = await bukaDB();
    return new Promise((resolve, reject) => {
        const tx = db.transaction('handles', 'readonly');
        const req = tx.objectStore('handles').get(String(id));
        req.onsuccess = () => resolve(req.result);
        req.onerror = () => reject(req.error);
    });
}