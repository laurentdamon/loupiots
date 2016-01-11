<?php
class Calendar_model extends CI_Model {
	
	var $conf;
	var $holidays_table = 'holidays';
	
	function __construct() {
	}
	
	function init($userId) {
		//$periods = $this->Period_model->get_periods();
		$periods = $this->Days_model->get_daysPeriods();
		
		$this->conf = array(
			'day_type' => 'long',
			'start_day' => 'monday',
			'show_next_prev' => true,
			'next_prev_url' => base_url().'index.php/user/viewUser/'.$userId
		);		

		$this->conf['template'] = '
			{table_open}<table border="0" cellpadding="0" cellspacing="0" class="calendar">{/table_open}
			{table_open_print}<table border="1" cellpadding="0" cellspacing="0" class="calendar">{/table_open_print}
			
			{heading_row_start}<tr>{/heading_row_start}
			
			{heading_previous_cell}
				<th><a href="{previous_url}"><img src="'.base_url().'/resource/img/left.ico" alt="precedent"/></a></th>
			{/heading_previous_cell}
			{heading_title_cell}
				<th class="title_cell" colspan="{colspan}">{heading}</th>
			{/heading_title_cell}
			{heading_next_cell}
				<th><a href="{next_url}"><img src="'.base_url().'/resource/img/right.ico" alt="suivant"/></a></th>
			{/heading_next_cell}
			
			{heading_row_end}</tr>{/heading_row_end}
			
			{week_row_start}<tr class="days_title">{/week_row_start}
			{period_row_start}<tr class="periods_title">{/period_row_start}
			
			{week_child_cell}<th >{famille}</th>{/week_child_cell}
			{familly_next_cell}
				<th>
						<a href="{next_family}"><img src="'.base_url().'/resource/img/down.png" alt="suivant"/></a>
						<a href="{previous_family}"><img src="'.base_url().'/resource/img/up.png" alt="suivant"/></a>
				</th>
			{/familly_next_cell}
						
			{week_day_cell}<td colspan={period_size}>{week_day}</td>{/week_day_cell}
			{week_dayoff_cell}<td>{week_day}</td>{/week_dayoff_cell}
			{week_row_end}</tr>{/week_row_end}

			{cal_row_start}<tr class="days">{/cal_row_start}
			
			{cal_cell_start}<td class="day" colspan={period_size}>{/cal_cell_start}
			{cal_celloff_start}<td class="mid_periodoff">{/cal_celloff_start}
			{cal_cellhol_start}<td class="mid_periodoff" colspan={period_size}>{/cal_cellhol_start}
					
			{cal_cell_no_content}
				<div class="day_num">{day}</div>
			{/cal_cell_no_content}
			{cal_cell_no_content_today}
				<div class="day_num highlight">{day}</div>
			{/cal_cell_no_content_today}
			{cal_cell_blank}
				&nbsp;
			{/cal_cell_blank}
			
			{cal_cell_end}</td>{/cal_cell_end}
			{cal_row_end}</tr>{/cal_row_end}

			{child_row_start}<tr class="child">{/child_row_start}
			
			{cal_child_cell}<td class="child_name">{child}</td>{/cal_child_cell}
			{child_row_end}</tr>{/child_row_end}
			
			{cal_period_cell}
				<td class="{resa_type}">&nbsp;<p class="content">{year}-{month}-{day}-{period}-{child}</p></td>
			{/cal_period_cell}
			{cal_mid_period_cell}
				<td class="{resa_type} mid_day">&nbsp;<p class="content">{year}-{month}-{day}-{period}-{child}</p></td>
			{/cal_mid_period_cell}

			{cal_period_cell_print}
				<td class="{resa_type}">{resa_type_print}</td>
			{/cal_period_cell_print}
			{cal_mid_period_cell_print}
				<td class="{resa_type} mid_day">{resa_type_print}</td>
			{/cal_mid_period_cell_print}
			
			{cal_periodoff_cell}
				<td class="{resa_type}">&nbsp;<p class="content">{year}-{month}-{day}-{period}-{child}</p></td>
			{/cal_periodoff_cell}
			{cal_mid_periodoff_cell}
				<td class="{resa_type} mid_day">&nbsp;<p class="content">{year}-{month}-{day}-{period}-{child}</p></td>
			{/cal_mid_periodoff_cell}
			
			{table_close}</table>{/table_close}
		';
	}
	
	function get_calendar_data($id, $year, $month) {
		$cal_data = array();
		
		$cal_data['users'] = $this->User_model->get_users(TRUE, $id);
		//$cal_data['periods'] = $this->Period_model->get_periods();
		$cal_data['periods'] = $this->Days_model->get_daysPeriods();
		$cal_data['resas']= $this->Resa_model->get_resa_where(array('YEAR(date)' => $year, 'MONTH(date)' => $month));
		$holidays = $this->get_holidays_where(array('YEAR(date)' => $year, 'MONTH(date)' => $month));
		foreach ($holidays as $currentHolidays) {
			$cal_data['holidays'][] = strtotime($currentHolidays["date"]);
		}
		return $cal_data;
		
	}
	
	function generate ($id, $year, $month) {
		$this->load->library('calendar', $this->conf);
		$cal_data = $this->get_calendar_data($id, $year, $month);
		return $this->calendar->generate($year, $month, $cal_data);
		
	}
	
	function add_holidays($start, $end) {
		//Get already inserted date
		$alredyHolidays = $this->get_holidays_per_period($start, $end);
		$currentDate = $start;
		while ($currentDate<=$end) {
			if (!in_array($currentDate, $alredyHolidays)) {
				$this->db->insert($this->holidays_table, array('date' => date("Y-m-d", $currentDate)));
			}			
			$currentDate = strtotime('+1 day', $currentDate);
		}
	}
	
	public function get_holidays_per_period($start, $end) {
		$startSQL = date("Y-m-d", $start);
		$endSQL = date("Y-m-d", $end);
		
		$this->db->where('date >=', $startSQL);
		$this->db->where('date <=', $endSQL);
		
		$this->db->select("UNIX_TIMESTAMP( date )");
		$query = $this->db->get($this->holidays_table);
		$result = array();
		foreach( $query->result_array() as $current )  {
			$result[]=$current["UNIX_TIMESTAMP( date )"];
		}
		return $result;
	}
	
	public function get_holidays_where($where) {
		$query = $this->db->get_where($this->holidays_table, $where);
		return $query->result_array();
	}
		
	public function getDaysOfWeeks($date) {
		$date = strtotime($date);
		$days["Lundi"] = date('d-m-Y', strtotime('monday this week', $date));
		$days["Mardi"] = date('d-m-Y', strtotime('tuesday this week', $date));
		$days["Mercredi"] = date('d-m-Y', strtotime('wednesday this week', $date));
		$days["Jeudi"] = date('d-m-Y', strtotime('thursday this week', $date));
		$days["Vendredi"] = date('d-m-Y', strtotime('friday this week', $date));
		return $days;
	}
	
}
