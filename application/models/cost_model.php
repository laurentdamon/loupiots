<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Cost_model extends CI_Model {
	var $cost_table = 'cost';
	var $resa_table = 'reservation';
	var $child_table = 'child';
	var $user_table = 'users';
	var $period_table = 'period';
	
	public function __construct() {
		$this->load->database();
		$this->load->model('Resa_model');
		$this->load->model('Payment_model');
	}
		
	function create($cost) {
		if(!$this->db->insert($this->cost_table, $cost)) { 
			return FALSE;						
		}
		return TRUE;
	}

	public function get_costs($id = FALSE) {
		if ($id === FALSE) {
			$query = $this->db->get($this->cost_table);
			return $query->result_array();
		}
		$query = $this->db->get_where($this->cost_table, array('id' => $id));
		return $query->row_array();
	}
	
	public function get_cost_where($where) {
		$query = $this->db->get_where($this->cost_table, $where);
		return $query->result_array();
	}
	
	function delete($id = FALSE) {
		if ($id === FALSE) {
			return FALSE;
		}
		$this->db->delete($this->cost_table, array('id' => $id));
		if ($this->db->affected_rows() > 0)
			return TRUE;
		return FALSE;

	}

	function update($id, $cost) {
		$this->db->where('id', $id);
		$this->db->update($this->cost_table, $cost); 
	}
	
	
	public function resetTest() {
	    $this->db->from($this->cost_table);
	    $this->db->truncate();
	}
	
	
	// Util function /////////////////////////////////////
	public function setBalance($balanceDate) {
	    $year = date("Y", $balanceDate);
	    $month = date("m", $balanceDate);
	    $prevMonth = date('n', mktime(0, 0, 0, $month-1, 1, $year)); //mois precedent le mois facturé
	    $prevYear = date('Y', mktime(0, 0, 0, $month-1, 1, $year));
	    
	    
	    $users = $this->User_model->get_users(TRUE);
	    foreach ($users as $user) {
	        $userId = $user['id'];
	        $cost[$userId]['user_id'] = $user['id'];
	        $cost[$userId]['month_paided'] = date("Y-m-d", $balanceDate);
	        
	        //Get validated month payment
	        $payment = $this->Payment_model->get_total_payment_where(array('user_id' => $user['id'], 'status' => "3" ));
	        if (isset($payment['amount'])) {
	            $cost[$userId]['paid'] = $payment['amount'];
	        } else {
	            $cost[$userId]['paid'] = 0;
	        }
	        
	        //Get total resa
	        $resa = $this->Resa_model->getResaSummary($year, $month, $user['id']);
	        
	        //Get last month debt
	        $DBCostPrev = current($this->Cost_model->get_cost_where(array('user_id' => $user['id'], 'YEAR(month_paided)' => $prevYear, 'MONTH(month_paided)' => $prevMonth )));
	        if($DBCostPrev) {
	            $previousDebt = $DBCostPrev["debt"];
	        } else {
	            $previousDebt = 0;
	        }
	        
	        $cost[$userId]['debt'] = round(($previousDebt + $resa['sum']['total'] - $cost[$userId]['paid']),2);
	        
	        //Store cost in DB
	        $DBCost = current($this->Cost_model->get_cost_where(array('user_id' => $user['id'], 'YEAR(month_paided)' => $year, 'MONTH(month_paided)' => $month )));
	        if($DBCost) {
	            $this->update($DBCost["id"], $cost[$userId]);
	        } else {
	            $this->create($cost[$userId]);
	        }
	        
	        //Update paiement status to comptabilise=5
	        $payments = $this->Payment_model->get_payment_where(array('user_id' => $user['id'], 'status' => "3" ));
	        foreach ($payments as $curPayment) {
	            $curPayment['status'] = 5; //Comptabilisé
	            $cost['paiementSQL'][] = $this->Payment_model->update($curPayment['id'], $curPayment);
	        }
	
	        $cost[$userId]['debug'] = "previousDebt: ".$previousDebt." + resa: ".$resa['sum']['total']." - paid: ".$cost[$userId]['paid']."<br>";
	    }

	    return $cost;
	}
	
	function getMonthBalance($year, $month) {
	    
	    $sql = "SELECT count(id) as numResa,resa_type FROM ".$this->resa_table." ";
	    $sql .= "WHERE YEAR( date ) = '".$year."' AND MONTH( date ) = '".$month."' GROUP BY resa_type";
	    
	    $resas = $this->db->query($sql)->result_array();
	    
	    $balance['priceDep'] = 0;
	    $balance['priceStandard'] = 0;
	    foreach ($resas as $resa) {
	        $type = $resa['resa_type'];
	        if( $type==2) {
	            $balance['standard']['numResaStandard'] = $resa['numResa'];
	            $balance['standard']['cout'] = $balance['standard']['numResaStandard'] * LOUP_PERIOD_PRICE; // Attention raccourci a voir par un select dans la table period
	        } elseif($type==3) {
	            $balance["dep"]['numResaDep'] = $resa['numResa'];
	            $balance["dep"]['cout'] = $resa['numResa'] * LOUP_DEPASSEMENT_PRICE;
	        }
	    }
	    $balance['totalResa'] = $balance['standard']['numResaStandard'];
	    if (isset($balance["dep"]['numResaDep'])) {
	        $balance['totalResa'] += $balance["dep"]['numResaDep'];
	    }
	    $balance['totalResaCout'] = $balance['standard']['cout'];
	    if (isset($balance["dep"]['cout'])) {
	        $balance['totalResa'] += $balance["dep"]['cout'];
	    }
	    return $balance;    	    
	}
}
?>
