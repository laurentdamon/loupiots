
<div class="holder_content">
	<h3>Selectionner le mois</h3>

<form class="form" method="post" action="<?php echo site_url()?>/payment/report">
	<div>		
		<label for="paymentMonth">Mois pay&eacute;</label>
   		<?php echo form_dropdown('month', generate_options_array(0,12,'callback_month'), $month, 'class="InputSelect"'); ?>
   		<?php echo form_dropdown('year', generate_options_array(date('Y')+1,2010), $year, 'class="InputSelect"');?>
		<br><label for="paymentMonth">Familles actives seulement</label>
   		<?php echo form_checkbox('onlyActive', TRUE, $onlyActive);?>
   		<input class="InputSubmit" type="submit" value="Selectionner"/>
	</div>
</form>
</div>

<div class="holder_content_separator"></div>

<div class="holder_content">

	<?php if ($loggedPrivilege >= 2) { 		?>
		<h3>G&eacute;rer les factures </h3>
		<br>
		<?php foreach ($users as $user) { //User row title 
		echo "<h4>\n".$user['user_name'];
			echo "<a class='button' href='".site_url()."/report/paymentHistory/".$user['id']."/$year/$month'>Voir l'historique</a>";
			echo "</h4>\n";
			$userId = $user["id"];
?>
			<table border=1>
			<tr>
				<td>Restant du<br>mois precedent</td>
				<td>D&eacute;passement<br>mois</td>
				<td>Mois courant du</td>
				<td>Montant total du</td>
				<td>&nbsp;</td>
				<td>Montant paye</td>
				<td>Date de paiement</td>
				<td>Date de validation</td>
				<td>Type</td>
				<td>Banque</td>
				<td>Num. cheque</td>
				<td>Statut</td>
				<td>&nbsp;</td>
				<td>Restant du</td>
				<td><a class="button" href="<?php echo site_url()?>/payment/create/<?php echo $userId?>/1">Ajouter paiement</a></td>
			</tr>
			<?php
			$row=0;
			foreach ($payments[$userId] as $curPayment) {
				if ($curPayment['status']==1) {
					$staus = "En attente de r&eacute;ception";
				} else if ($curPayment['status']==2) {
					$staus = "Recu";
				} else if ($curPayment['status']==3) {
					$staus = "Valid&eacute;";
				} else if ($curPayment['status']==4) {
					$staus = "Annul&eacute;";
				} else if ($curPayment['status']==5) {
				    $staus = "Comptabilis&eacute;";
				} else {
					$staus = "-";
				}
				echo "<tr>";
				if ($row==0) {
					echo "<td rowspan=".sizeof($payments[$userId]).">".$costTotal[$userId]['debtPrev']."</td>		
   						<td rowspan=".sizeof($payments[$userId]).">".$costTotal[$userId]['sum']['depassement']."</td>		
   						<td rowspan=".sizeof($payments[$userId]).">".$costTotal[$userId]['sum']['resa']."</td>		
   						<td rowspan=".sizeof($payments[$userId])."><b>".$costTotal[$userId]['sum']['total']."</b></td>		
   						<td rowspan=".sizeof($payments[$userId]).">&nbsp;</td>";
				}
				if ($curPayment['bank_id'] == "-") {
				    $bank = "-";
				} else {
				    $bankId = $curPayment['bank_id'];
				    $bank = $banks[$bankId];
				}
				if (!isset($curPayment['validation_date']) || $curPayment['validation_date'] == "0000-00-00") {
				    $validation_date = "-";
				} else {
				    $validation_date = $curPayment['validation_date'];
                }
				echo "<td>".$curPayment['amount']."</td>
   						<td>".$curPayment['payment_date']."</td>
                        <td>".$validation_date."</td>
						<td>".$curPayment["type"]."</td>
						<td>".$bank."</td>
						<td>".$curPayment["cheque_Num"]."</td>
   						<td>".$staus."</td>";
				echo "<td>&nbsp;</td>";
				if ($row==0) {
					echo "<td rowspan=".sizeof($payments[$userId]).">".$costTotal[$userId]['debt']."</td>";
				}
				if (isset($curPayment["id"])) {
					echo "<td><a class='button' href='".site_url()."/payment/update/".$curPayment["id"]."/1'>Modifier</a></td>\n";
				}
				echo "</tr>";
				$row++;
			}
			?>
			</table>
			
			<?php }?>
<?php } ?>

<div class="holder_content_separator"></div>
</div>

</div>

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

