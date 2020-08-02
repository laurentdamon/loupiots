<div class="holder_content">
	<h3>
	<?php 
	$nextMonth = mktime(0, 0, 0, date("m")+1, date("d"), date("Y"));
	echo date("F", $nextMonth);
	?>
	</h3>
	<a href="<?php echo site_url()?>/calendar/validateResa/">Validation des reservations</a>
	<br>
</div>

<div class="holder_content_separator"></div>

<div class="holder_content">
	<h3>Periodes de fermeture</h3>

	<form class="form" method="post" action="<?php echo site_url()?>/calendar/createHolidays">
	<div>
		<label for="start">Debut:</label> 
		<input name="start" id="dateSel" class="InputDate" /> <br/>
		<label for="end">Fin:</label>
		<input name="end" id="end-date" class="InputDate" /> <br/>
		<input class="InputSubmit" type="submit" value="Enregistrer"/>
	</div>
	</form>

</div>

<div class="holder_content_separator"></div>