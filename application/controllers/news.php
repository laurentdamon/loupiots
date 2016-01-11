<?php
class news extends CI_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('news_model');
		$this->load->helper('form');
		$this->load->library('form_validation');
	}

	public function index() {
		$data['news'] = $this->news_model->get_news();
		$data['title'] = 'News archive';
	
		$this->load->view('templates/header', $data);
		$this->load->view('news/index', $data);
		$this->load->view('templates/footer');
	}

	public function view($slug) {
		$data['news_item'] = $this->news_model->get_news($slug);
	
		if (empty($data['news_item'])) {
			show_404();
		}
	
		$data['title'] = $data['news_item']['title'];
	
		$this->load->view('templates/header', $data);
		$this->load->view('news/view', $data);
		$this->load->view('templates/footer');
	}

	public function viewLastNews($nbNews=5) {
		$news_item = $this->news_model->get_news();
		$total = sizeof($news_item);
		$minNews = $total-$nbNews>0 ? $total-$nbNews : 0;
		
		$out = "";
		for ($i=$minNews; $i<$total; $i++) {
			$out .= "<h2>".$news_item[$i]['title']."</h2>";
    		$out .= "<div id='main'>".$news_item[$i]['text']."</div>";
		}
		$out .= "<br>";
		echo $out;
	}
	
	public function create() {
		//check access rights
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] != 3) {
			show_404();
		}
		
		$data['title'] = 'Create a news item';
		
		$this->form_validation->set_rules('title', 'Title', 'required');
		$this->form_validation->set_rules('text', 'text', 'required');
		
		if ($this->form_validation->run() === FALSE) {
			$this->load->view('templates/header', $data);	
			$this->load->view('news/create');
			$this->load->view('templates/footer');
		} else {
			$this->news_model->set_news();
			redirect('news/', 'refresh');
		}
	}
}