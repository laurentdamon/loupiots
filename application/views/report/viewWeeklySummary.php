<div id=siteUrl>
	<?php echo site_url()?>
</div>

<div class="holder_content">
	<h3>Recapitulatif hebdomadaire</h3>
	<form class="form" method="post" action="<?php echo site_url()?>/report/weeklySummary">
	<div>
		<label for="weekCall">Date:</label> 
		<input name="weekCall" id="dateSel" class="InputDate" /> <br/>
		<label for="paymentMonth">Familles actives seulement</label>
		<?php echo form_checkbox('onlyActive', TRUE, $onlyActive);?>
		
		<input class="InputSubmit" type="submit" value="Generer"/>
	</div>
	</form>
	<a class="button" href="javascript:printDiv('recapPrint')">Imprimer</a>
</div>

<div class="holder_content" id="recapPrint">

	<h3>Semaine du <?php echo $startDate; ?> au <?php echo $endDate; ?> </h3>
	<div id="classroomCall">
		<?php echo $output; ?>	
	</div>

	<div class="holder_content_separator"></div>
	
</div>

