
<div class="holder_content">

<section class="container_left">
<h3>Selectionner le mois</h3>
<form class="form" method="post" action="<?php echo site_url()?>/report/balance">
	<div>		
		<label for="paymentMonth">Mois pay&eacute;</label>
   		<?php echo form_dropdown('month', generate_options_array(0,12,'callback_month'), $month, 'class="InputSelect"'); ?>
   		<?php echo form_dropdown('year', generate_options_array(date('Y')+1,2010), $year, 'class="InputSelect"');?>
   		<input class="InputSubmit" type="submit" value="Selectionner"/>
	</div>
</form>
</section>

</div>

<div class="holder_content_separator"></div>

<div class="holder_content">
<?php
//print_r($balance);

//echo "<br>";
//echo $sql;

//echo $month;

?>

	<?php if ($loggedPrivilege == 3) { 		?>
		<h3>Balance comptable <?php echo callback_month($month)." ".$year ?></h3>
		<h4>Paiement</h4>
			<ul>
				<li>Declare : <?php echo round($declared['amount'],2) ?></li>
				<li>Valide : <?php echo round($validated['amount'],2) ?></li>
			</ul>
		<h4>Reservations</h4>
			<table border=1>
				<tr><td>&nbsp;</td><td>Nb reservation</td><td>Chiffre d'affaire</td></tr>
				<tr><td>Standard : </td><td> <?php echo $balance['standard']['numResaStandard'] ?></td><td> <?php echo $balance['standard']['cout'] ?></td></tr>
				<tr><td>Depassement : </td><td> <?php  if (isset($balance["dep"]['numResaDep'])) {echo $balance["dep"]['numResaDep'];} ?></td><td> <?php  if (isset($balance["dep"]['cout'])) {echo $balance["dep"]['cout'];} ?></td></tr>
				<tr><td>Total reservation : </td><td><?php echo $balance['totalResa'] ?></td><td> <?php echo $balance['totalResaCout'] ?></td></tr>
			</table>
			
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

