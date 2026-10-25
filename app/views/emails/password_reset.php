<?php
$logoUrl = !empty($logo_url) ? $logo_url : (topupcenter() . 'assets-front/images/logo/playsel-logo.png');
$greetingText = !empty($greeting) ? $greeting : (!empty($userName) ? ('Merhaba ' . htmlspecialchars($userName) . ',') : '');
$resetUrl = !empty($reset_url) ? $reset_url : ($resetLink ?? '#');
$expiryMinutes = !empty($expiry_minutes) ? $expiry_minutes : 5;
$resetReqUrl = !empty($reset_request_url) ? $reset_request_url : (topupcenter() . 'forgot-password');
$supportUrl = !empty($support_url) ? $support_url : (topupcenter() . 'contact');
?>
<!doctype html>
<html lang="tr" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="color-scheme" content="dark">
<meta name="supported-color-schemes" content="dark">
<title>Playsel hesabının şifresini sıfırla</title>
<style>
body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%}table,td{mso-table-lspace:0;mso-table-rspace:0}table{border-collapse:collapse}img{border:0;outline:none;text-decoration:none;display:block}body{margin:0!important;padding:0!important;width:100%!important}a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important}
@media screen and (max-width:620px){.outer-pad{padding:24px 12px!important}.card-pad{padding:30px 24px!important}.mail-title{font-size:28px!important;line-height:36px!important}.header-pad{padding-bottom:22px!important}.brand-logo{width:120px!important;height:auto!important}.security-tag{font-size:9px!important;letter-spacing:1px!important}.button-table{width:100%!important}.button-link{display:block!important;padding:17px 12px!important}.footer-pad{padding:22px 12px!important}}
</style>
<!--[if mso]><style>body,table,td,a,p,h1{font-family:Arial,sans-serif!important}</style><![endif]-->
</head>
<body style="margin:0;padding:0;background-color:#0d1119;color:#f5f6fa;font-family:Arial,Helvetica,sans-serif;">
<div style="display:none;font-size:1px;line-height:1px;color:#0d1119;max-height:0;max-width:0;opacity:0;overflow:hidden;mso-hide:all;">Playsel hesabın için yeni bir şifre belirle. Bağlantı <?= (int)$expiryMinutes ?> dakika boyunca geçerli.</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0d1119"><tr><td align="center" class="outer-pad" style="padding:48px 20px;">
<!--[if mso]><table role="presentation" width="600" cellpadding="0" cellspacing="0"><tr><td><![endif]-->
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="max-width:600px;">
<tr><td class="header-pad" style="padding:0 4px 28px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td align="left" valign="middle"><img class="brand-logo" src="<?= htmlspecialchars($logoUrl) ?>" alt="Playsel" width="140" height="60" style="width:140px;height:60px;object-fit:contain;"></td><td align="right" valign="middle"><span class="security-tag" style="font-size:10px;line-height:16px;letter-spacing:1.7px;color:#8e9aaf;font-weight:bold;">HESAP GÜVENLİĞİ</span></td></tr></table>
</td></tr>
<tr><td bgcolor="#1c222d" style="background-color:#1c222d;border:1px solid #303744;border-radius:20px;overflow:hidden;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td height="4" bgcolor="#ffad0a" style="height:4px;line-height:4px;font-size:1px;background-color:#ffad0a;">&nbsp;</td></tr>
<tr><td class="card-pad" align="center" style="padding:40px;text-align:center;">
<table role="presentation" align="center" cellpadding="0" cellspacing="0"><tr><td bgcolor="#343025" style="padding:9px 12px;border:1px solid #67502a;border-radius:7px;color:#ffc052;font-size:10px;font-weight:bold;line-height:14px;letter-spacing:1.3px;">ŞİFRE SIFIRLAMA</td></tr></table>
<h1 class="mail-title" style="font-size:34px;line-height:42px;letter-spacing:-1px;margin:24px 0 18px;color:#ffffff;font-weight:700;">Şifreni sıfırla</h1>
<p style="font-size:15px;line-height:25px;color:#b8c1d1;margin:0 0 28px;">
<?php if (!empty($greetingText)): ?>
<strong style="color:#f0f3f8;"><?= htmlspecialchars($greetingText) ?></strong><br>
<?php endif; ?>
Playsel hesabın için şifre sıfırlama talebi aldık. Aşağıdaki butona tıklayarak yeni şifreni oluşturabilirsin.</p>
<table class="button-table" align="center" role="presentation" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" bgcolor="#ffa500" style="border-radius:10px;background-color:#ffa500;">
<!--[if mso]><v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="<?= htmlspecialchars($resetUrl) ?>" style="height:54px;v-text-anchor:middle;width:240px;" arcsize="16%" stroke="f" fillcolor="#ffa500"><w:anchorlock/><center style="color:#171b22;font-family:Arial,sans-serif;font-size:15px;font-weight:bold;">Şifremi sıfırla</center></v:roundrect><![endif]-->
<!--[if !mso]><!--><a class="button-link" href="<?= htmlspecialchars($resetUrl) ?>" ses:no-track="true" style="display:inline-block;padding:17px 34px;color:#171b22;font-size:15px;line-height:20px;font-weight:bold;text-decoration:none;border-radius:10px;">Şifremi sıfırla &nbsp; &#8594;</a><!--<![endif]-->
</td></tr></table>
<p style="margin:17px 0 8px;color:#929eb2;font-size:12px;line-height:20px;">Bu bağlantı <strong style="color:#d2d9e6;font-weight:600;"><?= (int)$expiryMinutes ?> dakika</strong> boyunca geçerli.</p>
<p style="margin:0 0 28px;font-size:12px;line-height:20px;color:#929eb2;">Bağlantının süresi dolduysa <a href="<?= htmlspecialchars($resetReqUrl) ?>" ses:no-track="true" style="color:#ffc052;text-decoration:underline;">yeni bir sıfırlama bağlantısı iste.</a></p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr><td bgcolor="#151c27" style="padding:22px 24px;border:1px solid #59472e;border-radius:12px;text-align:center;background-color:#151c27;">
<p style="margin:0 0 10px;font-size:13px;line-height:21px;color:#ffc052;font-weight:bold;">Bu talep sana ait değil mi?</p>
<p style="margin:0;font-size:13px;line-height:22px;color:#aebbd0;">Sıfırlama bağlantısını kullanma. Hesabının güvenliği için durumu hemen <strong style="color:#f0f3f8;">Playsel destek ekibine bildir.</strong></p>
<p style="margin:14px 0 0;font-size:13px;line-height:22px;"><a href="<?= htmlspecialchars($supportUrl) ?>" ses:no-track="true" style="color:#ffc052;text-decoration:underline;font-weight:bold;">Playsel desteğe ulaş &#8594;</a></p>
</td></tr></table>
</td></tr></table>
</td></tr>
<tr><td class="footer-pad" align="center" style="padding:26px 20px 0;text-align:center;">
<p style="margin:0 0 10px;color:#8b97ac;font-size:12px;line-height:20px;">Buton çalışmıyorsa aşağıdaki bağlantıyı kopyalayıp tarayıcında aç:</p>
<p style="margin:0 0 24px;font-size:11px;line-height:19px;word-break:break-all;overflow-wrap:anywhere;"><a href="<?= htmlspecialchars($resetUrl) ?>" ses:no-track="true" style="color:#aeb9cd;text-decoration:underline;word-break:break-all;"><?= htmlspecialchars($resetUrl) ?></a></p>
<p style="margin:0;color:#6f7c92;font-size:11px;line-height:18px;">Bu e-posta, Playsel hesabın için yapılan şifre sıfırlama talebi üzerine gönderildi.</p>
</td></tr>
</table>
<!--[if mso]></td></tr></table><![endif]-->
</td></tr></table>
</body></html>
