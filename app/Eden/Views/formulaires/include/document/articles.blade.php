@include('eden::formulaires.include.document.includes.tableau_des_articles', ['recapitulatif' => false])

<!-- nouvel article -->
@if($articles_modifiables === true && empty(moi_extranet()))
    
    <div id="affichage_articles" v-clique_en_dehors="{ func: desaffichage_scroll}" @click="afficher_scroll_articles = true">
        <div class="row css_row_ajouter_article_au_document" style="padding-top: 1%;">
            @if(fonctionnalite('gescom_mode_selection_articles_via_fournisseur') === false || fonctionnalite('gescom_cacher_saisie_article_generale') === false || !in_array($management->_type_element, array('devis_achat', 'commande_achat')))
                <div class="col-md-12" style="position:relative;height: 50px;">
                    <input type="text" :placeholder="traduction('interface.document.articles.ajouter')" id="js_valeur_champ_recherche" v-model="valeur_champ_recherche" @keyup="afficher_articles(false)" style="background: #f9f9f9; color: #272727; padding: 14px 10px; height: 50px;border:2px solid var(--background_menus);position: absolute;top: 0;left: 0;">@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === true)<span class="fas fa-times" @click="valeur_champ_recherche = ''" style="position: absolute;top: 50%;right: 20px;transform: translateY(-50%);cursor: pointer;font-size: 17px;"></span>@endif
                </div>
            @endif
        </div>
        <div class="row" v-if="valeur_champ_recherche != ''" style="margin-bottom: 2%;">
            <div class="col-md-12" >
                @include('eden::formulaires.include.document_affichage_resultat_recherche')
            </div>
        </div>
    </div>

@endif


<template v-if="afficher_modale_ajout_article">
	<transition name="modal" >
		<div class="modal-mask" id="affichage_articles_modale">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">

					<div class="modal-header">
						<h5 class="modal-title">@traduction('document.blocs.articles.ajouter')</h5>
					</div>

					<div class="modal-body" v-clique_en_dehors="{ func: desaffichage_scroll, params: ['modale'] }" @click="afficher_scroll_articles_autres.modale = true">
						<div class="row css_row_ajouter_article_au_document" style="padding-top: 1%;">
							<div class="col-md-12" style="position:relative;height: 50px;">
								<input type="text" :placeholder="traduction('interface.document.articles.ajouter')" id="js_valeur_champ_recherche" v-model="valeur_champ_recherche_modale" @keyup="afficher_articles(true)"  style="background: #f9f9f9; color: #272727; padding: 14px 10px; height: 50px;border:2px solid var(--background_menus);position: absolute;top: 0;left: 0;">@if(fonctionnalite('gescom_garder_valeur_saisie_des_articles') === true)<span class="fas fa-times" @click="valeur_champ_recherche = ''" style="position: absolute;top: 50%;right: 20px;transform: translateY(-50%);cursor: pointer;font-size: 17px;width:100%;"></span>@endif
							</div>
						</div>
						<div class="row" v-if="valeur_champ_recherche_modale != ''" style="margin-bottom: 2%;">
							<div class="col-md-12" >
								@include('eden::formulaires.include.document_affichage_resultat_recherche',['type' =>  'modale'])
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" @click="afficher_modale_ajout_article = false;informations_ajout_article_modale = {};" data-dismiss="modal">@traduction('interface.modales.fermer')</button>
					</div>
				</div>
			</div>
		</div>
	</transition>
</template>

@push('scripts')
<script>

	/**
	*
	*	Permet de naviguer dans la liste des réponses via les flèches directionnelles et d'ajouter l'article via la touche entrée
	*
	 */
	$(document).unbind('keydown').on('keydown','#js_valeur_champ_recherche', function(e){

		if($("#js_valeur_champ_recherche").val() != ''){

			var resultat_recherche = $(".js_resultat_recherche");

			if(e.keyCode == 38){

				if($(".js_selected").length == 0){

					$(resultat_recherche[0]).addClass('js_selected css_selected');
					event.stopImmediatePropagation();
					return false;

				}

				else{

					$('.js_selected').prev('.js_resultat_recherche').addClass('js_selected css_selected');
					$($('.js_selected')[1]).removeClass('js_selected css_selected');
					event.stopImmediatePropagation();
					return false;

				}

				return false

			}

			else if(e.keyCode == 40){

				if($(".js_selected").length == 0){

					$(resultat_recherche[0]).addClass('js_selected css_selected');
					event.stopImmediatePropagation();
					return false;

				}

				else{

					$('.js_selected').next('.js_resultat_recherche').addClass('js_selected css_selected');
					$($('.js_selected')[0]).removeClass('js_selected css_selected');
					event.stopImmediatePropagation();
					return false;

				}

				return false

			}

			else if(e.keyCode == 13 && $(".js_selected").length != 0){

				var position_article_selectionne = resultat_recherche.index($('.js_selected'));
				var article = articles_match.slice(0, 50)[position_article_selectionne]
				@{{ vue_instance.ajouter_article( article ) }};

			}

		}

	});
	/*
	$( ".sortable" ).sortable({
		handle: '.handle',
		items: 'tr, .js_champ',
		update: function() {

			vue_instance.enregistrer();
		},
	});
	$( "#sortable" ).disableSelection();
	*/

	$(document).ready(function() {
		$("body").tooltip({ selector: '[data-toggle=tooltip]' });
	});
</script>
@endpush

@push('donnees_pour_vuejs_data')
	articles_match: [],
	regroupement_en_cours: 0,
	<?php
		if(!empty($management->colonnes_articles()['numero_de_serie'])){
			$numeros_de_serie_deja_utilises = modele($management->_type_element.'_lignes')
				->whereNotNull('numero_de_serie')->where('numero_de_serie','!=','');
			if(!empty($management->modele))
				$numeros_de_serie_deja_utilises->where('document_id','!=',$management->modele->id);

			$numeros_de_serie_deja_utilises=$numeros_de_serie_deja_utilises->groupBy('numero_de_serie')->get()->pluck('numero_de_serie');
		}
		else
			$numeros_de_serie_deja_utilises = collect([]);
	?>
	numeros_de_serie_deja_utilises: {!!  collect($numeros_de_serie_deja_utilises) !!},
	afficher_modale_calculateur: false,
	detail_calculateur: {},
	variables: {},
	noms_unites: {!! $noms_unites !!},
	afficher_modale_supprimer_calculateur: false,
	afficher_modale_ajout_article: false,
	informations_ajout_article_modale: {},
@endpush

@push('donnees_pour_vuejs_methods')

	continuer_enregistrement_document : function(){

		this.modale_annulation_partielle = false;
		this.enregistre_document(false, true);

	},

	mise_a_jour_remise_document_vue(article){

		article.remise = ((parseInt(article.tarif) * parseInt(article.quantite) - parseInt(article.total)) / (parseInt(article.tarif) * parseInt(article.quantite)))  * 100

		this.arrondi_prix_article_depuis_fonctionnalite(article);
		this.mise_a_jour_total_document_vue();
	},

	supprimer_article_du_document(article_sur_document, article_index) {

		// on supprime l'article du document
		this.articles_du_document.splice(article_index, 1);

		if(!article_sur_document.type_ligne)
			this.maj_infos_bloc_articles_fournisseur(article_sur_document, 0);
		else if(article_sur_document.type_ligne == 'coefficient')
			this.modification_coeff_document(article_sur_document);

		// on recalcule les totaux
		this.mise_a_jour_total_document_vue();
	},

	ajouter_coefficient_article(article) {
		var id = 1

		var vue_composant = this;

		var coefficients = article.coefficient;

		if(coefficients != undefined && coefficients.length != 0){

			var dernier_coefficient = coefficients[coefficients.length -1];
			id = dernier_coefficient.id + 1

		}

		if(coefficients != undefined && coefficients.length != 0){

			article.coefficient.push({
				id: id,
				nom: "",
				type: 0,
				quantite: 0,
			});

		}
		else{

			article.coefficient = [{
				id: 1,
				nom: "",
				type: 0,
				quantite: 0,
			}];
		}

		vue_instance.$forceUpdate();
	},

	ajouter_variable_article(article) {

		var vue_composant = this;

		var calculateur = article.calculateur;

		if(calculateur == undefined || calculateur.length == 0){

			article.calculateur = [{
				calcul: {},
				ponderation: [],
				variables: [],
				quantite: {},
				erreur: false,
			}];

		}

		article.calculateur.variables.push({

			nom: "",
			titre: "",
			resultat: 0,
			valeur: "",
			ordre: article.calculateur.variables.length,

		});

		vue_instance.$forceUpdate();
	},

	ajouter_ponderation_article(article) {

		var vue_composant = this;

		var calculateur = article.calculateur;

		if(calculateur == undefined || calculateur.length == 0){

			article.calculateur = [{
				calcul: {},
				ponderation: [],
				variables: [],
				quantite: {},
				erreur: false,
			}];

		}

		article.calculateur.ponderation.push({

			designation: "",
			conditions: [],
			taille_tableau: [],

		});

		vue_instance.$forceUpdate();
	},

	ajouter_condition_article(ponderation) {

		ponderation.conditions.push({

			type: 0,
			donnees: [],

		});

		vue_instance.taille_tableau(ponderation);

		vue_instance.$forceUpdate();
	},

	supprimer_condition_article(ponderation, condition_index) {

		ponderation.conditions.splice(condition_index,1);

		vue_instance.taille_tableau(ponderation)

	},

	taille_tableau(ponderation){

		var max = 0;

		ponderation.conditions.forEach(function(conditions, index){

			if(conditions.donnees.length > max){

				max = conditions.donnees.length;

			}

		});

		ponderation.taille_tableau = [];

		if(max > 0){
			for(let i = 0 ; i < max ; i++){

				ponderation.taille_tableau[i] = i;

			}
		}

	},

	check_condition(ponderation, condition){

		if(condition.type == 1 && condition.donnees.length == 0){

			condition.donnees.push({

				critere: "",
				comp: 0,
				valeur: "",

			});

		}

		else if(condition.type == 0 && condition.donnees.length > 0){

			condition.donnees = [];

		}

		vue_instance.taille_tableau(ponderation)
		vue_instance.$forceUpdate();

	},

	ajouter_condition_conditions(ponderation, condition) {

		condition.donnees.push({

			critere: "",
			comp: 0,
			valeur: "",

		});

		vue_instance.taille_tableau(ponderation);
		vue_instance.$forceUpdate();

	},

	masquer_coefficient_sur_document(article, coefficient_id){

		article.coefficient.forEach(function(coefficient, index){

			if(coefficient.id == coefficient_id){

				article.coefficient.splice(index,1);

			}

		});

	},

	ajouter_disponibilite_article(article) {

		article.disponibilite = "";
	},
	masquer_disponibilite_sur_document(article) {

		article.disponibilite = null;

		vue_instance.$forceUpdate();
	},

	/*
	*
	* Ajoute un lot à un document
	*
	*/
	ajout_lot_document: function(article) {

		article.numeros_de_lot.push({

			numero_de_lot: '',
			date_de_peremption: '',
			quantite: 1,
		});

	},

	/**
	 *
	 * Change le total d'un article en fonction de son conditionnement
	 *
	 */
	changement_total: function(article, article_mere = false){

		$.each(article.conditionnement_possible, (index,conditionnement) => {

			if(index == article.conditionnement){

				if(index == 0){
					@if($management->est_un_achat())
						article.tarif = this.arrondi_nombre_depuis_fonctionnalite(article.modele.prix_d_achat);
					@else
						article.tarif = this.arrondi_nombre_depuis_fonctionnalite(article.modele.tarif);
					@endif
					article.prix_achat = this.arrondi_nombre_depuis_fonctionnalite(article.modele.prix_d_achat);
				}

				else{
					@if($management->est_un_achat())
						article.tarif = this.arrondi_nombre_depuis_fonctionnalite(conditionnement.prix_achat);
					@else
						article.tarif = this.arrondi_nombre_depuis_fonctionnalite(conditionnement.tarif);
					@endif
					article.prix_achat = this.arrondi_nombre_depuis_fonctionnalite(conditionnement.prix_achat);
				}
			}
		})

		if(article_mere !== false)
			this.calcule_tarif_nomenclature(article_mere);
		else
			this.gestion_condition_commerciale(article);

		this.mise_a_jour_total_document_vue();
	},

	suppression_regroupement : async function(article, article_index, verification = true){

		if(verification && !await confirm_eden("Êtes-vous sûr de vouloir supprimer ce regroupement ? Cela entraînera la suppression de tout son contenu."))
			return;

		var tableau_retour = this.suppression_regroupement_boucle(article, article_index,structuredClone(this.articles_du_document));

		var index_regroupement_supprimer = null;

		tableau_retour.forEach(function(ligne, ligne_index){
			if(JSON.stringify(article) == JSON.stringify(ligne))
				index_regroupement_supprimer = ligne_index;
		});

		if(index_regroupement_supprimer != null)
			tableau_retour.splice(index_regroupement_supprimer,1);

		this.articles_du_document = tableau_retour;

	    this.mise_a_jour_total_document_vue();
	},

	/*
	*
	* Supprime toutes les infos d'un regroupement à sa suppression
	*
	*/
	suppression_regroupement_boucle: function(article, article_index, tableau_entree) {

		var regroupement_id_a_supprimer = article.id;

		var index_supprimer = 0;

		var vue_composant = this;

		var tableau_retour = tableau_entree;

		vue_composant.articles_du_document.forEach(function(article_tmp, article_index_tmp){

			if(article_tmp.regroupement_id !== false && article_tmp.regroupement_id == regroupement_id_a_supprimer){

				if(article_tmp.type_ligne == "regroupement")
					tableau_retour = vue_instance.suppression_regroupement_boucle(article_tmp, article_index_tmp, tableau_retour);

				tableau_retour.forEach(function(ligne, ligne_index){
					if(JSON.stringify(article_tmp) == JSON.stringify(ligne))
						index_supprimer = ligne_index;
				});

				if(index_supprimer != 0)
					tableau_retour.splice(index_supprimer,1);

			}

		})

		return tableau_retour;

	},

	/**
	*
	* Fais le total de tous les articles d'un regroupement
	*
	*/
	total_regroupement_affiche : function(article){

		var vue_composant = this;

		var total = 0;
		var total_sans_coeff = 0;

		if(article.id == null || article.id == undefined || article.id == "undefined"){
			return total;
		}

		this.articles_du_document.forEach(function(article_regroupement, index){

			if(article_regroupement.regroupement_id == article.id && article_regroupement.type_ligne == undefined){

				if(article_regroupement.coefficient != undefined && article_regroupement.coefficient != null && article_regroupement.coefficient.length > 0){

					var total_coeff = 0;

					article_regroupement.coefficient.forEach(function(coefficient,index){

						total_coeff += parseFloat(coefficient.quantite);

					})

					total_sans_coeff += parseFloat(article_regroupement.tarif) * parseInt(article_regroupement.quantite) * ((100 + parseFloat(total_coeff)) /100);

				}
				else{
					total_sans_coeff += parseFloat(article_regroupement.tarif) * parseInt(article_regroupement.quantite);
				}

				total += parseFloat(article_regroupement.total);

			}

			if(article_regroupement.regroupement_id == article.id && article_regroupement.type_ligne == "regroupement"){

				total += parseFloat(vue_composant.total_regroupement_affiche(article_regroupement));

			}

		});

		article.total = total;
		article.total_sans_coeff = total_sans_coeff;

		return total.toFixed(2);

	},

	/**
	*
	* Retourne le total ht d'un article après application des coefficients
	*
	*/
	total_article_ht_apres_coeff : function(article){

		/*var vue_composant = this;

		var total_ht = article.total;

		//var total_coefficients_pourcentage = 1;
		var total_coefficients_pourcentage = 0;
		var total_coefficients_fixe = 0;

		article.coefficient.forEach(function(coefficient,index){

			if(coefficient.type == 0){

				total_coefficients_pourcentage += parseFloat(coefficient.quantite)
				//total_coefficients_pourcentage = total_coefficients_pourcentage * parseFloat(coefficient.quantite)/100;

			}
			else{

				total_coefficients_fixe += parseFloat(coefficient.quantite);

			}

		});

		//if(total_coefficients_pourcentage != 1 && total_coefficients_pourcentage != 0)
			//var total = total_ht * parseFloat( total_coefficients_pourcentage ) + parseFloat(total_coefficients_fixe);
		if(total_coefficients_pourcentage > 0)
			var total = total_ht * parseFloat( total_coefficients_pourcentage ) / 100 + parseFloat(total_coefficients_fixe);
		else
			var total = parseFloat(total_ht + total_coefficients_fixe);

		return total;*/

		article.total = parseFloat(article.tarif) * parseInt(article.quantite) * (100 + parseFloat(article.coefficient_article)) / 100 * (100 + parseFloat(article.coefficient_regroupement)) / 100 * (100 + parseFloat(article.coefficient_devis)) / 100;

	},



	/**
	*
	* Retourne le total ajouté par le coefficient sur le devis
	*
	*/
	total_coefficient_devis : function(coefficient){

		var vue_composant = this;

		var total_devis = 0;

		if(coefficient.type_coefficient == 0){

			vue_composant.articles_du_document.forEach(function(article, index){

				if(article.type_ligne == undefined || article.type_ligne == "undefined"){

					total_devis += parseFloat(article.tarif) * parseInt(article.quantite) * (100 + parseFloat(article.remise)) / 100 * (100 + parseFloat(article.coefficient_article)) / 100 * (100 + parseFloat(article.coefficient_regroupement)) / 100;

				}

			});

			var total_apres_coeff = (total_devis * parseFloat(coefficient.quantite) / 100).toFixed(2);

		};

		return total_apres_coeff;

	},

	/**
	*
	* Met à jour le coefficient du regroupement pour tous les articles du regroupement
	*
	*/
	modification_coeff_regroupement : function(regroupement, coefficient_regroupement = 0, premier_appel = true){

		var vue_composant = this;

		if(regroupement.coefficient == null || regroupement.coefficient == undefined || regroupement.coefficient == false)
			regroupement.coefficient = [];

		if(premier_appel == true && regroupement.regroupement_id != undefined && regroupement.regroupement_id != false){

			var regroupements = vue_composant.articles_du_document.filter(ligne => ligne.id == regroupement.regroupement_id && ligne.type_ligne == "regroupement");

			if(regroupements.length > 0){
				vue_composant.modification_coeff_regroupement(regroupements[0]);
				return;
			}

		}

		regroupement.coefficient.forEach(function(coefficient,index){

			if(coefficient.quantite && coefficient.quantite != "" && coefficient.quantite != undefined && coefficient.quantite != null)
				coefficient_regroupement += parseFloat(coefficient.quantite);

		});

		vue_composant.articles_du_document.forEach(function(article,index){

			if(article.regroupement_id == undefined || article.regroupement_id == null || article.regroupement_id.length == 0)
				article.coefficient_regroupement = 0
			else if(article.regroupement_id == regroupement.id && article.type_ligne != "regroupement"){

				article.coefficient_regroupement = coefficient_regroupement

			}
			else if(article.regroupement_id == regroupement.id && article.type_ligne == "regroupement"){

				vue_composant.modification_coeff_regroupement(article, coefficient_regroupement, false);

			}

		});

		vue_composant.mise_a_jour_total_document_vue();

	},

	/**
	*
	* Retourne le total ajouté des coefficient d'un regroupement
	*
	*/
	total_ajoute_coeff : function(coefficient, regroupement, premier_appel = true, total_ajoute = 0){

		var vue_composant = this;

		vue_composant.articles_du_document.forEach(function(article,index){

			if(article.regroupement_id == regroupement.id && article.type_ligne != "regroupement"){

				if(article.coefficient != undefined && article.coefficient != null && article.coefficient.length > 0){

					var total_coeff = 0;

					article.coefficient.forEach(function(coefficient,index){

						total_coeff += parseFloat(coefficient.quantite);

					})

					total_ajoute += parseFloat(article.tarif) * parseInt(article.quantite) * ((100 + parseFloat(total_coeff)) /100);

				}
				else
					total_ajoute += parseFloat((parseFloat(parseFloat(article.tarif) * parseInt(article.quantite)) * (coefficient.quantite/100)).toFixed(2))

			}
			else if(article.regroupement_id == regroupement.id && article.type_ligne == "regroupement"){

				total_ajoute = parseFloat(vue_composant.total_ajoute_coeff(coefficient, article, false , parseFloat(total_ajoute)));

			}

		});

		return total_ajoute;

	},

	/**
	*
	* Met à jour le coefficient d'un article
	*
	*/
	modification_coeff_article : function(article){

		var vue_composant = this;

		article.coefficient_article = 0

		if(article.coefficient){

			article.coefficient.forEach(function(coefficient,index){

				article.coefficient_article += parseFloat(coefficient.quantite);

			});

		}

		vue_composant.mise_a_jour_total_document_vue();

	},

	/**
	*
	* Met à jour le coefficient de tous les articles du document
	*
	*/
	modification_coeff_document : function(coefficient = null){

		var vue_composant = this;

		var coefficient_document = vue_composant.articles_du_document.filter(ligne => ligne.type_ligne == 'coefficient');

		var total_coefficient_document = 0

		vue_composant.articles_du_document.forEach(function(article, index){

			article.coefficient_devis = 0;

			coefficient_document.forEach(function(coefficient_unique, index){

				if(coefficient_unique.type_coefficient == 0){

						article.coefficient_devis += parseFloat(coefficient_unique.quantite);

				}

			});

		});

		vue_composant.mise_a_jour_total_document_vue();

	},

	/**
	*
	* Retourne le nom du regroupement
	*
	*/
	nom_regroupement : function(regroupement_id){

		var vue_composant = this;

		var regroupement_nom = "";

		var regroupement = vue_composant.articles_du_document.filter(article => article.type_ligne == "regroupement" && article.id == regroupement_id)[0];

		if(regroupement != undefined && regroupement.nom != undefined)
			regroupement_nom = regroupement.nom;

		return regroupement_nom;

	},

	/**
	*
	* Retourne le booléen d'affichage du regroupement
	*
	*/
	nomenclature_affiche : function(regroupement_id){

		var vue_composant = this;

		var regroupement_affiche = true;

		var regroupement = vue_composant.articles_du_document.filter(article => article.type_ligne == "regroupement" && article.id == regroupement_id)[0];

		if(regroupement == undefined)
			return regroupement_affiche;
		else if(regroupement.regroupement_id == undefined || regroupement.regroupement_id == false)
			regroupement_affiche = regroupement.afficher_regroupement;
		else{
			regroupement_affiche = this.nomenclature_affiche(regroupement.regroupement_id);
			if(regroupement_affiche == true && regroupement.afficher_regroupement != true)
				regroupement_affiche = false;
		}

		return regroupement_affiche;

	},

	/**
	*
	* Affiche le calculateur
	*
	*/
	afficher_calculateur: function(ligne, article_nomenclature_index = "false", article_sous_nomenclature = "false"){

		var vue_composant = this;

		vue_composant.afficher_modale_calculateur = true;

		ligne.article_nomenclature_index = undefined;
		ligne.article_sous_nomenclature = undefined;

		if(article_nomenclature_index >= 0){

			if(ligne.calculateur != undefined && Object.keys(ligne.calculateur).length >= 4){
				if(ligne.nomenclature[article_nomenclature_index].calculateur == undefined || Object.keys(ligne.nomenclature[article_nomenclature_index].calculateur).length < 4){

					ligne.nomenclature[article_nomenclature_index].calculateur = {

						quantite: {
							designation : '',
							unite : '0',
						},
						variables: [],
						ponderation: [],
						calcul: {
							formule: '',
							total: 0,
						},
						erreur: false,

					}

				}

				ligne.article_nomenclature_index = article_nomenclature_index;

			}

			else{
				ligne.article_nomenclature_index = article_nomenclature_index;

				ligne.calculateur = {

					quantite: {
						designation : '',
						unite : '0',
					},
					variables: [],
					ponderation: [],
					calcul: {
						formule: '',
						total: 0,
					},
					erreur: false,

				}

				if(ligne.nomenclature[article_nomenclature_index].calculateur == undefined || Object.keys(ligne.nomenclature[article_nomenclature_index].calculateur).length < 4){

					ligne.nomenclature[article_nomenclature_index].calculateur = {

						quantite: {
							designation : '',
							unite : '0',
						},
						variables: [],
						ponderation: [],
						calcul: {
							formule: '',
							total: 0,
						},
						erreur: false,

					}

				}

			}
		}

		else{

			if(ligne.calculateur != undefined && Object.keys(ligne.calculateur).length >= 4){

				ligne.article_nomenclature_index = article_nomenclature_index;

			}

			else{

				ligne.calculateur = {
					quantite: {
						designation : '',
						unite : '0',
					},
					variables: [],
					ponderation: [],
					calcul: {
						formule: '',
						total: 0,
					},
					erreur: false,

				};
				ligne.article_nomenclature_index = article_nomenclature_index;

			}
		}



		if(article_sous_nomenclature >= 0){

			if(ligne.nomenclature[article_nomenclature_index].nomenclature[article_sous_nomenclature].calculateur == undefined || Object.keys(ligne.nomenclature[article_nomenclature_index].nomenclature[article_sous_nomenclature].calculateur).length < 4){

				ligne.nomenclature[article_nomenclature_index].nomenclature[article_sous_nomenclature].calculateur = {

					quantite: {
						designation : '',
						unite : '0',
					},
					variables: [],
					ponderation: [],
					calcul: {
						formule: '',
						total: 0,
					},
					erreur: false,

				}

			}

			ligne.article_sous_nomenclature = article_sous_nomenclature;

		}

		vue_composant.detail_calculateur = ligne;

	},

	/**
	*
	* Calcul une chaine de caractère donnée
	*
	*/
	calculer: async function(calcul, article, variable_source = false, alert_ou_non = false, premier_alerte = true){

		var calcul_tmp = calcul;
		var erreur = false;
		var index_debut = calcul_tmp.indexOf('[');
		var index_fin = calcul_tmp.indexOf(']');
		if(calcul_tmp == "[]"){

			variable_source.erreur = true;
			article.calculateur.erreur = true;

			erreur = true;

		}
		if(variable_source != false && variable_source.boucle == undefined){

			variable_source.boucle = 0;

		}

		var regex = new RegExp('[a-zA-Z]','g');

		if(variable_source.nom == '' || variable_source.designation == ''){

			variable_source.erreur = true;
			article.calculateur.erreur = true;

			erreur = true;

		}

		if(index_fin == -1 && regex.test(calcul_tmp) == true){

			if(variable_source.nom != undefined && variable_source.resultat != false){

				if(alert_ou_non != "no_alerte" && premier_alerte == true){
					await alerte_eden(vue_instance.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.nom]),'{{ traduction('interface.alerte.attention') }}');
				}
				vue_instance.variables[variable_source.nom] = vue_instance.traduction('messages.js.documents.erreur');

			}
			else if(variable_source.designation != undefined && variable_source.resultat != false){

				if(alert_ou_non != "no_alerte" && premier_alerte == true){
					await alerte_eden(vue_instance.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.designation]) + ' ' + traduction('messages.js.documents.critere_condition_errone'),'{{ traduction('interface.alerte.attention') }}');
				}
				vue_instance.variables[variable_source.designation] = vue_instance.traduction('messages.js.documents.erreur');

			}

			variable_source.resultat = vue_instance.traduction('messages.js.documents.erreur');
			variable_source.erreur = true;
			article.calculateur.erreur = true;

			erreur = true;

		}

		if(variable_source != false && variable_source.boucle > 2){

			if(alert_ou_non != "no_alerte" && premier_alerte == true){
				await alerte_eden(vue_instance.traduction('messages.js.documents.variables_boucle_infinie', null, [variable_source.nom]), '{{ traduction('interface.alerte.attention') }}');
			}
			erreur = true;
			variable_source.erreur = true;
			calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
		}

		if(index_fin != -1 && erreur == false){

			var index_debut = 0;
			var index_fin = 0;
			var variable = "";
			var erreur = false;

			while(index_fin != -1){

				index_debut = calcul_tmp.indexOf('[');
				index_fin = calcul_tmp.indexOf(']');
				variable = calcul_tmp.substring(index_debut, index_fin).replace('[','').replace(']','');

				if(variable == variable_source.nom || variable == variable_source.designation){
					if(alert_ou_non != "no_alerte" && premier_alerte == true){
						await alerte_eden(vue_instance.traduction('messages.js.documents.variable_boucle_infinie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
					}
					erreur = true;
					variable_source.erreur = true;
					calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');

				}
				if(erreur == true){

					return{
						erreur : true
					};

				}

				else if(variable != "" && variable.length > 0){
					if(typeof vue_instance.variables[variable] === 'number'){
						calcul_tmp = calcul_tmp.replace("[" + variable + "]",vue_instance.variables[variable]);
					}
					else{
						var variable_a_definir = article.calculateur.variables.filter(variable_tmp => variable_tmp.nom == variable);
						if(variable_a_definir.length == 0){

							for(article_document of vue_instance.articles_du_document){

								if(article_document.calculateur != undefined && article_document.calculateur != null && article_document.calculateur.length > 4){

									if(article_document.type_ligne == 'calculateur' && article.type_ligne != 'calculateur'){

										variable_a_definir = article_document.calculateur.variables.filter(variable_tmp => variable_tmp.nom == variable);

									}

									else if(article_document.type_ligne == 'regroupement' && article.type_ligne != 'calculateur' && article.type_ligne != 'regroupement' && article.regroupement_id == article_document.id){

										variable_a_definir = article_document.calculateur.variables.filter(variable_tmp => variable_tmp.nom == variable);

									}

									if(variable_a_definir > 0)
										continue;

								}

							}

						}
						if(variable_a_definir.length > 1){
							if(alert_ou_non != "no_alerte" && premier_alerte == true){
								await alerte_eden(vue_instance.traduction('messages.js.documents.variable_deja_definie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
							}
							erreur = true;
							variable_source.erreur = true;
							calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
						}
						else if(variable_a_definir.length == 1){
							variable_source.boucle += 1;
                            if(premier_alerte == true)
                                premier_alerte = false;

							var calcul_test = await vue_instance.calculer(variable_a_definir[0].valeur,article,variable_a_definir[0], false, premier_alerte);
							if(calcul_test.erreur == false){

								calcul_tmp = calcul_tmp.replace("[" + variable + "]", calcul_test.resultat);

							}
							else{

								if(alert_ou_non != "no_alerte" && premier_alerte == true){
									await alerte_eden(vue_instance.traduction('messages.js.documents.erreur_calcul', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
								}
								erreur = true;
								variable_source.erreur = true;

							}

						}
						else{
							if(alert_ou_non != "no_alerte" && premier_alerte == true){
								await alerte_eden(vue_instance.traduction('messages.js.documents.variable_non_definie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
							}
							erreur = true;
							variable_source.erreur = true;
							calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
						}

					}
				}

				if(index_fin == -1 && regex.test(calcul_tmp) == true && variable_source != false){

					if(variable_source.nom && vue_instance.variables[variable_source.nom] == undefined){

						if(alert_ou_non != "no_alerte" && premier_alerte == true){
							await alerte_eden(vue_instance.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.nom]),'{{ traduction('interface.alerte.attention') }}');
						}
						vue_instance.variables[variable_source.nom] = vue_instance.traduction('messages.js.documents.erreur');

					}
					else if(variable_source.designation && vue_instance.variables[variable_source.designation] == undefined){

						if(alert_ou_non != "no_alerte" && premier_alerte == true){
							await alerte_eden(vue_instance.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.designation]), '{{ traduction('interface.alerte.attention') }}');
						}
						vue_instance.variables[variable_source.designation] = vue_instance.traduction('messages.js.documents.erreur');

					}

					variable_source.resultat = vue_instance.traduction('messages.js.documents.erreur');
					variable_source.erreur = true;
					article.calculateur.erreur = true;

					erreur = true;

				}


			}

		}

		if(erreur != true){
			calcul_tmp = calcul_tmp.replace(/[^-()\d/*+.<>=]/g, '');
			calcul_tmp = eval(calcul_tmp);
			if(variable_source != false && !isNaN(calcul_tmp))
				variable_source.resultat = Number.parseFloat(calcul_tmp).toFixed(2);

			vue_instance.variables[variable_source.nom] = calcul_tmp;
			variable_source.boucle = 0;
			variable_source.erreur = false;
		}

		if(calcul_tmp != undefined){
			calcul_tmp = Math.round(calcul_tmp * 100) / 100;
		}

		return {
			'resultat': calcul_tmp,
			'erreur': erreur,
		};

	},

	/**
	*
	* Exécute la fonction calculer_total_calculateur_par_article pour chaque article
	*
	* Cela a été fait pour conserver le fonctionnement des variables de manière hiérarchique
	*
	*/
	calculer_total_calculateur : async function(){

		for(let[article_index, article] of Object.entries(vue_instance.articles_du_document)){

			if(article.type_ligne != undefined && article.type_ligne != "calculateur" && article.type_ligne != "regroupement")
				continue;

			if(article.calculateur)
				article.calculateur.deja_traite = false;

			if(article.calculateur)
				await vue_instance.calculer_total_calculateur_par_article(article.calculateur, article, "false");

			if(article.nomenclature && article.nomenclature.length > 0){

				for(let [index_nomenclature, article_nomenclature] of Object.entries(article.nomenclature)){

					if(article_nomenclature.calculateur)
						article_nomenclature.calculateur.deja_traite = false;

					if(article_nomenclature.calculateur != undefined && Object.keys(article_nomenclature.calculateur).length > 0)
						await vue_instance.calculer_total_calculateur_par_article(article.calculateur, article, index_nomenclature);

					if(article_nomenclature.nomenclature && article_nomenclature.nomenclature.length > 0){

						for(let [index_sous_nomenclature, article_sous_nomenclature] of Object.entries(article_nomenclature.nomenclature)){

							if(article_sous_nomenclature.calculateur)
								article_sous_nomenclature.calculateur.deja_traite = false;

							if(article_sous_nomenclature.calculateur != undefined && Object.keys(article_sous_nomenclature.calculateur).length > 0)
								await vue_instance.calculer_total_calculateur_par_article(article.calculateur, article, index_nomenclature, index_sous_nomenclature);

						};

					}

				};

			}

		};

		vue_instance.mise_a_jour_total_document_vue();

	},

	/**
	*
	* Récupère les lignes liés à la ligne données de manière hiérarchique et traite leur calculateur
	*
	*/
	calculer_total_calculateur_par_article : async function(calculateur, article_mere, article_nomenclature_index, article_sous_nomenclature_index){

		if(article_mere.calculateur != undefined && article_mere.calculateur != null && Object.keys(article_mere.calculateur).length > 0 && article_mere.calculateur.deja_traite == true)
			return;

		vue_instance.variables = [];

		var article_a_traite = [];

		article_a_traite = vue_instance.article_a_parcourir(article_mere);

		article_a_traite.push(article_mere);

		if(article_mere.type_ligne == undefined || article_mere.type_ligne == "regroupement"){

            if(article_mere.type_ligne == undefined){
                if(article_mere.modele != undefined && article_mere.modele.type_article != undefined){

                    article_a_traite = article_a_traite.concat(article_mere.nomenclature);

					$.each(article_mere.nomenclature, function(index, article_nomenclature){

						if(article_nomenclature.type_article == 1 || article_nomenclature.type_article == 3)
							article_a_traite = article_a_traite.concat(article_nomenclature.nomenclature);

					});

                }

                else if(article_mere.type_article != undefined){

                    article_a_traite = article_a_traite.concat(article_mere.nomenclature);

                }
            }

		}

		var erreur = false;

		for(let [article_index, article] of Object.entries(article_a_traite)){	

			if(article == undefined || !article.calculateur || article.calculateur == undefined || Object.keys(article.calculateur).length < 4){
				continue;
			}

			if(article.calculateur.variables == undefined)
				article.calculateur.variables = [];
			if(article.calculateur.ponderation == undefined)
				article.calculateur.ponderation = [];

			article.calculateur.variables.forEach(function(variable, variable_index){

				if(variable.boucle && variable.boucle != 0){

					variable.boucle = 0;

				}

			});

			for(let [variable_index, variable] of Object.entries(article.calculateur.variables)){

				var calcul = await vue_instance.calculer(variable.valeur, article, variable);

				if(calcul.erreur == false)
					vue_instance.variables[variable.nom] = calcul.resultat;
				else{

					article.calculateur.erreur = true;
					erreur = true;
					continue;

				}

			};

			for(let [ponderation_index, ponderation] of Object.entries(article.calculateur.ponderation)){

				var condition_remplie = false;

				for(let [condition_index, condition] of Object.entries(ponderation.conditions)){

					if(condition.type == 1 && condition_remplie == false && erreur != true){

						var condition_complete = [];

						for(let [donnee_index, donnee] of Object.entries(condition.donnees)){

							if(donnee.comp == 0){
								article.calculateur.erreur = true;
								erreur = true;
								continue;
							}

							var calcul = await vue_instance.calculer(donnee.critere + donnee.comp + donnee.valeur, article, donnee, "no_alert");

							if(calcul.erreur == false)
								condition_complete.push(calcul.resultat);
							else{

								article.calculateur.erreur = true;
								erreur = true;
								continue;

							}

							if(donnee.cond){

								condition_complete.push(donnee.cond);

							}

						};

						var test_final = "";

						for(resultat of condition_complete){

							test_final += resultat;

						}

						if(eval(test_final) == true){

							var calcul = await vue_instance.calculer(condition.resultat, article);

                            if(calcul.erreur == false){
                                vue_instance.variables[ponderation.designation] = parseFloat(calcul.resultat);
                                ponderation.resultat = parseFloat(calcul.resultat);
                                condition_remplie = true;
                            }
                            else{

                                article.calculateur.erreur = true;
                                erreur = true;
                                continue;
                            }

						}

					}

					else if(condition_remplie == false && erreur != true){

						var calcul = await vue_instance.calculer(condition.resultat, article);

						if(calcul.erreur == false){
							vue_instance.variables[ponderation.designation] = parseFloat(calcul.resultat);
							ponderation.resultat = parseFloat(calcul.resultat);
							condition_remplie = true;
						}
						else{

							article.calculateur.erreur = true;
							erreur = true;
							continue;
						}

					}

					else if(erreur == true){

						vue_instance.variables[ponderation.designation] = 1;
						ponderation.resultat = 1;
						condition_remplie = true;

					}

				};

			};

			if(erreur == false){
				if(article.type_ligne == undefined){
						var calcul = await vue_instance.calculer(article.calculateur.calcul.formule, article);

						if(calcul.erreur == false && typeof calcul.resultat == 'number'){

							if(article.resultat_force != true)
								article.quantite = calcul.resultat;
							article.calculateur.resultat_calcul = calcul.resultat;
							if(erreur == false){
								article.calculateur.erreur = false;
								article.calculateur.calcul.erreur = false;
						}

					}
					else{

						article.calculateur.erreur = true;
						article.calculateur.calcul.erreur = true;
						article.quantite = 0;

					}
				}
				else{
					article.calculateur.erreur = false;
				}
			}
			else{
				var calcul = await vue_instance.calculer(article.calculateur.calcul.formule, article);

				if(typeof calcul.resultat == 'number'){

					if(article.resultat_force != true)
						article.quantite = calcul.resultat;

					article.calculateur.resultat_calcul = calcul.resultat;
				}

				article.calculateur.erreur = true;
				article.calculateur.calcul.erreur = true;
			}
			if(article_sous_nomenclature_index >= 0){

				article_mere.nomenclature[article_nomenclature_index].erreur = article.calculateur.erreur;
				article_mere.erreur = article.calculateur.erreur;

			}
			else if(article_nomenclature_index >= 0){

				article_mere.erreur = article.calculateur.erreur;

			}

			};
			if(article_mere.calculateur != undefined && article_mere.calculateur != null && Object.keys(article_mere.calculateur).length > 0)
				article_mere.calculateur.deja_traite = true;

		},

		fermer_calculateur : function(){

			var vue_composant = this;

			var supprimer = true;

			if(vue_composant.detail_calculateur.article_nomenclature_index >= 0){

				if(vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.calcul.formule != "" && vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.calcul.formule != undefined)
					supprimer = false

				if(vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.ponderation.length > 0 && vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.ponderation != undefined)
					supprimer = false

				if(vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.quantite.designation != "" && vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.quantite.designation != undefined)
					supprimer = false

				if(vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.variables.length > 0 && vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur.variables != undefined)
					supprimer = false

				if(supprimer == true){
					delete vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur;
					var nomenclature_avec_calculateur = vue_composant.detail_calculateur.nomenclature.filter(nomenclature => nomenclature.calculateur != undefined && nomenclature.calculateur != null && Object.keys(nomenclature.calculateur).length > 0);
					if(nomenclature_avec_calculateur.length == 0)
						delete vue_composant.detail_calculateur.erreur;
				}

			}
			else{

				if(vue_composant.detail_calculateur.calculateur.calcul.formule != undefined && vue_composant.detail_calculateur.calculateur.calcul.formule != "")
					supprimer = false

				if(vue_composant.detail_calculateur.calculateur.ponderation != undefined && vue_composant.detail_calculateur.calculateur.ponderation.length > 0)
					supprimer = false

				if(vue_composant.detail_calculateur.calculateur.quantite.designation != undefined && vue_composant.detail_calculateur.calculateur.quantite.designation != "")
					supprimer = false

				if(vue_composant.detail_calculateur.calculateur.variables != undefined && vue_composant.detail_calculateur.calculateur.variables.length > 0)
					supprimer = false

				if(supprimer == true){
					delete vue_composant.detail_calculateur.calculateur;
				}

			}

			vue_composant.afficher_modale_calculateur = false;
			vue_composant.detail_calculateur = {};
			vue_composant.variables = {};


		},

		supprimer_certain_calculateur : function(){

			var vue_composant = this;

			vue_composant.afficher_modale_supprimer_calculateur = true;

		},

		supprimer_element_calculateur : async function(tableau, index){

			var vue_composant = this;

			if(await confirm_eden("Êtes-vous sûr de vouloir supprimer cet élément ?")){
				tableau.splice(index,1);
				vue_composant.$forceUpdate();
			}

		},

		supprimer_definitivement_calculateur(detail_calculateur){

			var vue_composant = this;

			loading(true);

			if(vue_composant.detail_calculateur.article_sous_nomenclature >= 0){
				delete vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].nomenclature[vue_composant.detail_calculateur.article_sous_nomenclature].calculateur;
			}

			if(vue_composant.detail_calculateur.article_nomenclature_index >= 0){
				delete vue_composant.detail_calculateur.nomenclature[vue_composant.detail_calculateur.article_nomenclature_index].calculateur;
				var nomenclature_avec_calculateur = vue_composant.detail_calculateur.nomenclature.filter(nomenclature => nomenclature.calculateur != undefined && nomenclature.calculateur != null && Object.keys(nomenclature.calculateur).length > 0);
				if(nomenclature_avec_calculateur.length == 0)
					delete vue_composant.detail_calculateur.erreur;
			}
			else
				delete vue_composant.detail_calculateur.calculateur;
			vue_composant.afficher_modale_supprimer_calculateur = false;
			vue_composant.afficher_modale_calculateur = false;

			vue_composant.calculer_total_calculateur();

		loading(false);
	},

	changement_quantite_calculateur : function(article){

		if(article.calculateur != undefined && Object.keys(article.calculateur).length > 4){
			if(article.calculateur.resultat_calcul != article.quantite && (article.quantite != '' && article.quantite != undefined)){

				article.resultat_force = true;

			}
			else if(article.quantite == '' || article.quantite == undefined){

				article.quantite = article.calculateur.resultat_calcul;
				article.resultat_force = false;

			}

		}
	},

	corrige_virgule : function(table, element){

		if (typeof table[element] === 'string' || table[element] instanceof String)
			table[element] = table[element].replace(',','.');
	},

	/*
	 *
	 * Affiche une modale d'ajout d'article sur un regroupement
	 *
	 */
	ajout_article_par_modale: function(parametres,type_ajout) {

		if(type_ajout == 'regroupement'){
			this.informations_ajout_article_modale = {
				'type' : 'regroupement',
				'nom_valeur' : 'regroupement_id',
				'valeur' :  parametres.id,
			};
		}

		if(type_ajout == 'nomenclature'){

			this.informations_ajout_article_modale = {
				'type' : 'nomenclature',
				'nom_valeur' : 'nomenclature_ligne_index',
				'valeur' :  parametres,
			};
		}

		vue_instance.afficher_modale_ajout_article = true;
		vue_instance.valeur_champ_recherche = '';
	},

	/*
	 *
	 * Changement de modèle de calculateur
	 *
	 */
	change_modele_de_calculateur : async function(article, $event, index_nomenclature = 'false', index_sous_nomenclature = 'false'){

		if(article.modele_de_calculateur_id != undefined){
			var ancien_modele = JSON.parse(JSON.stringify(article.modele_de_calculateur_id));

			if(!await confirm_eden("Cette action va supprimer tout le calculateur actuel")){
				$event.target.value = ancien_modele;
				return;
			}
		}

		var nouveau_modele = $event.target.value;

		$.post({
			url: "{{ route('document.recupere_modele_de_calculateur') }}",
			data: {
				modele_de_calculateur_id : nouveau_modele,
			},
			dataType: 'json'
		}).done(async function(retour){

			article.modele_de_calculateur_id = nouveau_modele;
			if(index_sous_nomenclature >= 0)
				article.nomenclature[index_nomenclature].nomenclature[index_sous_nomenclature].calculateur = retour.calculateur;
			else if(index_nomenclature >= 0)
				article.nomenclature[index_nomenclature].calculateur = retour.calculateur;
			else
				article.calculateur = retour.calculateur;
			vue_instance.$forceUpdate();
			await vue_instance.calculer_total_calculateur();

		});
	},

	contenu_produit_assemble: function(article_parent, article_parent_parent) {

		if(article_parent && article_parent.modele && article_parent.modele.type_article == 3)
			return true;

		if(article_parent_parent && article_parent_parent.modele && article_parent_parent.modele.type_article == 3)
			return true;

		return false;
	},

	calcule_quantite_total_article: function(article, article_parent, article_parent_parent) {

		return this.retourne_quantite_conditionnement(article) * this.retourne_quantite_conditionnement(article_parent) * this.retourne_quantite_conditionnement(article_parent_parent);
	},

	retourne_quantite_conditionnement: function(article) {

		if(!article)
			return 1;

		var quantite_conditionnement = 1;

		if(article.conditionnement > 0) {

			for(var i in article.conditionnement_possible) {

				var conditionnement_possible = article.conditionnement_possible[i];

				if(conditionnement_possible.id == article.conditionnement)
					quantite_conditionnement = conditionnement_possible.quantite;

			}
		}

		return quantite_conditionnement * article.quantite;
	},

	desaffichage_scroll : function(type = false){
		if(type === false)
			this.afficher_scroll_articles = false;
		else
        	this.afficher_scroll_articles_autres[type] = false;
	},

@endpush
