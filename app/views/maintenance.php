<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <?php $siteName = site_setting('site_name', getenv('SITE_NAME') ?: 'Playsel'); ?>
    <title><?= html_escape(site_setting('maintenance_title', 'Bakım Çalışması')) ?> - <?= html_escape($siteName) ?></title>
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            margin: 0;
            display: grid;
            place-items: center;
            padding: 24px;
            background: #111827;
            color: #f9fafb;
            font-family: Arial, sans-serif;
        }
        main {
            width: min(100%, 560px);
            padding: 48px 32px;
            border: 1px solid #374151;
            border-radius: 18px;
            background: #1f2937;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, .25);
        }
        .icon { font-size: 56px; margin-bottom: 20px; }
        h1 { margin: 0 0 16px; font-size: clamp(28px, 5vw, 40px); }
        p { margin: 0; color: #d1d5db; font-size: 17px; line-height: 1.6; }
    </style>
</head>
<body>
    <main>
        <div class="icon" aria-hidden="true">🔧</div>
        <h1><?= html_escape(site_setting('maintenance_title', 'Bakım çalışması yapıyoruz')) ?></h1>
        <p><?= html_escape(site_setting('maintenance_message', 'Sitemiz kısa süreliğine bakımda. Lütfen daha sonra tekrar deneyin.')) ?></p>
    </main>
</body>
</html>
