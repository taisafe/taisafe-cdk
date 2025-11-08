# BT Panel 部署指南（對應 FR-008）

## 1. 環境需求
- BT Panel (CentOS 7+)
- Nginx 1.24、PHP-FPM 8.3、MySQL 5.7
- SSH/SFTP 權限以放置程式檔案與腳本

## 2. 部署步驟
1. 將程式碼部署於 `/www/wwwroot/taisafe-cdk`（或自訂路徑）。
2. 於 BT Panel 後台建立 Site，DocumentRoot 指向 `public/`。
3. 複製 `config/env.example.php` 為 `config/env.php` 並設定 DB/Session/安全參數。
4. 匯入資料庫：
   ```bash
   ./scripts/bt/import_schema.sh <db_user> <db_pass> <db_name>
   ```
5. 設定 Nginx：
   - 啟用 HTTPS、HTTP/2。
   - FastCGI 指向 PHP 8.3。
   - 加入 `/public/healthz` 供監測。
6. 設定檔案權限：`runtime/cache`、`runtime/logs` 需由 PHP-FPM 可寫。
7. 設定 cron / BT Panel 排程：
   - 每 5 分鐘執行 `php scripts/export_queue.php`。
   - 每日執行 `php scripts/audit/backup.php`。
8. 設定防火牆/反向 Proxy 僅允許內部網段呼叫 `/api/cdk/*`、`/api/audit/*`。

## 3. 回滾與維護
- 匯入前請使用 `mysqldump` 備份資料庫。
- 若需回滾，重新部署上一版本並匯入備份資料。
- 更新部署流程時同步調整本文件與 checklist。
