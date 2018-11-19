<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class resa extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('Resa_model');
		$this->load->model('Calendar_model');
		$this->load->model('User_model');
		$this->load->model('Period_model');
		$this->load->model('Days_model');
		$this->load->model('Cost_model');
		$this->load->model('Child_model');
		$this->load->helper('form');
		$this->load->library('form_validation');
	}

	function index() {
	}

	public function create($output=null) {
	    if (isset($_POST)) {
			$resaData=$this->Resa_model->setResaFromPostData($_POST);
			if ($resaData) {
				$output = $this->Resa_model->create($resaData);
//				$child = $this->Child_model->get_child_by_id($resaData["child_id"]);
				$this->Cost_model->persistCost($_POST["month"], $_POST["year"], $_POST["child"]);
			} else {
				$output=false;
			}
		} else {
			$output=false;
		}
		$output["resaData"] = $resaData;
		$output["POST"] = $_POST;
		echo $this->my_json_encode(array_values($output));
	}

	public function delete() {
		if (isset($_POST)) {
			$resa=$this->Resa_model->setResaFromPostData($_POST);
			$resa=$this->Resa_model->get_resa_where($resa);
			if ($this->Resa_model->delete($resa[0]["id"])) {
				$output_string=true;
				$child = $this->Child_model->get_child_by_id($resa[0]["child_id"]);
				$this->Cost_model->persistCost($_POST["month"], $_POST["year"], $child["user_id"]);
			} else {
				$output_string=false;
			}
		} else {
			$output_string=false;
		}
		echo $this->my_json_encode($output_string);
	}

	public function getCalendar() {
//		$this->output->enable_profiler(TRUE);
		
		$this->Calendar_model->init($_REQUEST['user_id']);
		$query = $this->db->get_where('child', array('user_id' => $_REQUEST['user_id']));
		$children = $query->result_array();
		$calData="";
		if (sizeof($children)>0) {
			$calData=$this->Calendar_model->generate( $_REQUEST['user_id'], $_REQUEST['year'], $_REQUEST['month'] );
		}
		echo $calData;
	}

	public function getCost() {
		$userId = $_GET['user_id'];
		$year = $_GET['year'];
		$month = $_GET['month'];
		$cost['sum']['resa'] = 0;
		$cost['sum']['depassement'] = 0;
		$cost['sum']['total'] = 0;
		
		$children = $this->db->get_where('child', array('user_id' => $userId, 'is_active' => true))->result_array();
		foreach ($children as $child) {
			$childNum=$child['id'];
			//cout des resas du mois courant
			$resas[$childNum]= $this->Resa_model->get_full_resa_where(array('child_id' => $childNum, 'YEAR(date)' => $year, 'MONTH(date)' => $month, 'resa_type !=' => 3 ));
			
			if (sizeof($resas[$childNum])>0) {
				$price = $resas[$childNum][0]['price'];
				$totalResa = sizeof($resas[$childNum])*$price;
				$cost['children'][$childNum]['resaStr'] = sizeof($resas[$childNum])." x ".$price." = ".$totalResa;
			} else {
				$totalResa = 0;
				$cost['children'][$childNum]['resaStr'] = "0";
			}
			//cout des depassemants du mois courant
			$depassement[$childNum]= $this->Resa_model->get_full_resa_where(array('child_id' => $childNum, 'YEAR(date)' => $year, 'MONTH(date)' => $month, 'resa_type =' => 3 ));
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
		echo $this->my_json_encode($cost);
	}

	// TODO check version of php
	public function my_json_encode($data) {
			switch ($type = gettype($data)) {
				case 'NULL':
					return 'null';
				case 'boolean':
					return ($data ? 'true' : 'false');
				case 'integer':
					return '"' . addslashes($data) . '"';
				case 'double':
				case 'float':
					return '"' . addslashes($data) . '"';
				case 'string':
					return '"' . addslashes($data) . '"';
				case 'object':
					$data = get_object_vars($data);
				case 'array':
					$output_index_count = 0;
					$output_indexed = array();
					$output_associative = array();
					foreach ($data as $key => $value) {
						$output_indexed[] = $this->my_json_encode($value);
						$output_associative[] = $this->my_json_encode($key) . ':' . $this->my_json_encode($value);
						if ($output_index_count !== NULL && $output_index_count++ !== $key) {
							$output_index_count = NULL;
						}
					}
					if ($output_index_count !== NULL) {
						return '[' . implode(',', $output_indexed) . ']';
					} else {
						return '{' . implode(',', $output_associative) . '}';
					}
				default:
					return '"not supported"'; // Not supported
			}
	}
}

?>