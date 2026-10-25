<?php
$logoUrl = !empty($logo_url) ? $logo_url : (topupcenter() . 'assets-front/images/logo/playsel-logo.png');
$greetingText = !empty($greeting) ? $greeting : (!empty($userName) ? ('Merhaba ' . htmlspecialchars($userName) . ',') : 'Merhaba,');
$otpCode = (string)(!empty($otp_code) ? $otp_code : ($otpCode ?? ''));
$expiryMinutes = !empty($expiry_minutes) ? $expiry_minutes : 3;
$supportUrl = !empty($support_url) ? $support_url : (topupcenter() . 'contact');
$badgeText = !empty($badge_text) ? $badge_text : 'İKİ AŞAMALI DOĞRULAMA';
$titleText = !empty($title) ? $title : 'Doğrulama kodun';
$introText = !empty($intro_text) ? $intro_text : 'Playsel hesabındaki işlemi tamamlamak için aşağıdaki kodu doğrulama ekranına gir.';
?>
<!doctype html>
<html lang="tr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="supported-color-schemes" content="dark">
<title>Playsel · <?= htmlspecialchars($titleText) ?></title>
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table,td{mso-table-lspace:0;mso-table-rspace:0}table{border-collapse:collapse}img{border:0;outline:none;text-decoration:none;display:block}body{margin:0!important;padding:0!important;width:100%!important}a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important}
@media screen and (max-width:620px){.outer-pad{padding:24px 12px!important}.card-pad{padding:30px 24px!important}.mail-title{font-size:26px!important;line-height:36px!important}.header-pad{padding-bottom:22px!important}.brand-logo{width:120px!important;height:auto!important}.security-tag{font-size:9px!important;letter-spacing:1px!important}.button-table{width:100%!important}.button-link{display:block!important;padding:17px 12px!important}.footer-pad{padding:22px 12px!important}}
</style>
<!--[if mso]><style>body,table,td,a,p,h1{font-family:Arial,sans-serif!important}</style><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#0d1119;color:#f5f6fa;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;font-size:1px;line-height:1px;color:#0d1119;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;"><?= htmlspecialchars($introText) ?></div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0d1119"><tr><td align="center" class="outer-pad" style="padding:48px 20px;">
<!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
<tr><td class="header-pad" style="padding:0 4px 28px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="left" valign="middle"><img class="brand-logo" src="<?= htmlspecialchars($logoUrl) ?>" alt="Playsel" width="140" height="60" style="width:140px;height:60px;object-fit:contain;"></td><td align="right" valign="middle"><span class="security-tag" style="font-size:10px;line-height:16px;letter-spacing:1.7px;color:#8e9aaf;font-weight:bold;">HESAP GÜVENLİĞİ</span></td></tr></table>
</td></tr>
<tr><td bgcolor="#1c222d" style="background-color:#1c222d;border:1px solid #303744;border-radius:20px;overflow:hidden;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td height="4" bgcolor="#ffad0a" style="height:4px;line-height:4px;font-size:1px;background-color:#ffad0a;">&nbsp;</td></tr>
<tr><td class="card-pad" align="center" style="padding:40px;text-align:center;">
<p style="margin:0 0 14px;color:#ffc052;font-size:11px;letter-spacing:1.5px;font-weight:bold;"><?= htmlspecialchars($badgeText) ?></p>
<h1 class="mail-title" style="font-size:32px;line-height:40px;margin:0 0 20px;color:#fff;"><?= htmlspecialchars($titleText) ?></h1>
<p style="font-size:15px;line-height:25px;color:#b8c1d1;margin:0 0 28px;"><strong style="color:#f0f3f8;"><?= htmlspecialchars($greetingText) ?></strong><br><?= htmlspecialchars($introText) ?></p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;"><tr><td bgcolor="#151c27" align="center" style="padding:24px 12px;border:1px solid #67502a;border-radius:12px;">
<p style="margin:0 0 12px;color:#aebbd0;font-size:11px;line-height:18px;letter-spacing:1px;">DOĞRULAMA KODUN</p>
<p id="otp-code" dir="ltr" style="margin:0;font-family:Consolas,'Courier New',monospace;font-size:36px;font-weight:bold;line-height:48px;letter-spacing:5px;color:#ffc052;white-space:nowrap;-webkit-user-select:text;user-select:text;"><?= htmlspecialchars($otpCode) ?></p>
<p style="margin:12px 0 0;color:#aebbd0;font-size:12px;line-height:20px;">Kopyalamak için kodun üzerine basılı tut.</p>
</td></tr></table>
<p style="margin:18px 0 26px;color:#aebbd0;font-size:13px;line-height:22px;">Bu kod <strong style="color:#fff;"><?= (int)$expiryMinutes ?> dakika</strong> boyunca geçerli.<br>Kodu kimseyle paylaşma.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:separate;"><tr><td style="padding:20px;background:#232b38;border:1px solid #394455;border-radius:12px;text-align:left;">
<p style="margin:0 0 8px;color:#fff;font-size:13px;font-weight:bold;">Bu işlemi sen başlatmadın mı?</p>
<p style="margin:0;color:#aebbd0;font-size:13px;line-height:22px;">Kodu kullanma veya başka biriyle paylaşma. Durumu Playsel destek ekibine bildir.</p>
<p style="margin:12px 0 0;font-size:13px;"><a href="<?= htmlspecialchars($supportUrl) ?>" ses:no-track="true" style="color:#ffc052;text-decoration:underline;">Playsel desteğe ulaş &#8594;</a></p>
</td></tr></table>
</td></tr></table>
</td></tr>
<tr><td class="footer-pad" align="center" style="padding:26px 20px 0;text-align:center;">
<p style="margin:0;color:#8290a6;font-size:11px;line-height:19px;">Bu e-posta, Playsel hesabındaki iki aşamalı doğrulama işlemi için gönderildi.</p>
</td></tr>
</table>
<!--[if mso]></td></tr></table><![endif]-->
</td></tr></table>
</body></html>
