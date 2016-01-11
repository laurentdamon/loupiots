<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//require_once(dirname(__FILE__).'\..\libraries\phpass-0.3\PasswordHash.php');


class Child_model extends CI_Model 
{
	var $CI;
	var $child_table = 'child';

	public function __construct() {
		$this->load->database();
	}
		
	/**
	 * Create a child
	 */
	function create($name = '', $class_id = '', $birth = '', $userId ='') {
		$this->CI =& get_instance();
		//Make sure account info was sent
		if($name == '' || $class_id == '' || $birth == '' || $userId == '') {
			return false;
		}
		//Check against child table
		$query = $this->CI->db->get_where($this->child_table, array('name' => $name, 'user_id' => $userId));
		if ($query->num_rows() > 0) //child already exists
			return false;

		//Insert child into the database
		$data = array(
					'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
					'is_active' => true,
					'class_id' => $class_id,
					'birth' => $birth,
					'user_id' => $userId
				);
 		$this->CI->db->set($data); 

 		if(!$this->CI->db->insert($this->child_table)) //There was a problem! 
 			return false;						
				
		return true;
	}

	/**
	 * Update a child
	 */
	function update($id, $name = '', $isActive = true, $class_id = '', $birth = '', $userId ='') {
		$this->CI =& get_instance();

		//Make sure account info was sent
		if($name == '' || $class_id == '' || $birth == '' || $userId == '') {
			return "info not sent";
		}
		//Check against child table
		$query = $this->CI->db->get_where($this->child_table, array('id' => $id));
		if ($query->num_rows() != 1) //child not existing
			return "child not existing numrow=> ".$query->num_rows()." for id=".$id;

		//Insert child into the database
		$data = array(
					'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
					'is_active' => $isActive,
					'class_id' => $class_id,
					'birth' => $birth,
					'user_id' => $userId
				);
 		$this->CI->db->set($data); 
		$this->db->where('id', $id);
		if( !$this->CI->db->update($this->child_table, $data) ) //There was a problem! 
			return "wrong sql";	
				
		return "should be good";
	}
	

	/**
	 * Delete child
	 *
	 */
	function delete($childId) 
	{
		$this->CI =& get_instance();
		
		if(!is_numeric($childId))
			return false;			

		return $this->CI->db->delete($this->child_table, array('id' => $childId));
	}
	

	/**
	 * Find child
	 *
	 */
	public function get_children_where($where) {
		$query = $this->db->get_where($this->child_table, $where);
		return $query->result_array();
	}
	
	public function get_child_by_id($id) {
		$query = $this->db->get_where($this->child_table, array('id' => $id));
		$child = $query->row_array();
		return $child;
	}
	
	
	
}
?>
