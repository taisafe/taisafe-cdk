<!--
Sync Impact Report
Version change: N/A → 1.0.0
Modified principles: N/A (initial publication)
Added sections: Architecture & Code Standards; Delivery Workflow & Quality Gates
Removed sections: None
Templates requiring updates:
- ✅ .specify/templates/plan-template.md
- ✅ .specify/templates/spec-template.md
- ✅ .specify/templates/tasks-template.md
Follow-up TODOs: None
-->

# Taisafe-CDK Constitution

## Core Principles

### I. 規格驅動的單一真實來源
- 任何功能、流程、資料結構 MUST 對應 `/specs/` 下的 `.spec.md`，缺失規格者不得開發或部署。
- 規格 MUST 包含 Goal、User Stories、Input/Output Contract、State Transition、Error Cases，並維護追蹤編號。
- 實作、測試與提交訊息 MUST 引用相關規格條目，確保稽核可追溯。
**Rationale**：以規格統一定義行為邊界與驗收標準，支撐可審計與可預測的交付。

### II. 原生簡約與目錄清晰
- 系統堅持 Nginx + PHP 8.3 + MySQL 5.7 + Bootstrap 5 + HTMX + Hyperscript，禁止未經憲章批准的額外框架或編譯流程。
- `/public/`（前端）、`/src/`（業務邏輯）、`/config/`（設定）、`/runtime/`（暫存與日誌）分層 MUST 維持，並以 `snake_case` 命名。
- Hyperscript 僅限輕量互動；複雜狀態管理 MUST 回到 PHP/HTMX 實作。
**Rationale**：維持原生、簡潔的可維運架構，落實 KISS 與 YAGNI。

### III. 可預測模組契約
- 每個模組 MUST 宣告輸入、輸出、依賴與錯誤處理，並於規格的契約章節中詳載。
- 程式碼 MUST 以單一職責拆分；違反 SRP 的檔案需在審查中被拒絕或拆分重寫。
- 單元／整合測試 MUST 覆蓋規格中的正常與失敗路徑，嚴禁隱含狀態或未記錄的副作用。
**Rationale**：透過明確契約與測試，確保模組行為可預期與可替換。

### IV. 可追蹤變更與審計
- 所有變更 MUST 連結規格或任務單，並於提交訊息遵循 Conventional Commits。
- `audit_log` MUST 記錄登入、登出、權限調整、序號建立／核銷／匯入匯出、設定改變等敏感操作。
- 任何未更新規格的程式改動 MUST 被阻擋；文件、程式與審計紀錄需同步。
**Rationale**：維持完整變更脈絡，使審計與回溯具備證據鏈。

### V. 預設安全與合規運行
- 輸入驗證、輸出過濾、防 SQL Injection/XSS/CSRF 為必備工作項；Session 需啟用 `HttpOnly`、`Secure` 與強制過期控制。
- 憑證、密碼、金鑰 MUST 透過環境變數或 `/config/` 受控檔案管理，嚴禁硬編碼。
- 例行備份資料庫、設定與審計日誌，並定期演練還原；安全缺口須立即通報並建立補救規格。
**Rationale**：以安全預設保護序號資產與企業營運合規需求。

## Architecture & Code Standards

- `PHP` 檔案僅承擔單一責任，跨模組共用邏輯應抽離至服務或協助函式。
- 控制器/入口點 MUST 僅處理請求協調，資料處理與驗證在專用服務內執行。
- Bootstrap 5 為唯一樣式框架；自訂樣式需於 `/public/css/` 並建立規格中的契約說明。
- HTMX 呼叫 MUST 指向 Http endpoint，並於規格契約記錄 `hx-*` 行為；Hyperscript 片段需簡單可審查。
- PSR-12 為強制編碼標準；Formatter/Linter 設定需維持在版本控制中。

## Delivery Workflow & Quality Gates

- 任務啟動前 MUST 通過「Constitution Check」：確認規格齊備、架構一致、安全審計計畫與追蹤連結。
- `/speckit.plan`、`/speckit.spec`、`/speckit.tasks` 產物 MUST 直接引用規格 ID、Contract ID 與 Error Case 編號。
- Code Review MUST 驗證：規格同步更新、測試覆蓋、目錄與命名符合憲章、審計紀錄與安全控制已實作或排程。
- 發布前 MUST 完成：資料庫備援檢查、日誌與備份策略確認、主要用例手動或自動驗證、憲章差異審閱。

## Governance

- 此憲章優先於其他開發指南；與憲章衝突的流程或工具需先通過修憲。
- 修訂流程：提出議案 → 團隊審議與共識 → 更新規格與相關模板 → 調整版本號並記錄於版本線。
- 版本遵循 Semantic Versioning：重大原則變更使用 MAJOR，新增顯著指引用 MINOR，文字澄清使用 PATCH。
- 全體貢獻者 MUST 於提交描述引用本憲章版本（`constitution v1.0.0` 等）與相關條目。
- 每季至少一次憲章遵循度審查，產出報告與必要改善提案。

**Version**: 1.0.0 | **Ratified**: 2025-11-07 | **Last Amended**: 2025-11-07
