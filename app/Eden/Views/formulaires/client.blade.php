<div class="row">
	@champ('client', 'raison_sociale', 2,4)
</div><div class="row">
	@champ('client', 'nom', 2,4)
	@champ('client', 'prenom', 2,4)
</div>

@if(fonctionnalite('mode_multi_entites') === true)
	<div class="row">
		@champ('client', 'entite_id', 2,4)
	</div>
@endif


@if(fonctionnalite('mode_multi_adresses') === false)
	<div class="row">
		@champ('client', 'adresse', 2,4)
		@champ('client', 'adresse_complement', 2,4)
	</div>
	<div class="row">
		@champ('client', 'code_postal', 2,4)
		@champ('client', 'ville', 2,4)
	</div>
@endif
<div class="row">
	@champ('client', 'adresse_email', 2,4)
	@champ('client', 'telephone', 2,4)
</div>
<div class="row">
	@champ('client', 'groupe', 2,4)
</div>
<div class="row">
	@champ('client', 'modalite_paiement_id', 4, 2)
	@champ('client', 'compte_bancaire_id', 4, 2)
</div>

@include('eden::formulaires.sous_formulaire_dynamique', ['type_element' => 'client'])

@include('eden::formulaires.include.sous_formulaire_dynamique_vuejs', [
			'nom_formulaire' => 'client',
			'nom_sous_formulaire' => 'client_sous_formulaire_contact',
			'champ_libre' => array(),
			'informations_type_element' => array(),
			'type_element' => 'client'
		])

@include('eden::formulaires.include.sous_formulaire_dynamique_vuejs', [
			'nom_formulaire' => 'client',
			'nom_sous_formulaire' => 'client_sous_formulaire_adresse_de_facturation',
			'champ_libre' => array(),
			'informations_type_element' => array(),
			'type_element' => 'client'
		])

@include('eden::formulaires.include.sous_formulaire_dynamique_vuejs', [
			'nom_formulaire' => 'client',
			'nom_sous_formulaire' => 'client_sous_formulaire_adresse_de_livraison',
			'champ_libre' => array(),
			'informations_type_element' => array(),
			'type_element' => 'client'
		])
