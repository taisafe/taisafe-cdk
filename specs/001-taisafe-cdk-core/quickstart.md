# Quickstart – Taisafe-CDK 核心系統

## 1. 環境需求
- BT Panel（Nginx 1.24 + PHP 8.3 + MySQL 5.7）
- Node 18（僅供 Cypress 煙測）與 npm
- Composer 非必須；僅使用原生 PHP

## 2. 安裝步驟
1. 建立資料庫並匯入 `database/setup.sql`（含 `cdk_batches`, `cdk_codes`, `audit_log`, `users`, `sessions`）。
2. 複製 `config/env.example.php` 為 `config/env.php`，填寫 DB host、帳密、Session 金鑰。
3. 於 BT Panel 設定站點指向 `public/`，啟用 HTTPS 與 HTTP/2，FastCGI 使用 PHP 8.3。
4. 建立 `runtime/cache`、`runtime/logs`，賦予 PHP-FPM 寫入權限。
5. 安裝前端依賴（Bootstrap 5、HTMX、Hyperscript）至 `public/`（已隨 Repo 提供）。

## 3. 測試指令
```bash
php vendor/bin/phpunit --configuration phpunit.xml
npm install && npx cypress run --spec "tests/smoke/htmx.cy.js"
```

## 4. 啟動
```bash
php -S localhost:8080 -t public
```
或使用 BT Panel Nginx 站點並重載服務。

## 5. 帳號初始化
```sql
INSERT INTO users (account, password_hash, role, status)
VALUES ('admin', PASSWORD('ChangeMe123!'), 'admin', 'active');
```

## 6. 重要腳本
- `artisan/schedule.php`（或 cron）每 5 分鐘執行 `php scripts/export_queue.php`
- `scripts/audit_backup.php` 每日匯出 `audit_log` 至安全位置

## 7. 驗證清單
- 造訪 `/auth/login` 能登入/登出並於 `audit_log` 看到記錄
- `/cdk/batches` 可建立 100 筆測試序號並確認 `cdk_codes`
- `/cdk/redeem` 測試成功與重複核銷行為
- `/audit/logs` 呈現表格 + 簡易圖表且可下載 CSV
