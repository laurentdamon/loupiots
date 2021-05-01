<?php if ($loggedPrivilege >= 2) { ?>
<div class="holder_content">
	<h3>Selectionner la famille</h3>

<form class="form" method="post" action="<?php echo site_url()?>/report/paymentHistory/">
	<div>		
  		<label for="userId">Famille: </label>
		<?php echo form_dropdown('selId', $usersOption, $userId, 'class="InputSelect"'); ?><br/>
   		<input class="InputSubmit" type="submit" value="Selectionner"/>
	</div>
</form>
</div>

<div class="holder_content_separator"></div>
<?php } ?>

<div class="holder_content">
		<h3>Liste des factures</h3>
	
		<br>
			<table border=1>
			<tr>
				<td>&nbsp;</td>
				<td>Restant du<br>mois precedent</td>
				<td>Depassemen<br>mois precedent</td>
				<td>Mois courant du</td>
				<td>Montant total du</td>
				<td>&nbsp;</td>
				<td>Montant paye</td>
				<td>Date de Paiment</td>
				<td>Type</td>
				<td>Banque</td>
				<td>Num. cheque</td>
				<td>Statut</td>
				<td>&nbsp;</td>
				<td>Restant du</td>
			</tr>
		<?php 
		foreach ($dates as $dateKey => $date) { //Date row title
			$rowspan = sizeof($date['payments'])>0 ? sizeof($date['payments']) : 1;
			echo "<tr>";
			if (isset($date['payments']) && sizeof($date['payments'])>0) {
				for ($i=0; $i<sizeof($date['payments']); $i++) {
					echo "<tr>";
					if ($i==0) {
						echo "
						<td rowspan='$rowspan'><a class='button' href='".site_url()."/user/viewUser/".$userId."/".$date["year"]."/".$date["month"]."'>".$date["month"]." - ".$date["year"]."</a></td>
						<td rowspan='$rowspan'>".$date['monthlyStatus']['debtPrev']."</td>
						<td rowspan='$rowspan'>".$date['monthlyStatus']['sum']['depassement']."</td>
						<td rowspan='$rowspan'>".$date['monthlyStatus']['sum']['resa']."</td>
						<td rowspan='$rowspan'><b>".$date['monthlyStatus']['totalDu']."</b></td>
						<td rowspan='$rowspan'>&nbsp;</td>
						";
					}
					$payment=$date['payments'][$i];
					if ($payment['status']==1) {
						$staus = "En attente de r&eacute;ception";
					} else if ($payment['status']==2) {
						$staus = "Recu";
					} else if ($payment['status']==3) {
						$staus = "Valid&eacute;";
					} else if ($payment['status']==4) {
						$staus = "Annul&eacute;";
					} else if ($payment['status']==5) {
					    $staus = "Comptabilis&eacute;";
					} else {
						$staus = "Error";
					}
					echo "
					<td>".$payment['amount']."</td>
					<td>".$payment['payment_date']."</td>
					<td>".$payment["type"]."</td>
						<td>".$payment['bank_id']."</td>
						<td>".$payment["cheque_Num"]."</td>
						<td>".$staus."</td>";
					if ($i==0) {
						echo "
						<td rowspan='$rowspan'>&nbsp;</td>
						<td rowspan='$rowspan'>".$date['monthlyStatus']['debt']."</td>";
					}
				echo "</tr>";
				}
			} else {
				echo "
					<td rowspan='$rowspan'><a class='button' href='".site_url()."/user/viewUser/".$userId."/".$date["year"]."/".$date["month"]."'>".$date["month"]." - ".$date["year"]."</a></td>
					<td rowspan='$rowspan'>".$date['monthlyStatus']['debtPrev']."</td>
					<td rowspan='$rowspan'>".$date['monthlyStatus']['sum']['depassement']."</td>
					<td rowspan='$rowspan'>".$date['monthlyStatus']['sum']['resa']."</td>
					<td rowspan='$rowspan'><b>".$date['monthlyStatus']['totalDu']."</b></td>
					<td rowspan='$rowspan'>&nbsp;</td>
									
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>

					<td rowspan='$rowspan'>&nbsp;</td>
					<td rowspan='$rowspan'>".$date['monthlyStatus']['debt']."</td>";
				echo "</tr>";
			}
		}
		?>
			</table>


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

