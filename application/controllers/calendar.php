<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class calendar extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Calendar_model');
		$this->load->model('Period_model');
		$this->load->model('Days_model');
		$this->load->model('Resa_model');
		$this->load->model('User_model');
		$this->load->model('Cost_model');
		
		$this->load->helper('dob');
		$this->load->helper('form');
	}

	function index() {
		$this->output->enable_profiler(TRUE);
		
		$this->load->library('form_validation');
		
		$data['title'] = 'Gerer le calendrier';
		
		//check access rights
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] < 3) {
			show_404();
		}
		
		$file = 'lastValidate.txt';
		$closedMonthStr = file_get_contents($file);
		if (!isset($closedMonth) || $closedMonth=="" ) {
			$closedMonth=0;
		}
		$closedMonth = mktime (0, 0, 0, (date("n", $closedMonthStr)-1), 1, date("Y", $closedMonthStr) );
		
		$data['closedMonth'] = $closedMonth;
		$data['month'] = date("n", $closedMonth);
		$data['year'] = date("Y", $closedMonth);

		$this->load->view('templates/header', $data);
		$this->load->view('admin/viewCalendarSetting', $data);
		$this->load->view('templates/footer');
		
	}

	public function validateResa() {
		$this->output->enable_profiler(TRUE);
		
		$data['title'] = 'Gerer le calendrier';
		
		//check access rights
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] < 3) {
			show_404();
		}
		
		//initialisation
		$year = $this->input->post('year');
		$month = $this->input->post('month');
		if (!isset($year) || !isset($month)) {
			$nextMonth = mktime(0, 0, 0, (date("m")+1), 1, date("Y"));
			$year=date("Y", $nextMonth);
			$month=date("m", $nextMonth);
		}
		$data['year'] = $year;
		$data['month'] = $month;
		$newClosedMonth = mktime(0, 0, 0, ($month+1), 1, $year);
		
		$file = 'lastValidate.txt';
		$closedMonth = file_get_contents($file);
		if (!isset($closedMonth) || $closedMonth=="" ) {
			$closedMonth=0;
		}
		
		$data['sql'] = $this->Resa_model->validateResaByMonth($newClosedMonth, $closedMonth);
		file_put_contents($file, $newClosedMonth);
		
 		$users = $this->User_model->get_users(TRUE);
 		foreach ($users as $user) {
// 			$data['userId'][] = $user["id"];
 			$this->Cost_model->persistCost($month, $year, $user["id"]);			
 		}
				
//		redirect('user/', 'refresh');
		$this->load->view('templates/header', $data);
		$this->load->view('admin/viewCalendarSetting', $data);
		$this->load->view('templates/footer');		
	}
	
	public function createHolidays() {
		$data['title'] = 'Gerer le calendrier';
		
		//check access rights
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] != 3) {
			show_404();
		}
		
		$this->load->helper('form');
		$this->load->library('form_validation');
	
		$this->form_validation->set_rules('start', 'Debut', 'trim|required|xss_clean');
		$this->form_validation->set_rules('end', 'Fin', 'trim|required|xss_clean');
		$this->form_validation->set_rules('order', 'Date de fin avant le debut', 'callback_order');
	
		if ($this->form_validation->run() === FALSE) {
			$this->load->view('templates/header', $data);
			$this->load->view('admin/viewCalendarSetting', $data);
			$this->load->view('templates/footer');
		} else {
			$startArr = explode("-", $this->input->post('start'));
			$endArr = explode("-", $this->input->post('end'));
			$start = mktime(0, 0, 0, $startArr[1], $startArr[0], $startArr[2]);
			$end = mktime(0, 0, 0, $endArr[1], $endArr[0], $endArr[2]);
			$this->Calendar_model->add_holidays($start, $end);
			
			$this->load->view('templates/header', $data);
			$this->load->view('admin/viewCalendarSetting', $data);
			$this->load->view('templates/footer');
		}
	}

	
	/*
	 * Form validation callback functions
	*/
	
	function order() {
		$start = $this->input->post('start');
		$end = $this->input->post('end');
		
		$start = explode("-", $start);
		$end = explode("-", $end);
		$startDate = mktime(0, 0, 0, $start[1], $start[0], $start[2]);
		$endDate = mktime(0, 0, 0, $end[1], $end[0], $end[2]);
	
		if($startDate > $endDate) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
}

?>