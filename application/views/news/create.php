<div class="holder_content">
<h3>Créer une actualit&eacute;</h3>

<form class='form' method='post' action='<?php echo site_url()?>/news/create'>
<div>
	<label for="title">Titre</label> 
	<input class="InputText" type="input" name="title" /><br />

	<label for="text">Texte</label>
	<textarea class="InputText" name="text"></textarea><br />
	
	<?php echo validation_errors(); ?>
	
	<input type="submit" class="InputSubmit" name="submit" value="Cr&eacute;er une actualit&eacute;" /> 
</div>
</form>
</div>
