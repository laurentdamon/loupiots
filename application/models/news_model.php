<?php
class News_model extends CI_Model {

	public function __construct() {
	}
		
	public function get_news($slug = FALSE) {
		if ($slug === FALSE) {
			$this->db->order_by("id", "desc");
			$query = $this->db->get('news');
			
			return $query->result_array();
		}
		
		$query = $this->db->get_where('news', array('slug' => $slug));
		return $query->row_array();
	}
	
	public function set_news() {
		$this->load->helper('url');
		$slug = url_title($this->input->post('title'), 'dash', TRUE);
		
		$data = array(
			'title' => htmlentities($this->input->post('title'), ENT_QUOTES, "UTF-8"),
			'slug' => $slug,
			'text' => htmlentities($this->input->post('text'), ENT_QUOTES, "UTF-8")
		);
		
		return $this->db->insert('news', $data);
	}
}