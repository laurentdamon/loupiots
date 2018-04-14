<div id=siteUrl>
	<?php echo site_url()?>
</div>

<div class="holder_content">
	<h3>Liste d'appel par classe</h3>
	
	<form class="form" method="post" action="<?php echo site_url()?>/report/classroomCall">
	<div>
		<label for="weekCall">Date d'appel:</label> 
		<input name="weekCall" id="dateSel" class="InputDate" /> <br/>
		
		<input class="InputSubmit" type="submit" value="Generer"/>
	</div>
	</form>
</div>

<div class="holder_content" id="classCallPrintPM">
	
	<h3>Semaine du <?php echo $startDate; ?> au <?php echo $endDate; ?> (Apres-midi) </h3>
	<a class="button" href="javascript:printDiv('classCallPrintPM')">Imprimer</a>
	<div id="classroomCall"></div>
	<?php echo $outputPM; ?>	
	<div class="holder_content_separator"></div>
	
</div>

<div class="holder_content" id="classCallPrintAM">
	<h3>Semaine du <?php echo $startDate; ?> au <?php echo $endDate; ?> (Matin) </h3>
	<a class="button" href="javascript:printDiv('classCallPrintAM')">Imprimer</a>
	<div id="classroomCall"></div>
	<?php echo $outputAM; ?>	
	<div class="holder_content_separator"></div>
	
</div>

