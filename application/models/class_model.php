<?php
class Class_model extends CI_Model {

	public function __construct() {
		$this->load->database();
	}
		
	public function get_class($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get('class');
			return $query->result_array();
		}
		$query = $this->db->get_where('class', array('id' => $id));
		return $query->row_array();
	}
	
	public function set_class() {
		$this->load->helper('url');
		$data = array(
			'id' => $this->input->post('id'),
			'class' => $this->input->post('class')
		);
		return $this->db->insert('class', $data);
	}
	
	//utils /////////////////////////////////////////////
	public function get_option_classes() {
		$classesOption=array();
		$classes = $this->get_class();
		foreach ($classes as $currentClass) {
			$currentClassId = $currentClass['id'];
			$classesOption[$currentClassId] = $currentClass['class'];
		}
		return $classesOption;
	}
	
	
	
}