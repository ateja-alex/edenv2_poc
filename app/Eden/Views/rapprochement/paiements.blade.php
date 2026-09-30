<div class="row" v-show="budget_insight_rapprochement_affichage === true && budget_insight_nombre_transactions_selectionnees == 1">
	<div class="col-lg-12 col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<div class="row">
					<div class="col-md-8" style="flex-direction:column">
						<h4 class="col-xs-12">
							@traduction('interface.budget_insight.rapprocher_paiement') <span style="font-size: 16px;">(@{{ transaction_selectionnee_texte }})</span>
						</h4>
						<div class="">
							<select name="" id="" class=""  v-model="filtres_rapprochement.mode_de_paiement_choisi" @change="filtrer_rapprochements">
								<option value="0">Tous les moyens de paiement</option>
								<option v-for="(type_de_paiement,index) in mode_de_paiement" :key="index" :value="type_de_paiement.id"> @{{type_de_paiement.nom}}</option>
							</select>
							<input type="text" name="date_de_debut" :placeholder="traduction('interface.budget_insight.placeholder.date_debut')" class="js_datepicker js_date_de_debut" />
							<input type="text" name="date_de_debut" :placeholder="traduction('interface.budget_insight.placeholder.date_fin')" class="js_datepicker js_date_de_fin" />
						</div>

					</div>
					<form class="css_block_btn_recherche_liste col-md-2	" method="post">
						<input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.budget_insight.placeholder.rechercher')" style="padding-left: 5px" type="text" name="recherche" v-model="budget_insight_recherche_paiement" />
						<div class="css_btn_recherche_liste" >
							<i class="fa fa-search" aria-hidden="true"></i>
						</div>
					</form>
				</div>
			</div>
			<div class="card-body">

				<div class="row">
					<div class="col-md-3"><b>@traduction('interface.budget_insight.montant_a_rapprocher') : @{{ total_restant_a_rapprocher_2 }} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b></div>
					<div class="col-md-6" v-show="paiements_selectionnes.length > 0">
						<b>@traduction('interface.budget_insight.paiements_selectionnes') : @{{ total_ttc_selectionne_rapprochement|montant }} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b><br/>
						<span v-for="(paiement, id) in paiements_selectionnes">
							<span v-html="paiement.reference_document"></span>
							<span v-show="paiement.reference_document != null">,</span>
							<span v-html="paiement.client_id"></span>, @{{ paiement.montant|montant }} {!! maquette('devise_application_symbole') !!} <span class="fa fa-times" @click="supprime_paiement_selectione(id, paiement)"></span><br/>
						</span>
						<br/>
						<br/>
					</div>
					<div class="col-md-3" v-show="paiements_selectionnes.length > 0">
						<b>@traduction('interface.budget_insight.actions') : </b><br/>
						<span class="css__lien" v-show="total_restant_a_rapprocher_2 <= 0.05 && total_restant_a_rapprocher_2 >= -0.05" @click="budget_insight_enregistrer_rapprochement">@traduction('interface.budget_insight.enregistrer_rapprochements')</span>
					</div>
				</div>


				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>#</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.client_fournisseur')</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.reference_document')</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.date')</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.objet')</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.mode')</th>
								<th>@traduction('interface.budget_insight.tableau_paiement.montant_ttc')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="paiement in paiements_a_rapprocher_filtres" :class="{'css_liste_ligne_selectionnee': paiement.selectionnee_budget_insight === true}" @click="ajoute_paiement_pour_rapprochement(paiement)"  v-show="budget_insight_recherche_paiement == '' || correspond_a_la_recherche(paiement)">
								<td>@{{ paiement.id }}</td>
								<td v-html="paiement.client_id"></td>
								<td>@{{ paiement.reference_document }}</td>
								<td>@{{ paiement.date }}</td>
								<td>@{{ paiement.titre }}</td>
								<td>@{{ paiement.mode_paiement_id | affiche_mode_paiement }}</td>
								<td>@{{ paiement.montant|montant }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

{{-- <script> --}}

@push('donnees_pour_vuejs_data')

	paiements_a_rapprocher: {},
	paiements_a_rapprocher_filtres : [],

	paiements_selectionnes: [],
	total_ttc_a_rapprocher_paiements: 0,

@endpush

@push('donnees_pour_vuejs_computed')

	total_ttc_selectionne_rapprochement: function() {

		var total = parseFloat(0);

		this.paiements_selectionnes.forEach(function(element) {

			total += parseFloat(element.montant);
		});

		return total;
	},

	total_restant_a_rapprocher_2: function() {

		return Math.round((this.total_ttc_a_rapprocher_paiements - this.total_ttc_selectionne_rapprochement) * 100) / 100;
	},

@endpush


@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Filtre sur les rapprochement en fonction du mode de paiement et/ou les dates
	 *
	 */
	filtrer_rapprochements : function() {

		var filtres = this.filtres_rapprochement;

		var date_de_debut = filtres.date_de_debut;
		var date_de_fin = filtres.date_de_fin;


		//console.log({date_de_debut, date_de_fin});

		var paiements_a_afficher = [];

		// on recu
		this.paiements_a_rapprocher.map(function(element) {

			if(element.mode_paiement_id == filtres.mode_de_paiement_choisi || filtres.mode_de_paiement_choisi == 0) {

				var date_du_paiement_a_rapprocher = moment(element.date, 'DD/MM/YYYY').format('YYYY-MM-DD');

				if(date_de_debut == null && date_de_fin == null) {

					paiements_a_afficher.push(element);
				}

				// entre deux date
				if(date_de_debut != null && date_de_fin != null) {

					if(date_du_paiement_a_rapprocher >= date_de_debut && date_du_paiement_a_rapprocher <= date_de_fin) {

						paiements_a_afficher.push(element);
					}
				}

				// à partir de
				if(date_de_debut != null && date_de_fin == null) {

					if(moment(date_du_paiement_a_rapprocher).isSameOrAfter(date_de_debut)) {

						paiements_a_afficher.push(element);
					}
				}

				if(date_de_debut == null && date_de_fin != null) {

					if(moment(date_du_paiement_a_rapprocher).isSameOrBefore(date_de_fin)) {

						paiements_a_afficher.push(element);
					}
				}

			}
		});


		this.paiements_a_rapprocher_filtres = paiements_a_afficher
	},

	/**
	 *
	 * Rapproche la ligne du relevé bancaire avec un paiement
	 *
	 */
	budget_insight_enregistrer_rapprochement: function() {

		loading(true);

		var context = this;
		var paiements_rapproches = [];

		this.paiements_selectionnes.forEach(function(element) {

			paiements_rapproches.push(element.id);
		});

		var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');

		$.post({

			url: "{{ route('budget_insight.enregistre_rapprochement') }}",
			dataType: "json",
			data: {

				paiements_rapproches: paiements_rapproches,
				transaction_id: transaction_id
			}
		}).done(async function(retour) {

			// une erreur ?
			if(retour.resultat !== true) {

				loading(false);

				await alerte_eden(retour.erreur);

				return;
			}

			vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();

			// on supprime la sélection de ligne
			vue_instance.$refs['liste_libre_{{$id_liste}}'].deselectionner_toutes_les_lignes();

			context.paiements_selectionnes = [];

			context.budget_insight_rapprochement_affichage = false;

			setTimeout(function() {

				context.budget_insight_maj_nombre_transactions_selectionnees();
				context.transaction_selectionnee = false;
			}, 250);

			vue_instance.transaction_selectionnee = false;

			var speed = 750; // Durée de l'animation (en ms)
			$('html, body').animate( { scrollTop: $('#base-content').offset().top }, speed ); // Go

			loading(false);
		});
	},

	/**
	 *
	 * Action appelée pour ouvrir la liste des paiements pour un rapprochement
	 *
	 */
	budget_insight_rapprocher: function() {

		$('#modale_choix_type_rapprochement').modal('hide');

		// on regarde si la ligne sélectionnée n'est pas déjà reportée ou rapprochée
		if($('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').length > 1) {

			toastr.error(this.traduction('interface.budget_insight.erreur.impossible_rapprocher_plusieurs_lignes'));
			return;
		}
		if($('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').length == 0) {

			toastr.error(this.traduction('interface.budget_insight.erreur.selection_au_moins_une_ligne'));
			return;
		}

		var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');

		// on va chercher la transaction
		var deja_rapprochee = false;

		vue_instance.$refs['liste_libre_{{$id_liste}}'].liste.lignes.forEach(function(element) {

			if(element.id == transaction_id) {

				if(element.element.statut_eden == 2) {

					toastr.error("Cette transaction est déjà rapprochée");

					deja_rapprochee = true;
				}
			}
		});

		if(deja_rapprochee === true) {

			this.budget_insight_paiement_affichage = false;
			this.budget_insight_rapprochement_affichage = false;
			return;
		}

		// on récupère la liste des paiements non rapprochés
		loading(true);

		this.budget_insight_rapprochement_affichage = true;
		this.budget_insight_paiement_affichage = false;
		this.budget_insight_recherche_rapprochement = '';

		$.ajax({

			url: "{{ route('budget_insight.recupere_paiements_non_rapproches') }}",
			dataType: "json",
			method: 'post',
			data: {
				transaction_id:transaction_id
			},
		}).done(function(paiements) {

			vue_instance.paiements_a_rapprocher = paiements;

			vue_instance.paiements_a_rapprocher_filtres = paiements;

			loading(false);
		});
	},

	ajoute_paiement_pour_rapprochement: function(paiement) {

		var paiement_trouve = false;

		// on vérifie que la facture n'est pas déjà sélectionnée
		this.paiements_selectionnes.forEach(function(element, index) {

			if(element.id == paiement.id) {

				paiement_trouve = true;
				paiement.selectionnee_budget_insight = false;
				vue_instance.paiements_selectionnes.splice(index,1);
			}
		});

		if(paiement_trouve === true) {
			return;
		}

		paiement.selectionnee_budget_insight = true;

		this.paiements_selectionnes.push(paiement);
	},

	supprime_paiement_selectione: function(id, paiement) {

		this.paiements_a_rapprocher.forEach(function(element) {

			if(element.id == paiement.id)
				paiement.selectionnee_budget_insight = false;
		});



		this.paiements_selectionnes.splice(id, 1);
	},

	correspond_a_la_recherche(paiement){

		var tableau_recherche = this.budget_insight_recherche_paiement.toLowerCase().split(' ');

		var correspond = true;

        var chaine_tags_recherche = (paiement.chaine_tags_recherche ?? '').toLowerCase();

		tableau_recherche.forEach(function(recherche){

			if(!chaine_tags_recherche.includes(recherche)){

			correspond = false;
			}
		});

		return correspond;
	},

@endpush
