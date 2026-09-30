<div id="liste_des_articles">

	@if($management->existe() === false || ($management->existe() && empty($management->modele->valide)))
		@include('eden::formulaires.include.document.articles', ['articles_modifiables' => true])
	@else

		@include('eden::formulaires.include.document.articles', ['articles_modifiables' => $management->articles_modifiables()])
	@endif

</div>

<!-- modale de gestion des achats -->
<div class="modal fade" id="gestion_achats" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('document.creation_document_articles.gestion_achats')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				{!! formulaire('achat_sur_document') !!}
			</div>
			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.annuler')</button>
				<button type="button" class="btn btn-danger">@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_achat()">@traduction('document.creation_document_articles.enregistrer_achat')</button>
			</div>
		</div>
	</div>
</div>

@if(fonctionnalite('commentaires_wysiwyg_documents') === true)
<!-- modale commentaire wysiwyg article -->
<div class="modal fade" id="commentaire_article_wysiwyg" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('document.creation_document_articles.commentaire_article')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<textarea-wysiwyg-vue :modele="commentaire" nom_sql="commentaire_modal_wysiwyg"></textarea-wysiwyg-vue>
			</div>
			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.annuler')</button>
				<button type="button" class="btn btn-danger">@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_commentaire_article_wysiwyg()">@traduction('interface.modales.enregistrer')</button>
			</div>
		</div>
	</div>
</div>
@endif

@if(fonctionnalite('notes_internes_wysiwyg_documents') === true)
<!-- modale commentaire wysiwyg article -->
<div class="modal fade" id="note_interne_article_wysiwyg" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('document.creation_document_articles.note_interne')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				<textarea-wysiwyg-vue :modele="commentaire" nom_sql="note_interne_modal_wysiwyg"></textarea-wysiwyg-vue>
			</div>
			<div class="modal-footer">

				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.annuler')</button>
				<button type="button" class="btn btn-danger">@traduction('interface.modales.supprimer')</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_note_interne_article_wysiwyg()">@traduction('interface.modales.enregistrer')</button>
			</div>
		</div>
	</div>
</div>
@endif

<!-- modale ajout commentaire -->
<div class="modal fade" id="commentaire_modele" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('document.creation_document_articles.modele_commentaire')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>

			<div class="modal-body">
				@foreach(collect($modeles_commentaire) as $modele)
					@php
						$modele->type_ligne = "commentaire";
						$modele->commentaire_wysiwyg = $modele->commentaire;
						$modele->contenu = $modele->commentaire;
                        unset($modele->id);
					@endphp
					<div class="badge" @click="ajoute_ligne_au_document({{ collect($modele) }})" style="margin-right: 1%;">
						{!! $modele->nom !!}
					</div>
				@endforeach
				@php
					$modele = modele('modele_commentaire');
					$modele->type_ligne = "commentaire";
					$modele->commentaire_wysiwyg = "";
					$modele->contenu = "";
					$modele->commentaire = "";
				@endphp
				<div class="badge" @click="ajoute_ligne_au_document({{ collect($modele) }})" style="margin-right: 1%;">
					@traduction('document.creation_document_articles.commentaire_vierge')
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('interface.modales.annuler')</button>
			</div>
		</div>
	</div>
</div>

@include('eden::formulaires.include.document.vues_a_surcharger.fonction_recherche_article')
@include('eden::formulaires.include.document.vues_a_surcharger.fonction_ajout_colonne_spe')
@include('eden::formulaires.include.document.condition_commerciale')
@include('eden::formulaires.include.document.categorie_comptable')


@push('donnees_pour_vuejs_data')


	colonnes_articles: {!! collect($colonnes_articles) !!},
	articles_du_document: {!! $articles_du_document !!},
	achat_sur_document: {},
	article_pour_achat: false,
	modification_achat_en_cours: false,
	totaux: {},
	la_marge_est_elle_bonne_critique: true,
	la_marge_est_elle_bonne_alerte: true,
	commentaire : {
		commentaire_modal_wysiwyg : '',
		note_interne_modal_wysiwyg : '',
	},
	article_wysiwyg_index: '',
	modeles_commentaire: {!! collect($modeles_commentaire) !!},
	modele_commentaire: {},
	modele_commentaire_vierge: {},
	ligne_commentaire_tmp: {},
	appel_mise_a_jour_total_document: undefined,
	totaux_affichage : {},
    tableau_de_tva: {},
    conditions_commerciales : [],
@endpush

@push('donnees_pour_vuejs_computed')
    tableau_de_tva_computed: function() {

		if(!this.tableau_de_tva)
			return {};

        return Object.keys(this.tableau_de_tva).sort((a, b) => {
            return parseFloat(a) - parseFloat(b);
        }).reduce(
            (obj, key) => {
                obj[key] = this.tableau_de_tva[key];
                return obj;
            },
            {}
        );
    },
@endpush

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Toutes les méthodes pour les calculs de tarif doivent être placées ci dessous
	 *
	 * Le principe est assez simple :
	 *
	 * On a une méthode globale qui recalcule les totaux (en faisant un appel ajax), et la marge, etc sur toutes les lignes
	 *
	 * Ensuite on a une série de méthode qui permettent de recalculer des champs (par exemple on change la marge appliquée = on recalcule le prix de vente)
	 *
	 * D'une manière générale : on calcule d'abord les modifications à faire sur les champs, puis ensuite systématiquement, on appelle la méthode de recalcul global
	 *
	 */

	/**
	 *
	 * 1) Méthodes individuelles sur les modifications de prix
	 *
	 */

	/**
	 *
	 * On met à jour la quantité
	 *
	 * Eventuellement, on trouve un nouveau prix par article (lié aux tarifs par palier)
	 * Ensuite on appelle la méthode de recalcul global
	 *
	 */
	mise_a_jour_quantite : function(article_sur_document) {

		this.gestion_condition_commerciale(article_sur_document);
		this.arrondi_prix_article_depuis_fonctionnalite(article_sur_document);

		// on met à jour dans le tableau des fournisseurs
		this.maj_infos_bloc_articles_fournisseur(article_sur_document);

		this.mise_a_jour_total_document_vue();
	},

	mise_a_jour_marge_brute_pourcentage : function(article_sur_document) {

		let nouveau_tarif = article_sur_document.prix_achat * (1 + (article_sur_document.marge_brute_pourcentage / 100));

		if(article_sur_document.application_eco_contribution != undefined && article_sur_document.application_eco_contribution != 1)
			nouveau_tarif += article_sur_document.quantite_unite_eco_contribution * article_sur_document.tarif_eco_contribution;
		
		nouveau_tarif = this.arrondi_nombre_depuis_fonctionnalite(nouveau_tarif);

		article_sur_document.tarif = nouveau_tarif;

		// on met à jour dans le tableau des fournisseurs

		this.maj_infos_bloc_articles_fournisseur(article_sur_document);

		this.arrondi_prix_article_depuis_fonctionnalite(article_sur_document);

		this.mise_a_jour_total_document_vue();
	},

	/**
	 *
	 * Calcule le tarif d'une ligne nomenclature (le paramètre passé est différent de la fonction ci dessous)
	 *
	 */
	calcule_tarif_ligne_nomenclature: function(article_du_document,tva = null) {

		if(article_du_document.modele == undefined || article_du_document.modele === null)
			return;

		if(article_du_document.modele.type_article != 1 && article_du_document.modele.type_article != 3)
			return;

		// on est a priori bien dans une nomenclature
		var tarif_total = 0;
		var prix_achat_total = 0;

		if(tva == null)
			tva = article_du_document.tva;

		$.each(article_du_document.nomenclature, (osef, article) => {

			if(article.modele && (article.modele.type_article == 1 || article.modele.type_article == 3))
				this.calcule_tarif_ligne_nomenclature(article,tva);

			this.verifie_attributs_sur_article(article);

			tarif_total += article.quantite * article.tarif;
			prix_achat_total += article.quantite * article.prix_achat;

			article.total = Math.round(article.quantite * article.tarif * 100) / 100;
			var eco_contribution = this.eco_contribution_active ? this.calcul_eco_contribution(article,1) : 0;
			article.total_ttc = Math.round(((article.quantite * article.tarif) + eco_contribution) * (1+tva/100) * 100) / 100;
		});

		article_du_document.tarif = this.arrondi_nombre_depuis_fonctionnalite(tarif_total);
		article_du_document.prix_achat = this.arrondi_nombre_depuis_fonctionnalite(prix_achat_total);

		if(article_du_document.tarif_force != null && article_du_document.tarif_force > 0)
			article_du_document.tarif = this.arrondi_nombre_depuis_fonctionnalite(article_du_document.tarif_force);

        if(article_du_document.prix_achat_force != null && article_du_document.prix_achat_force > 0)
            article_du_document.prix_achat = this.arrondi_nombre_depuis_fonctionnalite(article_du_document.prix_achat_force);

		if(this.utilisation_devise_etrangere())
			this.calcul_montant_devise_par_montant(article_du_document);
	},

	/**
	 *
	 * Calcule le prix de la nomenclature (vente et achat) => l'argument est un article (le modèle, et je ne sais pas d'ou vient le .nomenclature)
	 *
	 */
	calcule_tarif_nomenclature: function(article, article_parent = undefined) {

		if(article.type_article != 1 && article.type_article != 3)
			return;

		var tarif_total = 0;
		var prix_achat_total = 0;

		$.each(article.nomenclature, (osef, composition) => {

			if(composition.tarif == null)
				composition.tarif = 0;
			if(composition.prix_achat == null)
				composition.prix_achat = 0;

			tarif_total += composition.quantite * composition.tarif;
			prix_achat_total += composition.quantite * composition.prix_achat;

			composition.total = Math.round(composition.quantite * composition.tarif * 100) / 100;

			if(article_parent != undefined)
				composition.total_ttc = Math.round(composition.quantite * composition.tarif * (1 + article_parent.tva / 100) * 100) / 100;
		});

		article.tarif = Math.round(tarif_total * 100) / 100;
		article.prix_achat = Math.round(prix_achat_total * 100) / 100;

		if(article.tarif_force !== null && article.tarif_force != undefined)
			article.tarif = article.tarif_force;

	    if(article.prix_achat_force !== null && article.prix_achat_force != undefined)
	        article.prix_achat = article.prix_achat_force;

		this.mise_a_jour_total_document_vue();
	},

	/**
	 *
	 * Appelé quand on modifie le prix d'achat, on recalcule le prix de vente (si nécessaire)
	 *
	 */
	modification_prix_achat: function(article) {

		this.arrondi_prix_article_depuis_fonctionnalite(article);
		this.mise_a_jour_total_document_vue();
	},

	/**
	 *
	 * Appelé quand on modifie le prix d'achat, on recalcule le prix de vente (si nécessaire)
	 *
	 */
	modification_prix_de_vente: function(article) {

		if(article.tarif_force != null && article.tarif_force != false) {

			this.corrige_virgule(article,'tarif_force');
			article.tarif = article.tarif_force;
		}
		else {

			article.tarif_force = null;
		}

		this.corrige_virgule(article,'tarif');

		this.arrondi_prix_article_depuis_fonctionnalite(article);

		article.marge_brute_pourcentage = null;
		
		this.mise_a_jour_total_document_vue();
	},

	/**
	 *
	 * Appelé quand on modifie le prix d'achat, on recalcule le prix de vente (si nécessaire)
	 *
	 */
	modification_prix_net: async function(article) {

		if(article.tarif_force != null && article.tarif_force != false) {

			this.corrige_virgule(article,'tarif_force');
			article.tarif = article.tarif_force;
            this.mise_a_jour_total_document_vue();
            return;
		}

		this.corrige_virgule(article,'tarif_net');

		var remise = article.tarif == 0 ? 0 : (article.tarif - article.tarif_net) / article.tarif * 100;

        if(remise < 0){
            article.remise = 0;
            await alerte_eden("{{ traduction('messages.js.document.erreur_tarif_net_superieur_pu') }}");
        } else
            article.remise = remise.toFixed(2);

		this.arrondi_prix_article_depuis_fonctionnalite(article);

		this.mise_a_jour_total_document_vue();
	},

	/**
	 *
	 * 2) Méthodes de recalcul global des totaux
	 *
	 */

	/**
	 *
	 * Rend les valeurs sur les articles "saines" (on vire les null, undefined, etc)
	 *
	 */
	verifie_attributs_sur_article: function(article) {

		if(article.quantite == undefined || article.quantite === null)
			article.quantite = 0;

		if(article.tva == undefined || article.tva === null)
			article.tva = 0;

		if(article.prix_achat == undefined || article.prix_achat === null)
			article.prix_achat = 0;

		if(article.tarif == undefined || article.tarif === null)
			article.tarif = 0;

		if(article.remise == undefined || article.remise === null)
			article.remise = 0;
	},

	mise_a_jour_total_document_vue: function(origine) {

		// dans un premier temps on va recalculer le tarif de tous les articles de nomenclatures
		this.articles_du_document.forEach((article) => {

			// on est dans une nomenclature, on met le tarif à jour
			this.calcule_tarif_ligne_nomenclature(article);
		});

		if (this.appel_mise_a_jour_total_document !== undefined)
			this.appel_mise_a_jour_total_document.abort();

		acompte_type = 0;
		acompte = 0;

		if($('[name=acompte_1]').val() != 0){
			acompte = $('[name=acompte_1]').val();
			acompte_type = 1;
		} else if($('[name=acompte_2]').val() != 0) {
			acompte = $('[name=acompte_2]').val();
			acompte_type = 2;
		} else {
            acompte = $('[name=acompte_3]').val();
            acompte_type = 3;
        }

		var articles_du_document = this.articles_du_document_informations_necessaires();

		var donnees = {
			type_element: '{{ $management->_type_element}}',
			articles: articles_du_document,
			remise_globale: this.document.remise_globale,
			remise_globale_type: this.document.remise_globale_type,
			ecart_gestion_ttc: this.document.ecart_gestion_ttc,
			client_id: this.document.client_id,
			date: this.document.date,
			type_facture: this.document.type_facture,
			frais_de_port_saisie: this.document.frais_de_port_saisie,
			client_id: this.document.client_id,
			date: this.document.date,
			type_facture: this.document.type_facture,
			frais_de_port_saisie: this.document.frais_de_port_saisie,
		}

		if(this.document.coupon_reduction != undefined)
			donnees['coupon_reduction'] = this.document.coupon_reduction.id;

		this.appel_mise_a_jour_total_document = $.ajax({

			url: "{{ route('document.calculer_total') }}",
			dataType: "json",
			method: "POST",
			contentType: "application/json",
			data: JSON.stringify(donnees),
		}).fail(function(retour) {
			return;
		}).done((retour) => {

			if(retour.frais_de_transport === undefined || retour.frais_de_transport === null || retour.frais_de_transport === '')
				retour.frais_de_transport = 0;

			frais_de_livraison = parseFloat(retour.frais_de_transport.toFixed(2));

            this.tableau_de_tva = retour.par_tva;

			this.$set(this.totaux_affichage,'total_ht',retour.ht.toFixed(2));
			this.$set(this.totaux_affichage,'eco_contribution',retour.eco_contribution);
			this.$set(this.totaux_affichage,'eco_contribution_inclus',retour.eco_contribution_inclus);

			@if(fonctionnalite('frais_de_port_sur_documents_commerciaux') === true)
				this.$set(this.totaux_affichage,'total_frais_livraison',frais_de_livraison);
			@endif

			this.$set(this.totaux_affichage,'total_ttc',retour.ttc != undefined ? retour.ttc.toFixed(2) : 0.00);
			this.$set(this.totaux_affichage,'total_ttc_apres_remise',retour.ttc_apres_remise != undefined ? retour.ttc_apres_remise.toFixed(2) : 0.00);
			this.$set(this.totaux_affichage,'total_ht_apres_remise',retour.ht_apres_remise != undefined ? retour.ht_apres_remise.toFixed(2) : 0.00);
			
			// les totaux par ligne
			if(retour.par_ligne) {

				retour.par_ligne.forEach((total, ligne) => {
					
					this.$set(this.articles_du_document[ligne], 'total', Math.round(total * 100) / 100);
					this.$set(this.articles_du_document[ligne], 'marge_brute_montant', retour.par_ligne_marge_brute_montant[ligne]);
					this.$set(this.articles_du_document[ligne], 'marge_brute_pourcentage', retour.par_ligne_marge_brute_pourcentage[ligne]);
					this.$set(this.articles_du_document[ligne], 'marge', retour.par_ligne_marge_nette_montant[ligne]);
					this.$set(this.articles_du_document[ligne], 'marge_pourcentage', retour.par_ligne_marge_nette_pourcentage[ligne]);
					
					if(retour.par_ligne_tarif_article != undefined)
						this.$set(this.articles_du_document[ligne], 'tarif', this.arrondi_nombre_depuis_fonctionnalite(retour.par_ligne_tarif_article[ligne]));

					@if($management->est_un_achat())
						this.articles_via_fournisseur.articles.forEach((article_tmp) => {

							if(this.articles_du_document[ligne].article_id != article_tmp.article_id || (this.articles_du_document[ligne].conditionnement != article_tmp.conditionnement_id && this.articles_du_document[ligne].conditionnement != null && article_tmp.conditionnement_id != null))
								return;

							article_tmp.total = total;
						});
					@endif
				});
			}

			// les totaux TTC par ligne
			if(retour.par_ligne_ttc) {

				retour.par_ligne_ttc.forEach((total, ligne) => {

                    this.$set(this.articles_du_document[ligne],'total_ttc',Math.round(total * 100) / 100);
				});
			}

			this.totaux = retour;

			//Permet d'update les remises et les tarifs net
			this.articles_du_document.forEach((article,index) => {

				article.tarif_net = this.totaux.par_ligne_tarif_net[index];
			});


			var liste_tmp = new Array();

			this.articles_du_document.forEach((article,index) => {

				this.articles_du_document[index].regroupement_id = this.recupere_dernier_regroupement_id(index);
				liste_tmp.push(this.articles_du_document[index]);

			});

			this.la_marge_est_elle_bonne_methode();

			this.$forceUpdate();

			this.appel_mise_a_jour_total_document = undefined;

			if(this.utilisation_devise_etrangere)
				this.calcul_document_ht_devise_etrangere();

			this.$emit('fin_mise_a_jour_total_document_vue');
		});
	},




	/**
	 *
	 * 3) Autres méthodes de la saisie des documents
	 *
	 */

	commentaire_article_wysiwyg: function(article, article_index) {

		// On affiche la modal
		$('#commentaire_article_wysiwyg').modal('show');

		// On stock l'index de l'article dans vue
		this.article_wysiwyg_index = article_index;

		// On met à jour le commentaire de l'article pour la modal
		this.commentaire.commentaire_modal_wysiwyg = article.commentaire_wysiwyg;


	},

	enregistrer_commentaire_article_wysiwyg: function() {

		this.$set(this.articles_du_document[this.article_wysiwyg_index], 'commentaire_wysiwyg', this.commentaire.commentaire_modal_wysiwyg);

		$('#commentaire_article_wysiwyg').modal('hide');
	},

	note_interne_article_wysiwyg: function(article, article_index) {

		// On affiche la modal
		$('#note_interne_article_wysiwyg').modal('show');

		// On stock l'index de l'article dans vue
		this.article_wysiwyg_index = article_index;

		// On met à jour le note_interne de l'article pour la modal
		this.commentaire.note_interne_modal_wysiwyg = article.note_interne_wysiwyg;


	},

	enregistrer_note_interne_article_wysiwyg: function() {

		this.$set(this.articles_du_document[this.article_wysiwyg_index], 'note_interne_wysiwyg', this.commentaire.note_interne_modal_wysiwyg);

		$('#note_interne_article_wysiwyg').modal('hide');
	},

	nouvel_achat: function(article) {

		this.achat_sur_document = {};

		this.article_pour_achat = article;
		this.modification_achat_en_cours = false;

		$('#gestion_achats').modal('show');
	},

	modifier_achat: function(achat) {

		this.achat_sur_document = achat;
		this.modification_achat_en_cours = true;

		$('#gestion_achats').modal('show');
	},

	supprimer_achat: function(article,achat_index) {

		article.achats.splice(achat_index, 1);
	},

	enregistrer_achat: function() {

		if(this.modification_achat_en_cours === true) {

			$('#gestion_achats').modal('hide');
			return;
		}

		var achat = JSON.parse(JSON.stringify(this.achat_sur_document));

		this.article_pour_achat.achats.push(achat);

		$('#gestion_achats').modal('hide');
	},

	ajoute_ligne_achat: function(article) {

		article.achats.push({});

		setTimeout(maj_champs_achat_typeahead, 250);
	},

	la_marge_est_elle_bonne_methode: function() {

		if(this.totaux.marge_nette_pourcentage == 'n/a') {

			this.la_marge_est_elle_bonne_critique = true;
			this.la_marge_est_elle_bonne_alerte = true;
			return;
		}

		if(parseFloat(this.totaux.marge_nette_pourcentage) <= parseFloat({{ fonctionnalite('marge_mini_sur_documents_commerciaux') }})) {

			this.la_marge_est_elle_bonne_critique = false;
			return;
		}

		this.la_marge_est_elle_bonne_critique = true;

		if(parseFloat(this.totaux.marge_nette_pourcentage) <= parseFloat({{ fonctionnalite('marge_recommandee_sur_documents_commerciaux') }})) {

			this.la_marge_est_elle_bonne_alerte = false;
			return;
		}

		this.la_marge_est_elle_bonne_alerte = true;
	},

	afficher_regroupement : function(regroupement){

		var regroupement_id = regroupement.id;

		if(regroupement.afficher_regroupement == undefined || regroupement.afficher_regroupement == false)
			this.$set(regroupement,'afficher_regroupement',true);
		else
			this.$set(regroupement,'afficher_regroupement',false);

		for(article of this.articles_du_document){

			if(article.regroupement_id != undefined && article.regroupement_id == regroupement_id){

				this.$set(article,'afficher_article',regroupement.afficher_regroupement);

			}

		}

		this.$forceUpdate();

	},

	total_regroupement : function(regroupement, total_avant_remise, index_max, liste_articles){

		if(liste_articles == undefined)
			return;

		var vue_instance = this;

		liste_articles.forEach(function(article){

			if(liste_articles.indexOf(article) < index_max && article.regroupement_id === regroupement.id){

				if(article.type_ligne == undefined && article.article_id !== undefined)
					total_avant_remise += article.total;

				else if(article.type_ligne == "regroupement")
					total_avant_remise = vue_instance.total_regroupement(article, total_avant_remise, index_max, liste_articles);

			}

		});

		return total_avant_remise;

	},

	/**
	*
	* On supprime la couleur d'un regroupement
	*
	*/
	supprimer_couleur_regroupement : function(regroupement){

		regroupement.couleur_regroupement = "";

		this.changement_couleur_regroupement_chaque_article();

	},

	changement_couleur_regroupement_chaque_article : function(){

		var vue_composant = this;

		this.articles_du_document.forEach(function(article, index){

			if(article.type_ligne != 'regroupement')
				article.couleur_regroupement = vue_composant.recupere_couleurs_regroupements(article, vue_composant.articles_du_document);

		})

		vue_composant.$forceUpdate();

	},

	/**
	 *
	 * Met à jour la couleur du regroupement
	 *
	 */
	recupere_couleurs_regroupements : function(ligne, liste_article = this.articles_du_document){

		if(ligne.regroupement_id == false || ligne.regroupement_id == undefined)
			return undefined;

		var regroupement = liste_article.filter(ligne_tmp => ligne_tmp.id == ligne.regroupement_id && ligne_tmp.type_ligne == "regroupement");

		if(regroupement.length > 0)
			return regroupement[0].couleur_regroupement;
		else
			return undefined

	},

	calcul_document_ht_devise_etrangere(){

		if(this.document.devise !== this.devise_euro_id && !isNaN(this.document.taux_de_change) && this.document.taux_de_change > 0 && !isNaN(this.totaux.ht_apres_remise) ){
			this.totaux.ht_apres_remise_devise = this.totaux.ht_apres_remise * this.document.taux_de_change;
		}
		else{
			this.totaux.ht_apres_remise_devise = 0;
		}
	},

	recupere_dernier_regroupement_id : function(index_max = this.articles_du_document.length, liste_article = this.articles_du_document){

		var dernier_regroupement_id = [];

		for(let i = 0; i < index_max; i++){

			var article = liste_article[i];

			if(article.type_ligne != undefined && article.type_ligne == "regroupement")
				dernier_regroupement_id.push(article.id);
			else if(article.type_ligne != undefined && article.type_ligne == "regroupement_fermeture" && dernier_regroupement_id.includes(article.regroupement_id)){
				var position_a_supprimer = dernier_regroupement_id.indexOf(article.regroupement_id)
				dernier_regroupement_id.splice(position_a_supprimer);
			}

		}

		if(dernier_regroupement_id.length > 0)
			return dernier_regroupement_id[dernier_regroupement_id.length - 1];

		return false;
	},

	articles_du_document_informations_necessaires(articles_du_document = false){

		var vue_instance = this;

		var champ_a_supprimer = [
			'designation',
			'style_ligne_document_id',
			'afficher_photo',
			'description',
			'disponibilite',
			'chaine_affichage',
			'modifie_le',
			'cree_le',
			'cree_par',
			'modifie_par',
			'modele',
			'cle_externe',
			'couleur_regroupement',
			'calculateur',
			'chaine_tags_recherche',
			'afficher_nomenclature',
			'conditionnement_possible',
			'choix_code_article',
			'stock',
			'conditions_commerciales',
			'categorie_comptable_article_defaut'
		];

		if(articles_du_document === false)
			articles_du_document = structuredClone(this.articles_du_document);

		var articles_du_document_informations_necessaires = {};

		articles_du_document.forEach(function(article_du_document,index){

			if(article_du_document.type_ligne == undefined
				|| article_du_document.type_ligne == 'remise'
				|| article_du_document.type_ligne == 'regroupement'
				|| article_du_document.type_ligne == 'regroupement_fermeture'
				|| article_du_document.type_ligne == 'sous_total'){

				champ_a_supprimer.forEach(function(champ){
					if(article_du_document[champ] !== undefined)
						delete article_du_document[champ];
				});

				if(article_du_document.nomenclature != undefined && article_du_document.nomenclature.length > 0)
					article_du_document.nomenclature = vue_instance.articles_du_document_informations_necessaires(article_du_document.nomenclature);

				articles_du_document_informations_necessaires[index] = article_du_document;
			}
			else{
				articles_du_document_informations_necessaires[index] = {
					type_ligne : 'ligne_non_calculee',
				};
			}
		});

		return articles_du_document_informations_necessaires;
	},

	gestion_affichage_nouvelle_lignes : function(){

		this.$nextTick(() => {

			$('.css_tableau_liste_articles_document .ajout_recent:first input[type="text"]').focus();

			setTimeout(() => {
				this.articles_du_document.forEach((ligne) => {
					if(ligne.affichage_nouvelle_ligne !== undefined)
						this.$delete(ligne, 'affichage_nouvelle_ligne');
				});
			},1000);

		});
	},

	ajoute_article_au_document_vue : async function(article_id, quantite, mettre_a_jour_le_total_du_document, tarif_manuel = false, callback = false, informations_supplementaires = []) {

		// Seulement si pas en mode remplacement
		var quantite = (typeof quantite !== 'undefined') ? quantite : 1;
		var mettre_a_jour_le_total_du_document = (typeof mettre_a_jour_le_total_du_document !== 'undefined') ? mettre_a_jour_le_total_du_document : true;
		var type_element = this.type_element;
		var index_a_retourner = 0;


		// on va chercher les informations de l'article
		var article = await $.post({
			url: "{{ URL::to('/eden/document/recupere_article') }}/"+article_id +"/"+type_element,
			dataType: "json",
			data: {
				date_document: this.document.date,
				quantite : quantite,
				catalogue_groupement_id: this.document.catalogue_groupement_id,
				@if($management->est_une_vente())
				client_id: this.document.client_id,
				@else
				fournisseur_id: this.document.fournisseur_id,
				@endif
				categorie_comptable_id : this.document.categorie_comptable_id,
				conditionnement_id : informations_supplementaires.conditionnement_id ?? null,
			}
		});

		// On est pas en mode remplacement
		var article_pour_le_document = {};

		@if(fonctionnalite('choix_code_article_sur_saisie_document') || !empty($fonctionnalite_colonnes['code_article']))
			article_pour_le_document.choix_code_article = article.choix_code_article;
		@endif

		article_pour_le_document.modele = article;
		article_pour_le_document.article_id = article_id;
		article_pour_le_document.quantite = quantite;
		article_pour_le_document.designation = article.designation;
		article_pour_le_document.remise = article.remise;
		article_pour_le_document.conditions_commerciales = article.conditions_commerciales;

		this.ajout_colonnes_specifiques_ajout_article(article_pour_le_document,article);

		@if($management->est_une_vente())
			article_pour_le_document.tarif = this.arrondi_nombre_depuis_fonctionnalite(article.tarif);
			article_pour_le_document.tarif_initial = this.arrondi_nombre_depuis_fonctionnalite(article.tarif);
		@else
			article_pour_le_document.tarif = this.arrondi_nombre_depuis_fonctionnalite(article.prix_d_achat);
			article_pour_le_document.tarif_initial = this.arrondi_nombre_depuis_fonctionnalite(article.prix_d_achat);
		@endif

		article_pour_le_document.prix_achat = this.arrondi_nombre_depuis_fonctionnalite(article.prix_d_achat);

		article_pour_le_document.code_article = article.code_article;
		article_pour_le_document.stock = article.stock;
		article_pour_le_document.unite = article.unite;
		article_pour_le_document.conditionnement_possible = article.conditionnement_possible;
		article_pour_le_document.conditionnement = 0;
		article_pour_le_document.tva = article.taux_de_tva;
		article_pour_le_document.entrepot_id = article.entrepot_id;
		article_pour_le_document.marge_appliquee = article.marge_pourcent;

		if(this.eco_contribution_active == true) {

			article_pour_le_document.categorie_eco_contribution_id = article.categorie_eco_contribution_id;
			article_pour_le_document.tarif_eco_contribution = article.tarif_eco_contribution;
			article_pour_le_document.application_eco_contribution = article.application_eco_contribution;
			article_pour_le_document.quantite_unite_eco_contribution = article.quantite_unite_eco_contribution;

		}

		@foreach($colonnes_articles as $nom_colonne => $colonne)
			@if(!empty($colonne['colonne_fiche_article']))
				article_pour_le_document.{{$nom_colonne}} = article.{{$colonne['colonne_fiche_article']}};
			@endif
		@endforeach

		if(article.calculateur)
			article_pour_le_document.calculateur = article.calculateur;

		if(article.modele_de_calculateur_id)
			article_pour_le_document.modele_de_calculateur_id = article.modele_de_calculateur_id;
		else
			article_pour_le_document.modele_de_calculateur_id = 0;

		@if(isset($management->modele) && !empty($management->modele->id))
			article_pour_le_document.document_id = {{ $management->modele->id }};
		@endif
		article_pour_le_document.new = true;


		@if(maquette('taux_tva_bali'))
			article_pour_le_document.tva = 3;
		@endif

		if(article.description_sur_document  == 1 && (typeof article.description_courte === 'string' || article.description_courte instanceof String))
			article_pour_le_document.description = article.description_courte;


		if(article_pour_le_document.tarif == '' || article_pour_le_document.tarif == undefined || article_pour_le_document.tarif === null) {

			article_pour_le_document.tarif = 0;
		}

		if(article_pour_le_document.prix_achat == '' || article_pour_le_document.prix_achat == undefined || article_pour_le_document.prix_achat === null)
			article_pour_le_document.prix_achat = 0;

		@if(!empty($fonctionnalite_colonnes['categorie_comptable_article_id']))

			article_pour_le_document.categorie_comptable_article_id = null;

			article_pour_le_document.categorie_comptable_article_defaut = article.categorie_comptable_article_defaut ?? null;
		@endif

		@if(fonctionnalite('regroupement_articles_documents'))

			article_pour_le_document.regroupement_id = this.recupere_dernier_regroupement_id();
			article_pour_le_document.afficher_article = true;

		@endif

		@if($management->est_une_vente())

			if(article.coefficient_article == '' || article.coefficient_article == undefined || article.coefficient_article === null)
				article_pour_le_document.coefficient_article = 0;
			else
				article_pour_le_document.coefficient_article = article.coefficient_article;

			if(article.coefficient_regroupement == '' || article.coefficient_regroupement == undefined || article.coefficient_regroupement === null)
				article_pour_le_document.coefficient_regroupement = 0;
			else
				article_pour_le_document.coefficient_regroupement = article.coefficient_regroupement;

			if(article.coefficient_devis == '' || article.coefficient_devis == undefined || article.coefficient_devis === null)
				article_pour_le_document.coefficient_devis = 0;
			else
				article_pour_le_document.coefficient_devis = article.coefficient_devis;

		@endif

		// on vérifie si l'article a une nomenclature
		article_pour_le_document.nomenclature = [];

		// Nous permet de gérer si on affiche ou pas les détails des nomenclatures
		// Si c'est activé, par défaut on masque les lignes, sinon on les affiche
		@if(fonctionnalite('gescom_nomenclature_afficher_lignes'))
			article_pour_le_document.afficher_nomenclature = false;
		@else
			article_pour_le_document.afficher_nomenclature = true;
		@endif

		if(article.type_article == 1 || article.type_article == 3) {
			if(article.composition.length > 0) {
				var valeurs = this.gestion_composition(article.composition);

				article_pour_le_document.nomenclature = valeurs[0];
				article_pour_le_document.tarif = valeurs[1];
				article_pour_le_document.prix_achat = valeurs[2];
			}
		}


		@if($management->est_une_vente())
			if(article.tarif_force != null && article.tarif_force != 0) {
				article_pour_le_document.tarif = article.tarif_force;
				article_pour_le_document.tarif_initial = article.tarif_force;
				article_pour_le_document.tarif_force = article.tarif_force;
				article_pour_le_document.affichage_tarif_force = true;
			}

			if(article.prix_achat_force != null && article.prix_achat_force != 0) {
				article_pour_le_document.prix_achat = article.prix_achat_force;
				article_pour_le_document.prix_achat_force = article.prix_achat_force;
			}
		@endif

		if(tarif_manuel !== false) {

			article_pour_le_document.tarif = tarif_manuel;
		}

		// on vérifie si l'article a des lots
		article_pour_le_document.numeros_de_lot = [];

		article_pour_le_document.achats = [];

		// doit on afficher la photo sur le document ? par défaut non
		article_pour_le_document.afficher_photo = 0;

		// On récupère la disponibilité de l'article
		article_pour_le_document.disponibilite = article.disponibilite;

		if(this.entrepot_preselection != undefined && this.entrepot_preselection.id > 0)
			article_pour_le_document.entrepot_id = this.entrepot_preselection.id;

		// on gère les paramètres supplémentaires
		if(callback != false) {

			callback(article_pour_le_document);
		}

		index_a_retourner = this.ajoute_ligne_au_document(article_pour_le_document, false, informations_supplementaires);

		// on gère le contenu du pack
		if(article.contenu_pack.length > 0) {

			for(article_contenu_pack of article.contenu_pack) {

				if(informations_supplementaires.index_article_reference != null)
					informations_supplementaires.index_article_reference++;

				await this.ajoute_article_au_document_vue(article_contenu_pack.article_enfant_id, article_contenu_pack.quantite, false, false, false, structuredClone(informations_supplementaires));
			}
		}

		@if(fonctionnalite('utiliser_reglage_marge_par_nature'))
			if(article_pour_le_document.modele.nature_id && this.document.marge_par_nature[article_pour_le_document.modele.nature_id] != undefined)
				this.appliquer_marge_par_nature([article_pour_le_document]);
		@endif

		this.calculer_total_calculateur();

		if(mettre_a_jour_le_total_du_document === true) {

			this.$nextTick(() => {
				this.mise_a_jour_total_document_vue();
			});
		}

		this.modification_coeff_document();
		this.maj_infos_bloc_articles_fournisseur(article_pour_le_document);

		// au cas ou, car dans certains cas on active le loader avant d'appeler la fonction
		loading(false);

		return index_a_retourner;
	},
@endpush

@push('donnees_pour_vuejs_mounted')

	var vue_instance = this;

	@if(isset($colonnes_articles['designation']) && isset($colonnes_articles['designation']['sous_colonnes']) && isset($colonnes_articles['designation']['sous_colonnes']['tarif_force']) && empty(moi_extranet()))
		await $.each(vue_instance.articles_du_document,function(index,article_sur_document){
			if(article_sur_document.modele && (article_sur_document.modele.type_article == 1 || article_sur_document.modele.type_article == 3) )
				article_sur_document.affichage_tarif_force = article_sur_document.tarif_force == article_sur_document.tarif;
		});
	@endif

	vue_instance.mise_a_jour_total_document_vue();

@endpush