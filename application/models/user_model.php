<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

//require_once(dirname(__FILE__).'\..\libraries\phpass-0.3\PasswordHash.php');	//localhost
//require_once(dirname(__FILE__).'/../libraries/phpass-0.3/PasswordHash.php');	//free

define('PHPASS_HASH_STRENGTH', 8);
define('PHPASS_HASH_PORTABLE', false);


class User_model extends CI_Model 
{
	var $CI;
	var $user_table = 'users';

	public function __construct() {
		$this->load->database();
		$this->load->model('Class_model');
	}
		
	/**
	 * Create a user account
	 */
	function create($mail = '', $pass = '', $name = '', $privilege = 1) {
		$this->CI =& get_instance();

		//Make sure account info was sent
		if($mail == '' || $pass == '' || $name == '' ) {
			return false;
		}
		//Check against user table
		$this->CI->db->where('mail', $mail); 
		$query = $this->CI->db->get_where($this->user_table);
		if ($query->num_rows() > 0) //user_email already exists
			return false;

		//Insert account into the database
		$data = array(
					'mail' => $mail,
					'pass' => $pass,
					'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
					'privilege' => $privilege,
					'modified' => date('c')
				);

		$this->CI->db->set($data); 

		if(!$this->CI->db->insert($this->user_table)) //There was a problem! 
			return false;						
				
		return $this->db->insert_id();
	}

	/**
	 * Create a user account
	 */
	function update($userId, $mail = '', $pass = '', $name = '', $privilege = 1) {
		//Make sure account info was sent
		if($userId == '' || $mail == '' || $pass == '' || $name == '' ) {
			return FALSE;
		}

		//Check against user table
		$users = $this->get_users_where(array('id' => $userId));
		if ( sizeof($users) == 0) //user does not exists
			return FALSE;

		//Update account into the database
		$data = array(
					'mail' => $mail,
					'name' => htmlentities($name, ENT_QUOTES, "UTF-8"),
					'privilege' => $privilege,
					'modified' => date('c')
				);
		if ($pass != "fake") {
			//Hash user_pass using phpass
//			$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
//			$pass_hashed = $hasher->HashPassword($pass);

//			$data['pass'] = $pass_hashed;
			$data['pass'] = $pass;
		}

		$this->db->where('id', $userId);
		$this->CI =& get_instance();

		
		if( !$this->CI->db->update($this->user_table, $data) ) //There was a problem! 
			return FALSE;						
				
		return $userId;
	}

	/**
	 * Login and sets session variables
	 *
	 * @access	public
	 * @param	string
	 * @param	string
	 * @return	bool
	 */
	function login($mail = '', $pass = '') {
		$this->CI =& get_instance();

		if($mail == '' || $pass == '')
			return false;

		//Check if already logged in
		if($this->CI->session->userdata('mail') == $mail)
			return true;
		
		//Check against user table
		$this->CI->db->where('mail', $mail); 
		$query = $this->CI->db->get_where($this->user_table);
		
		if ($query->num_rows() > 0) {
			$user_data = $query->row_array(); 
//			$hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
//			if(!$hasher->CheckPassword($pass, $user_data['pass']))
//				return false;
			if($pass != $user_data['pass'])
				return false;
			
			//Destroy old session
			$this->CI->session->sess_destroy();
			
			//Create a fresh, brand new session
			$this->CI->session->sess_create();

			$this->CI->db->simple_query('UPDATE ' . $this->user_table  . ' SET last_login = NOW() WHERE id = ' . $user_data['id']);

			//Set session data
			unset($user_data['pass']);
			$user_data['logged_in'] = true;
			$this->CI->session->set_userdata($user_data);
			return true;
		} else {
			return false;
		}	

	}

	/**
	 * Delete user
	 *
	 * @access	public
	 * @param integer
	 * @return	bool
	 */
	function delete($user_id) {
		$this->CI =& get_instance();
		
		if(!is_numeric($user_id))
			return false;			

		return $this->CI->db->delete($this->user_table, array('user_id' => $user_id));
	}
	
	/**
	 * Get user or users list
	 * 
	 * TODO make a real query !!!
	 *
	 * @access	public
	 * @param integer
	 * @return	bool
	 */
	public function get_users($onlyActiveChild = FALSE, $id = FALSE) {
		$users=array();
		$this->db->order_by("name");
		if ($id === FALSE) {
			$query = $this->db->get($this->user_table);
			foreach ($query->result_array() as $user) {
			    $where = array('user_id' => $user["id"]);
				if ($onlyActiveChild) {
					$where['is_active']=1;
				}
				$children_size = 0;
				$query = $this->db->get_where('child', $where);
				foreach ($query->result_array() as $child) {
					$classNum=$child['class_id'];
					$child['class']= $this->Class_model->get_class($classNum);
					$user['children'][] = $child;
					$children_size = sizeof($user['children']);
				}
				
				if (!$onlyActiveChild || ($onlyActiveChild && $children_size>0)) {
				    $users[] = $user;
				}
			}
		} else {
			$query = $this->db->get_where($this->user_table, array('id' => $id));
			$users = $query->row_array();
				
			$where = array('user_id' => $id);
			if ($onlyActiveChild) {
				$where['is_active']=1;
			}
			$query = $this->db->get_where('child', $where);
			$children = $query->result_array();
			foreach ($children as $child) {
				$classNum=$child['class_id'];
				$child['class']= $this->Class_model->get_class($classNum);
				$users['children'][] = $child;
			}
		}
		return $users;
	}
	
	public function get_users_where($where = FALSE) {
		$this->db->order_by("name", "asc");
		if ($where === FALSE) {
			$query = $this->db->get($this->user_table);
		} else {
			$query = $this->db->get_where($this->user_table, $where);
		}
		return $query->result_array();
	}
	
	public function get_option_users($id = FALSE) {
		$usersOption=array();
		$users=array();
		if ($id === FALSE) {
			$users = $this->get_users_where();
		} else {
			$users = $this->get_users_where(array('id' => $id));
		}		
		foreach ($users as $currentUser) {
			$currentUserId = $currentUser['id'];
			$usersOption[$currentUserId] = $currentUser['name'];
		}
		return $usersOption;
	}
	
	
	
}
?>
