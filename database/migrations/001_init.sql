-- T008: Core schema aligned with data-model.md
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    account VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','operator','auditor') NOT NULL DEFAULT 'operator',
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS sessions (
    session_id CHAR(64) PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin','operator','auditor') NOT NULL,
    ip VARBINARY(16) NOT NULL,
    user_agent VARCHAR(255) NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_expires_at (expires_at),
    CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cdk_batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_name VARCHAR(64) NOT NULL UNIQUE,
    pattern VARCHAR(128) NOT NULL,
    quantity INT UNSIGNED NOT NULL,
    expires_at DATETIME NULL,
    tags JSON NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_batches_creator FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cdk_codes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    code CHAR(32) NOT NULL UNIQUE,
    status ENUM('drafted','issued','redeemed','expired','revoked','audited') NOT NULL DEFAULT 'issued',
    redeemed_by BIGINT UNSIGNED NULL,
    redeemed_at DATETIME NULL,
    metadata JSON NULL,
    INDEX idx_batch_status (batch_id, status),
    CONSTRAINT fk_codes_batch FOREIGN KEY (batch_id) REFERENCES cdk_batches(id) ON DELETE CASCADE,
    CONSTRAINT fk_codes_redeemer FOREIGN KEY (redeemed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    actor_id BIGINT UNSIGNED NULL,
    action VARCHAR(64) NOT NULL,
    target_type VARCHAR(64) NULL,
    target_id VARCHAR(64) NULL,
    payload JSON NULL,
    ip VARBINARY(16) NULL,
    result_code VARCHAR(32) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_action_time (action, created_at),
    INDEX idx_actor_time (actor_id, created_at),
    CONSTRAINT fk_audit_actor FOREIGN KEY (actor_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
