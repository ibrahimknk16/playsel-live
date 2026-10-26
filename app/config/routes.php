<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| 1) Prefix‐less exceptions
|   “home”, “v1” ve “callback” controller’ları hiçbir /tr, /en vs. olmadan çalışsın
|--------------------------------------------------------------------------
|
|  - /home
|  - /home/xxx
|  - /v1/…
|  - /callback/…
|
*/
$route['auth/googleLogin'] = 'auth/googleLogin';
$route['auth/googleCallback'] = 'auth/googleCallback';
$route['feeds/googleMerchantFeed.xml'] = 'feeds/googleMerchantFeed';
$route['(home|v1|callback|payment|feeds|cron|cron2)(/.*)?'] = '$0';

// The redirect hook validates this pattern against active locales in the DB.
// Keeping the route generic allows a newly activated locale to work without
// changing the route configuration.
$lang = '(?:[a-z]{2}-[A-Z]{2,3})';

// Ana dizindeki sitemap index rotası
$route['sitemap.xml'] = 'feeds/sitemaps';
$route['sitemap_index.xml'] = 'feeds/sitemaps';
$route['sitemap'] = 'feeds/sitemaps';
$route['news.xml'] = 'feeds/newsSitemap';
$route['google-merchant-feed.xml'] = 'feeds/googleMerchantFeed';
$route['contact-us'] = 'home/contactUs';

{
    // 1. STATİK VE NET URL'LER (Öncelikli grup, performans için en başa)
    $route["{$lang}/404"] = "homee/error404";
    $route["{$lang}/user-agreement"] = "homee/staticPages";
    $route["{$lang}/cookie-policy"] = "homee/staticPages";
    $route["{$lang}/refund-policy"] = "homee/staticPages";
    $route["{$lang}/privacy-policy"] = "homee/staticPages";
    $route["{$lang}/aml-kyc-policy"] = "homee/staticPages";
    $route["{$lang}/kvkk-policy"] = "homee/staticPages";
    $route["{$lang}/distance-sales-agreement"] = "homee/staticPages";
    $route["{$lang}/about-us"] = "homee/staticPages";
    $route["{$lang}/contact"] = "homee/contactUs"; 
    
    // $route["{$lang}/terms-conditions"] = "home/staticPages";
    $route["{$lang}/check-user"] = "categoriess/checkUser";
    // $route["{$lang}/faq"] = "home/staticPages";
    $route["{$lang}/login"] = "auth/login";
    $route["{$lang}/login/google"] = "auth/googleLogin";
    $route["{$lang}/login/google/callback"] = "auth/googleCallback";
    $route["{$lang}/register"] = "auth/register";
    $route["{$lang}/forgot-password"] = "auth/forgotPassword";
    $route["{$lang}/reset-password"] = "auth/resetPassword";
    $route["{$lang}/logout"] = "auth/logout";
    $route["{$lang}/mail-verify"] = "auth/mailVerify";
    $route["{$lang}/profile"] = 'profile/index';
    $route["{$lang}/news.xml"] = 'feeds/newsSitemap';
    $route["{$lang}/news"] = 'news/index';
    $route["{$lang}/news/(:any)"] = 'news/detail/$1';

    // 2. CRITICAL FIX: Dil bazlı sitemap'ler (:any) ve (.+) kurallarına takılmasın diye burada araya giriyor
    $route["{$lang}/sitemap.xml"] = 'feeds/sitemap';
    $route["{$lang}/sitemap"] = 'feeds/sitemap';

    // 3. PARAMETRELİ SPESİFİK KURALLAR
    $route["{$lang}/invoice/(:any)"] = 'payment/invoice/$1';
    $route["{$lang}/profile/order-detail/(:num)"] = 'profile/orderDetail/$1';
    $route["{$lang}/profile/order-detail/([^/]+)"] = 'profile/legacyOrderDetail/$1';
    $route["{$lang}/profile/change-region"] = 'profile/changeRegion';
    //$route["{$lang}/(:any)-p-(:num)"] = "products/index/$2";
    //$route["{$lang}/(:any)-c-(:num)"] = "categories/index/$2";
    $route["{$lang}/categories"] = "categoriess/index/$2";
    // $route["{$lang}/(:any)"] = "categories/categoryDetail/$1";
    $route["{$lang}/(:any)"] = "categoriess/categoryDetail/$1";
    $route[$lang]          = 'homee';
    $route["{$lang}/(.+)"] = '$1';
}

/*
|--------------------------------------------------------------------------
| 3) Standart fallback
|--------------------------------------------------------------------------
*/
$route['default_controller']   = 'homee';
$route['404_override']         = 'homee/error404';
$route['translate_uri_dashes'] = FALSE;