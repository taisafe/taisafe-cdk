# Requirements Quality Checklist – PR Review

**Purpose**: PR 審查用，驗證整體規格與任務的完整性/清晰度/一致性  
**Created**: 2025-11-07  
**Audience**: PR Reviewer（開始開發前的正式審核）

## Requirement Completeness

- [X] CHK001 是否每個 User Story (US1~US5) 都有對應的 FR/CT/EC ID 並記錄於 spec？[Completeness, Spec §User Stories + §Requirements]
- [X] CHK002 是否為所有需要的 API（CT-001~CT-006）提供輸入/輸出/授權/錯誤碼描述？[Completeness, Spec §Input/Output Contract]
- [X] CHK003 是否為非功能需求（安全、效能、可維運、部署、觀測）逐一列出在 Spec 與 plan？[Completeness, Spec §Non-Functional, plan Technical Context]
- [X] CHK004 tasks.md 是否覆蓋所有 FR/SC/NFR 並明確標註 ID？[Completeness, tasks.md 全檔]

## Requirement Clarity

- [X] CHK005 所有「合理」或「簡易」等模糊詞是否已被具體指標取代（如 SC-004 的 60 秒/5 分鐘）？[Clarity, Spec §Success Criteria, Spec §US5]
- [X] CHK006 是否清楚聲明稽核端點僅供內部 Session + IP 白名單使用？[Clarity, Spec §Clarifications, Spec §CT-005]
- [X] CHK007 是否說明 zero-state、匯出過期、背景失敗等情境的 UI/JSON 反饋內容？[Clarity, Spec §Edge Cases]
- [X] CHK008 tasks.md 任務描述是否具體指出檔案路徑與實作內容，避免模糊指示？[Clarity, tasks.md]

## Requirement Consistency

- [X] CHK009 稽核 API 的路徑/命名在 spec、plan、tasks、contracts 中是否完全一致？[Consistency, Spec §CT-005, plan Technical Context, tasks Phase 7, contracts/openapi.yaml]
- [X] CHK010 FR-009「無對外 API」是否與 plan Constraints 及安全任務 (T015/T045) 描述一致？[Consistency, Spec §FR-009, plan Constraints, tasks Phase N]
- [X] CHK011 Non-functional 成功準則在 plan 與 tasks 中是否有對應腳本與驗證流程？[Consistency, Spec §Success Criteria, plan Technical Context, tasks Phase N]

## Acceptance Criteria Quality

- [X] CHK012 User Story 的 Acceptance Scenario 是否可直接對應到 Spec 的 contracts 與 tasks 的測試項？[Acceptance Criteria, Spec §User Stories, tasks Phase 3-7]
- [X] CHK013 非功能需求（SC-001~SC-005）是否有具體的測量方法與成果要求（報表、腳本、CI）？[Acceptance Criteria, Spec §Success Criteria, tasks Phase N]
- [X] CHK014 是否記錄稽核圖表的資料粒度、刷新頻率與驗收條件？[Acceptance Criteria, Spec §Clarifications, Spec §US5]

## Scenario Coverage

- [X] CHK015 是否涵蓋所有角色（Admin/Operator/Auditor/Guest）在成功／失敗狀態的需求？[Coverage, Spec §User Roles + §User Stories]
- [X] CHK016 是否定義背景匯出排程失敗、重試與通知流？[Coverage, Spec §Edge Cases, tasks T047]
- [X] CHK017 是否覆蓋 Session 強制失效或安全設定改變時的行為？[Coverage, Spec §Edge Cases, tasks T019]

## Edge Case Coverage

- [X] CHK018 EC-001~EC-010 是否全部在 tasks 中具體實作或測試？[Edge Case, Spec §Error Cases, tasks]
- [X] CHK019 是否在 Spec/plan 說明匯出檔案過期（EXPORT_EXPIRED）時的使用者引導？[Edge Case, Spec §Error Cases EC-010]
- [X] CHK020 是否為 403/410/202 回應定義 log/audit 字段與審計需求？[Edge Case, Spec §Input/Output Contract, FR-007]

## Non-Functional Requirements

- [X] CHK021 效能與壓測腳本（T041~T044）是否與 Spec 的指標相符並記錄結果需求？[NFR, Spec §Success Criteria, tasks Phase N]
- [X] CHK022 Audit log 延遲監控腳本與安全掃描流程是否與 SC-003/SC-005 相符？[NFR, Spec §Success Criteria, plan Technical Context, tasks T050–T051]
- [X] CHK023 是否明確定義健康檢查 `/healthz` 與 log correlation 的可驗證條件？[NFR, Spec §Non-Functional, tasks T014]

## Dependencies & Assumptions

- [X] CHK024 是否列出所有外部依賴（BT Panel、Nginx/PHP 版本、cron/CI 工具）與設定方式？[Dependencies, plan Technical Context, tasks Phase 1-2]
- [X] CHK025 是否對「無 2FA」「僅內部 API」等假設提供替代控管措施？[Assumptions, Spec §Clarifications, tasks T015/T045/T048]
- [X] CHK026 是否記錄背景匯出、監控與備份所需的排程/CI 任務？[Dependencies, plan Technical Context, tasks T042/T047/T051]

## Ambiguities & Conflicts

- [X] CHK027 是否仍有用語（如「簡易趨勢圖」）缺少具體輸入/輸出定義？[Ambiguity, Spec §Clarifications]
- [X] CHK028 Spec/plan/tasks/contract 是否存在對同一資源不同命名或互斥敘述？[Ambiguity, 全檔]
- [X] CHK029 是否建立完整的追蹤 ID 系統（FR/CT/EC/SC/NFR）並避免重複或遺漏？[Traceability, Spec §Requirements, tasks.md]
