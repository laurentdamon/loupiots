	<table border=1>
		<tr>
			<td>&nbsp;</td>
			<td>Mois precedent<br>Restant du
			</td>
			<td>Mois precedent<br>Depassement
			</td>
			<td>Mois courant</td>
			<td>Total</td>
		</tr>
		<?php 
			$totalCost = 0;
			$totalDepassement = 0;
			foreach ($user['children'] as $child) { 
				$costResa=$cost[$child['id']];
				$costDep=$costDepassement[$child['id']];
				$totalCost += $costResa["total"];
				$totalDepassement += $costDep["total"];
		?>
		<tr>
			<td><?php echo $child['name']?></td>
			<td>&nbsp;</td>
			<td>
				<?php 
					echo $costDep["str"]; 
				?>
			</td>
			<td>
				<?php 
					echo $costResa["str"]; 
				?>
			</td>
			<td>
				<?php 
				$total=	$costResa["total"] + $costDep["total"];
				echo ($total); 
				?>
			</td>
		</tr>
		<?php } ?>
		<tr>
			<td>Total</td>
			<td><?php echo $debt; ?></td>
			<td><?php echo $totalDepassement; ?></td>
			<td><?php echo $totalCost; ?></td>
			<td>
				<?php 
					$total=	$totalDepassement + $totalCost;
					echo ($total);
				?>
			</td>
		</tr>

	</table>
