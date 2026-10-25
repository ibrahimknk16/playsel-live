<?php
class Sidebar extends Widget
{

	public function display($data)
	{
		$this->view('widgets/sidebar', $data);
	}
	
}
