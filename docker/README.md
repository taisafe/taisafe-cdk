# Docker 開發環境

## 服務組成

- `php`: PHP 8.3 FPM，已安裝 `pdo_mysql`、`intl` 與 Composer。
- `nginx`: 反向代理並服務 `public/` 目錄，連線至 `php`。
- `db`: MySQL 5.7，預設帳號 `taisafe` / `ChangeMe123!`，資料庫 `taisafe_cdk`。

## 使用方式

```bash
# 一次性啟動
docker compose up -d

# 查看服務狀態
docker compose ps

# 匯入 schema
docker compose exec db bash -c "mysql -u root -proot taisafe_cdk < /var/www/html/database/setup.sql"

# 執行 PHPUnit
docker compose exec php php ./vendor/bin/phpunit

# 停止並移除容器
docker compose down
```

> 提醒：請將 `config/env.php` 中的 `db.host` 設為 `db` 才能連線容器內的 MySQL。
