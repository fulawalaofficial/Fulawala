<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Password Reset Code</title>
</head>
<body style="margin:0;padding:24px;background:#fff7ef;font-family:Arial,sans-serif;color:#1f2937;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #f4c99b;border-radius:18px;padding:28px;">
        <div style="font-size:30px;margin-bottom:8px;">🌸</div>
        <h2 style="margin:0 0 12px;color:#b93a00;">Reset your Flower Delivery password</h2>
        <p style="line-height:1.6;">Use this one-time code in the Flower Delivery app:</p>
        <div style="font-size:34px;font-weight:800;letter-spacing:8px;background:#fff0e2;padding:18px;border-radius:14px;text-align:center;color:#b93a00;">
            {{ $code }}
        </div>
        <p style="line-height:1.6;margin-top:18px;">This code expires in {{ $expiresInMinutes }} minutes and can be used only once.</p>
        <p style="line-height:1.6;color:#6b7280;">If you did not request a password reset, you can ignore this email.</p>
    </div>
</body>
</html>
