<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="utf-8">
    <title>建立序號批次</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="container py-5">
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    建立序號批次
                </div>
                <div class="card-body">
                    <form id="batch-create-form"
                          class="vstack gap-3"
                          method="post"
                          hx-post="/api/cdk/batches.php"
                          hx-target="#batch-result"
                          hx-swap="innerHTML"
                          hx-indicator="#batch-create-loading">
                        <div>
                            <label class="form-label">批次名稱</label>
                            <input type="text" class="form-control" name="batch_name" minlength="3" maxlength="64" required>
                        </div>
                        <div>
                            <label class="form-label">模板</label>
                            <input type="text" class="form-control" name="pattern" value="TAI-{DATE}-{SEQ}" required>
                            <div class="form-text">僅支援 {DATE}/{SEQ}/{RANDOMn}</div>
                        </div>
                        <div>
                            <label class="form-label">數量</label>
                            <input type="number" class="form-control" name="quantity" min="1" max="10000" value="100" required>
                        </div>
                        <div>
                            <label class="form-label">到期日</label>
                            <input type="date" class="form-control" name="expires_at">
                        </div>
                        <div>
                            <label class="form-label">標籤 (以逗號分隔)</label>
                            <input type="text" class="form-control" name="tags" placeholder="campaign,audience">
                        </div>
                        <button type="submit" class="btn btn-primary">建立批次</button>
                    </form>
                    <div id="batch-create-loading" class="htmx-indicator mt-3 text-muted">建立中...</div>
                    <div id="batch-result" class="mt-3"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="alert alert-info">
                <h5 class="alert-heading">Zero State</h5>
                <p class="mb-0">目前尚未建立任何批次。送出表單後，系統會立即生成序號並顯示結果。</p>
            </div>
        </div>
    </div>
</body>
</html>
