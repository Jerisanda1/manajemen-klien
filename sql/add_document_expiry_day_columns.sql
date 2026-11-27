-- Tambah penanda pengiriman notifikasi pada tanggal dokumen berakhir.
-- Jalankan sekali pada database manajemen_klien.

ALTER TABLE documents
    ADD COLUMN expiry_day_notified_at DATETIME NULL DEFAULT NULL AFTER wa_reminder_sent_at,
    ADD COLUMN wa_expiry_day_notified_at DATETIME NULL DEFAULT NULL AFTER expiry_day_notified_at;
