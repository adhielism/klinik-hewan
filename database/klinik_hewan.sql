CREATE DATABASE IF NOT EXISTS klinik_hewan_NIM
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE klinik_hewan_NIM;

CREATE TABLE jenis_hewan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nama_jenis VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE pasien_hewan (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode_pasien CHAR(10) NOT NULL UNIQUE,
  nama_hewan VARCHAR(100) NOT NULL,
  nomor_rekam VARCHAR(30) NOT NULL UNIQUE,
  usia_tahun TINYINT UNSIGNED NOT NULL,
  jenis_hewan_id BIGINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
             ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT chk_usia CHECK (usia_tahun BETWEEN 0 AND 30),
  CONSTRAINT fk_pasien_jenis FOREIGN KEY (jenis_hewan_id)
    REFERENCES jenis_hewan(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO jenis_hewan (nama_jenis) VALUES ('Kucing'), ('Anjing');

INSERT INTO pasien_hewan
  (kode_pasien, nama_hewan, nomor_rekam, usia_tahun, jenis_hewan_id) VALUES
('PH00000001', 'Milo',  'RMH-0001', 3, (SELECT id FROM jenis_hewan WHERE nama_jenis='Kucing')),
('PH00000002', 'Bruno', 'RMH-0002', 5, (SELECT id FROM jenis_hewan WHERE nama_jenis='Anjing')),
('PH00000003', 'Luna',  'RMH-0003', 2, (SELECT id FROM jenis_hewan WHERE nama_jenis='Kucing'));