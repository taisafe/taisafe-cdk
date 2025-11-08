# Implementation Plan: Taisafe-CDK 核心系統

**Branch**: `001-taisafe-cdk-core` | **Date**: 2025-11-07 | **Spec**: `/specs/001-taisafe-cdk-core/spec.md`
**Input**: Feature specification from `/specs/001-taisafe-cdk-core/spec.md`

**Note**: This template is filled in by the `/speckit.plan` command. See `.specify/templates/commands/plan.md` for the execution workflow.

## Summary

建置內部 Taisafe-CDK 核心系統，覆蓋序號建立、核銷、查詢、登入權限與稽核報表/匯出（僅內部 API）；採原生 PHP 8.3 + MySQL 5.7 + Bootstrap 5 + HTMX/Hyperscript，遵循規格驅動流程，確保所有模組具有明確 I/O 契約、審計留痕與 10k 序號批次效能需求。

## Technical Context

**Language/Version**: PHP 8.3（原生）、Hyperscript 0.9、HTMX 1.9  
**Primary Dependencies**: Nginx 1.24（BT Panel 管理）、Bootstrap 5.3、MySQL 5.7、PHP PDO、PHPUnit 11（單元/整合測試）  
**Storage**: MySQL 5.7（`cdk_batches`, `cdk_codes`, `audit_log`, `users`, `sessions`）  
**Testing**: PHPUnit 11（單元與整合）、自動化 SQL 稽核腳本、HTMX 互動以 Cypress 低頻煙測  
**Target Platform**: Linux（BT Panel / CentOS 7+），Nginx + PHP-FPM（FastCGI）  
**Project Type**: 單一 web 專案（/public + /src + /config + /runtime）  
**Performance Goals**: 批量建立 10k 序號於 30 秒內完成；核銷 API p95 < 200ms；CSV 50k 筆於 60 秒出檔或轉背景任務  
**Constraints**: 僅允許原生 PHP（無 Composer 框架）、單層序號模板、無對外 API（含稽核端點皆限內部）、Session 30 分鐘固定逾時、所有操作寫入 `audit_log`  
**Scale/Scope**: 內部三角色（Admin/Operator/Auditor）、單節點部署、同時 50 使用者、每日 100k API 呼叫  
**Deployment Assets**: `database/setup.sql`, `scripts/bt/import_schema.sh`, `docs/deploy/bt-panel.md`（支援一鍵部署/回滾）  
**Observability & Security Assets**: `/healthz` endpoint、log correlation、`scripts/monitor/audit_latency.php`（SC-003 量測）、`scripts/audit/backup.php`、`tests/perf/batch_redeem.php`、`scripts/security/run_scan.sh` 以及 `.github/workflows/security_scan.yml`（SC-001~SC-005）  

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- ✅ 規格對齊：`/specs/001-taisafe-cdk-core/spec.md` 已定義 Goal、User Stories、Contracts、State Transition、Error Cases。
- ✅ 架構一致：設計維持 Nginx + PHP 8.3 + MySQL 5.7、Bootstrap 5 + HTMX/Hyperscript、`/public` `/src` `/config` `/runtime` 分層。
- ✅ 安全審計：所有端點規範輸入驗證、Session 控制（30 分鐘）、`audit_log` 留痕與 CSV 導出稽核。
- ✅ 變更追蹤：規格條目（CT/FR/EC）將映射到提交說明與計畫任務，無需求未配對。

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit.plan command output)
├── research.md          # Phase 0 output (/speckit.plan command)
├── data-model.md        # Phase 1 output (/speckit.plan command)
├── quickstart.md        # Phase 1 output (/speckit.plan command)
├── contracts/           # Phase 1 output (/speckit.plan command)
└── tasks.md             # Phase 2 output (/speckit.tasks command - NOT created by /speckit.plan)
```

### Source Code (repository root)
<!--
  ACTION REQUIRED: Replace the placeholder tree below with the concrete layout
  for this feature. Delete unused options and expand the chosen structure with
  real paths (e.g., apps/admin, packages/something). The delivered plan must
  not include Option labels.
-->

```text
public/
├── index.php
├── css/
├── js/
└── htmx/

src/
├── controllers/
├── services/
├── repositories/
├── validators/
└── middleware/

config/
├── database.php
├── env.php
└── security.php

runtime/
├── cache/
└── logs/

tests/
├── unit/
└── integration/
```

**Structure Decision**: 採單一 web 專案；所有 HTTP 入口位於 `public/`，業務與資料層放在 `src/`，設定集中在 `config/`，暫存與日誌於 `runtime/`，測試分 unit/integration。

## Complexity Tracking

> **Fill ONLY if Constitution Check has violations that must be justified**

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| [e.g., 4th project] | [current need] | [why 3 projects insufficient] |
| [e.g., Repository pattern] | [specific problem] | [why direct DB access insufficient] |

## Constitution Check (Post-Design)

- ✅ 規格映射：`research.md`、`data-model.md`、`contracts/openapi.yaml` 與 `quickstart.md` 均引用 CT/FR/EC ID。
- ✅ 架構一致：設計仍採原生 PHP + MySQL，並限制模板、Session、權限行為，無新增未授權技術。
- ✅ 安全審計：稽核匯出背景任務、固定 30 分鐘 Session、無 2FA（由密碼+IP 控制）均已記錄於 NFR 與 Clarifications。
- ✅ 追蹤與交付：產出研究/模型/契約/quickstart，同步更新 Codex agent context，具備可審計工件。
