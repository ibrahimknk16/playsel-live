<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Topup Center Invoice</title>
    <style>
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            text-align: center;
            color: #777;
        }

        body h1 {
            font-weight: 300;
            margin-bottom: 0px;
            padding-bottom: 0px;
            color: #000;
        }

        body h3 {
            font-weight: 300;
            margin-top: 10px;
            margin-bottom: 20px;
            font-style: italic;
            color: #555;
        }

        body a {
            color: #06f;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            line-height: 24px;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }

        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 600px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="<?= topupcenter() ?>assets-front/images/topup-logo-black.png"
                                alt="Topup Center logo" style="width: 100%; max-width: 150px" />
                            </td>
                            <td style="font-size: 12px;">
                                DIJIGATE Technology Services L.L.C FZ<br />
                                Meydan Grandstand, 6th floor, Meydan Road, Nad Al Sheba, Dubai, U.A.E.<br />
                                Certificate No: 2422413.01
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <h4>Customer:</h4>
                                <?= html_escape(trim(($order->user_name ?? '') . ' ' . ($order->user_surname ?? '')) ?: $order->user_name) ?><br />
                                <?= html_escape($order->user_email) ?>
                            </td>
                            <td>
                                Invoice #: <?= $order->order_id ?><br />
                                Order Date: <?= $order->order_created_at ?><br />
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Product</td>

                <td>Price</td>
            </tr>
            <tr class="item">
                <td><?= $order->product_name ?></td>

                <td><?= $order->order_sale_price ?> <?= $order->order_sale_price_currency ?></td>
            </tr>
            <tr class="total">
                <td></td>
                <td>Total: <?= $order->order_sale_price ?> <?= $order->order_sale_price_currency ?></td>
            </tr>
        </table>
        <a href="https://topupcenter.com" target="_blank" style="display: block; text-align: center; margin-top: 20px; font-size: 16px; font-weight:700; color:rgb(229, 185, 100); text-decoration: none;">topupcenter.com</a>
    </div>
    <button onclick="window.print()" style="margin-top: 20px; padding: 10px 20px; font-size: 16px; background-color: #4CAF50; color: white; border: none; cursor: pointer;">Print Invoice</button>
    <button onclick="saveAsPDF()" style="margin-top: 20px; padding: 10px 20px; font-size: 16px; background-color: #2196F3; color: white; border: none; cursor: pointer;">Save as PDF</button>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function saveAsPDF() {
            const element = document.querySelector('.invoice-box');
            const opt = {
                margin: 1,
                filename: '<?= $order->order_id ?>.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            // html2pdf kütüphanesi ile PDF oluştur ve indir
            html2pdf().set(opt).from(element).save();
        }
    </script>

</body>

</html>