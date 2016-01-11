<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class contact extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->helper('url');
		
	}

	function index() {
		$data['title'] = 'Contact';
		
		$this->load->view('templates/header', $data);	
		$this->load->view('pages/viewContact');
		$this->load->view('templates/footer');
	}
	
	function viewLogin() {
		$data['title'] = 'Contact';
		
		$this->load->view('templates/header_login', $data);	
		$this->load->view('pages/viewContact');
		$this->load->view('templates/footer');
	}
	
	
}

?>