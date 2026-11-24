<script>
// =========================================
// SMART BACK BUTTON (dipakai bersama semua halaman detail/form)
// Syarat : <a id="backBtn" href="[fallback]"> ada di halaman.
// Opsional: elemen lain (mis. tombol "Batal") cukup diberi atribut
//           data-smart-back agar ikut kembali ke halaman asal.
// Opsional: data-back-key="nama-unik" pada #backBtn untuk halaman form
//           yang dirender ulang dari URL lain saat validasi gagal.
// =========================================
(function () {
    var btn = document.getElementById('backBtn');
    if (!btn) return;

    var targets = [btn].concat(
        Array.prototype.slice.call(document.querySelectorAll('[data-smart-back]'))
    );

    var fallback = btn.getAttribute('href');           // fallback dari server
    // Kunci penyimpanan asal. Default per halaman; halaman form yang URL-nya
    // berubah saat validasi gagal bisa memberi data-back-key agar tetap konsisten.
    var KEY      = 'back_ctx:' + (btn.getAttribute('data-back-key') || location.pathname);
    var FLAG     = 'back_going_to';
    var ref      = document.referrer || '';

    function get(k)    { try { return sessionStorage.getItem(k); } catch (e) { return null; } }
    function set(k, v) { try { sessionStorage.setItem(k, v); } catch (e) {} }
    function del(k)    { try { sessionStorage.removeItem(k); } catch (e) {} }

    // Apakah halaman ini dibuka lewat tombol Kembali dari halaman lain?
    // Kalau ya, jangan timpa asal yang sudah tersimpan.
    var cameFromBackBtn = (get(FLAG) === location.pathname);
    del(FLAG);

    var useRef = false;
    if (!cameFromBackBtn && ref) {
        try {
            var u = new URL(ref);
            var sameOrigin = u.origin === location.origin;
            var isSelf     = u.pathname === location.pathname;
            // halaman form/aksi bukan "asal" yang berguna
            var isAction   = /\/(edit|tambah|simpan|update|hapus|status)(\/|$)/i.test(u.pathname);
            useRef = sameOrigin && !isSelf && !isAction;
        } catch (e) {}
    }

    var backUrl;
    if (useRef) {
        backUrl = ref;   // termasuk query (?page=2&q=...) agar filter/pagination terjaga
        set(KEY, backUrl);
    } else {
        backUrl = get(KEY) || fallback;
    }

    targets.forEach(function (el) {
        el.href = backUrl;
        // Tandai tujuan supaya halaman tujuan tidak menimpa asalnya sendiri
        el.addEventListener('click', function () {
            try { set(FLAG, new URL(el.href, location.origin).pathname); } catch (e) {}
        });
    });
})();
</script>