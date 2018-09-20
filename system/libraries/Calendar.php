<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * CodeIgniter Calendar Class
 *
 * This class enables the creation of calendars
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author		ExpressionEngine Dev Team
 * @link		http://codeigniter.com/user_guide/libraries/calendar.html
 */
class CI_Calendar {

	var $CI;
	var $lang;
	var $local_time;
	var $template		= '';
	var $start_day		= 'sunday';
	var $month_type		= 'long';
	var $day_type		= 'abr';
	var $show_next_prev	= FALSE;
	var $next_prev_url	= '';

	/**
	 * Constructor
	 *
	 * Loads the calendar language file and sets the default time reference
	 */
	public function __construct($config = array()) {
		$this->CI =& get_instance();

		if ( ! in_array('calendar_lang.php', $this->CI->lang->is_loaded, TRUE)) {
			$this->CI->lang->load('calendar');
		}

		$this->local_time = time();

		if (count($config) > 0) {
			$this->initialize($config);
		}

		log_message('debug', "Calendar Class Initialized");
	}

	// --------------------------------------------------------------------

	/**
	 * Initialize the user preferences
	 *
	 * Accepts an associative array as input, containing display preferences
	 *
	 * @access	public
	 * @param	array	config preferences
	 * @return	void
	 */
	function initialize($config = array())
	{
		foreach ($config as $key => $val)
		{
			if (isset($this->$key))
			{
				$this->$key = $val;
			}
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Generate the calendar
	 *
	 * @access	public
	 * @param	integer	the year
	 * @param	integer	the month
	 * @param	array	the data to be shown in the calendar cells
	 * @return	string
	 */
	function generate($year = '', $month = '', $data = array(), $forPrint = FALSE) {
		
		// Set and validate the supplied month/year
		if ($year == '') $year  = date("Y", $this->local_time);
		if ($month == '') $month = date("m", $this->local_time);
		if (strlen($year) == 1) $year = '200'.$year;
		if (strlen($year) == 2) $year = '20'.$year;
		if (strlen($month) == 1) $month = '0'.$month;

		$adjusted_date = $this->adjust_date($month, $year);

		$month	= $adjusted_date['month'];
		$year	= $adjusted_date['year'];

		$periods = $data['periods'];

		// Determine the total days in the month
		$total_days = $this->get_total_days($month, $year);

		// Set the starting day of the week
		$start_days	= array('sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6);
		$start_day = ( ! isset($start_days[$this->start_day])) ? 0 : $start_days[$this->start_day];

		// Set the starting day number
		$local_date = mktime(12, 0, 0, $month, 1, $year);
		$date = getdate($local_date);
		$day  = $start_day + 1 - $date["wday"];
		while ($day > 1) {
			$day -= 7;
		}
		
		// Set the current month/year/day
		// We use this to determine the "today" date
		$cur_year	= date("Y", $this->local_time);
		$cur_month	= date("m", $this->local_time);
		$cur_day	= date("j", $this->local_time);

		$is_current_month = ($cur_year == $year AND $cur_month == $month) ? TRUE : FALSE;

		// Generate the template data array
		$this->parse_template();

		// Begin building the calendar output
		if (!is_null($forPrint) && $forPrint) {
			$out = $this->temp['table_open_print'];
		} else {
			$out = $this->temp['table_open'];
		}
		$out .= "\n";

		$out .= "\n";
		$out .= $this->temp['heading_row_start'];
		$out .= "\n";

		// "previous" month link
		if ($this->show_next_prev == TRUE) {
			// Add a trailing slash to the  URL if needed
			$this->next_prev_url = preg_replace("/(.+?)\/*$/", "\\1/",  $this->next_prev_url);

			$adjusted_date = $this->adjust_date($month - 1, $year);
			$out .= str_replace('{previous_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_previous_cell']);
			$out .= "\n";
		}

		// Heading containing the month/year
		$colspan = ($this->show_next_prev == TRUE) ? 26 : 22;
		$this->temp['heading_title_cell'] = str_replace('{colspan}', $colspan, $this->temp['heading_title_cell']);
		$this->temp['heading_title_cell'] = str_replace('{heading}', $this->get_month_name($month)."&nbsp;".$year, $this->temp['heading_title_cell']);

		$out .= $this->temp['heading_title_cell'];
		$out .= "\n";

		// "next" month link
		if ($this->show_next_prev == TRUE) {
			$adjusted_date = $this->adjust_date($month + 1, $year);
			$out .= str_replace('{next_url}', $this->next_prev_url.$adjusted_date['year'].'/'.$adjusted_date['month'], $this->temp['heading_next_cell']);
		}

		$out .= "\n";
		$out .= $this->temp['heading_row_end'];
		$out .= "\n";

		// Write the cells containing the days of the week
		$out .= "\n";
		$out .= $this->temp['week_row_start'];
		$out .= "\n";

		// Heading title for child
		$this->temp['week_child_cell'] = str_replace('{famille}', $data['users']['name'], $this->temp['week_child_cell']);
		$out .= $this->temp['week_child_cell'];
		$out .= "\n";

		$day_names = $this->get_day_names();
		for ($i = 0; $i < 7; $i ++) {
			$day_name = $day_names[($start_day + $i) %7];
			if ($day_name=='Samedi' || $day_name=='Dimanche' || $day_name=='Mercredi') {
				$out .= str_replace('{week_day}', $day_name, $this->temp['week_dayoff_cell']);
			} else {
				$temp = str_replace('{week_day}', $day_name, $this->temp['week_day_cell']);
				$temp = str_replace('{period_size}', sizeof($periods[$day_name]), $temp);
				$out .= $temp;
			}
		}

		$out .= "\n";
		$out .= $this->temp['week_row_end'];
		$out .= "\n";

		//Period title row
		$out .= "\n";
		$out .= $this->temp['period_row_start'];
		$out .= "\n";
		
		// next family link
		$this->temp['familly_next_cell'] = str_replace('{next_family}', $data['users']['id']+1, $this->temp['familly_next_cell']);
		$this->temp['familly_next_cell'] = str_replace('{previous_family}', $data['users']['id']-1, $this->temp['familly_next_cell']);
		$out .= $this->temp['familly_next_cell'];

		for ($i = 0; $i < 7; $i ++) {
			$day_name = $day_names[($start_day + $i) %7];
			if ($day_name=='Samedi' || $day_name=='Dimanche' || $day_name=='Mercredi') {
				$out .= "<td id ='period_mid_day'>&nbsp;</td>";
			} else {
				$j=0;
				foreach ($periods[$day_name] as $period) {
					$j++;
					if (!isset($period["next_period"]) && "AM"==$period["type"]) {
						$style = "id ='period_mid_day'";
					} elseif (sizeof($periods[$day_name])==$j) {
						$style = "id ='period_mid_day'";
					} else {
						$style = "";
					}
					if ("PM"==$period["type"]) {
						$time=explode(":", $period["stop_time"]);
						$out .= "<td $style>-".$time[0].":".$time[1]."</td>";
					} else {
						$time=explode(":", $period["start_time"]);
						$out .= "<td $style>".$time[0].":".$time[1]."-</td>";
					}
				}
			}
		}

		$out .= "\n";
		$out .= $this->temp['period_row_end'];
		$out .= "\n";

		// Build the main body of the calendar
		while ($day <= $total_days) {
				
			//days row
			$out .= "\n";
			$out .= $this->temp['cal_row_start'];
			$out .= "\n";
				
			$out .= "<td>&nbsp;</td>";

			for ($i = 0; $i < 7; $i++) {
				$day_name = $day_names[($start_day + $i) %7];

				//Determine if holidays
				$currentDate = mktime(0, 0, 0, $month, $day, $year);
				
				if ($day_name=='Samedi' || $day_name=='Dimanche' || $day_name=='Mercredi') {
					$out .= ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_celloff_start_today'] : $this->temp['cal_celloff_start'];
				} elseif (array_key_exists('holidays', $data) && in_array($currentDate, $data['holidays'])) {
					//$out .= ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cellhol_start_today'] : $this->temp['cal_cellhol_start'];
					if ($is_current_month == TRUE AND $day == $cur_day) {
						$temp = $this->temp['cal_cellhol_start_today'];
					} else {
						$temp = str_replace('{period_size}', sizeof($periods[$day_name]), $this->temp['cal_cellhol_start']);
					}
					$out .= $temp;
				} else {
					if ($is_current_month == TRUE AND $day == $cur_day) {
						$temp = $this->temp['cal_cell_start_today'];
					} else {
						$temp = str_replace('{period_size}', sizeof($periods[$day_name]), $this->temp['cal_cell_start']);
					}
					$out .= $temp;
				}

				if ($day > 0 AND $day <= $total_days) {
					$temp = ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_no_content_today'] : $this->temp['cal_cell_no_content'];
					$out .= str_replace('{day}', $day, $temp);
				} else {
					// Blank cells
					$out .= $this->temp['cal_cell_blank'];
				}
				$out .= ($is_current_month == TRUE AND $day == $cur_day) ? $this->temp['cal_cell_end_today'] : $this->temp['cal_cell_end'];
				$rowDay[$i]=$day;
				$day++;
			}
			$out .= "\n";
			$out .= $this->temp['cal_row_end'];
			$out .= "\n";
				
			//child row
			$children = $data['users']['children'];
			foreach ($children as $child) {
				$childNum=$child['id'];
				$resas = $data['resas'][$childNum];
				$out .= "\n";
				$out .= $this->temp['child_row_start'];
				$out .= "\n";

				$out .= str_replace('{child}', $child['name'], $this->temp['cal_child_cell']);
				$out .= "\n";
				for ($i = 0; $i < 7; $i++) {
					$currentDay=$rowDay[$i];
					$day_name = $day_names[($start_day + $i) %7];
					if ($day_name=='Samedi' || $day_name=='Dimanche' || $day_name=='Mercredi') {
						$out .= "<td class='mid_periodoff'>&nbsp;</td>";
					} else {
						if ($currentDay > 0 AND $currentDay <= $total_days) {
							$currentDate = mktime(0, 0, 0, $month, $currentDay, $year);
							$date=date("Y-m-d", $currentDate);
							
							$j=0;
							foreach ($periods[$day_name] as $period) {
								if (array_key_exists('holidays', $data) && in_array($currentDate, $data['holidays'])) {
									$j++;
									if (!isset($period["next_period"]) && "AM"==$period["type"]) {
										$out .= "<td class='mid_periodoff'>&nbsp;</td>";
									} elseif (sizeof($periods[$day_name])==$j) {
										$out .= "<td class='mid_periodoff'>&nbsp;</td>";
									} else {
										$out .= "<td class='periodoff'>&nbsp;</td>";
									}
								} else {
									$resaType="period";
									foreach($resas as $resa) {
										if ($resa["child_id"]==$childNum && $resa["date"]==$date && $resa["period_id"]==$period["id"]) {
                                            $resaType="period_".$resa["resa_type"];
											break;
										} else {
											$resaType="period";
										}
									}
									$j++;
									if (!is_null($forPrint) && $forPrint) {
										if (!isset($period["next_period"]) && "AM"==$period["type"]) {
											$t1 = str_replace('{year}', $year, $this->temp['cal_mid_period_cell_print']);
										} elseif (sizeof($periods[$day_name])==$j) {
											$t1 = str_replace('{year}', $year, $this->temp['cal_mid_period_cell_print']);
										} else {
											$t1 = str_replace('{year}', $year, $this->temp['cal_period_cell_print']);
										}
									} else {
										if (!isset($period["next_period"]) && "AM"==$period["type"]) {
											$t1 = str_replace('{year}', $year, $this->temp['cal_mid_period_cell']);
										} elseif (sizeof($periods[$day_name])==$j) {
											$t1 = str_replace('{year}', $year, $this->temp['cal_mid_period_cell']);
										} else {
											$t1 = str_replace('{year}', $year, $this->temp['cal_period_cell']);
										}
									}
									$resaType_print = $resaType."print";
									if ($resaType=="period_1") {
										$resaType_print = "Res";
									} else if ($resaType=="period_2") {
										$resaType_print = "Val";
									} else if ($resaType=="period_3") {
										$resaType_print = "Dep";
									} else {
										$resaType_print = "&nbsp;";
									}
									
									$t2 = str_replace('{month}', $month, $t1);
									$t3 = str_replace('{day}', $currentDay, $t2);
									$t4 = str_replace('{child}', $childNum, $t3);
									$t5 = str_replace('{resa_type}', $resaType, $t4);
									$t6 = str_replace('{resa_type_print}', $resaType_print, $t5);
									$out .= str_replace('{period}', $period["periodId"], $t6);
								}
							}
						} else {
							$out .= "<td class='mid_periodoff' colspan=".sizeof($periods[$day_name]).">&nbsp;</td>";
						}
					}
				}
				
				$out .= "\n";
				$out .= $this->temp['child_row_end'];
				$out .= "\n";
			}

		}

		$out .= "\n";
		$out .= $this->temp['table_close'];

//		$out .= print_r($outtest);
//		$clot = strtotime("29 March 2018") ;
//		$out .= "test $clot";
//		$out .= $test;
		
		return $out;
	}

	// --------------------------------------------------------------------

	/**
	 * Get Month Name
	 *
	 * Generates a textual month name based on the numeric
	 * month provided.
	 *
	 * @access	public
	 * @param	integer	the month
	 * @return	string
	 */
	function get_month_name($month)
	{
		if ($this->month_type == 'short')
		{
			$month_names = array('01' => 'cal_jan', '02' => 'cal_feb', '03' => 'cal_mar', '04' => 'cal_apr', '05' => 'cal_may', '06' => 'cal_jun', '07' => 'cal_jul', '08' => 'cal_aug', '09' => 'cal_sep', '10' => 'cal_oct', '11' => 'cal_nov', '12' => 'cal_dec');
		}
		else
		{
			$month_names = array('01' => 'cal_january', '02' => 'cal_february', '03' => 'cal_march', '04' => 'cal_april', '05' => 'cal_mayl', '06' => 'cal_june', '07' => 'cal_july', '08' => 'cal_august', '09' => 'cal_september', '10' => 'cal_october', '11' => 'cal_november', '12' => 'cal_december');
		}

		$month = $month_names[$month];

		if ($this->CI->lang->line($month) === FALSE)
		{
			return ucfirst(str_replace('cal_', '', $month));
		}

		return $this->CI->lang->line($month);
	}

	// --------------------------------------------------------------------

	/**
	 * Get Day Names
	 *
	 * Returns an array of day names (Sunday, Monday, etc.) based
	 * on the type.  Options: long, short, abrev
	 *
	 * @access	public
	 * @param	string
	 * @return	array
	 */
	function get_day_names($day_type = '')
	{
		if ($day_type != '')
		$this->day_type = $day_type;

		if ($this->day_type == 'long')
		{
			$day_names = array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
		}
		elseif ($this->day_type == 'short')
		{
			$day_names = array('sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat');
		}
		else
		{
			$day_names = array('su', 'mo', 'tu', 'we', 'th', 'fr', 'sa');
		}

		$days = array();
		foreach ($day_names as $val)
		{
			$days[] = ($this->CI->lang->line('cal_'.$val) === FALSE) ? ucfirst($val) : $this->CI->lang->line('cal_'.$val);
		}

		return $days;
	}

	// --------------------------------------------------------------------

	/**
	 * Adjust Date
	 *
	 * This function makes sure that we have a valid month/year.
	 * For example, if you submit 13 as the month, the year will
	 * increment and the month will become January.
	 *
	 * @access	public
	 * @param	integer	the month
	 * @param	integer	the year
	 * @return	array
	 */
	function adjust_date($month, $year)
	{
		$date = array();

		$date['month']	= $month;
		$date['year']	= $year;

		while ($date['month'] > 12)
		{
			$date['month'] -= 12;
			$date['year']++;
		}

		while ($date['month'] <= 0)
		{
			$date['month'] += 12;
			$date['year']--;
		}

		if (strlen($date['month']) == 1)
		{
			$date['month'] = '0'.$date['month'];
		}

		return $date;
	}

	// --------------------------------------------------------------------

	/**
	 * Total days in a given month
	 *
	 * @access	public
	 * @param	integer	the month
	 * @param	integer	the year
	 * @return	integer
	 */
	function get_total_days($month, $year)
	{
		$days_in_month	= array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

		if ($month < 1 OR $month > 12)
		{
			return 0;
		}

		// Is the year a leap year?
		if ($month == 2)
		{
			if ($year % 400 == 0 OR ($year % 4 == 0 AND $year % 100 != 0))
			{
				return 29;
			}
		}

		return $days_in_month[$month - 1];
	}

	// --------------------------------------------------------------------

	/**
	 * Set Default Template Data
	 *
	 * This is used in the event that the user has not created their own template
	 *
	 * @access	public
	 * @return array
	 */
	function default_template()
	{
		return  array (
						'table_open'				=> '<table border="0" cellpadding="4" cellspacing="0">',
						'table_open_print'			=> '<table border="1" cellpadding="4" cellspacing="0">',
						'heading_row_start'			=> '<tr>',
						'heading_previous_cell'		=> '<th><a href="{previous_url}">&lt;&lt;</a></th>',
						'heading_title_cell'		=> '<th colspan="{colspan}">{heading}</th>',
						'week_child_cell'			=> '<th>Enfant</th>',
						'heading_next_cell'			=> '<th><a href="{next_url}">&gt;&gt;</a></th>',
						'familly_next_cell'			=> '&nbsp;',
						'heading_row_end'			=> '</tr>',
						'week_row_start'			=> '<tr>',
						'period_row_start'			=> '<tr>',
						'cal_child_cell'			=> '<td>{child}</td>',
						'week_day_cell'				=> '<td>{week_day}</td>',
						'week_row_end'				=> '</tr>',
						'period_row_end'				=> '</tr>',
						'cal_row_start'				=> '<tr>',
						'cal_cell_start'			=> '<td>',
						'cal_cell_start_today'		=> '<td>',
						'cal_cell_content'			=> '<a href="{content}">{day}</a>',
						'cal_cell_content_today'	=> '<a href="{content}"><strong>{day}</strong></a>',
						'cal_cell_no_content'		=> '{day}',
						'cal_cell_no_content_today'	=> '<strong>{day}</strong>',
						'cal_cell_blank'			=> '&nbsp;',
						'cal_cell_end'				=> '</td>',
						'cal_cell_end_today'		=> '</td>',
						'cal_row_end'				=> '</tr>',
						'table_close'				=> '</table>'
						);
	}

	// --------------------------------------------------------------------

	/**
	 * Parse Template
	 *
	 * Harvests the data within the template {pseudo-variables}
	 * used to display the calendar
	 *
	 * @access	public
	 * @return	void
	 */
	function parse_template() {
		$this->temp = $this->default_template();

		if ($this->template == '') {
			return;
		}

		$today = array('cal_cell_start_today', 'cal_celloff_start_today', 'cal_cellhol_start_today', 'cal_cell_content_today', 'cal_cell_no_content_today', 'cal_cell_end_today');

		foreach (array( 'table_open', 
						'table_open_print',
						'table_close', 
						'heading_row_start', 
						'week_child_cell', 
						'heading_previous_cell', 
						'familly_next_cell',
						'heading_title_cell', 
						'heading_next_cell', 
						'heading_row_end', 
						'week_row_start', 
						'period_row_end', 
						'period_row_start', 
						'child_row_start', 
						'child_row_end', 
						'cal_child_cell', 
						'week_day_cell', 
						'period_size',
						'week_dayoff_cell', 
						'week_row_end', 
						'cal_row_start', 
						'cal_cell_start', 
						'cal_celloff_start', 
						'cal_cellhol_start', 
						'cal_cell_no_content',  
						'cal_cell_blank', 
						'cal_cell_end', 
						'cal_row_end', 
						'cal_period_cell', 
						'cal_mid_period_cell', 
						'cal_period_cell_print', 
						'cal_mid_period_cell_print', 
						'cal_celloff_start_today', 
						'cal_cellhol_start_today', 
						'cal_cell_start_today', 
						'cal_cell_no_content_today', 
						'cal_cell_end_today'
					) as $val)
		{
			if (preg_match("/\{".$val."\}(.*?)\{\/".$val."\}/si", $this->template, $match))
			{
				$this->temp[$val] = $match['1'];
			}
			else
			{
				if (in_array($val, $today, TRUE))
				{
					$this->temp[$val] = $this->temp[str_replace('_today', '', $val)];
				}
			}
		}
	}

}

// END CI_Calendar class

/* End of file Calendar.php */
/* Location: ./system/libraries/Calendar.php */
