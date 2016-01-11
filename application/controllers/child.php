<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class child extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('User_model');
		$this->load->model('Child_model');
		$this->load->model('Class_model');
		$this->load->helper('url');
		$this->load->helper('form');
		$this->load->library('form_validation');
		
	}

	function index() {
	}
	
	public function create($userId='') {
		//$this->output->enable_profiler(TRUE);
		
		//check access rights
		$data['userId'] = $userId;
		$data['child'] = array();
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] != 3 && $data['loggedId']!=$data['userId']) {
			show_404();
		}
		
		$data['title'] = 'Ajouter un enfant';
		
		$data['classesOption'] = $this->Class_model->get_option_classes();
		$dob = $this->input->post('year')."-".$this->input->post('month')."-".$this->input->post('day');
		
		$this->form_validation->set_rules('name', 'Prenom', 'trim|required|xss_clean');
		$this->form_validation->set_rules('class_id', 'Classe', 'trim|required|xss_clean');
		$this->form_validation->set_rules('dob', 'Date de naissance', 'callback_date_check');
		$this->form_validation->set_rules('childExists', 'Child exists', 'callback_child_already_exists');
		
		if ($this->form_validation->run() === FALSE) {
			$this->load->view('templates/header', $data);	
			$this->load->view('user/viewCreateChild', $data);
			$this->load->view('templates/footer');
		} else {
			$name = htmlentities($this->input->post('name'), ENT_QUOTES, "UTF-8");
			$class_id = $this->input->post('class_id');
			$userId = $this->input->post('userId');
			$this->Child_model->create($name, $class_id, $dob, $userId);
			
			redirect('user/'.$userId, 'refresh');
		}
	}
	
	public function update($childId='') {
		//$this->output->enable_profiler(TRUE);
		
		//initialisation
		$data['child'] = $this->Child_model->get_child_by_id($childId);
		$data['child']['isActive'] = ($data['child']['is_active']) ? 'checked' : '';
		$data['userId'] = $data['child']['user_id'];
		$data['loggedId'] = $this->session->userdata('id');
		$data['day'] = date("j", strtotime($data['child']['birth']));
		$data['month'] = date("n", strtotime($data['child']['birth']));
		$data['year'] = date("Y", strtotime($data['child']['birth']));
		
		//check access rights
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] != 3 && $data['loggedId']!=$data['userId']) {
			show_404();
		}
		$data['title'] = 'Modifier un enfant';
		$data['classId'] = $data['child']['class_id'];
		
		$data['classesOption'] = $this->Class_model->get_option_classes();
		$dob = $this->input->post('year')."-".$this->input->post('month')."-".$this->input->post('day');
		
		$this->form_validation->set_rules('name', 'Prenom', 'trim|required|xss_clean');
		$this->form_validation->set_rules('class_id', 'Classe', 'trim|required|xss_clean');
		$this->form_validation->set_rules('dob', 'Date de naissance', 'callback_date_check');
		
		if ($this->form_validation->run() === FALSE) {
			$this->load->view('templates/header', $data);	
			$this->load->view('user/viewUpdateChild', $data);
			$this->load->view('templates/footer');
		} else {
			$name = htmlentities($this->input->post('name'), ENT_QUOTES, "UTF-8");
			$iActive = ($this->input->post('isActive')=='on') ? TRUE : FALSE;
			$class_id = $this->input->post('class_id');
			$userId = $this->input->post('userId');
			$childId = $this->input->post('childId');
			$this->Child_model->update($childId, $name, $iActive, $class_id, $dob, $userId);
			
			redirect('user/'.$data['userId'], 'refresh');
		}
	}
	
	
	/*
	 * Form validation callback functions
	*/
	
	function child_already_exists($mail) {
		$name = $this->input->post('name');
		$userId = $this->input->post('userId');
		//query the database
		$where = array('name'=>$name, 'user_Id'=>$userId);
		$result = $this->Child_model->get_children_where($where);
	
		if($result) {
			$this->form_validation->set_message('child_already_exists', 'La famille existe deja!');
			return FALSE;
		} else {
			return TRUE;
		}
	}
	
	public function date_check() {
		if(checkdate($this->input->post('month'),$this->input->post('day'),$this->input->post('year'))) {
			return TRUE;
		} else {
			$this->form_validation->set_message('date_check', 'Date de naissance invalide');
			return FALSE;
		}
	}
	
}

?>