<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Search extends MY_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('search/Search_model');
	}

	public function index()
	{
		$rawQ = mb_substr((string) $this->input->get('q', true), 0, 100);
		$empty = ['categories' => [], 'products' => []];
		$q = Search_model::normalize($rawQ);
		if (strlen($q) < 2) {
			return json($empty);
		}

		$country = preg_replace('/[^A-Z]/', '', strtoupper(trim((string) $this->input->get('country', true))));
		if ($country === '') {
			$segment = explode('-', (string) $this->uri->segment(1));
			$country = isset($segment[1]) ? preg_replace('/[^A-Z]/', '', strtoupper($segment[1])) : '';
		}

		// IP başına dakikada en fazla 40 arama.
		if ($this->throttleHit('search_' . getIp(), 40, 60) === false) {
			return json($empty, 429);
		}

		// Aynı sorgu 5 dakika önbellekten döner.
		$this->load->driver('cache', ['adapter' => 'file']);
		$cacheKey = 'srch_' . md5($q . '|' . $country);
		$cached = $this->cache->get($cacheKey);
		if (is_array($cached)) {
			return json($cached);
		}

		$results = $this->Search_model->search($rawQ, $country);
		if (!empty($results['categories']) || !empty($results['products'])) {
			$this->cache->save($cacheKey, $results, 300);
		}
		return json($results);
	}
}
