<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Period_model extends CI_Model {
	var $period_table = 'period';
	
	public function __construct() {
		$this->load->database();
	}
		
	/**
	 * Create a period
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @param	bool
	 * @return	bool
	 */
	function create($period) {
		//Insert resa into the database
		if(!$this->db->insert($this->period_table, $resa)) { 
			return FALSE;						
		}
		return TRUE;
	}

	public function get_periods($id = FALSE) {
		if ($id === FALSE) {
		    $this->db->order_by("start_time", "asc");
		    $query = $this->db->get($this->period_table);
			return $query->result_array();
		}
		$query = $this->db->get_where($this->period_table, array('id' => $id));
		return $query->row_array();
	}
	
	public function get_period_where($where) {
		$query = $this->db->get_where($this->period_table, $where);
		return $query->result_array();
	}
	
	function delete($id = FALSE) {
		if ($id === FALSE) {
			return FALSE;
		}
		$this->db->delete($this->period_table, array('id' => $id));
		if ($this->db->affected_rows() > 0)
			return TRUE;
		return FALSE;

	}

	function getPeriodPrices() {
	    $periods = $this->get_periods();
		$prices = array();
		foreach ($periods as $period ) {
			if (!in_array($period['price'], $prices)) {
				$prices[]=$period['price'];
			}
		}
		return $prices;
	}
	
	function getNextPeriod($periodId) {
		$currentPeriod = $this->get_periods($periodId);
		if (sizeof($currentPeriod)>0) {
			$nextPeriod = $this->get_periods($currentPeriod['next_period']);
			return $nextPeriod;
		}
		return false;
	}
	
	
}
?>
