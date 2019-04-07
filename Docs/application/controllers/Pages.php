<?php
class Pages extends CI_Controller {
	

	public function view($page = 'realhome')
	{
		// Проверка существования страницы
		if ( ! file_exists(APPPATH.'views/pages/'.$page.'.php'))
		{
			// No such page!
			show_404();
		}
		
		$data['title'] = 'Welcome to Stin';
		
		$this->load->view('templates/realheader', $data);
		$this->load->view('pages/'.$page, $data);
		$this->load->view('templates/realfooter', $data);
	}
}