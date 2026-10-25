<?php
class Header extends Widget
{

	public function display($data)
	{
		$CI =& get_instance();
		$country = $data['country'] ?? 'US';

		$mainCategories = $CI->db
			->select('category_id, category_title, category_slug, category_publisher, category_type, category_thumbnail, category_image')
			->from('categories')
			->where('category_status', 1)
			->group_start()
				->where('category_parent_category_id', 0)
				->or_where('category_parent_category_id', null)
				->or_where('category_parent_category_id', '')
			->group_end()
			->where("JSON_CONTAINS(category_countries, '\"$country\"')", null, false)
			->where('category_is_popular', 1)
			->order_by('category_sort_id', 'ASC')
			->get()->result();

		if (!empty($mainCategories)) {
			$mainCategoryIds = array_column($mainCategories, 'category_id');

			$allSubCategories = $CI->db
				->select('category_id, category_parent_category_id, category_title, category_slug, category_thumbnail')
				->from('categories')
				->where('category_status', 1)
				->where_in('category_parent_category_id', $mainCategoryIds)
				->get()->result();

			foreach ($mainCategories as $mainCat) {
				$mainCat->sub_categories = [];
				foreach ($allSubCategories as $subCat) {
					if ($subCat->category_parent_category_id == $mainCat->category_id) {
						$mainCat->sub_categories[] = $subCat;
					}
				}
			}
		}

		$data['header_categories'] = $mainCategories;

		$this->view('widgets/header', $data);
	}
	
}
