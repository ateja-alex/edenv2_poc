<div class="row">
	<div class="col-sm-12 css_form_ligne_titre">@traduction('formulaire.divers.informations_generales')</div>
</div>
<div class="row">
	@champ('client', 'nom', 2,4)
	@champ('client', 'prenom', 2,4)
</div>
<div class="row">
	@champ('client', 'telephone', 2,4)
	@champ('client', 'adresse_email', 2,4)
</div>
<div class="row">
	@champ('client', 'date_de_naissance', 2,4)
	<div class="col-sm-2">Date d'inscription</div>
	<div class="col-sm-4">@{{ client.date_d_inscription }}</div>
</div>
