<?php 
// echo "test<br>";
// print_r($userId);
// echo "test<br>";
// print_r($persist);
?>

<div class="holder_content">
	<h3>Cloture des inscriptions</h3>

	<form class="form" method="post" action="<?php echo site_url()?>/calendar/validateResa/">
	<div>		
		<label for="paymentMonth">Mois clotur&eacute;</label>
   		<?php echo form_dropdown('month', generate_options_array(0,12,'callback_month'), $month, 'class="InputSelect"'); ?>
   		<?php echo form_dropdown('year', generate_options_array(date('Y')+1,2010), $year, 'class="InputSelect"');?>
   		<input class="InputSubmit" type="submit" value="Cloturer"/>
	</div>
	</form>
	
	<br>
</div>

<div class="holder_content_separator"></div>

<div class="holder_content">
	<h3>Periodes de vacances</h3>

	<form class="form" method="post" action="<?php echo site_url()?>/calendar/createHolidays">
	<div>
		<label for="start">Debut:</label> 
		<input name="start" id="start-date" class="InputDate" /> <br/>
		<label for="end">Fin:</label>
		<input name="end" id="end-date" class="InputDate" /> <br/>
		<input class="InputSubmit" type="submit" value="Enregistrer"/>
	</div>
	</form>

</div>

<div class="holder_content_separator"></div>

<?php 
function generate_options_array($from,$to,$callback=false) {
	$reverse=false;
	if($from>$to) {
		$tmp=$from;
		$from=$to;
		$to=$tmp;
		$reverse=true;
	}
	$options=array();
	$init=$from-1;
	for($i=$from;$i<=$to;$i++) {
		$val=$callback?$callback($i):$i;
		$options["$i"]=$val;
	}
	if($reverse) {
		$options=array_reverse($options, true);
	}
	return $options;
}
?>