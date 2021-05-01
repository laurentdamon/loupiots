<div class="holder_content">
	<h3>Modifier un paiement</h3>

	<form class="form" method="post" action="<?php echo site_url()?>/payment/update/<?php echo $payment['id']."/".$fromReport;?>">
	<div>

		<label>Famille</label>
		<?php echo $payment['user'] ['name']; ?><br/>
		<input class="InputText" type="hidden" name="user_id" value="<?php echo $payment['user_id']; ?>"/>
		<br/>
				
    	<label>Date de paiement</label>
   		<?php echo $payment['payment_date']; ?>
   		<input class="InputText" type="hidden" name="payment_date" value="<?php echo $payment['payment_date']; ?>"/>
		<br/>
		
    	<label>Date de validation</label>
   		<?php echo $payment['validation_date'];?>
   		<input class="InputText" type="hidden" name="validation_date" value="<?php echo $payment['validation_date']; ?>"/>	
		<br/>
		
		<label for="amount">Montant</label>
		<input class="InputText" type="input" name="amount" value="<?php echo $payment['amount']; ?>" />
		<br/>
	
		<label for="type">Type</label>
    	<?php echo form_dropdown('type', $payment_types, $payment['type'], 'type="InputSelect" id="paymentType"'); ?>
    	<br/>
	
		<span id="forCheque">
		<label for="bank">Banque</label>
		<?php echo form_dropdown('bank', $banques, $payment['bank_id'], 'type="InputSelect"'); ?>
		<br/>
		<label for="chequeNum">Numero de cheque</label>
		<input class="InputText" type="input" name="chequeNum" value="<?php echo $payment['cheque_Num']; ?>"/><br/>
		</span>
		
		<span id="forVir">
		<label>Coordonn&eacute;es banquaire des Loupiots:</label><br/>
		RIB: 10278 08938 00041943240 87<br/>
		IBAN: FR76 1027 8089 3800 0419 4324 087<br/>
		BIC: CMCIFR2A<br/>
		</span>
		<br>
    				
    	<label for="paymentMonth">Mois pay&eacute;</label>
   		<?php echo strftime("%B %Y", strtotime($payment['month_paided'])) ?>
		<input class="InputText" type="hidden" name="month_paided" value="<?php echo $payment['month_paided']; ?>"/>
		<br/>
		
		<label for="class">Statut</label>
    	<?php echo form_dropdown('status', $payment_status, $payment['status'], 'class="InputSelect"'); ?>
    	<input class="InputText" type="hidden" name="previousStatus" value="<?php echo $payment['status']; ?>"/>
    	<br/>
	
		<span>
		<?php
			if(validation_errors()) {
				echo validation_errors();
			}
		?>
		</span>

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
