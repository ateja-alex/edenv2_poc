<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">Informations thème de filtres</div>
</div>


<div class="row">
	@champ('theme_de_filtres', 'nom', 4, 8)
</div>
<?php
/*
<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">Filtres</div>
</div>
<div class="row js_ligne_filtre">
	<div class="col-sm-4">Filtre</div>
	<div class="col-sm-8"><input type="text" name="filtre[]" class="js_filtre" placeholder="Valeur" /></div>
</div>
*/
?>
<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">Catégories</div>
</div>
<div class="row">
	<div class="col col-sm-4">Catégories</div>
	<div class="col col_xl-8">
			<select multiple id="js_select_categorie" size="10" style="height: 150px" name="famille[]">
				@foreach(modele('famille')->get() as $famille)
					<option value="{{ $famille->id }}">{{ $famille->nom }}</option>
				@endforeach
			</select>
	</div>
</div>


