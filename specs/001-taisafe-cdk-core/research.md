# Research – Taisafe-CDK 核心系統

## Decision 1: 測試與驗證策略
- **Decision**: 採 PHPUnit 11 進行單元與整合測試，並以輕量 Cypress 腳本覆蓋關鍵 HTMX 互動；資料層使用內嵌 MySQL 測試資料庫。
- **Rationale**: PHPUnit 與原生 PHP 生態相容，易於在 BT Panel 主機或 CI 上執行；Cypress 僅針對登入與序號核銷等主要流程，可驗證 HTMX partial 行為；MySQL 實際跑 schema 可提早發現索引/鎖問題。
- **Alternatives considered**:
  - Pest：語法簡潔但與既有 PHP 8.3 原生專案整合需要額外 bootstrap。
  - 手動測試：無法滿足憲章的可追溯與可重複驗證要求。

## Decision 2: 部署與環境配置
- **Decision**: 使用 BT Panel 管理 Nginx 1.24 + PHP-FPM 8.3，所有設定以版本控制的 `config/` 模板 + `.env` 匯入。
- **Rationale**: 與企業既有基礎設施一致，可快速複製環境；Nginx + PHP-FPM 提供較佳效能與可觀測性（access/error log 對齊）；配置模板化方便審計。
- **Alternatives considered**:
  - Apache + mod_php：與現行面板預設不同，需額外維護。
  - Docker 化：BT Panel 既有流程未標準化容器部署，增加複雜度。

## Decision 3: CSV 導出與長時任務
- **Decision**: 50k 筆以上的報表請求回傳 202 並建立背景排程（cron + queue table），完成後提供下載連結；較小報表同步輸出。
- **Rationale**: 避免 PHP request 超時，符合 Success Criteria；背景任務能寫入 `audit_log` 以追蹤操作者與結果。
- **Alternatives considered**:
  - 全部同步導出：對大報表易逾時、影響其他使用者。
  - 立即轉發到外部 BI：不在專案範圍且增加授權風險。

## Decision 4: Audit/Logging 儲存
- **Decision**: `audit_log` 使用 MySQL 表，並以每日匯出腳本將資料備份到離線存放；Nginx/PHP error log 以批次 ID/Session ID 標籤。
- **Rationale**: 關聯式存放便於 SQL 查詢與 CSV 導出；每日備份支援稽核要求；log 標籤能交叉比對。
- **Alternatives considered**:
  - 外部集中式 log（ELK）：需額外維運，超出目前資源。
  - 僅檔案紀錄：查詢困難且難以套用 RBAC 權限。
