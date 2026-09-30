@push('donnees_pour_vuejs_data')
	valeur_champ_recherche: '',
	valeur_champ_recherche_modale: '',
    affichage_select: true,
    articles_a_charger: true,
    chargement_select : false,
    pagination : 0,
    requete : false,
	afficher_scroll_articles: true,
	afficher_scroll_articles_autres: {
		remplacement : false,
		ajout_ligne : false,
		modale : false,
	},
@endpush

@push('donnees_pour_vuejs_methods')

	afficher_articles: function(modale = false) {
		var composant = this;

		if(composant.affichage_select === true) {
			this.articles_match = [];
			this.pagination = 0;
			composant.articles_a_charger = true;
			composant.charger_nouveaux_articles(modale);
		}
	},

	charger_nouveaux_articles : function(modale = false){
		var pagination = this.pagination;
		var composant = this;

		var champ_recherche = modale ? this.valeur_champ_recherche_modale : this.valeur_champ_recherche;

		if(champ_recherche === ''){
			return;
		}

		if(composant.affichage_select === false)
			return;

		composant.chargement_select = true;

		if(this.requete != false)
			this.requete.abort();

		this.requete = $.post({
			url: '{{URL::to('eden/document/recherche_article/'.$management->_type_element)}}/'+champ_recherche,
			dataType: "json",
			data: {
				nombre_elements : 11,
				pagination : pagination,
				recherche : champ_recherche,
				parametres_article : {
					date_document: this.document.date,
					catalogue_groupement_id: this.document.catalogue_groupement_id,
					@if($management->est_une_vente())
						client_id: this.document.client_id,
					@else
						fournisseur_id: this.document.fournisseur_id,
					@endif
					type_modele_document: this.document.type_modele_document ?? null,
				}
			},
		}).done(async (donnees) =>{

			this.requete = false;
			if(donnees.length === 0 || donnees.length < 11)
				composant.articles_a_charger = false;

			composant.chargement_select = false;
			this.articles_match = this.articles_match.concat(donnees);
		});
	},

	scroll_article : function(){
		var element = $(event.target);

		if (this.affichage_select && this.articles_a_charger && this.chargement_select === false && element.scrollTop() + element.innerHeight() >= element[0].scrollHeight) {
			this.pagination ++;
			this.charger_nouveaux_articles();
		}
	},

    ajouter_article: function(article) {

		var article_selectionne = article;

		this.ajoute_article_au_document_vue(article.id, 1,true);

		@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === false)
			vue_instance.valeur_champ_recherche = "";
            this.afficher_scroll_articles = false;
		@else
			if(vue_instance.articles_match.length == 1)
				vue_instance.valeur_champ_recherche = "";
		@endif
    },

    ajouter_article_via_modale:async function(article) {

		var article_selectionne = article;

		var row = "";

		var informations_supplementaires = {};

		if(this.affichage_options_ajout_ligne != null)
			informations_supplementaires = {index_article_reference : this.affichage_options_ajout_ligne};
		else
			informations_supplementaires = structuredClone(this.informations_ajout_article_modale);
			
		var index_a_retourner = await this.ajoute_article_au_document_vue(article.id, 1,true,false, false, informations_supplementaires);

		@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === false)
			this.valeur_champ_recherche_modale = "";
			this.afficher_scroll_articles_autres.modale = false;
		@else
			if(vue_instance.articles_match.length == 1)
				this.valeur_champ_recherche_modale = "";
		@endif

		return index_a_retourner;
    },


@endpush
