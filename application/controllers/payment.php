<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payment extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('User_model');
		$this->load->model('Payment_model');
		$this->load->model('Bank_model');
		$this->load->model('Cost_model');
		$this->load->model('Child_model');
		$this->load->helper('url');
		$this->load->helper('dob'); 
		$this->load->helper('form');
		$this->load->library('form_validation');
		
		$this->payment_types = array('Virement' => 'Virement', 'Cheque' => 'Cheque', 'Especes' => 'Especes');
		$this->banks = $this->Bank_model->get_option_banks();
		$this->payment_status = array(1 => 'En attente de r&eacute;ception');
		if ($this->session->userdata('privilege') >=2 ) {
			$this->payment_status[2] = 'Recu';
		}
		if ($this->session->userdata('privilege') == 3 ) {
			$this->payment_status[3] = 'Valid&eacute;';
			$this->payment_status[4] = 'Annul&eacute;';
		}
	}

	function index() {
		
	}
	
	public function create($userId='') {
//		$this->output->enable_profiler(TRUE);
		
		//check access rights
		$data['loggedId']=$this->session->userdata('id');
		if (isset($userId) && $userId!='') {
			$data['userId'] = $userId;
		} else {
			$data['userId'] = $this->input->post('user_id');
		}
		
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] < 2 && $data['loggedId']!=$data['userId']) {
			show_404();
		}
				
		$data['title'] = 'Ajouter un paiement';
		$data['payment_types'] = $this->payment_types;
		$data['payment_status'] = $this->payment_status;
		$data['banques'] = $this->banks;
		$data['date'] = date("Y-n-j", strtotime('previous month'));

		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] >= 2) {
			$data['usersOption'] = $this->User_model->get_option_users();
			$selId = $this->input->post('selId');
		} else {
			$data['usersOption'] = $this->User_model->get_option_users($data['loggedId']);
		}
		
		$this->form_validation->set_rules('amount', 'Montant', 'trim|required|xss_clean');
		$this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
		$this->form_validation->set_rules('month', 'Mois payé', 'numeric');
		//$this->form_validation->set_rules('exist', 'paiement deja enregistré', 'callback_already_exists');
		
		if ($this->form_validation->run() !== FALSE) {
			$payment = $this->Payment_model->setPaymentFromPostData($_POST);
			$this->Payment_model->create($payment);
		}
 		
		if ( sizeof($_POST)==0 ) {
			$this->load->view('templates/header', $data);	
 			$this->load->view('user/viewCreatePayment', $data);
 			$this->load->view('templates/footer');
		} else {
			redirect('user/'.$_POST['user_id'].'/'.$_POST['year'].'/'.$_POST['month'], 'refresh');
		}
		
	}

	public function update($paymentId='',$fromReport=0) {
		//$this->output->enable_profiler(TRUE);
		
		$data['title'] = 'Modifier un paiement';

		$data['payment'] = $this->Payment_model->get_payment($paymentId);
		$data['payment']['month'] = date("n", strtotime($data['payment']['month_paided']));
		$data['payment']['year'] = date("Y", strtotime($data['payment']['month_paided']));
		$previousPaymentStatus=$data['payment']["status"];
		
		$data['payment_types'] = $this->payment_types;
		$data['payment_status'] = $this->payment_status;
		$data['banques'] = $this->banks;

		$year = $this->input->post('year');
		$month = $this->input->post('month');
		if ($month=="" || $year=="") {
			$year=date("Y");
			$month=date("n");
		}
		$data['month'] = $month;
		$data['year'] = $year;
		
		$data['users'] = $this->User_model->get_users();
		$data["payments"] =array();
		foreach ($data['users'] as $user) {
			$userId = $user["id"];
			$where = array('user_id'=>$userId, 'YEAR(month_paided)' => $year, 'MONTH(month_paided)' => $month);
			$payment = $this->Payment_model->get_payment_where($where);
			$cost = $this->Cost_model->get_cost_where($where);
			$data["payments"][$userId]=$payment;
			$data["costs"][$userId]=current($cost);
		}
		
		$data['fromReport'] = $fromReport;

		//check access rights
		$data['loggedId']=$this->session->userdata('id');
		if ( sizeof($_POST)==0 ) {
			$data['userId'] = $data['payment']['user_id'];
		} else {
			$data['userId'] = $this->input->post('user_id');
		}
		
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] < 2 && $data['loggedId']!=$data['userId']) {
			show_404();
		}

		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] >= 2) {
			$data['usersOption'] = $this->User_model->get_option_users();
			$selId = $this->input->post('selId');
		} else {
			$data['usersOption'] = $this->User_model->get_option_users($data['loggedId']);
		}
		
		$this->form_validation->set_rules('amount', 'Montant', 'trim|required|xss_clean');
		$this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
		$this->form_validation->set_rules('month', 'Mois payé', 'numeric');
		
		if ($this->form_validation->run() !== FALSE) {
			$payment = $this->Payment_model->setPaymentFromPostData($_POST);
			$this->Payment_model->update($paymentId, $payment);

			//store or update cost + debt
/*normandie
			if ($payment["status"]==3  || $previousPaymentStatus==3) {
				$this->Cost_model->storeOnPaymentUpdate($payment);	
			}
*/			
		}

		if ( sizeof($_POST)==0 ) {
			$this->load->view('templates/header', $data);
			$this->load->view('user/viewUpdatePayment', $data);
			$this->load->view('templates/footer');
		} else {
			if ( $fromReport==1 ) {
				$data['userId'] = $this->input->post('user_id');
				$year = $this->input->post('year');
				$month = $this->input->post('month');
				
				redirect('payment/report?year='.$year.'&month='.$month, 'refresh');
			} else {
				redirect('user/'.$data['userId'].'/'.$data['payment']['year'].'/'.$data['payment']['month'], 'refresh');
			}
		}
		
	}
	
	public function report() {
//		$this->output->enable_profiler(TRUE);
		
		//check access rights
		$data['loggedId'] = $this->session->userdata('id');
		if (!isset($data['loggedId']) || !is_numeric($data['loggedId']) ) {
			show_404();
		}
		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] < 2) {
			show_404();
		}
		
		//initialisation
		$year = $this->input->get_post('year');
		$month = $this->input->get_post('month');
		$onlyActive = $this->input->get_post('onlyActive');
		if ($month=="" || $year=="") {
			$year=date("Y");
			$month=date("n");
			$onlyActive = TRUE;
		}
		$data['month'] = $month;
		$data['year'] = $year;
		$data['onlyActive'] = $onlyActive;
		
		$data['title'] = 'Liste des paiements';

		$data['users'] = $this->Child_model->get_fullChildren($onlyActive);
		
		$data["payments"] = array();
		$data['banks'] = $this->banks;
		
		foreach ($data['users'] as $userId => $user) {
		    $data['costTotal'][$userId] = $this->Resa_model->getResaSummary($year, $month, $userId);

		    $prevDate = strtotime( $year."-".($month-1)."-01" );
		    $prevMonth = date("m", $prevDate);
		    $prevYear = date("Y", $prevDate);
		    $DBCostPrev = current($this->Cost_model->get_cost_where(array('user_id' => $userId, 'YEAR(month_paided)' => $prevYear, 'MONTH(month_paided)' => $prevMonth )));
		    if($DBCostPrev) {
		        $data['costTotal'][$userId]['debtPrev'] = $DBCostPrev["debt"];
		    } else {
		        $data['costTotal'][$userId]['debtPrev'] = 0;
		    }

		    $DBCost = current($this->Cost_model->get_cost_where(array('user_id' => $userId, 'YEAR(month_paided)' => $year, 'MONTH(month_paided)' => $month )));
		    if($DBCostPrev) {
		        $data['costTotal'][$userId]['debt'] = $DBCost["debt"];
		    } else {
		        $data['costTotal'][$userId]['debt'] = 0;
		    }
		    
		    $where = array('user_id'=>$userId, 'YEAR(month_paided)' => $year, 'MONTH(month_paided)' => $month);
		    $data["payments"][$userId] = $this->Payment_model->get_payment_where($where);		    		    
		    if (sizeof($data["payments"][$userId])==0) {
				$data["payments"][$userId][0]["status"]="-";
				$data["payments"][$userId][0]["amount"]="-";
   				$data["payments"][$userId][0]["payment_date"]="-";
				$data["payments"][$userId][0]["type"]="-";
				$data["payments"][$userId][0]["bank_id"]="-";
				$data["payments"][$userId][0]["cheque_Num"]="-";
			}
		}
		
		$this->load->view('templates/header', $data);	
		$this->load->view('admin/viewPayment', $data);
		$this->load->view('templates/footer');
	}
	
	/*
	 * Form validation callback functions
	*/
	function already_exists() {
		$userId = $this->input->post('user_id');
		$month = $this->input->post('month');
		$year = $this->input->post('year');
		$type = $this->input->post('type');
		//query the database
		$where = array('user_id'=>$userId, 'type'=>$type, 'YEAR(month_paided)' => $year, 'MONTH(month_paided)' => $month);
		$result = $this->Payment_model->get_payment_where($where);
	
		if($result) {
			$this->form_validation->set_message('already_exists', 'Paiement d&eacute;ja enregistr&eacute;!');
			return FALSE;
		} else {
			$this->form_validation->set_message('already_exists', 'Paiement enregistr&eacute;!');
			return TRUE;
		}
	}
	
}

?>