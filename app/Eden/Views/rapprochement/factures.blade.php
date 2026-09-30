<div class="row" v-show="budget_insight_paiement_affichage === true && budget_insight_nombre_transactions_selectionnees == 1">
	<div class="col-lg-12 col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<div class="row">
					<h4 class="col-xs-12 col-md-10">
						@traduction('interface.budget_insight.enregistrer_paiement') <span style="font-size: 16px;">(@{{ transaction_selectionnee_texte }})</span>
					</h4>

					<form class="css_block_btn_recherche_liste col-md-2	" method="post">
						<input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.budget_insight.placeholder.rechercher')" style="padding-left: 5px" type="text" name="recherche" v-model="budget_insight_recherche_paiement" />
						<div class="css_btn_recherche_liste" @click="$refs['liste_libre_{{$id_liste}}'].actualisation_filtres()">
							<i class="fa fa-search" aria-hidden="true"></i>
						</div>
					</form>
				</div>
			</div>
			<div class="card-body">

				<div class="row">
					<div class="col-md-3"><b>@traduction('interface.budget_insight.montant_a_encaisser')  : @{{ total_restant_a_rapprocher_factures | montant}} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b></div>
					<div class="col-md-6" v-show="factures_selectionnees.length > 0">
						<b>@traduction('interface.budget_insight.documents_selectionnes') : @{{ total_ttc_selectionne_paiement_factures | montant }} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b><br/>
						<span v-for="(facture, id) in factures_selectionnees">
							<span v-html="facture.reference_document"></span>
							<span v-show="facture.reference_document != null">,</span>
							<span v-html="facture.client_id"></span>, @{{ facture.solde_document_ttc | montant }} {!! maquette('devise_application_symbole') !!} <span class="fa fa-times" @click="supprime_facture_selectionnee(id, facture)"></span><br/>
						</span>
						<br/>
						<br/>
					</div>
					<div class="col-md-3" v-show="factures_selectionnees.length > 0">
						<b>@traduction('interface.budget_insight.actions') : </b></br>
						<span class="css__lien" @click="budget_insight_enregistrer_paiement">@traduction('interface.budget_insight.enregistrer_paiements')</span>
					</div>
				</div>


				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>#</th>
								<th>@traduction('interface.budget_insight.table_document.client')</th>
								<th>@traduction('interface.budget_insight.table_document.reference_document')</th>
								<th>@traduction('interface.budget_insight.table_document.date')</th>
								<th>@traduction('interface.budget_insight.table_document.objet')</th>
								<th>@traduction('interface.budget_insight.table_document.montant_initial_ttc')</th>
								<th>@traduction('interface.budget_insight.table_document.solde_ttc')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="facture in facture_a_regler" :class="{'css_liste_ligne_selectionnee': facture.selectionnee_budget_insight === true}" @click="ajoute_facture_pour_paiement(facture)"  v-show="budget_insight_recherche_paiement == '' || (facture.client_id.toUpperCase().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0 || facture.reference_document.toUpperCase().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0 || facture.solde_document_ttc.toString().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0)">
								<td>@{{ facture.id }}</td>
								<td v-html="facture.client_id"></th>
								<td>@{{ facture.reference_document }}</th>
								<td>@{{ facture.date }}</th>
								<td>@{{ facture.objet }}</th>
								<td>@{{ facture.montant_document_ttc | montant }}</th>
								<td>@{{ facture.solde_document_ttc | montant }}</th>
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

	facture_a_regler: {},
	factures_selectionnees: [],

	total_ttc_a_rapprocher_factures: 0,

@endpush

@push('donnees_pour_vuejs_computed')

	total_ttc_selectionne_paiement_factures: function() {

		var total = parseFloat(0);

		this.factures_selectionnees.forEach(function(element) {

			total += parseFloat(element.solde_document_ttc);
		});

		return total;
	},

	total_restant_a_rapprocher_factures: function() {

		//console.log('this.total_ttc_a_rapprocher_factures', this.total_ttc_a_rapprocher_factures);
		//console.log('this.total_ttc_selectionne_paiement_factures', this.total_ttc_selectionne_paiement_factures);

		return Math.round((this.total_ttc_a_rapprocher_factures - this.total_ttc_selectionne_paiement_factures) * 100) / 100;
	},


@endpush


@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Enregistre un paiement sur une facture
	 *
	 */
	budget_insight_enregistrer_paiement: function() {

		loading(true);

		var elements = [];
		var context = this;

		this.factures_selectionnees.forEach(function(element) {

			elements.push(element.id);
		});

		var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');

		$.post({

			url: "{{ route('budget_insight.enregistre_paiement', array('facture_vente')) }}",
			dataType: "json",
			data: {

				elements: elements,
				transaction_id: transaction_id
			}
		}).done(async function(retour) {

			console.log('retour', retour);

			// une erreur ?
			if(retour.resultat !== true) {

				loading(false);
				await alerte_eden(retour.erreur);
				alert(retour.erreur);
				return;
			}


			vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();

			vue_instance.change_fenetre_affichee(false);

			context.factures_selectionnees = [];


			// on supprime la sélection de ligne
			vue_instance.$refs['liste_libre_{{$id_liste}}'].deselectionner_toutes_les_lignes();

			setTimeout(function() {

				context.budget_insight_maj_nombre_transactions_selectionnees();
				context.transaction_selectionnee = false;
			}, 250);

			var speed = 750; // Durée de l'animation (en ms)
			$('html, body').animate( { scrollTop: $('#base-content').offset().top }, speed ); // Go



			loading(false);

		});
	},

	/**
	 *
	 * Action du clic sur l'option paiement pour afficher la liste des paiements pour rapprochement
	 *
	 */
	budget_insight_paiement: function() {

		$('#modale_choix_type_paiement').modal('hide');

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

			this.change_fenetre_affichee(false);
			return;
		}

		this.change_fenetre_affichee('paiement_facture');
		this.budget_insight_paiement_recuperer();
	},


	/**
	 *
	 * Récupère la liste des factures en attente de paiement
	 *
	 */
	budget_insight_paiement_recuperer: function() {

		$.ajax({

			url: "{{ route('budget_insight.recupere_facture_pour_paiement') }}",
			dataType: "json",
			method: 'post',
			data: {
				entite_id:vue_instance.entite_selectionnee
			},
		}).done(function(factures) {

			vue_instance.facture_a_regler = factures;

		});
	},

	ajoute_facture_pour_paiement: function(facture) {

		var facture_trouvee = false;

		// on vérifie que la facture n'est pas déjà sélectionnée
		this.factures_selectionnees.forEach(function(element, index) {

			if(element.id == facture.id) {

				facture_trouvee = true;
				facture.selectionnee_budget_insight = false;
				vue_instance.factures_selectionnees.splice(index,1);
			}
		});

		if(facture_trouvee === true)
			return;

		facture.selectionnee_budget_insight = true;

		this.factures_selectionnees.push(facture);
	},

	supprime_facture_selectionnee: function(id, facture) {

		this.facture_a_regler.forEach(function(element) {

			if(element.id == facture.id)
				facture.selectionnee_budget_insight = false;
		});



		this.factures_selectionnees.splice(id, 1);
	},

@endpush
