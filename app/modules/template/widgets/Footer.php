<?php
class Footer extends Widget
{

	public function display($data)
	{
		$this->view('widgets/footer', $data);
	}
	
}
