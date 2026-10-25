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
