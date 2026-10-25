<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? html_escape($title) : 'Sözleşme' ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f9f9f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .print-btn-wrapper {
            text-align: right;
            margin-bottom: 20px;
        }
        .btn-print {
            background-color: #2980b9;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-print:hover {
            background-color: #2471a3;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border: none;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }

        /* Generic styles to ensure the injected HTML looks good */
        .ps-static-body h1 {
            font-size: 22px;
            text-align: center;
            color: #2980b9;
            border-bottom: 2px solid #2c3750;
            padding-bottom: 10px;
            margin-bottom: 30px;
            text-transform: uppercase;
        }
        .ps-static-body h2 {
            font-size: 18px;
            color: #2980b9;
            margin-top: 30px;
            border-bottom: 2px solid #ecf0f1;
            padding-bottom: 5px;
        }
        .ps-static-body h3 {
            font-size: 15px;
            color: #2980b9;
            margin-top: 20px;
        }
        .ps-static-body p, .ps-static-body li {
            font-size: 14px;
            margin-bottom: 12px;
            text-align: justify;
        }
        .ps-static-body ul {
            margin-top: 5px;
            margin-bottom: 15px;
            padding-left: 20px;
        }
        .ps-static-body .bold {
            font-weight: bold;
        }
        .ps-static-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .ps-static-body th, .ps-static-body td {
            border: 1px solid #bdc3c7;
            padding: 10px;
            text-align: left;
        }
        .ps-static-body th {
            background-color: #ecf0f1;
            color: #2c3750;
        }
        .ps-static-body .highlight {
            background-color: #fcf8e3;
            padding: 15px;
            border-left: 4px solid #f39c12;
            margin: 20px 0;
            border-radius: 3px;
        }
        .ps-static-body .danger {
            background-color: #fdeaea;
            padding: 15px;
            border-left: 4px solid #e74c3c;
            margin: 20px 0;
            border-radius: 3px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="print-btn-wrapper no-print">
            <button onclick="window.print()" class="btn-print">Yazdır / İndir (PDF)</button>
        </div>

        <div class="ps-static-body">
            <?= isset($content) ? $content : '' ?>
        </div>
    </div>

</body>
</html>
