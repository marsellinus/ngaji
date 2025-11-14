-- Buat database jika belum ada
CREATE DATABASE IF NOT EXISTS db_ngaji;
USE db_ngaji;

-- Tabel santri: menyimpan data santri dan RFID ID mereka
-- Santri bisa login dengan username/password untuk lihat log absensi mereka
CREATE TABLE IF NOT EXISTS santri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_santri VARCHAR(100) NOT NULL,
    rfid_id VARCHAR(50) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255),
    kelas VARCHAR(50) DEFAULT 'Umum',
    keterangan TEXT,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;    -- Tabel absensi: menyimpan data kehadiran santri
    CREATE TABLE IF NOT EXISTS absensi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        rfid_id VARCHAR(50) NOT NULL,
        waktu_absen DATETIME NOT NULL,
        status ENUM('Hadir', 'Tidak Hadir', 'Izin', 'Sakit') DEFAULT 'Hadir',
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (rfid_id) REFERENCES santri(rfid_id) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Hapus data lama jika ada (untuk re-import yang aman)
DELETE FROM absensi;
DELETE FROM santri;

-- Reset auto increment
ALTER TABLE santri AUTO_INCREMENT = 1;
ALTER TABLE absensi AUTO_INCREMENT = 1;

-- Insert data dummy santri untuk testing
-- Password untuk semua santri: admin123
INSERT INTO santri (nama_santri, rfid_id, username, password, kelas, keterangan, status) VALUES
('Ahmad Fauzi', 'A1B2C3D4', 'ahmad', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Kelas Iqro 1', 'Santri aktif', 'aktif'),
('Fatimah Zahra', 'E5F6G7H8', 'fatimah', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Kelas Iqro 2', 'Santri aktif', 'aktif'),
('Muhammad Rizki', 'I9J0K1L2', 'rizki', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Kelas Iqro 3', 'Santri aktif', 'aktif'),
('Aisyah Nur', 'M3N4O5P6', 'aisyah', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Kelas Al-Quran', 'Santri aktif', 'aktif'),
('Umar Abdullah', 'Q7R8S9T0', 'umar', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Kelas Al-Quran', 'Santri aktif', 'aktif');

-- Insert data dummy absensi untuk testing
INSERT INTO absensi (rfid_id, waktu_absen, status, keterangan) VALUES
('A1B2C3D4', '2025-11-10 08:00:00', 'Hadir', 'Datang tepat waktu'),
('E5F6G7H8', '2025-11-10 08:05:00', 'Hadir', 'Datang tepat waktu'),
('I9J0K1L2', '2025-11-10 08:10:00', 'Hadir', 'Datang tepat waktu'),
('M3N4O5P6', '2025-11-10 08:15:00', 'Hadir', 'Datang tepat waktu'),
('A1B2C3D4', '2025-11-09 08:00:00', 'Hadir', 'Datang tepat waktu'),
('E5F6G7H8', '2025-11-09 08:05:00', 'Hadir', 'Datang tepat waktu');

-- Index untuk performa query (compatible dengan MySQL 5.x)
ALTER TABLE santri ADD INDEX idx_rfid (rfid_id);
ALTER TABLE absensi ADD INDEX idx_waktu (waktu_absen);
ALTER TABLE absensi ADD INDEX idx_status (status);

-- ========================================
-- TABEL ADMIN & SISTEM MANAGEMENT
-- ========================================

-- Tabel admin: hanya untuk admin yang mengelola sistem
-- Admin bisa kelola santri, absensi, dan setting sistem
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    foto VARCHAR(255),
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel settings: untuk pengaturan sistem
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    setting_type VARCHAR(50) DEFAULT 'text',
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Tabel activity_log: untuk tracking aktivitas admin
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    activity_type VARCHAR(50),
    description TEXT,
    ip_address VARCHAR(50),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert admin default (username: admin, password: admin123)
INSERT INTO admin (username, password, nama_lengkap, email, status) VALUES
('admin', '$2y$10$t8rQGkmSBAqCvQTCMMxU4ewUF5Lb3LPEwlLbURMEL/4LhiuuTkrHu', 'Administrator', 'admin@ngaji.local', 'aktif');

-- Insert pengaturan default sistem
INSERT INTO settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'Sistem Absensi Ngaji', 'text', 'Nama aplikasi'),
('site_description', 'Sistem Absensi berbasis IoT ESP32 dan RFID', 'text', 'Deskripsi aplikasi'),
('timezone', 'Asia/Jakarta', 'text', 'Zona waktu'),
('auto_logout', '30', 'number', 'Auto logout dalam menit'),
('max_login_attempts', '3', 'number', 'Maksimal percobaan login'),
('enable_notifications', '1', 'boolean', 'Aktifkan notifikasi'),
('absensi_start_time', '06:00', 'time', 'Jam mulai absensi'),
('absensi_end_time', '22:00', 'time', 'Jam selesai absensi'),
('allow_duplicate_daily', '0', 'boolean', 'Izinkan absen lebih dari 1x per hari'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance');

-- Index untuk performa admin system (compatible dengan MySQL 5.x)
ALTER TABLE admin ADD INDEX idx_admin_username (username);
ALTER TABLE santri ADD INDEX idx_santri_username (username);
ALTER TABLE settings ADD INDEX idx_settings_key (setting_key);
ALTER TABLE activity_log ADD INDEX idx_activity_admin (admin_id);
ALTER TABLE activity_log ADD INDEX idx_activity_date (created_at);
