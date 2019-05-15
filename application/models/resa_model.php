<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Resa_model extends CI_Model {
	var $resa_table = 'reservation';
	var $period_table = 'period';
	var $child_table = 'child';
	var $user_table = 'users';
	
	public function __construct() {
		$this->load->database();
		$this->load->model('Period_model');
	}
		
	/**
	 * Create a resa
	 *
	 * @return	bool
	 */
	function insert($resa) {
		$dbResa=$this->get_resa_where(array('child_id' => $resa['child_id'], 'period_id' => $resa['period_id'], 'date' => $resa['date'] ));
		if (sizeof($dbResa)==0) {
			if($this->db->insert($this->resa_table, $resa)) { 
				return TRUE;						
			}
		}
		return FALSE;
	}

	public function get_resa($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get($this->resa_table);
			return $query->result_array();
		}
		$query = $this->db->get_where($this->resa_table, array('id' => $id));
		return $query->row_array();
	}
	
	public function get_resa_where($where) {
		$this->db->where($where);
		$query = $this->db->get($this->resa_table);
		return $query->result_array();
	}
	
	public function get_full_resa_where($where) {
		$this->db->where($where);
		$this->db->join('period', 'period.id = reservation.period_id');
		$query = $this->db->get($this->resa_table);
		
		return $query->result_array();
	}
	
	function delete($id = FALSE) {
		if ($id === FALSE) {
			return FALSE;
		}
		$this->db->delete($this->resa_table, array('id' => $id));
		if ($this->db->affected_rows() > 0)
			return TRUE;
		return FALSE;

	}

	function validateResaByMonth($newClosedMonth, $closedMonth) {
		$resas = array();
		//$sql = "SELECT * FROM ".$this->resa_table." WHERE MONTH(date)='".date("m", $closedMonth)."' and YEAR(date)='".date("Y", $closedMonth)."'";
		$sql = "SELECT * FROM ".$this->resa_table." WHERE date > '".date("Y-m-d", $newClosedMonth)."' and resa_type = 2 ";
		$resas = $this->db->query($sql);
		if ($resas->num_rows() > 0) {
			foreach ($resas->result_array() as $resa) {
				$resa['resa_type']=1;
				$this->db->update($this->resa_table, $resa, "id = ".$resa['id']);
			}
		}
		
		$sql = "SELECT * FROM ".$this->resa_table." WHERE date > '".date("Y-m-d", $closedMonth)."' and date < '".date("Y-m-d", $newClosedMonth)."' and resa_type = 1 ";
		$resas = $this->db->query($sql);
		if ($resas->num_rows() > 0) {
			foreach ($resas->result_array() as $resa) {
				$resa['resa_type']=2;
				$this->db->update($this->resa_table, $resa, "id = ".$resa['id']);
			}
		}
		return $sql;
	}

	function validateResaByDate($lastCloseDate, $closeDate) {
		$resas = array();
		//$sql = "SELECT * FROM ".$this->resa_table." WHERE MONTH(date)='".date("m", $closedMonth)."' and YEAR(date)='".date("Y", $closedMonth)."'";
		$sql = "SELECT * FROM ".$this->resa_table." WHERE date > '".date("Y-m-d", $closeDate)."' and resa_type = 2 ";
		$resas = $this->db->query($sql);
		if ($resas->num_rows() > 0) {
			foreach ($resas->result_array() as $resa) {
				$resa['resa_type']=1;
				$this->db->update($this->resa_table, $resa, "id = ".$resa['id']);
			}
		}
		
		$sql = "SELECT * FROM ".$this->resa_table." WHERE date > '".date("Y-m-d", $lastCloseDate)."' and date < '".date("Y-m-d", $closeDate)."' and resa_type = 1 ";
		$resas = $this->db->query($sql);
		if ($resas->num_rows() > 0) {
			foreach ($resas->result_array() as $resa) {
				$resa['resa_type']=2;
				$this->db->update($this->resa_table, $resa, "id = ".$resa['id']);
			}
		}
		return $sql;
	}
	
	function getClassroomCallPerDay($date, $classId, $AMPM) {
		$results = array();
		$sql = "SELECT child.name as childName, users.name, max(period.stop_time) as time";
		$sql .= " FROM ".$this->resa_table.", ".$this->period_table.", ".$this->child_table.", ".$this->user_table." ";
		$sql .= " WHERE reservation.child_id=child.id and reservation.period_id=period.id and child.user_id=users.id ";
		$sql .= " AND period.type = '".$AMPM."' AND reservation.date = '".date("Y-m-d", strtotime($date))."' AND child.class_id = '".$classId."'";
		$sql .= " GROUP BY child.name, users.name";
		return $this->db->query($sql)->result_array();
	}
	
	// Util function /////////////////////////////////////
	function setResaFromPostData($post) {
		$date = mktime(0, 0, 0, $post['month'], $post['day'], $post['year']);
		$resa['date'] = date("Y-m-d", $date);
		$resa['period_id'] = $post['period'];
		$resa['child_id'] = $post['child'];
		
		$closedMonth = file_get_contents('lastValidate.txt');
		$closedWeek = file_get_contents('lastVisit.txt');
		$closedDate = ($closedMonth>$closedWeek) ? $closedMonth : $closedWeek;
		
		if ($date >= $closedDate) {
			$resa['resa_type'] = "1";   								// normal
		} elseif ($this->session->userdata('privilege')>=2) {			// validee
			$resa['resa_type'] = "3";									// rajout
		} else {
			return FALSE;
		}
		return $resa;
	}
	
	public function create($post, $output=array()) {
		if ($this->insert($post)) {
			$output[] = $post;
			//Recursive on adjacent period
			$nextPeriod = $this->Period_model->getNextPeriod($post["period_id"]);
			if ($nextPeriod) {
				$post['period_id'] = $nextPeriod["id"];
				return $this->create($post, $output);
			}
		}
		return $output;
	}

	public function get_cost($resas) {
		$costNum=array();
		$periodPrices = $this->Period_model->getPeriodPrices();
		$cost["periodPrices"]=$periodPrices;
		foreach ($resas as $resa) {
		    $resaId = $resa['id'];
			if (isset($resa['price'])) {
				$periodPrice = $resa['price'];
			} else {
				$periodId = $resa["period_id"];
				$periodPrice = $periodPrices[$periodId];
			}
			if ($resa["resa_type"]==3 && $resa["date"] > LOUP_DEPASSEMENT_INITIAL_DATE) {
				$periodPrice = LOUP_DEPASSEMENT_PRICE;
			}
			if (isset($costNum[$periodPrice])) {
				$numResa = sizeof($costNum[$periodPrice]);
				$costNum[$periodPrice][$resaId]=$numResa+1;
			} else {
				$costNum[$periodPrice][$resaId]=1;
			}
		}
		$separator="";
		$cost["str"]="&nbsp;";
		$cost["total"]=0;
		foreach ($costNum as $price=>$costNum) {
			$cost["str"] = $cost["str"].$separator.$price."x".sizeof($costNum);
			$separator=" + ";
			$cost["total"] += $price*sizeof($costNum);
		}
		return $cost;
	}
	
	public function getBill($userId, $year, $month) {
		$monthBilled = mktime(0, 0, 0, $month-1, 1, $year); //mois facturé
		$monthPrevBill = mktime(0, 0, 0, $month-2, 1, $year); //mois precedent le mois facturé
		
		$children = $this->db->get_where('child', array('user_id' => $userId, 'is_active' => true))->result_array();
		foreach ($children as $child) {
			$childNum=$child['id'];
			//cout des resas du mois facturé
			$resas[$childNum]= $this->Resa_model->get_full_resa_where(array('child_id' => $childNum, 'YEAR(date)' => $year, 'MONTH(date)' => $month-1, 'resa_type !=' => 3 ));
			
			if (sizeof($resas[$childNum])>0) {
				$price = $resas[$childNum][0]['price'];
				$totalResa = sizeof($resas[$childNum])*$price;
				$cost['children'][$childNum]['resaStr'] = sizeof($resas[$childNum])." x ".$price." = ".$totalResa;
			} else {
				$totalResa = 0;
				$cost['children'][$childNum]['resaStr'] = "0";
			}
			//cout des depassemants du mois precedent le mois facturé
			$depassement[$childNum]= $this->Resa_model->get_full_resa_where(array('child_id' => $childNum, 'YEAR(date)' => $year, 'MONTH(date)' => $month-2, 'resa_type =' => 3 ));
			if (sizeof($depassement[$childNum])>0) {
				$price = $depassement[$childNum][0]['price'];
				$totalDepas = sizeof($depassement[$childNum])*$price;
				$cost['children'][$childNum]['depassementStr'] = sizeof($depassement[$childNum])." x ".$price." = ".$totalDepas;
			} else {
				$totalDepas = 0;
				$cost['children'][$childNum]['depassementStr'] = "0";
			}
			
			$cost['children'][$childNum]['total'] = $totalResa + $totalDepas;
			
			$cost['sum']['resa'] += $totalResa;
			$cost['sum']['depassement'] += $totalDepas;
		}
		$cost['sum']['total'] = $cost['sum']['resa'] + $cost['sum']['depassement'];
		
		return "toto222";
	}
	
}
?>
