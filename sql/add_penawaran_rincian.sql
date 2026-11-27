-- Jalankan sekali pada database manajemen_klien untuk rincian penawaran.

ALTER TABLE penawaran
    ADD COLUMN subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN discount_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN payment_method VARCHAR(100) NULL,
    ADD COLUMN payment_account TEXT NULL,
    ADD COLUMN notes TEXT NULL;

CREATE TABLE IF NOT EXISTS penawaran_details (
    id INT NOT NULL AUTO_INCREMENT,
    penawaran_id INT NOT NULL,
    nama_item VARCHAR(255) NOT NULL,
    deskripsi TEXT NULL,
    kuantitas DECIMAL(12,2) NOT NULL DEFAULT 1,
    satuan VARCHAR(50) NULL,
    harga_satuan DECIMAL(15,2) NOT NULL DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    urutan INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_penawaran_details_penawaran_id (penawaran_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS penawaran_taxes (
    id INT NOT NULL AUTO_INCREMENT,
    penawaran_id INT NOT NULL,
    nama_pajak VARCHAR(100) NOT NULL,
    jenis VARCHAR(30) NOT NULL DEFAULT 'pajak',
    persentase DECIMAL(8,2) NOT NULL DEFAULT 0,
    dasar_perhitungan DECIMAL(15,2) NOT NULL DEFAULT 0,
    nilai DECIMAL(15,2) NOT NULL DEFAULT 0,
    urutan INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    KEY idx_penawaran_taxes_penawaran_id (penawaran_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
