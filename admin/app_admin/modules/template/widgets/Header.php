<?php
class Header extends Widget
{

	public function display($data)
	{
		$this->view('widgets/header', $data);
	}
	
}
