<?php defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Keep locale-prefixed URLs aligned with the active locales in the database.
 *
 * This runs after the database library is loaded so disabled locales cannot be
 * indexed or rendered with an empty currentLocale object.
 */
function lang_redirect()
{
    $CI =& get_instance();

    $host = $CI->input->server('HTTP_HOST');
    $baseHost = parse_url(rtrim(topupcenter(), '/'), PHP_URL_HOST);
    if (!$host || !$baseHost || strcasecmp($host, $baseHost) !== 0) {
        return;
    }

    $locales = $CI->db->select('locale_code, locale_lang_code')
        ->from('locales')
        ->where('locale_status', 1)
        ->order_by('locale_sort', 'ASC')
        ->get()
        ->result();

    if (empty($locales)) {
        log_message('error', 'Locale redirect skipped: no active locales found.');
        return;
    }

    $localeSlugs = [];
    $localesByCountry = [];
    foreach ($locales as $locale) {
        $slug = strtolower((string)$locale->locale_lang_code) . '-' . strtoupper((string)$locale->locale_code);
        $localeSlugs[$slug] = $slug;
        $country = strtoupper((string)$locale->locale_code);
        $localesByCountry[$country][] = [
            'slug' => $slug,
            'language' => strtolower((string)$locale->locale_lang_code),
        ];
    }

    $defaultLocale = reset($localeSlugs);
    if (!$defaultLocale) {
        return;
    }

    $rawUri = $CI->input->server('REQUEST_URI') ?: '/';
    $path = parse_url($rawUri, PHP_URL_PATH) ?: '/';
    $query = parse_url($rawUri, PHP_URL_QUERY) ?: '';

    $index = basename($CI->input->server('SCRIPT_NAME') ?: '');
    if ($index !== '' && strcasecmp($index, 'index.php') === 0) {
        $path = preg_replace('#^/' . preg_quote($index, '#') . '#i', '', $path);
    }

    $uri = trim($path, '/');
    if ($uri === 'sitemap.xml') {
        return;
    }

    $segments = $uri === '' ? [] : explode('/', $uri);
    $first = $segments[0] ?? '';
    $exempt = ['home', 'v1', 'callback', 'feeds', 'payment', 'cron', 'cron2', 'auth', 'admin', 'contact-us'];

    if (in_array($first, $exempt, true)) {
        return;
    }

    $targetLocale = null;
    if (isset($localeSlugs[$first])) {
        return;
    }

    if (preg_match('/^[a-z]{2}-[a-z]{2,3}$/i', $first)) {
        array_shift($segments);
        $restSegments = $segments;
        $targetLocale = $defaultLocale;
    } else {
        $restSegments = $segments;
        $country = strtoupper((string)$CI->input->server('HTTP_CF_IPCOUNTRY'));
        $countryLocales = $localesByCountry[$country] ?? [];
        $acceptLanguage = strtolower((string)$CI->input->server('HTTP_ACCEPT_LANGUAGE'));

        foreach ($countryLocales as $countryLocale) {
            if ($acceptLanguage !== '' && strpos($acceptLanguage, $countryLocale['language']) !== false) {
                $targetLocale = $countryLocale['slug'];
                break;
            }
        }
        if ($targetLocale === null && !empty($countryLocales)) {
            $targetLocale = $countryLocales[0]['slug'];
        }
        if ($targetLocale === null) {
            $targetLocale = $defaultLocale;
        }
    }

    $target = rtrim(topupcenter(), '/') . '/' . $targetLocale;
    if (!empty($restSegments)) {
        $target .= '/' . implode('/', $restSegments);
    }
    if ($query !== '') {
        $target .= '?' . $query;
    }

    redirect($target, 'location', 301);
    exit;
}
