# Windows Cronjobs

Windows Cronjobs adalah sebuah alat yang dirancang untuk membawa fungsionalitas cron jobs di cPanel ke Windows sehingga dapat digunakan untuk menguji cron jobs secara lokal.

Tautan Resmi GitHub: [https://github.com/putuardiworks/windows-cronjobs](https://github.com/putuardiworks/windows-cronjobs)

> for non-Indonesians: This README is also available in [English](https://github.com/putuardiworks/windows-cronjobs/blob/main/README.md).

## Motivasi

Saat mengembangkan proyek PHP yang membutuhkan cron jobs, saya biasanya mengujinya secara langsung di cPanel. Proses ini terasa agak merepotkan dan tidak efisien untuk pengembangan lokal, apalagi jika tidak punya hosting cPanel. Selain itu, rasanya sulit untuk mengetahui skrip mana saja yang telah dijadikan cron jobs dan konfigurasinya seperti apa di cPanel.

Windows Cronjobs dikembangkan untuk mengatasi masalah tersebut. Dengan menyediakan fungsionalitas yang mirip dengan cron jobs di cPanel, alat ini memungkinkan kita untuk melakukan pengujian cron jobs secara lokal di Windows dan memastikan bahwa konfigurasi cron jobs terdokumentasi dengan baik di dalam kode.

## Pengaturan

1. **Unduh dan Ekstrak:**
   - Unduh repositori ini dalam bentuk file `.zip`.
   - Ekstrak kontennya.

2. **Salin File Konfigurasi:**
   - Buat salinan dari `windows-cronjobs.config.example`.
   - Ganti nama file yang disalin menjadi `windows-cronjobs.config`.

3. **Konfigurasi:**
   - Buka `windows-cronjobs.config`.
   - Atur `php_path` ke lokasi `php.exe`. (wajib)
   - Atur `timezone`. (opsional)

4. **Tambah Cron Jobs:**
   - Edit `cronjobs_list.php`.
   - Masukkan cron jobs Anda seperti pada contoh.

## Penggunaan

- **Memulai Cron Jobs:**
  - Klik dua kali `windows-cronjobs.bat` untuk memulai cron jobs.

- **Menghentikan Cron Jobs:**
  - Tekan `Ctrl+C` di jendela PowerShell yang telah terbuka saat memulai cron jobs.

## Fitur

Windows Cronjobs v0.2.2 saat ini hanya mendukung pengaturan umum cron jobs yang ada di cPanel:

- **Sekali Semenit:** `* * * * *`
- **Sekali Per Lima Menit:** `*/5 * * * *`
- **Dua Kali Per Jam:** `0,30 * * * *`
- **Sekali Sejam:** `0 * * * *`
- **Dua Kali Per Hari:** `0 0,12 * * *`
- **Sekali Sehari:** `0 0 * * *`
- **Sekali Seminggu:** `0 0 * * 0`
- **Ke-1 dan Ke-15 Bulan Berjalan:** `0 0 1,15 * *`
- **Sekali Sebulan:** `0 0 1 * *`
- **Sekali Setahun:** `0 0 1 1 *`

## TODO

Penyempurnaan yang direncanakan untuk versi mendatang:

- Dukungan untuk nilai waktu spesifik.
- Dukungan untuk rentang waktu.
- Dukungan untuk daftar waktu.
- Dukungan untuk interval waktu.

## Lisensi

Proyek ini dilisensikan di bawah Lisensi MIT. Lihat file `LICENSE` untuk lebih jelasnya.