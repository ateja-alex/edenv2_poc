<span class="dropdown" data-toggle="tooltip" :title="traduction('document.actions.transformations_possibles.generer')" v-if="Object.keys(transformations_possibles).length > 0">
	<i class="css_action_icon primaire fa fa-fw fa-plus-circle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></i>
	<div class="dropdown-menu">
		<template v-for="(transformations_de_la_categorie, categorie_transformation) in transformations_possibles">

			<h6 class="dropdown-header separation_categorie_transformation_possible" style="padding: 0rem 1.5rem" v-text="traduction('interface.transformations_possibles.' + categorie_transformation)"></h6>

			<template v-for="donnees in transformations_de_la_categorie">

				<span class="dropdown-item css_pointer" v-if="modales_transformations.includes('modale_' + donnees.url)" @click="ouvre_modale_transformation(donnees.url)">
					@{{ donnees.nom }}
				</span>
				<a class="dropdown-item" :href="donnees.url" v-else>
					@{{ donnees.nom }}
				</a>

			</template>

		</template>

		<template v-if="Object.keys(modeles_de_relances).length > 0">

			<h6 class="dropdown-header separation_categorie_transformation_possible" style="padding: 0rem 1.5rem">@traduction('interface.transformations_possibles.modeles_de_relances')</h6>

			<span class="dropdown-item css_pointer" v-for="(traduction_modele, numero_modele) in modeles_de_relances" @click="ouvrir_modele_relance(numero_modele)">
				@{{ traduction_modele }}
			</span>

		</template>
	</div>
</span>

@php
	$modales_transformations_possibles = [];

	$dossiers_modales_transformations_possibles = [
		'../app/Eden/Views/formulaires/include/document/actions/include/transformations_possibles/*.blade.php',
		'../resources/views/vendor/eden/formulaires/include/document/actions/include/transformations_possibles/*.blade.php',
	];

	foreach($dossiers_modales_transformations_possibles as $dossier_modales) {

		foreach(glob($dossier_modales) as $fichier_modale) {

			$nom_modale = str_replace('.blade', '', pathinfo($fichier_modale, PATHINFO_FILENAME));

			$modales_transformations_possibles[$nom_modale] = 'eden::formulaires.include.document.actions.include.transformations_possibles.' . $nom_modale;
		}
	}
@endphp

@push('modales')
	@foreach($modales_transformations_possibles as $vue_modale_transformation)
		@include($vue_modale_transformation)
	@endforeach
@endpush

@push('donnees_pour_vuejs_data')

	transformations_possibles : {!! collect($transformations_possibles) !!},
	modeles_de_relances : {!! collect($modeles_de_relances) !!},
	modales_transformations : {!! collect(array_keys($modales_transformations_possibles)) !!},

	fournisseurs_par_article: [],

	document_transformer_fournisseur_avec_articles_succes : {},
	document_transformer_fournisseur_avec_articles_echecs : {},
	
@endpush

@push('donnees_pour_vuejs_methods')

	ouvre_modale_transformation : async function(url_transformation) {

		var methode_de_chargement = 'charge_donnees_modale_' + url_transformation;

		if(typeof this[methode_de_chargement] === 'function')
			await this[methode_de_chargement]();

		this['modale_' + url_transformation] = true;
	},

	charge_donnees_modale_transformer_fournisseurs_par_article_commande_achat : function() {

		loading(true);

		return $.get({

			url: "{{ route('document.fournisseurs_par_article', [$management->_type_element, $management->modele->id]) }}",
			dataType: "json"

		}).done((donnees) => {

			this.fournisseurs_par_article = donnees.fournisseurs_par_article;
			this.regroupements = donnees.regroupements;

			loading(false);
		});
	},

	charge_donnees_modale_transformer_fournisseurs_par_article_commande_achat_prefiltre : function() {

		return this.charge_donnees_modale_transformer_fournisseurs_par_article_commande_achat();
	},

	commande_frs_depuis_cmd_client_article_selectionne: function(infos_fournisseur) {
		
		if(infos_fournisseur.conditionnement_selectionne === false)
			return true;
		
		if(infos_fournisseur.conditionnement_selectionne === '')
			return true;
		
		if(infos_fournisseur.conditionnement_selectionne == undefined)
			return true;
		
		return false;	
	},

	affiche_conditionnement_selectionne_pour_commande_frs: function(infos_fournisseur) {
		
		if(infos_fournisseur.conditionnement_selectionne == '' || infos_fournisseur.conditionnement_selectionne == 0 || infos_fournisseur.conditionnement_selectionne == false || infos_fournisseur.conditionnement_selectionne == undefined) {
			
			return infos_fournisseur.unite;
		}
		
		var unite = '';
		
		for (const [key, info_fournisseur] of Object.entries(infos_fournisseur.tarifs_par_fournisseurs)) {
			
			info_fournisseur.articles.forEach(function(article_fournisseur) {
				
				if(article_fournisseur.id_article_fournisseur == infos_fournisseur.conditionnement_selectionne)
					unite = article_fournisseur.affichage_unite;
				
			})
			
		}
		
		
		return unite;
	},
	
	document_transformer_fournisseur_avec_articles() {
		
		loading(true)

		$.ajax({
			
			url: "{{ route('document.transformer_fournisseur_avec_articles', [$management->_type_element, $management->modele->id, 'commande_achat']) }}",
			dataType: "json",
			method:"post",
			data:$('#document_transformer_fournisseur_avec_articles').serialize()

		}).done(function(donnees) {
			
			loading(false);

			vue_instance.document_transformer_fournisseur_avec_articles_succes = donnees.documents_crees;
			vue_instance.document_transformer_fournisseur_avec_articles_echecs = donnees.documents_echecs;
		});
	},
@endpush