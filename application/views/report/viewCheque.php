
<div class="holder_content">

<section class="container_left">
<h3>Selectionner le mois</h3>
<form class="form" method="post" action="<?php echo site_url()?>/report/cheque">
	<div>		
		<label for="paymentMonth">Mois pay&eacute;</label>
   		<?php echo form_dropdown('month', generate_options_array(0,12,'callback_month'), $month, 'class="InputSelect"'); ?>
   		<?php echo form_dropdown('year', generate_options_array(date('Y')+1,2010), $year, 'class="InputSelect"');?>
   		<input class="InputSubmit" type="submit" value="Selectionner"/>
	</div>
</form>
</section>

<section class="container_rigth">
	<a class="button" href="javascript:printDiv('recapPrint')">Imprimer</a>
</section>

</div>

<div class="holder_content_separator"></div>

<div class="holder_content" id="recapPrint">

	<?php if ($loggedPrivilege >= 2) { 		?>
		<h3>Liste des cheques <?php echo callback_month($month)." ".$year ?></h3>
	
		<br>
			<table border=1>
			<tr>
				<td><b>Nom</b></td>
				<td><b>Banque</b></td>
				<td><b>Numero</b></td>
				<td><b>Montant</b></td>
			</tr>
			<?php 
			$total = 0;
			foreach ($cheques as $cheque) {
				echo "	
					<tr>
						<td>".$cheque['name']."</td>		
   						<td>".$cheque['bank']."</td>		
   						<td>".$cheque['cheque_Num']."</td>		
   						<td><b>".$cheque['amount']."</b></td>		
					</tr>";
				$total += $cheque['amount'];
			}
			echo "<tr>
					<td colspan=3>&nbsp;</td>
					<td><b>$total</b></td>
				</tr>";
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

