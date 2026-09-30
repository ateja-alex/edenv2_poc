@extends('eden::formulaires.include.document.base_saisie_des_articles')
@if(in_array(fonctionnalite('position_actions_saisie_des_articles'), ['haut','les_deux']))
	@section('options_header_articles')
		<div class="card-header">
			@if(fonctionnalite('gescom_preselection_articles_sur_document') === true && $management->existe() === false)
				<span class="js_choisissez_vos_articles">@traduction('document.blocs.saisie_des_articles.choisir')</span>
			@endif

			@include('eden::formulaires.include.document.includes.saisie_des_articles_options',['haut_liste' => true])
		</div>
	@endsection
@endif

@if(in_array(fonctionnalite('position_actions_saisie_des_articles'), ['bas','les_deux']))
	@section('options_footer_articles')
		<template v-show="articles_du_document.length > 0">
			@include('eden::formulaires.include.document.includes.saisie_des_articles_options')
		</template>
	@endsection
@endif

@push('donnees_pour_vuejs_methods')

	/**
	*
	* Ajoute une ligne au document
	*
	*/
	ajoute_ligne_au_document : function(ligne, placer_debut_liste = false, informations_supplementaires = []) {

		var nouvelle_liste_articles = new Array;
		var ligne_selectionnee = false;

		if(informations_supplementaires.index_article_reference != null && this.articles_du_document[informations_supplementaires.index_article_reference].regroupement_id > 0) {
			ligne.regroupement_id = this.articles_du_document[informations_supplementaires.index_article_reference].regroupement_id;
			ligne.couleur_regroupement = this.articles_du_document[informations_supplementaires.index_article_reference].couleur_regroupement;
		}

		this.$set(ligne, 'affichage_nouvelle_ligne', true);

		var index = 0;
		var index_nouvelle_liste = 0;

		if(ligne.type_ligne == 'commentaire' && Object.entries(this.modeles_commentaire).length !== 0){

			if (Object.entries(this.ligne_commentaire_tmp).length === 0) {

				this.ligne_commentaire_tmp = ligne;
				$('#commentaire_modele').modal('show');
				return false;
			}

			else{

				$('#commentaire_modele').modal('hide');
				this.ligne_commentaire_tmp = {};
				this.ligne = this.modele_commentaire;
			}
		}

		if(!Array.isArray(informations_supplementaires) && Object.entries(informations_supplementaires).length > 0 && informations_supplementaires.hasOwnProperty('titre')){

			ligne.designation = informations_supplementaires.titre;
			var regEx = new RegExp("(\/)(?!([^<]+)?>)", "gi");
			ligne.description = informations_supplementaires.commentaire.replaceAll(regEx, '<br>');


		}

		this.articles_du_document.forEach((ligne_tmp, index_ligne_tmp) => {

			if (ligne_tmp.type_ligne == 'regroupement_fermeture' && informations_supplementaires['type'] == 'regroupement' && ligne_tmp[informations_supplementaires['nom_valeur']] == informations_supplementaires['valeur']){

				nouvelle_liste_articles.push(ligne);
				index_nouvelle_liste++;
				ligne.regroupement_id = this.recupere_dernier_regroupement_id(index_nouvelle_liste, nouvelle_liste_articles);
				ligne.couleur_regroupement = this.recupere_couleurs_regroupements(ligne, nouvelle_liste_articles);
				ligne_selectionnee = true;
			}

			if(ligne_tmp.type_ligne != 'coefficient') {
				nouvelle_liste_articles.push(ligne_tmp)
				index_nouvelle_liste++;
			}

			if(ligne_selectionnee === false)
				index++;

			if(ligne_tmp.ligne_selectionnee === true && ligne.type_ligne != 'coefficient') {

				nouvelle_liste_articles.push(ligne);
				index_nouvelle_liste++;
				ligne_selectionnee = true;
			}
		});

		var ids = this.articles_du_document.map((article) => { return article.id_provisoire ? article.id_provisoire : article.id });

		var nouvelle_id = ids.length == 0 ? 1 : Math.max.apply(null,ids)+1;

		ligne.id_provisoire = nouvelle_id;

		if(ligne_selectionnee === false) {

			if (ligne.type_ligne != 'coefficient') {

				if(informations_supplementaires.type === 'nomenclature') {
					if(informations_supplementaires.valeur.sous_nomenclature_parent != null)
						nouvelle_liste_articles[informations_supplementaires.valeur.nomenclature_parent].nomenclature[informations_supplementaires.valeur.sous_nomenclature_parent].nomenclature.push(ligne);
					else
						nouvelle_liste_articles[informations_supplementaires.valeur.nomenclature_parent].nomenclature.push(ligne);
				}
				else if (informations_supplementaires.index_article_reference != null)
					nouvelle_liste_articles.splice(parseInt(informations_supplementaires.index_article_reference) + 1, 0, ligne);
				//pas bon faut que les coefficients soient tout en haut
				else if (placer_debut_liste == false)
					nouvelle_liste_articles.push(ligne);
				else if (placer_debut_liste == true)
					nouvelle_liste_articles.unshift(ligne);
			} else {
				nouvelle_liste_articles.unshift(ligne);
			}
		}

		this.articles_du_document.forEach((ligne_tmp, index) => {

			if(ligne_tmp.type_ligne == 'coefficient')
				nouvelle_liste_articles.unshift(ligne_tmp);
		});

		this.$set(this,'articles_du_document',nouvelle_liste_articles);

		this.gestion_affichage_nouvelle_lignes();

		return index;
	},

	/**
	 *
	 * Ajout des lignes divers sur les documents : titres, commentaires, sauts de lignes...
	 *
	 */
	document_ajout_ligne_divers(nom_ligne, haut_liste = false, index_article = null) {

		if(nom_ligne == 'regroupement')
			return this.document_ajout_regroupement(haut_liste, index_article);
		else if(nom_ligne == 'option'){
			this.document_ajout_option(haut_liste, index_article);
			return;
		}
		else if(nom_ligne == 'marge_par_nature')
			return this.document_ajout_marge_par_nature();

		var ligne = {

			type_ligne: nom_ligne,
		};

		var informations_supplementaires = [];

		if(index_article != null)
			informations_supplementaires.index_article_reference = index_article;

		this.ajoute_ligne_au_document(ligne,haut_liste, informations_supplementaires);

		if(nom_ligne == 'sous_total') {

			this.$nextTick(() => {
				this.mise_a_jour_total_document_vue();
			});
		}
	},

	document_ajout_regroupement : function(haut_liste = false, index_article = null) {

		var ligne_type_divers = this.articles_du_document.filter(ligne => ligne.type_ligne != undefined && ligne.type_ligne != "regroupement_fermeture" && ligne.type_ligne != "undefined" && ligne.type_ligne != null);

		var dernier_id = 0;

		if(ligne_type_divers.length != 0){

			ligne_type_divers.forEach(function(ligne, index){

				if(ligne.id != undefined && ligne.id != null && ligne.id != "undefined" && dernier_id < ligne.id)
					dernier_id = ligne.id;

			})

		}

		var ligne = {
			id : dernier_id + 1,
			id_temporaire: true,
			afficher_regroupement: true,
			afficher_article: true,
			type_ligne: 'regroupement',
			couleur_regroupement: "{{fonctionnalite('couleur_regroupement_articles_documents')}}",
			coefficient: [],
		};

		var affichage_options_ajout_ligne = this.affichage_options_ajout_ligne;

		var informations_supplementaires = [];

		if(index_article != null)
			informations_supplementaires.index_article_reference = index_article;

		if(haut_liste || affichage_options_ajout_ligne != null)
			this.fermeture_regroupement(ligne.id,haut_liste, informations_supplementaires);

		this.ajoute_ligne_au_document(ligne,haut_liste, informations_supplementaires);

		if(!haut_liste && affichage_options_ajout_ligne == null)
			this.fermeture_regroupement(ligne.id,haut_liste);

		return ligne;
	},

	document_ajout_option : function(haut_liste = false, index_article = null){

		var ligne = this.document_ajout_regroupement(haut_liste, index_article);

		ligne.contenu = 1;
		ligne.nom = 'Option';

	},

	fermeture_regroupement : function(regroupement_id,haut_liste = false, informations_supplementaires = []) {


		var ligne = {
			type_ligne: 'regroupement_fermeture',
			afficher_article: true,
			regroupement_id: regroupement_id,
			couleur_regroupement: "{{fonctionnalite('couleur_regroupement_articles_documents')}}",
			coefficient: [],
		};

		this.ajoute_ligne_au_document(ligne,haut_liste, informations_supplementaires);

	},

	document_ajout_coefficient : function(haut_liste = false) {

		var ligne = {
			type_ligne: 'coefficient',
			quantite: 0,
			nom: "",
			type_coefficient: "0",
		};

		this.ajoute_ligne_au_document(ligne,haut_liste);
	},


	document_ajout_marge_par_nature : function() {

		this.modale_marge_par_nature = true;
	},

	document_ajout_remise : function(haut_liste = false) {

		var ligne = {

			type_ligne: 'remise',
			remise: '',
			type_remise: 0,
			nom: '',
		};

		this.ajoute_ligne_au_document(ligne,haut_liste);
	},

@endpush