<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('news_content_clean_html')) {
	/**
	 * Removes editor/AI metadata attributes without changing the content markup.
	 */
	function news_content_clean_html($html)
	{
		$html = (string) $html;

		if ($html === '') {
			return '';
		}

		return preg_replace(
			'/\s+data-(?:path-to-node|index-in-node)\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/iu',
			'',
			$html
		);
	}
}

if (!function_exists('safe_rich_html')) {
	/**
	 * Strips active content (script/iframe/event handlers/javascript: URLs) from rich HTML.
	 */
	function safe_rich_html($html)
	{
		$html = (string) $html;
		if (trim($html) === '' || strpos($html, '<') === false) {
			return $html;
		}

		$previous = libxml_use_internal_errors(true);
		$dom = new DOMDocument('1.0', 'UTF-8');
		$loaded = $dom->loadHTML('<?xml encoding="UTF-8"><div id="safe-rich-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
		libxml_clear_errors();
		libxml_use_internal_errors($previous);
		if (!$loaded) {
			return html_escape(strip_tags($html));
		}

		$xpath = new DOMXPath($dom);
		foreach (array('script', 'iframe', 'object', 'embed', 'form', 'base', 'meta', 'link', 'style', 'applet', 'frame', 'frameset') as $tag) {
			foreach (iterator_to_array($dom->getElementsByTagName($tag)) as $node) {
				$node->parentNode->removeChild($node);
			}
		}
		foreach ($xpath->query('//*[@*]') as $node) {
			foreach (iterator_to_array($node->attributes) as $attr) {
				$name = strtolower($attr->nodeName);
				$value = preg_replace('/[\x00-\x20\x7f]+/', '', html_entity_decode($attr->nodeValue, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
				if (strpos($name, 'on') === 0 || $name === 'srcdoc' || $name === 'formaction'
					|| (in_array($name, array('href', 'src', 'xlink:href', 'action', 'poster', 'data'), true) && preg_match('/^(javascript|vbscript|data):/i', $value))) {
					$node->removeAttributeNode($attr);
				}
			}
		}

		$root = $dom->getElementById('safe-rich-root');
		if (!$root) {
			foreach ($xpath->query('//div[@id="safe-rich-root"]') as $root) {
				break;
			}
		}
		if (!$root) {
			return html_escape(strip_tags($html));
		}
		$output = '';
		foreach ($root->childNodes as $child) {
			$output .= $dom->saveHTML($child);
		}
		return $output;
	}
}
