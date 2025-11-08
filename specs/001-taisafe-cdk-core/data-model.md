# Data Model – Taisafe-CDK 核心系統

## cdk_batches
- **Purpose**: 序號批次主檔，描述來源、模板與有效範圍。
- **Fields**
  - `id` (BIGINT, PK, auto increment)
  - `batch_name` (VARCHAR 64, unique, indexed)
  - `pattern` (VARCHAR 128) – 僅允許單層佔位符 `{DATE}`, `{RANDOMn}`, `{SEQ}`
  - `quantity` (INT, >0, <=10000)
  - `expires_at` (DATETIME, nullable)
  - `tags` (JSON) – 儲存陣列字串
  - `created_by` (BIGINT FK → users.id)
  - `created_at` (DATETIME, default CURRENT_TIMESTAMP)
- **Constraints & Indexes**
  - UNIQUE (`batch_name`)
  - INDEX (`expires_at`)
- **Relationships**
  - 1:N with `cdk_codes`（`cdk_codes.batch_id`）

## cdk_codes
- **Purpose**: 單一序號紀錄與狀態。
- **Fields**
  - `id` (BIGINT, PK)
  - `batch_id` (BIGINT FK → cdk_batches.id)
  - `code` (CHAR 32, unique)
  - `status` (ENUM drafted|issued|redeemed|expired|revoked|audited)
  - `redeemed_by` (BIGINT FK → users.id, nullable)
  - `redeemed_at` (DATETIME, nullable)
  - `metadata` (JSON) – 渠道、用途等
- **Constraints & Indexes**
  - UNIQUE (`code`)
  - INDEX (`batch_id`, `status`)
  - CHECK：`redeemed_at` NOT NULL ↔ status = redeemed|audited
- **State**
  - drafted → issued → redeemed/expired/revoked → audited

## users
- **Purpose**: 系統登入使用者與角色。
- **Fields**
  - `id` (BIGINT, PK)
  - `account` (VARCHAR 64, unique)
  - `password_hash` (VARCHAR 255)
  - `role` (ENUM admin|operator|auditor)
  - `status` (ENUM active|suspended)
  - `last_login_at` (DATETIME, nullable)
- **Constraints**
  - UNIQUE (`account`)
  - CHECK：`role` 僅允許三種

## sessions
- **Purpose**: 自訂 Session 儲存，強制 30 分鐘逾時。
- **Fields**
  - `session_id` (CHAR 64, PK)
  - `user_id` (BIGINT FK → users.id)
  - `role` (ENUM admin|operator|auditor)
  - `ip` (VARBINARY 16)
  - `user_agent` (VARCHAR 255)
  - `expires_at` (DATETIME)
- **Constraints**
  - CHECK：`expires_at` = `created_at` + 30 minutes（透過 trigger）

## audit_log
- **Purpose**: 審計紀錄。
- **Fields**
  - `id` (BIGINT, PK)
  - `actor_id` (BIGINT FK → users.id, nullable for system)
  - `action` (VARCHAR 64) – e.g., BATCH_CREATE, CODE_REDEEM
  - `target_type` (VARCHAR 64)
  - `target_id` (VARCHAR 64)
  - `payload` (JSON)
  - `ip` (VARBINARY 16)
  - `result_code` (VARCHAR 32)
  - `created_at` (DATETIME)
- **Indexes**
  - (`action`, `created_at`)
  - (`actor_id`, `created_at`)
  - (`target_type`, `target_id`)

## Relationships Overview
- users 1:N cdk_batches (created_by)
- users 1:N cdk_codes (redeemed_by)
- users 1:N audit_log (actor_id)
- cdk_batches 1:N cdk_codes
- sessions N:1 users
