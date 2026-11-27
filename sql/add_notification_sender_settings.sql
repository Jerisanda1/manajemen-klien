-- Pengaturan pengirim email dan WhatsApp yang digunakan oleh semua reminder.
-- Rahasia SMTP dan token Fonnte disimpan terenkripsi oleh aplikasi.

CREATE TABLE IF NOT EXISTS notification_sender_settings (
    id TINYINT UNSIGNED NOT NULL,
    smtp_host VARCHAR(255) NULL,
    smtp_port SMALLINT UNSIGNED NULL,
    smtp_crypto VARCHAR(10) NULL,
    smtp_user VARCHAR(255) NULL,
    smtp_password_ciphertext MEDIUMTEXT NULL,
    email_from VARCHAR(255) NULL,
    email_from_name VARCHAR(150) NULL,
    fonnte_token_ciphertext MEDIUMTEXT NULL,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO notification_sender_settings (id) VALUES (1);
