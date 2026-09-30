<div class="card mb-3">
	@yield('options_header_articles')
	@stack('options_header_articles')

	@if(fonctionnalite('gescom_preselection_articles_sur_document') === true && $management->existe() === false)

		@if(in_array(fonctionnalite('gescom_mode_preselection_articles_sur_document'), array(null, 'classique')))
			<div class="row" id="preselection_des_articles" style="margin: 15px;">
				<div class="col-md-12">
					<h5>@traduction('document.blocs.saisie_des_articles.choisir')</h5>
				</div>
				<div class="col-md-12">
					<ul class="css_catalogue_articles_choix">
						<gescom-preselection-articles-sur-document v-for="(famille, index) in catalogue" :key="index" :model="famille" style="" class=""></gescom-preselection-articles-sur-document>
					</ul>
					<div class="btn btn-primary pull-right" style="margin-left:15px;" @click="ajoute_articles_au_document()">@traduction('document.blocs.saisie_des_articles.ok')</div>
				</div>
			</div>
		@elseif(fonctionnalite('gescom_mode_preselection_articles_sur_document') == 'affichage_total')

			@if(empty($articles_document))
				@include('eden::formulaires.include.document_preselection_des_articles_affichage_total')
			@endif
		@endif
	@endif

	@include('eden::formulaires.include.document_saisie_des_menus')

	<div class="card-body css_form"  id="articles_selectionnes" @if(fonctionnalite('gescom_preselection_articles_sur_document') === true && empty($articles_du_document)) style="display: none;" @endif>

		@include('eden::formulaires.include.creation_document_articles')

		@if(fonctionnalite('gescom_mode_selection_articles_via_fournisseur') === true && in_array($management->_type_element, array('devis_achat', 'commande_achat')) && (!$management->existe() || ($management->existe() && empty($management->modele->valide)) || ($management->existe() && $management->modele->valide == 1 && $articles_modifiables === true)))

			@include('eden::formulaires.include.document.vues_a_surcharger.tableau_saisie_article_fournisseur')

		@endif

		@if($articles_modifiables === true)
			<div v-show="articles_du_document.length > 0">

				<div class="css_options_sur_document_footer">
					<span class="js_options_sur_document d-flex align-items-center justify-content-between">
                        @yield('options_footer_articles')
                        @stack('options_footer_articles')
						<span>
							<span class="css_grand_total_ht">
								<span>@traduction('document.blocs.saisie_des_articles.grand_total_ht')</span>
								<span>@{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: '{!! maquette('devise_application_iso') !!}' }).format(totaux.ht_apres_remise) }}</span>
							</span>
							<span class="css_grand_total_ht" v-if="utilisation_devise_etrangere()">
								<span>@traduction('document.blocs.saisie_des_articles.grand_total_ht') (@{{ code_devise }})</span>
								<span>@{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: code_devise }).format(totaux.ht_apres_remise_devise) }}</span>
							</span>
						</span>
					</span>
				</div>
			</div>
		@endif
	</div>

	@include('eden::formulaires.include.document_specifique_bloc_article')
</div>

@push('donnees_pour_vuejs_data')

	fournisseur_articles:{},
	articles_via_fournisseur:{articles:[], familles:[]},
	articles_a_ajouter:{},
	input_recherche_articles_via_fournisseur: '',
	affichage_articles_fournisseur_pret: false,
	filtre_famille_article_fournisseur :0,
	filtre_entrepot_article_fournisseur :0,

@endpush

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Charge les familles et articles correspondants au fournisseur sélectionné
	 *
	 */
	fournisseur_articles_select: function() {

		$.ajax({

			url: "{{ URL::to('eden/document/articles_via_fournisseur') }}",
			method: "POST",
			dataType: "json",
			data: {
				date_document: this.document.date_document,
				catalogue_groupement_id: this.document.catalogue_groupement_id,
				fournisseur_id: this.fournisseur_articles.id,
				entrepot_id: this.filtre_entrepot_article_fournisseur,
			}
		}).done((donnees) => {

			this.articles_via_fournisseur = donnees;
			this.affichage_articles_fournisseur_pret = false;

			// on boucle sur les articles du document pour remplir les quantités
			this.articles_du_document.forEach((article) => {

				this.maj_infos_bloc_articles_fournisseur(article);
			});

			this.affichage_articles_fournisseur_pret = true;

		});

	},

	/**
	 *
	 * On ajoute un article depuis la saisie des articles par fournisseur
	 *
	 */
	augmenter_quantite_article_fournisseur: function(article) {

		article.quantite++;

		var article_present_sur_document = false;

		// on regarde si l'article est dans le document ?
		this.articles_du_document.forEach((article_sur_document, index) => {

		    if(article_sur_document.article_id != article.article_id)
				return;

			article_sur_document.quantite = article.quantite;

			vue_instance.mise_a_jour_quantite(article_sur_document);

			article_present_sur_document = true;
		});

		// l'article n'est pas présent sur le document, on l'ajoute automatiquement
		if(article_present_sur_document === false) {

			loading(true);

			this.ajoute_article_au_document_vue(article.article_id, 1, true, false, function(article_ajoute) {

				article_ajoute.designation = article.designation;
				article_ajoute.code_article = article.reference;
			});
		}
	},

	/**
	 *
	 * On met à jour la quantité d'un article depuis la saisie des articles par fournisseur
	 *
	 */
	maj_quantite_article_fournisseur: function(article) {

		var article_present_sur_document = false;

		// on regarde si l'article est dans le document ?
		this.articles_du_document.forEach((article_sur_document, index) => {

		    if(article.article_id != article_sur_document.article_id || (article.conditionnement != article_sur_document.conditionnement && article.conditionnement != null && article_sur_document.conditionnement != null))
				return;

			article_present_sur_document = true;
			
			if(article.quantite > 0) {

				article_sur_document.quantite = article.quantite;
				vue_instance.mise_a_jour_quantite(article_sur_document);
			}
			else {

				vue_instance.articles_du_document.splice(index, 1);
				this.mise_a_jour_total_document_vue();
			}

		});

		// l'article n'est pas présent sur le document, on l'ajoute automatiquement
		if(article_present_sur_document === false) {

			loading(true);

			this.ajoute_article_au_document_vue(article.article_id, article.quantite, true, false, function(article_ajoute) {

				article_ajoute.designation = article.designation;
				article_ajoute.code_article = article.reference;
				article_ajoute.quantite = article.quantite;
				article_ajoute.conditionnement = article.conditionnement_id == null ? '0' : article.conditionnement_id;
			}, {conditionnement_id : article.conditionnement_id});
		}
	},

	maj_infos_bloc_articles_fournisseur: function(article_sur_document, quantite_forcee = null) {

		// on regarde si l'article est dans le document ?
		this.articles_via_fournisseur.articles.forEach((article_tmp) => {

			if(article_sur_document.article_id != article_tmp.article_id || (article_sur_document.conditionnement != article_tmp.conditionnement_id && article_sur_document.conditionnement != null && article_tmp.conditionnement_id != null))
				return;

			if(quantite_forcee !== null){

				article_sur_document.quantite = quantite_forcee === 0 ? 1 : quantite_forcee;
				this.gestion_condition_commerciale(article_sur_document);
				this.arrondi_prix_article_depuis_fonctionnalite(article_sur_document);
			}
			
			article_tmp.quantite = quantite_forcee !== null ? quantite_forcee : article_sur_document.quantite;
			
			article_tmp.prix_achat = article_sur_document.prix_achat;
			article_tmp.tarif = article_sur_document.tarif;
			article_tmp.total = quantite_forcee === 0 ? 0 : article_sur_document.total;
		});

	},

	/**
	 *
	 * On retire un article depuis la saisie des articles par fournisseur
	 *
	 */
	diminuer_quantite_article_fournisseur: function(article) {

		if(article.quantite <= 0)
			return;

		article.quantite--;

		// on regarde si l'article est dans le document ?
		this.articles_du_document.forEach((article_sur_document, article_index) => {

		    if(article_sur_document.article_id != article.article_id)
				return;

			if(article.quantite > 0) {

				article_sur_document.quantite = article.quantite;

				vue_instance.mise_a_jour_quantite(article_sur_document);
			}
			else {

				vue_instance.articles_du_document.splice(article_index, 1);

				this.mise_a_jour_total_document_vue();
			}

		});

	},

	/**
	 *
	 * Ajoute les articles sélectionnés via fournisseur au document
	 *
	 */
	ajouter_articles_via_fournisseurs: function() {

		loading(true);

		// On ne prends que les articles qui ont une quantité > 0
		articles_au_panier = this.articles_via_fournisseur.articles.filter((article, index) => article.quantite > 0);

		// On les ajoute au document
		articles_au_panier.forEach((value, index) => {
		    this.ajoute_article_au_document_vue(value.id, value.quantite, true, false, function(article) {

				article.designation = value.designation;
				article.code_article = value.reference;
			})
		});

		// Et on réinitialise le formulaire d'ajout
		this.articles_via_fournisseur={articles:[], familles:[]};
		this.fournisseur_articles.id = 0;

		loading(false);
	},

	correspond_a_la_recherche(designation){

		var tableau_recherche = vue_instance.input_recherche_articles_via_fournisseur.toLowerCase().split(' ');

		var correspond = true;

		var designation = designation.toLowerCase();

		tableau_recherche.forEach(function(recherche){

		if(!designation.includes(recherche)){

		correspond = false;
		}
		});

		return correspond;
	},

	articles_famille_du_fournisseur_correspondant_a_la_recherche : function(famille){

		articles_famille_du_fournisseur_correspondant_a_la_recherche = [];

		this.articles_via_fournisseur.articles.forEach((article) => {

			if(article.famille_id == famille.id && this.correspond_a_la_recherche(article.chaine_tags_recherche))
				articles_famille_du_fournisseur_correspondant_a_la_recherche.push(article);
		});

		return articles_famille_du_fournisseur_correspondant_a_la_recherche;

	},

@endpush

@push('donnees_pour_vuejs_watch')

	'document.entrepot_id': {
		handler: async function(nouvel_entrepot_id, ancien_entrepot_id) {

			this.filtre_entrepot_article_fournisseur = nouvel_entrepot_id;

			this.fournisseur_articles_select();
		},
		deep: true

	},
@endpush

@push('donnees_pour_vuejs_computed')

	/**
	 *
	 * Renvoi True si des articles via fournisseurs ont une quantité supérieure à 0
	 *
	 */
	afficher_bouton_ajouter_articles_via_fournisseurs: function() {

		articles_au_panier = this.articles_via_fournisseur.articles.filter((article, index) => article.quantite > 0);

		return articles_au_panier.length > 0;

	},

	articles_via_fournisseur_familles: function(){

		if(vue_instance.filtre_famille_article_fournisseur == 0){

			return vue_instance.articles_via_fournisseur.familles;
		}
		else{
			return vue_instance.articles_via_fournisseur.familles.filter(a => a.id == vue_instance.filtre_famille_article_fournisseur);
		}
	},

@endpush