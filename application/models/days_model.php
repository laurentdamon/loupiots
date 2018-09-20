<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Days_model extends CI_Model {
	var $days_table = 'days';
	
	public function __construct() {
		$this->load->database();
		$this->load->model('Period_model');
	}
		
	public function get_days($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get($this->days_table);
			return $query->result_array();
		}
		$query = $this->db->get_where($this->days_table, array('id' => $id));
		return $query->row_array();
	}
	
	public function get_days_where($where) {
		$query = $this->db->get_where($this->days_table, $where);
		return $query->result_array();
	}
	
	function delete($id = FALSE) {
		if ($id === FALSE) {
			return FALSE;
		}
		$this->db->delete($this->days_table, array('id' => $id));
		if ($this->db->affected_rows() > 0)
			return TRUE;
		return FALSE;

	}
	
	function get_daysPeriods() {
	    $query = $this->db->query("SELECT * FROM period p, days d WHERE p.id=d.periodId order by d.day, d.periodId");
	    foreach($query->result_array() AS $row) {
	        $data[$row['day']][] = $row;
	    }
	    return $data;
	}
}
?>
