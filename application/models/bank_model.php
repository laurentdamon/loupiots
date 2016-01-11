<?php
class Bank_model extends CI_Model {

	public function __construct() {
		$this->load->database();
	}
		
	public function get_bank($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get('bank');
			return $query->result_array();
		}
		
		$query = $this->db->get_where('bank', array('id' => $id));
		return $query->row_array();
	}

	//utils /////////////////////////////////////////////
	public function get_option_banks() {
		$banksOption=array();
		$banksOption[0] = '-';
		$banks = $this->get_bank();
		foreach ($banks as $currentBank) {
			$currentBankId = $currentBank['id'];
			$banksOption[$currentBankId] = $currentBank['bank'];
		}
		return $banksOption;
	}
	

}