
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

	<?php if ($loggedPrivilege == 3) { 		?>
		<h3>Balance comptable <?php echo callback_month($month)." ".$year ?></h3>
		<h4>Paiment</h4>
			<ul>
				<li>Declare : <?php echo $declared['amount'] ?></li>
				<li>Valide : <?php echo $validated['amount'] ?></li>
			</ul>
		<h4>Reservations</h4>
			<table border=1>
				<tr><td>Standard : </td><td><?php echo $resa['str'] ?> </td><td> <?php echo $resa['total'] ?></td></tr>
				<tr><td>Depassement mois precedent : </td><td><?php echo $depassementPrev['str'] ?> </td><td> <?php echo $depassementPrev['total'] ?></td></tr>
				<tr><td>Total reservation : </td><td> &nbsp;</td><td><?php echo $totalResa ?></td></tr>
				<tr><td>Restant du mois precedent : </td><td>&nbsp;</td><td><?php echo $debt ?></td></tr>
				<tr><td>Reste a paye : </td><td>&nbsp;</td><td><?php echo $rest ?></td></tr>
				<tr><td>Total du : </td><td>&nbsp;</td><td><?php echo $totaldu ?></td></tr>
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

