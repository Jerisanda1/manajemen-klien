-- Atur jarak pengingat H-N secara terpisah untuk setiap klien.
-- Jalankan sekali pada database manajemen_klien.

ALTER TABLE clients
    ADD COLUMN reminder_days_before SMALLINT UNSIGNED NOT NULL DEFAULT 30;
