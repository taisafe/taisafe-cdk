# Requirements Quality Checklist – 全域

**Purpose**: 審查 Taisafe-CDK 核心系統需求（功能＋非功能）在實作前的完整性、清晰度、一致性與可追蹤性  
**Created**: 2025-11-07  
**Audience**: PR Reviewer（需求與規格審閱）

## Requirement Completeness

- [X] CHK001 是否針對所有 User Story（US1~US5）列出完整輸入/輸出與獨立驗收條件？[Completeness, Spec §User Stories]
- [X] CHK002 是否為 CTS（CT-001~CT-006）提供對應錯誤碼與授權描述？[Completeness, Spec §Input/Output Contract]
- [X] CHK003 是否在非功能需求中涵蓋部署、觀測、安全、效能與備援流程？[Completeness, Spec §Non-Functional Requirements]
- [X] CHK004 是否在 tasks.md 中為每個 FR/SC/NFR 指派至少一個任務並引用 ID？[Completeness, tasks.md 全檔]

## Requirement Clarity

- [X] CHK005 「零資料」與「匯出過期」行為是否在 Edge Cases 中提供具體訊息與後續動作？[Clarity, Spec §Edge Cases]
- [X] CHK006 是否為「合理時間」類描述提供量化條件（例如 SC-004 的 60 秒/5 分鐘）？[Clarity, Spec §Success Criteria, Spec §US5]
- [X] CHK007 是否於 plan.md 清楚界定稽核匯出端點僅供內部使用與受 Session/IP 控制？[Clarity, plan.md Summary & Constraints]
- [X] CHK008 tasks.md 的每條任務是否清楚列出檔案路徑與輸入輸出細節，避免模糊敘述？[Clarity, tasks.md Phase 1-Phase N]

## Requirement Consistency

- [X] CHK009 稽核 API 的命名與路徑是否在 Spec/Plan/Tasks 三者一致（`/api/audit/logs.php` / `/api/audit/export.php`）？[Consistency, Spec §CT-005, plan.md, tasks.md Phase 7]
- [X] CHK010 FR-009「無對外 API」是否與 Clarifications、plan Constraints 及防火牆任務敘述一致？[Consistency, Spec §FR-009, Clarifications, plan.md Constraints, tasks T045]
- [X] CHK011 成功準則（SC-001~SC-005）是否在 plan 與 tasks 中皆有對應量測與自動化機制？[Consistency, Spec §Success Criteria, plan Technical Context, tasks Phase N]

## Acceptance Criteria Quality

- [X] CHK012 User Story acceptance scenarios 是否與對應的 Contracts/FR 互相呼應且可客觀檢驗？[Acceptance Criteria, Spec §User Stories + §Input/Output Contract]
- [X] CHK013 是否為非功能指標（SC-001~SC-005）定義實際驗證方法，並在 quickstart 或 runbook 中指示操作？[Acceptance Criteria, Spec §Success Criteria, quickstart.md, docs/runbooks 宣告]
- [X] CHK014 是否記錄了稽核圖表（趨勢/長條）所需資料點、頻率與驗收條件？[Acceptance Criteria, Spec §Clarifications, Spec §US5]

## Scenario Coverage

- [X] CHK015 是否定義登入/Session 強制失效及回復流程（含被 Admin 強制登出）？[Coverage, Spec §Edge Cases, FR-004]
- [X] CHK016 是否在 Spec/taks 內描述背景匯出失敗、重試與通知行為？[Coverage, Spec §Edge Cases, tasks T047]
- [X] CHK017 是否定義零資料、批次/稽核列表為空時 UI/JSON 應呈現之狀態？[Coverage, Spec §Edge Cases, tasks T025/T032/T038]

## Edge Case Coverage

- [X] CHK018 EC-001~EC-010 是否在任務或測試中皆有對應處理？[Edge Case, Spec §Error Cases, tasks Phase 4-7]
- [X] CHK019 是否針對匯出檔案過期（`EXPORT_EXPIRED`）提供重新產製或提醒流程？[Edge Case, Spec §Error Cases EC-010, tasks T039/T040]
- [X] CHK020 是否為 API 403/410/202 等負面回應定義需要紀錄的 audit 欄位？[Edge Case, Spec §Input/Output Contract, FR-007]

## Non-Functional Requirements

- [X] CHK021 是否為效能與壓測需求（P95、10k 批次、背景匯出）提供腳本、頻率與成功準則？[NFR, Spec §Non-Functional, plan Technical Context, tasks T041–T044]
- [X] CHK022 是否將 audit log 寫入延遲量測（SC-003）與安全掃描（SC-005）記錄在計畫並於任務中落實？[NFR, Spec §Success Criteria, plan Technical Context, tasks T050–T051]
- [X] CHK023 是否描述資料備份與健康檢查（/healthz、log correlation）的具體驗收條件？[NFR, Spec §Non-Functional, tasks T014, T047]

## Dependencies & Assumptions

- [X] CHK024 是否列出所有外部依賴（BT Panel、Nginx/PHP 版本、cron/排程）並在 plan/tasks 中提供設定步驟？[Dependencies, plan Technical Context, tasks Phase 1–2]
- [X] CHK025 是否明確標註「無第三方核銷 API」「無 2FA」等假設並提供替代安全措施？[Assumptions, Spec §Clarifications, tasks T015, T048]
- [X] CHK026 是否記錄背景匯出與 audit 監測需依賴的排程/CI 工具？[Dependencies, plan Technical Context, tasks T042, T047, T051]

## Ambiguities & Conflicts

- [X] CHK027 是否存在任何詞彙（如「簡易趨勢圖」）仍缺具體規格？若有，是否在 Open Questions 或 TODO 中追蹤？[Ambiguity, Spec §Clarifications, Spec §Open Questions]
- [X] CHK028 是否存在 Spec/Plan/Tasks 對同一資源使用不同命名或行為（例如 `/audit/logs` vs `/api/audit/logs.php`）？[Ambiguity, Spec §CT-005 等]
- [X] CHK029 是否所有需要的追蹤 ID（FR/CT/EC/SC/NFR）皆已建立且無重複或遺漏？[Traceability, Spec §FR/CT/EC, tasks.md]
