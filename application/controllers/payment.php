<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payment extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->model('User_model');
		$this->load->model('Payment_model');
		$this->load->model('Resa_model');
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
			$this->payment_status[5] = 'Comptabilis&eacute;';
		}
	}

	function index() {
		
	}
	
	public function create($userId='',$fromReport=0) {
//		$this->output->enable_profiler(TRUE);
		
		//check access rights
		$data['loggedId']=$this->session->userdata('id');
		if (isset($userId) && $userId!='') {
			$data['userId'] = $userId;
		} else {
			$data['userId'] = $this->input->post('user_id');
		}
		
		$data['fromReport'] = $fromReport;
		
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
		$data['prevMonth'] = date("Y-m-d", strtotime('previous month'));
		if (date("j", strtotime('now')) <= 6) {  // Choix du mois courant entre le 1 et le 6
		    $data['month'] = date("Y-m-d", strtotime('now'));
		}

		$data['loggedPrivilege'] = $this->session->userdata('privilege');
		if ($data['loggedPrivilege'] >= 2) {
			$data['usersOption'] = $this->User_model->get_option_users();
			$selId = $this->input->post('selId');
		} else {
			$data['usersOption'] = $this->User_model->get_option_users($data['loggedId']);
		}
		
		$this->form_validation->set_rules('amount', 'Montant', 'numeric');
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
		    if ( $_POST['fromReport']==1 ) {
		        redirect('payment/report?year='.date("Y", strtotime($_POST['month_paided'])).'&month='.date("n", strtotime($_POST['month_paided'])), 'refresh');
		    } else {
		        redirect('user/'.$_POST['user_id'].'/'.date("Y", strtotime($_POST['month_paided'])).'/'.date("n", strtotime($_POST['month_paided']+1)), 'refresh');
		    }
		}
		
	}

	public function update($paymentId='',$fromReport=0) {
//		$this->output->enable_profiler(TRUE);
		
		$data['title'] = 'Modifier un paiement';

		$data['payment'] = $this->Payment_model->get_payment($paymentId);
		$data['payment']['user'] = $this->User_model->get_users(FALSE, $data['payment']['user_id']);
		
		$data['payment_types'] = $this->payment_types;
		$data['payment_status'] = $this->payment_status;
		$data['banques'] = $this->banks;

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

		$this->form_validation->set_rules('amount', 'Montant', 'trim|required|xss_clean');
		$this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
		$this->form_validation->set_rules('month', 'Mois payé', 'numeric');
		
		if ($this->form_validation->run() !== FALSE) {
			$payment = $this->Payment_model->setPaymentFromPostData($_POST);
			$this->Payment_model->update($paymentId, $payment);
			if ($_POST['status']==3 && $_POST['previousStatus']!=3 ) {     //validation
			    //modifier le debit du mois de paiment
			    $cost['user_id'] = $payment["user_id"];
			    $cost['month_paided'] = $payment['month_paided'];
			    //Get month paid resa
			    list($year, $month, $day) = explode("-", $cost['month_paided']);
			    $bill = $this->Resa_model->getResaSummary($year, $month, $payment['user_id']);
			    //Get month paid debt
			    $DBCost = current($this->Cost_model->get_cost_where(array('user_id' => $payment["user_id"], 'month_paided' => $payment['month_paided'] )));
			    if($DBCost) {
			        $cost['debt'] = round(($DBCost["debt"] + $bill['sum']['total'] - $payment["amount"]),2);
			        $this->Cost_model->update($DBCost["id"], $cost);
			    } else {
			        $cost['debt'] = round(($bill['sum']['total']  - $payment["amount"]),2);
			        $this->Cost_model->create($cost);
			    }
			}
			if ($_POST['status']==4 && $_POST['previousStatus']==3 ) {     //annulation
			    //modifier le debit du mois de paiment
			    //Get month paid resa
			    list($year, $month, $day) = explode("-", $payment['month_paided']);
			    $bill = $this->Resa_model->getResaSummary($year, $month, $payment['user_id']);
			    //Get current month debt
			    $DBCost = current($this->Cost_model->get_cost_where(array('user_id' => $payment["user_id"], 'month_paided' => $payment['payment_date'] )));
			    if($DBCost) {
			        $DBCost['debt'] = round(($DBCost["debt"] + $bill['sum']['total'] + $payment["amount"]),2);
			        $this->Cost_model->update($DBCost["id"], $DBCost);
			    }
			}
		}

		if ( sizeof($_POST)==0 ) {
			$this->load->view('templates/header', $data);
			$this->load->view('user/viewUpdatePayment', $data);
			$this->load->view('templates/footer');
		} else {
			if ( $fromReport==1 ) {
				$data['userId'] = $this->input->post('user_id');
				redirect('payment/report?year='.date("Y", strtotime($data['payment']['payment_date'])).'&month='.date("n", strtotime($data['payment']['payment_date'])), 'refresh');
			} else {  
			    redirect('user/'.$data['userId'].'/'.date("Y", strtotime($data['payment']['payment_date'])).'/'.date("n", strtotime($data['payment']['payment_date'])), 'refresh');
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