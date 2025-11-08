<form id="redeem-form" method="post" hx-post="/api/cdk/redeem.php" hx-target="#redeem-result" hx-swap="innerHTML">
    <label>序號
        <input type="text" name="code" maxlength="32" required>
    </label>
    <label>通路
        <input type="text" name="channel" value="counter" required>
    </label>
    <button type="submit">核銷序號</button>
</form>
<div id="redeem-result"></div>
