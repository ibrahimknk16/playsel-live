<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Search_model extends CI_Model
{
	const INDEX_FILE = 'uploads/search_index.json';

	public static function normalize(string $string): string
	{
		$string = mb_strtolower($string, 'UTF-8');
		$tr = ['ı', 'ğ', 'ü', 'ş', 'ö', 'ç', 'i̇'];
		$en = ['i', 'g', 'u', 's', 'o', 'c', 'i'];
		$string = str_replace($tr, $en, $string);
		return trim(preg_replace('/[^a-z0-9 ]+/', '', $string));
	}

	private function addToIndex(array &$index, string $title, string $idWithPrefix): void
	{
		foreach (explode(' ', self::normalize($title)) as $word) {
			if (strlen($word) < 2) {
				continue;
			}
			for ($len = 2; $len <= strlen($word); $len++) {
				$index[substr($word, 0, $len)][] = $idWithPrefix;
			}
		}
	}

	public function buildIndex(): bool
	{
		$indexFilePath = FCPATH . self::INDEX_FILE;
		$tmpFilePath = FCPATH . 'uploads/search_index.tmp.json';

		if (!is_dir(FCPATH . 'uploads')) {
			@mkdir(FCPATH . 'uploads', 0755, true);
		}

		$dbCategories = $this->db->select('category_id, category_title, category_slug, category_image, category_thumbnail, category_countries')
			->where('category_status', 1)
			->where('category_api_status', 1)
			->order_by('category_sort_id', 'ASC')
			->get('categories')->result_array();

		$categoriesMap = [];
		foreach ($dbCategories as $cat) {
			if (empty($cat['category_image']) && !empty($cat['category_thumbnail'])) {
				$cat['category_image'] = $cat['category_thumbnail'];
			}
			$categoriesMap[$cat['category_id']] = $cat;
		}

		$products = $this->db->select('product_id, product_name, product_category_id, product_image, product_countries')
			->where('product_status', 1)
			->where('product_api_status', 1)
			->order_by('product_sort_id', 'ASC')
			->get('products')->result_array();

		$index = [];
		$productsData = [];
		foreach ($products as $p) {
			$catId = $p['product_category_id'];
			$p['category_slug'] = isset($categoriesMap[$catId]) ? $categoriesMap[$catId]['category_slug'] : '#';
			$p['category_image'] = isset($categoriesMap[$catId]) ? $categoriesMap[$catId]['category_image'] : '';
			$productsData[$p['product_id']] = $p;
			$this->addToIndex($index, (string) $p['product_name'], 'p_' . $p['product_id']);
		}
		foreach ($categoriesMap as $c) {
			$this->addToIndex($index, (string) $c['category_title'], 'c_' . $c['category_id']);
		}

		$payload = json_encode([
			'timestamp' => time(),
			'index' => $index,
			'products' => $productsData,
			'categories' => $categoriesMap
		], JSON_UNESCAPED_UNICODE);

		// Atomic write
		$fp = @fopen($tmpFilePath, 'w');
		if ($fp && flock($fp, LOCK_EX)) {
			fwrite($fp, $payload);
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
			return @rename($tmpFilePath, $indexFilePath);
		}
		if ($fp) {
			fclose($fp);
		}
		return @file_put_contents($indexFilePath, $payload, LOCK_EX) !== false;
	}

	private function countryAllowed($countries, string $country): bool
	{
		$countries = (string) $countries;
		return $country === '' || $countries === '' || $countries === '[]'
			|| strpos($countries, '"' . $country . '"') !== false
			|| strpos($countries, '"' . strtolower($country) . '"') !== false;
	}

	public function search(string $rawQ, string $country): array
	{
		$results = ['categories' => [], 'products' => []];
		$q = self::normalize($rawQ);
		if (strlen($q) < 2) {
			return $results;
		}

		// Index is produced by the cron; never built or queried against the DB per request.
		$indexFilePath = FCPATH . self::INDEX_FILE;
		$data = null;
		if (file_exists($indexFilePath)) {
			$content = @file_get_contents($indexFilePath);
			if (!empty($content)) {
				$data = json_decode($content, true);
			}
		}
		if (empty($data) || empty($data['index'])) {
			return $results;
		}

		$matchSets = [];
		foreach (array_filter(explode(' ', $q)) as $word) {
			if (strlen($word) < 2) {
				continue;
			}
			if (empty($data['index'][$word])) {
				return $results;
			}
			$matchSets[] = array_unique($data['index'][$word]);
		}
		if (empty($matchSets)) {
			return $results;
		}

		usort($matchSets, function ($a, $b) {
			return count($a) <=> count($b);
		});
		$matches = array_shift($matchSets);
		foreach ($matchSets as $set) {
			$matches = array_intersect($matches, $set);
			if (empty($matches)) {
				break;
			}
		}

		foreach (array_unique($matches) as $match) {
			$id = substr($match, 2);
			if (strpos($match, 'c_') === 0 && isset($data['categories'][$id])) {
				$cat = $data['categories'][$id];
				if ($this->countryAllowed($cat['category_countries'] ?? '', $country)) {
					$results['categories'][] = $cat;
				}
			} elseif (strpos($match, 'p_') === 0 && isset($data['products'][$id])) {
				$prod = $data['products'][$id];
				if ($this->countryAllowed($prod['product_countries'] ?? '', $country)) {
					$results['products'][] = $prod;
				}
			}
		}

		$results['categories'] = array_slice($results['categories'], 0, 10);
		$results['products'] = array_slice($results['products'], 0, 20);
		return $results;
	}
}
