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