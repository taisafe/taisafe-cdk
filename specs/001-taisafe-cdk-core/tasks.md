---

description: "Task list for Taisafe-CDK 核心系統"
---

# Tasks: Taisafe-CDK 核心系統

**Input**: Design documents from `/specs/001-taisafe-cdk-core/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: 依照憲章要求，登入/批量/核銷/查詢/稽核流程皆需對應 PHPUnit + Cypress 驗證。

**Organization**: Tasks are grouped by user story以確保獨立實作與驗收。

## Format: `[ID] [P?] [Story?] Description with file path`

- **[P]**: 可平行執行（不同檔案且無相依）
- **[Story]**: 僅在 user story phase 使用（例如 [US1]）
- 任務敘述需含檔案路徑與對應規格 ID（FR/CT/EC/SC/NFR）
- 輸入驗證、Session 管理與 `audit_log` 相關工作需明確標註

## Phase 1: Setup (Shared Infrastructure)

- [X] T001 建立 `config/env.example.php`（含 DB/Session 參數）並文件化變數需求（FR-008, SC-001, SC-002）
- [X] T002 [P] 建立 PHPUnit 設定 `phpunit.xml` 與 `phpunit.bootstrap.php`（NFR-可維護性, FR-004）
- [X] T003 [P] 建立測試啟動檔 `tests/bootstrap.php`（設定 in-memory DB 與 schema 匯入）（NFR-可維護性, FR-001~FR-005）
- [X] T004 [P] 建立 Cypress 煙測骨架 `tests/smoke/htmx.cy.js`（涵蓋登入與核銷互動）（FR-002, FR-004, SC-002）
- [X] T005 建立 `database/setup.sql`（整合 migrations 內容並支援一鍵匯入）（FR-008）
- [X] T006 建立 `scripts/bt/import_schema.sh`（於寶塔面板執行 SQL 匯入與基礎設定）（FR-008）
- [X] T007 撰寫 `docs/deploy/bt-panel.md`（覆蓋一鍵部署、回滾、權限設定）（FR-008）

---

## Phase 2: Foundational (Blocking Prerequisites)

**⚠️ CRITICAL**：此階段未完成不得進入任何 user story。

- [X] T008 建立 `database/migrations/001_init.sql`（users/sessions/cdk_batches/cdk_codes/audit_log）並保持 data-model 對應（FR-001~FR-007）
- [X] T009 [P] 實作 DB 連線封裝 `src/repositories/DatabaseConnection.php`（含連線池與重試）（NFR-可維護性, FR-001~FR-005）
- [X] T010 [P] 實作 `src/services/AuditLogger.php`（提供 `logAction` API）（FR-005, FR-007, EC-005~EC-008）
- [X] T011 實作 Session middleware `src/middleware/SessionMiddleware.php`（固定 30 分鐘逾時、HttpOnly/Secure）（FR-004, EC-006, Clarifications）
- [X] T012 [P] 建立 `database/migrations/002_export_queue.sql`（背景匯出佇列）（FR-005, CT-005, CT-006）
- [X] T013 [P] 建立背景匯出腳本 `scripts/export_queue.php`（處理 202 任務、寫入 audit_log）（FR-005, CT-005, EC-007）
- [X] T014 [P] 建立健康檢查端點 `public/healthz.php` 與 log correlation 設定（NFR-觀測性）
- [X] T015 建立 `config/security.php` 以強制內網 IP 白名單與 API 可見性（FR-009, NFR-安全性）

---

## Phase 3: User Story 4 – 用戶登入/登出與權限（Priority: P1）

**Goal**：完成安全登入、登出、Session 控制與角色守門。
**Independent Test**：透過 `/auth/login` 登入 Admin，巡覽 `/cdk/batches`、`/api/audit/logs.php` 驗證授權，再 `/auth/logout` 確認 Session 立即失效並寫入 `audit_log`。

- [X] T016 [US4] 實作 `src/repositories/UserRepository.php`（查詢帳號、密碼雜湊、狀態）（FR-004, CT-004）
- [X] T017 [US4] 建立 `src/services/AuthService.php`（Session 建立/撤銷、登入記錄）（FR-004, EC-006）
- [X] T018 [US4] 建立 `public/auth/login.php` 與 `public/auth/logout.php`（HTMX 表單 + 錯誤處理）（FR-004, CT-004）
- [X] T019 [US4] 實作 `src/middleware/RoleGuard.php`（Admin/Operator/Auditor 權限檢查並記錄 `SEC_DENY`）（FR-004, EC-005）
- [X] T020 [US4] 撰寫整合測試 `tests/integration/AuthFlowTest.php`（成功/密碼錯誤/停用/Session 逾時）（FR-004, SC-002）

---

## Phase 4: User Story 1 – 序號批量建立（Priority: P1）

**Goal**：Operator/Admin 可依單層模板建立最多 10k 序號並產出批次清單。
**Independent Test**：呼叫 `POST /cdk/batches` 建立 100 筆測試序號，驗證資料寫入並回傳 CSV。

- [X] T021 [US1] 建立 `src/models/CdkBatch.php` 與驗證邏輯（模板/數量/到期日）（FR-001, EC-001, EC-002）
- [X] T022 [US1] 實作 `src/services/BatchService.php`（序號生成、碰撞檢查、audit_log）（FR-001, FR-007）
- [X] T023 [US1] 建立 `src/controllers/BatchController.php` 與 HTMX partial `public/partials/batches/create_form.php`（FR-001）
- [X] T024 [US1] 實作 API 端點 `public/api/cdk/batches.php`（CT-001，回傳 PATTERN_DUPLICATE/LIMIT_EXCEEDED）（FR-001, EC-001, EC-002）
- [X] T025 [US1] 撰寫整合測試 `tests/integration/BatchCreateTest.php`（成功 + 錯誤情境 + zero-state）（FR-001, SC-001, EC-009）

---

## Phase 5: User Story 2 – 序號核銷（Priority: P1）

**Goal**：Operator 可核銷有效序號並立即取得成功/失敗回饋，所有操作留痕。
**Independent Test**：透過 `/cdk/redeem` 提交序號，檢查 `cdk_codes.status` 與 `audit_log` 更新，並測試重複/過期案例。

- [X] T026 [US2] 建立 `src/validators/RedeemValidator.php`（格式/狀態/到期日檢查）（FR-002, EC-003, EC-004）
- [X] T027 [US2] 實作 `src/services/RedeemService.php`（鎖定序號、寫入 `redeemed_by`、audit_log）（FR-002, FR-007）
- [X] T028 [US2] 建立 API 端點 `public/api/cdk/redeem.php`（CT-002，回傳 success/invalid/expired/used）（FR-002, EC-003, EC-004）
- [X] T029 [US2] 建立 HTMX 表單 `public/partials/redeem/form.php`（顯示結果與錯誤碼）（FR-002, EC-003）
- [X] T030 [US2] 撰寫整合測試 `tests/integration/RedeemCodeTest.php`（成功/重複/過期/權限不足）（FR-002, SC-002）

---

## Phase 6: User Story 3 – 序號查詢與管理（Priority: P2）

**Goal**：Admin/Operator 可依批次、狀態、日期查詢序號並下載 CSV/統計。
**Independent Test**：呼叫 `GET /cdk/search` 顯示分頁與統計，並透過 `GET /cdk/search_export` 取得 CSV（或 202 背景 任務）。

- [ ] T031 [US3] 建立 `src/repositories/CdkSearchRepository.php`（批次/狀態/日期篩選 + 分頁）（FR-003, CT-003）
- [ ] T032 [US3] 實作 `src/services/SearchService.php`（統計卡 + zero-state 資料）（FR-003, EC-009）
- [ ] T033 [US3] 建立 `src/controllers/SearchController.php` 與 HTMX partial `public/partials/search/results_table.php`（FR-003）
- [ ] T034 [US3] 實作 CSV 導出 `public/api/cdk/search_export.php`（CT-006，處理 200/202/410）（FR-003, EC-010, SC-004）
- [ ] T035 [US3] 撰寫整合測試 `tests/integration/CdkSearchTest.php`（404/403/匯出排程/zero-state）（FR-003, EC-009, EC-010）

---

## Phase 7: User Story 5 – 稽核與報表（Priority: P2）

**Goal**：Auditor/Admin 可查詢核銷/設定變更紀錄，查看趨勢圖並下載 CSV（必要時背景任務）。
**Independent Test**：透過 `/api/audit/logs.php` 套用條件並顯示表格 + 圖表；若資料量 >50k，API 返回 202 並在完成後提供下載連結。

- [ ] T036 [US5] 擴充 `src/repositories/AuditLogRepository.php`（篩選、分頁、統計指標）（FR-005, CT-005）
- [ ] T037 [US5] 實作 `src/services/AuditReportService.php`（趨勢/長條圖資料與 CSV 佇列整合）（FR-005, SC-004）
- [ ] T038 [US5] 建立 UI `public/views/audit/index.php`（表格、統計卡、圖表 + zero-state）（FR-005, EC-009）
- [ ] T039 [US5] 建立匯出端點 `public/api/audit/export.php`（CT-005，處理 200/202/410 並記錄 `EXPORT_EXPIRED`）（FR-005, EC-007, EC-010）
- [ ] T040 [US5] 撰寫整合測試 `tests/integration/AuditReportTest.php`（同步/背景匯出、角色限制、CSV 過期）（FR-005, EC-007, EC-010）
- [ ] T041 [US5] 建立 JSON Audit API `public/api/audit/logs.php`（支援條件篩選、分頁、內部使用）（CT-005, FR-005, FR-009）
- [ ] T042 [US5] 撰寫 API 測試 `tests/integration/AuditLogsApiTest.php`（驗證 200/403/202 與 zero-state）（CT-005, EC-009, EC-010）

---

## Phase N: Polish & Cross-Cutting Concerns

- [ ] T043 建立批量/核銷壓測腳本 `tests/perf/batch_redeem.php` 並紀錄結果（SC-001, SC-002, SC-004）
- [ ] T044 建立 CI 監測工作流程 `.github/workflows/perf.yml`（定期跑壓測並上傳報告）（SC-001, SC-002）
- [ ] T045 設定內部防火牆/反向 proxy 規則，確保核銷/查詢/稽核 API 僅限內部流量（FR-009, NFR-安全性）
- [ ] T046 更新 `specs/001-taisafe-cdk-core/quickstart.md`（新增登入、批次、匯出步驟與健康檢查）（FR-001~FR-005, FR-008, NFR-觀測性）
- [ ] T047 建立 `docs/runbooks/export_failures.md`（背景匯出常見故障與補救）（FR-005, EC-007, EC-010）
- [ ] T048 進行安全檢查清單 `docs/security/review.md`（輸入驗證、Session、audit_log、IP 白名單）（FR-004, FR-005, FR-009, NFR-安全性）
- [ ] T049 撰寫每日 `audit_log` 備份腳本 `scripts/audit/backup.php` 並更新排程指引（FR-005, NFR-可審計性）
- [ ] T050 建立 `scripts/monitor/audit_latency.php` 與告警設定，量測 audit_log 寫入時間（SC-003, FR-007）
- [ ] T051 建立安全掃描腳本 `scripts/security/run_scan.sh` 與 CI 流程 `.github/workflows/security_scan.yml`（SC-005, NFR-安全性）

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 → User Story phases → Phase N；Setup/Foundational 完成前不得實作 user story。
- US4（登入/權限）完成後，US1 與 US2 才能開始；US3 依賴批次/核銷資料；US5 依賴 US2（核銷紀錄）與背景匯出骨架。

## Parallel Opportunities

- Phase 1 中 T002~T007 可平行；Phase 2 中 T009~T015 可分工。
- US4 完成後，US1 與 US2 可由不同開發者並行；US3 與 US5 可在共用 repository/service 介面定稿後平行開發。
- 測試撰寫（T020/T025/T030/T035/T040）可由專責 QA 與實作並行，但須於整合前完成。

## Implementation Strategy

- **MVP**：完成 Phase 1-2、US4、US1，即可展示登入 + 批量建立能力。
- **Incremental**：依序交付 US2 → US3 → US5，確保每次增量皆具獨立驗收。
- **Parallel Team**：開發者 A（US4→US1）、開發者 B（US2→US3）、開發者 C（US5 + Phase N）。
