-- Tambah kolom reminder project (konsep sama dengan reminder dokumen kerja sama)
-- Jalankan sekali pada database manajemen_klien.

ALTER TABLE project
    ADD COLUMN last_reminder_sent_at DATETIME NULL DEFAULT NULL AFTER status,
    ADD COLUMN wa_reminder_sent_at DATETIME NULL DEFAULT NULL AFTER last_reminder_sent_at,
    ADD COLUMN expired_notified_at DATETIME NULL DEFAULT NULL AFTER wa_reminder_sent_at,
    ADD COLUMN wa_expired_notified_at DATETIME NULL DEFAULT NULL AFTER expired_notified_at;
