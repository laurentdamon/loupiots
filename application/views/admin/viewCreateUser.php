
<div class="holder_content">
	<h3>Gerer une famille</h3>

<form class="form" method="post" action="<?php echo site_url()?>/user/create">
	<div>
		<?php if ($loggedPrivilege == 3) {?>
		<fieldset><legend>Selectionner:</legend>
  		
  		<label for="userId">Famille</label>
		<?php echo form_dropdown('selId', $usersOption, $userId, 'class="InputSelect"'); ?><br/>
		<input class="InputSubmit" type="submit" name="select" value="Selectionner"/>
		</fieldset>
		
		<fieldset><legend>Editer:</legend>
		<?php } else { ?>
		<input class="InputText" type="hidden" name="selId" value="<?php echo $userId; ?>"/><br/>
		<?php } ?>
		<label for="mail">Mail</label> 
		<input class="InputText" type="input" name="mail" value="<?php echo $user['mail']; ?>"/><br/>
	
		<label for="name">Nom</label>
		<input class="InputText" type="input" name="name" value="<?php echo $user['name']; ?>"/><br/>
	    
	    <?php if ($this->session->userdata('privilege')==3) {?>
		<label for="privilege">Privilege</label>
			<?php echo form_dropdown('privilege', $privilegeOptions, $user['privilege'], 'class="InputSelect"'); ?><br/>
		<?php } else { ?>
			<input class="InputText" type="hidden" name="privilege" value=" <?php echo $user['privilege']; ?> "/><br/>
		<?php } ?>
		
		<label for='password'>Mot de passe</label>
		<input class='InputText' type='password' name='password' value='fake' /><br/>

		<label for='confPassword'>Mot de passe confirmation</label>
		<input class='InputText' type='password' name='confPassword' value='fake' /><br/>
	
		<span>
		<?php
			if(validation_errors()) {
				echo validation_errors();
			}
		?>
		</span>
		<a class="button" href="<?php echo site_url()?>/user/<?php echo $userId?>">Voir le calendrier</a><br>
		<input class="InputSubmit" type="submit" name="update" value="Actualiser"/>
		<?php if ($loggedPrivilege == 3) {?>
		<input class="InputSubmit" type="submit" name="create" value="Creer"/>
		<?php } ?>		
		</fieldset>
	</div>

</form>

<div class="holder_content_separator"></div>
</div>
<?php if ($loggedPrivilege ==3) {?>
<div class="holder_content">
	<h3>Liste des familles</h3>
		<table border=0>
		<?php foreach ($users as $user) { //User row title 
			echo "<tr bgcolor='grey'>\n";
			echo "<td colspan='2'>".$user["name"]."</td>\n";
			echo "<td><a class='button' href='".site_url()."/user/".$user["id"]."'>Voir le calendrier</a></td>\n";
			echo "<td><a class='button' href='".site_url()."/child/create/".$user["id"]."'>Ajouter un enfant</a></td>\n";
			echo "</tr>\n";
			if (array_key_exists('children', $user)) {   //Child row
				$children = $user["children"];
				foreach ($children as $child) {  
					echo "<tr>\n";
					echo "<td>&nbsp;</td><td>".$child["name"]."</td>\n";
					echo "<td><a class='button' href='".site_url()."/child/update/".$child["id"]."'>Modifier</a></td>\n";
					echo "</tr>\n";
				}
			}
		} ?>
		</table>

<div class="holder_content_separator"></div>
</div>
<?php } ?>	