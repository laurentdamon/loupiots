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

	public function get() {
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

	// TODO check version of php
	public function my_json_encode($data) {
			switch ($type = gettype($data)) {
				case 'NULL':
					return 'null';
				case 'boolean':
					return ($data ? 'true' : 'false');
				case 'integer':
				case 'double':
				case 'float':
					return $data;
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