-- T012: Export queue schema
CREATE TABLE IF NOT EXISTS export_queue (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_type ENUM('search_csv','audit_csv') NOT NULL,
    payload JSON NOT NULL,
    status ENUM('pending','processing','completed','failed','expired') NOT NULL DEFAULT 'pending',
    result_path VARCHAR(255) NULL,
    error_message TEXT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL,
    INDEX idx_status (status),
    CONSTRAINT fk_export_queue_user FOREIGN KEY (requested_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
