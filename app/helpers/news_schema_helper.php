<?php defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('news_schema_text')) {
	function news_schema_text($value)
	{
		$value = html_entity_decode(strip_tags((string)$value), ENT_QUOTES, 'UTF-8');
		return trim(preg_replace('/\s+/u', ' ', str_replace("\xC2\xA0", ' ', $value)));
	}
}

if (!function_exists('news_schema_node_text')) {
	// textContent yerine: blok/hücre/br sınırlarına boşluk koyar (tablo hücreleri birleşmesin).
	function news_schema_node_text($node)
	{
		if ($node->nodeType === XML_TEXT_NODE || $node->nodeType === XML_CDATA_SECTION_NODE) {
			return $node->nodeValue;
		}
		if ($node->nodeType !== XML_ELEMENT_NODE) {
			return '';
		}
		$name = strtolower($node->nodeName);
		if (in_array($name, ['script', 'style'], true)) {
			return '';
		}
		$text = '';
		foreach ($node->childNodes as $child) {
			$text .= news_schema_node_text($child);
		}
		if (in_array($name, ['br', 'p', 'li', 'td', 'th', 'tr', 'div', 'dt', 'dd', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'table', 'ul', 'ol', 'blockquote'], true)) {
			return ' ' . $text . ' ';
		}
		return $text;
	}
}

if (!function_exists('news_schema_blocks')) {
	// Haber HTML'ini sıralı blok listesine çevirir (iç içe wrapper'lar düzleştirilir).
	function news_schema_blocks($html)
	{
		$html = trim((string)$html);
		if ($html === '' || !class_exists('DOMDocument')) {
			return [];
		}
		$previous = libxml_use_internal_errors(true);
		$doc = new DOMDocument();
		$doc->loadHTML('<?xml encoding="utf-8" ?><div id="news-schema-root">' . $html . '</div>');
		libxml_clear_errors();
		libxml_use_internal_errors($previous);

		$xpath = new DOMXPath($doc);
		$nodes = $xpath->query('//div[@id="news-schema-root"]//*[self::h1 or self::h2 or self::h3 or self::h4 or self::h5 or self::h6 or self::p or self::ul or self::ol or self::dl or self::details or self::blockquote or self::table]'
			. '[not(ancestor::li or ancestor::details or ancestor::blockquote or ancestor::table or ancestor::dl)]');
		$blocks = [];
		if (!$nodes) {
			return $blocks;
		}
		foreach ($nodes as $node) {
			$tag = strtolower($node->nodeName);
			$text = news_schema_text(news_schema_node_text($node));
			if ($text === '' && $tag !== 'details') {
				continue;
			}
			$level = (strlen($tag) === 2 && $tag[0] === 'h' && ctype_digit($tag[1])) ? (int)$tag[1] : 0;
			$blocks[] = ['tag' => $tag, 'level' => $level, 'text' => $text, 'node' => $node];
		}
		return $blocks;
	}
}

if (!function_exists('news_schema_is_bold_question')) {
	function news_schema_is_bold_question($block)
	{
		if ($block['tag'] !== 'p' || substr($block['text'], -1) !== '?') {
			return false;
		}
		foreach ($block['node']->childNodes as $child) {
			if (in_array(strtolower($child->nodeName), ['strong', 'b'], true)) {
				$boldText = news_schema_text($child->textContent);
				return $boldText !== '' && mb_strlen($boldText) >= mb_strlen($block['text']) * 0.8;
			}
			if ($child->nodeType === XML_TEXT_NODE && trim($child->textContent) === '') {
				continue;
			}
			return false;
		}
		return false;
	}
}

if (!function_exists('news_schema_extract_faqs')) {
	function news_schema_extract_faqs(array $blocks)
	{
		$faqs = [];
		$add = function ($question, $answer) use (&$faqs) {
			$question = news_schema_text($question);
			$answer = news_schema_text($answer);
			if ($question !== '' && $answer !== '' && !isset($faqs[mb_strtolower($question)])) {
				$faqs[mb_strtolower($question)] = ['q' => $question, 'a' => $answer];
			}
		};

		// 1) <details><summary>Soru</summary>Cevap</details>
		foreach ($blocks as $block) {
			if ($block['tag'] !== 'details') {
				continue;
			}
			$question = '';
			$answer = '';
			foreach ($block['node']->childNodes as $child) {
				if (strtolower($child->nodeName) === 'summary') {
					$question = news_schema_node_text($child);
				} else {
					$answer .= ' ' . news_schema_node_text($child);
				}
			}
			$add($question, $answer);
		}

		// 1b) <blockquote><p><strong>Soru?</strong><br>Cevap</p></blockquote>
		foreach ($blocks as $block) {
			if ($block['tag'] !== 'blockquote') {
				continue;
			}
			$full = $block['text'];
			foreach ($block['node']->getElementsByTagName('*') as $el) {
				if (!in_array(strtolower($el->nodeName), ['strong', 'b'], true)) {
					continue;
				}
				$boldText = news_schema_text(news_schema_node_text($el));
				if ($boldText !== '' && substr($boldText, -1) === '?' && strpos($full, $boldText) === 0) {
					$add($boldText, substr($full, strlen($boldText)));
					break;
				}
			}
		}

		// 2) <dl><dt>Soru</dt><dd>Cevap</dd></dl>
		$blockCount = count($blocks);
		foreach ($blocks as $block) {
			if ($block['tag'] !== 'dl') {
				continue;
			}
			$question = '';
			foreach ($block['node']->childNodes as $child) {
				$name = strtolower($child->nodeName);
				if ($name === 'dt') {
					$question = news_schema_node_text($child);
				} elseif ($name === 'dd' && $question !== '') {
					$add($question, news_schema_node_text($child));
					$question = '';
				}
			}
		}

		// 3) "Sık Sorulan Sorular" bölümü
		for ($i = 0; $i < $blockCount; $i++) {
			$start = $blocks[$i];
			if ($start['level'] < 2 || $start['level'] > 4 || !preg_match('/(s[ıiİI]k\s*sorulan|\bsss\b|\bfaq\b|frequently\s+asked|merak\s+edilen)/iu', $start['text'])) {
				continue;
			}
			$question = '';
			$answer = '';
			for ($j = $i + 1; $j < $blockCount; $j++) {
				$block = $blocks[$j];
				if ($block['level'] > 0 && $block['level'] <= $start['level']) {
					break;
				}
				if ($block['tag'] === 'details' || $block['tag'] === 'dl') {
					continue;
				}
				$isQuestion = ($block['level'] > $start['level']) || news_schema_is_bold_question($block);
				if ($isQuestion) {
					$add($question, $answer);
					$question = $block['text'];
					$answer = '';
				} elseif ($question !== '') {
					$answer .= ' ' . $block['text'];
				}
			}
			$add($question, $answer);
		}

		// 4) Bölüm başlığı yoksa: "?" ile biten h3-h5 başlıkları ve altındaki cevap
		if (count($faqs) === 0) {
			for ($i = 0; $i < $blockCount; $i++) {
				$block = $blocks[$i];
				if ($block['level'] < 3 || $block['level'] > 5 || substr($block['text'], -1) !== '?') {
					continue;
				}
				$answer = '';
				for ($j = $i + 1; $j < $blockCount && $blocks[$j]['level'] === 0; $j++) {
					if (!in_array($blocks[$j]['tag'], ['p', 'ul', 'ol'], true)) {
						continue;
					}
					$answer .= ' ' . $blocks[$j]['text'];
				}
				$add($block['text'], $answer);
			}
		}

		return array_values($faqs);
	}
}

if (!function_exists('news_schema_extract_howto')) {
	function news_schema_extract_howto(array $blocks)
	{
		$blockCount = count($blocks);
		for ($i = 0; $i < $blockCount; $i++) {
			$start = $blocks[$i];
			if ($start['level'] < 2 || $start['level'] > 3 || substr($start['text'], -1) === '?'
				|| !preg_match('/(nas[ıiİI]l|ad[ıi]m\s*ad[ıi]m|how\s+to|ad[ıi]mlar)/iu', $start['text'])) {
				continue;
			}
			for ($j = $i + 1; $j < $blockCount; $j++) {
				$block = $blocks[$j];
				if ($block['level'] > 0 && $block['level'] <= $start['level']) {
					break;
				}
				if ($block['tag'] !== 'ol') {
					continue;
				}
				$steps = [];
				foreach ($block['node']->childNodes as $li) {
					if (strtolower($li->nodeName) !== 'li') {
						continue;
					}
					$stepText = news_schema_text(news_schema_node_text($li));
					if ($stepText === '') {
						continue;
					}
					$steps[] = [
						'@type' => 'HowToStep',
						'position' => count($steps) + 1,
						'name' => mb_strlen($stepText) > 110 ? mb_substr($stepText, 0, 107) . '...' : $stepText,
						'text' => $stepText,
					];
				}
				if (count($steps) >= 2) {
					return ['name' => $start['text'], 'steps' => $steps];
				}
			}
		}
		return null;
	}
}

if (!function_exists('news_schema_extract_videos')) {
	function news_schema_extract_videos($html)
	{
		$videos = [];
		if (preg_match_all('~(?:youtube(?:-nocookie)?\.com/embed/|youtu\.be/|youtube\.com/watch\?v=)([A-Za-z0-9_-]{11})~i', (string)$html, $m)) {
			foreach (array_unique($m[1]) as $id) {
				$videos[] = $id;
			}
		}
		return $videos;
	}
}

if (!function_exists('news_schema_graph_nodes')) {
	// Haber detayı için FAQPage / HowTo / VideoObject düğümlerini üretir.
	function news_schema_graph_nodes($news, $pageUrl, $imageUrl, $publishedAt)
	{
		$html = (string)($news->news_content ?? '');
		$blocks = news_schema_blocks($html);
		$nodes = [];

		$faqs = news_schema_extract_faqs($blocks);
		if (!empty($faqs)) {
			$items = [];
			foreach (array_slice($faqs, 0, 30) as $faq) {
				$items[] = [
					'@type' => 'Question',
					'name' => $faq['q'],
					'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
				];
			}
			$nodes[] = ['@type' => 'FAQPage', '@id' => $pageUrl . '#faq', 'mainEntity' => $items];
		}

		$howTo = news_schema_extract_howto($blocks);
		if ($howTo) {
			$nodes[] = [
				'@type' => 'HowTo',
				'@id' => $pageUrl . '#howto',
				'name' => $howTo['name'],
				'step' => $howTo['steps'],
			];
		}

		foreach (news_schema_extract_videos($html) as $index => $videoId) {
			$video = [
				'@type' => 'VideoObject',
				'@id' => $pageUrl . '#video-' . ($index + 1),
				'name' => news_schema_text($news->news_title ?? ''),
				'description' => news_schema_text(($news->news_summary ?? '') !== '' ? $news->news_summary : ($news->news_title ?? '')),
				'thumbnailUrl' => ['https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg'],
				'embedUrl' => 'https://www.youtube.com/embed/' . $videoId,
			];
			if ($publishedAt) {
				$video['uploadDate'] = $publishedAt;
			}
			$nodes[] = $video;
		}

		return $nodes;
	}
}
