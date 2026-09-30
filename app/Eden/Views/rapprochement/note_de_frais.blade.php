<div class="row" v-show="budget_insight_paiement_note_de_frais_affichage === true && budget_insight_nombre_transactions_selectionnees == 1">
	<div class="col-lg-12 col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<div class="row">
					<h4 class="col-xs-12 col-md-10">
						@traduction('interface.budget_insight.enregistrer_paiement') <span style="font-size: 16px;">(@{{ transaction_selectionnee_texte }})</span>
					</h4>

					<form class="css_block_btn_recherche_liste col-md-2	" method="post">
						<input class='css_input_recherche_liste js_input_recherche_liste' :placeholder="traduction('interface.budget_insight.placeholder.rechercher')" style="padding-left: 5px" type="text" name="recherche" v-model="budget_insight_recherche_paiement" />
						<div class="css_btn_recherche_liste" @click="actualisation_filtres();">
							<i class="fa fa-search" aria-hidden="true"></i>
						</div>
					</form>
				</div>
			</div>
			<div class="card-body">

				<div class="row">
					<div class="col-md-3"><b>@traduction('interface.budget_insight.montant_a_decaisser') : @{{ total_restant_a_rapprocher_note_de_frais | montant}} @traduction('interface.budget_insight.ttc') </b></div>
					<div class="col-md-6" v-show="notes_de_frais_selectionnees.length > 0">
						<b>@traduction('interface.budget_insight.documents_selectionnes') : @{{ total_ttc_selectionne_paiement_note_de_frais | montant }} @traduction('interface.budget_insight.ttc') </b><br/>
						<span v-for="(note_de_frais, id) in notes_de_frais_selectionnees">
							<span v-html="note_de_frais.utilisateur_id"></span>
							<span v-html="note_de_frais.date"></span>, @{{ note_de_frais.montant_rembourse | montant }}<span class="fa fa-times" @click="supprime_note_de_frais_selectionnee(id, note_de_frais)"></span><br/>
						</span>
						<br/>
						<br/>
					</div>
					<div class="col-md-3" v-show="notes_de_frais_selectionnees.length > 0">
						<b>@traduction('interface.budget_insight.actions') : </b></br>
						<span class="css__lien" @click="budget_insight_enregistrer_paiement_note_de_frais">@traduction('interface.budget_insight.enregistrer_paiements')</span>
					</div>
				</div>


				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>#</th>
								<th>@traduction('interface.budget_insight.table_document.utilisateur')</th>
								<th>@traduction('interface.budget_insight.table_document.client')</th>
								<th>@traduction('interface.budget_insight.table_document.date')</th>
								<th>@traduction('interface.budget_insight.table_document.montant_rembourse')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="note_de_frais in note_de_frais_a_regler" :class="{'css_liste_ligne_selectionnee': note_de_frais.selectionnee_budget_insight === true}" @click="ajoute_note_de_frais_pour_paiement(note_de_frais)"  v-show="budget_insight_recherche_paiement == '' || (note_de_frais.utilisateur_id.toUpperCase().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0 || note_de_frais.client_id.toUpperCase().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0 || note_de_frais.montant_rembourse.toString().indexOf(budget_insight_recherche_paiement.toUpperCase()) >= 0)">
								<td>@{{ note_de_frais.id }}</td>
								<td v-html="note_de_frais.utilisateur_id"></th>
								<td v-html="note_de_frais.client_id"></th>
								<td>@{{ note_de_frais.date }}</th>
								<td>@{{ note_de_frais.montant_rembourse | montant }}</th>
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

	note_de_frais_a_regler: {},
	notes_de_frais_selectionnees: [],

	total_ttc_a_rapprocher_notes_de_frais: 0,

@endpush

@push('donnees_pour_vuejs_computed')

	total_ttc_selectionne_paiement_note_de_frais: function() {

		var total = parseFloat(0);

		this.notes_de_frais_selectionnees.forEach(function(element) {

			total += parseFloat(element.montant_rembourse);
		});

		return total;
	},

	total_restant_a_rapprocher_note_de_frais: function() {

		return Math.round((this.total_ttc_a_rapprocher_notes_de_frais - this.total_ttc_selectionne_paiement_note_de_frais) * 100) / 100;
	},


@endpush


@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Enregistre un paiement sur une note_de_frais
	 *
	 */
	budget_insight_enregistrer_paiement_note_de_frais: function() {

		loading(true);

		var elements = [];
		var context = this;

		this.notes_de_frais_selectionnees.forEach(function(element) {

			elements.push(element.id);
		});

		var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');

		$.post({

			url: "{{ route('budget_insight.enregistre_paiement', array('note_de_frais')) }}",
			dataType: "json",
			data: {

				elements: elements,
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

			vue_instance.change_fenetre_affichee(false);

			context.notes_de_frais_selectionnees = [];

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
	budget_insight_paiement_note_de_frais: function() {

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

		this.change_fenetre_affichee('paiement_note_de_frais');
		this.budget_insight_paiement_recuperer_notes_de_frais();
	},


	/**
	 *
	 * Récupère la liste des notes_de_frais en attente de paiement
	 *
	 */
	budget_insight_paiement_recuperer_notes_de_frais: function() {

		$.ajax({

			url: "{{ route('budget_insight.recupere_note_de_frais_pour_paiement') }}",
			dataType: "json",
			method: 'post',
			data: {
				entite_id:vue_instance.entite_selectionnee
			},
		}).done(function(notes_de_frais) {

			vue_instance.note_de_frais_a_regler = notes_de_frais;

		});
	},

	ajoute_note_de_frais_pour_paiement: function(note_de_frais) {

		var notes_de_frais_trouvee = false;

		// on vérifie que la note_de_frais n'est pas déjà sélectionnée
		this.notes_de_frais_selectionnees.forEach(function(element, index) {

			if(element.id == note_de_frais.id) {

				notes_de_frais_trouvee = true;
				note_de_frais.selectionnee_budget_insight = false;
				vue_instance.notes_de_frais_selectionnees.splice(index,1);
			}
		});

		if(notes_de_frais_trouvee === true)
			return;

		note_de_frais.selectionnee_budget_insight = true;

		this.notes_de_frais_selectionnees.push(note_de_frais);
	},

	supprime_note_de_frais_selectionnee: function(id, note_de_frais) {

		this.note_de_frais_a_regler.forEach(function(element) {

			if(element.id == note_de_frais.id)
				note_de_frais.selectionnee_budget_insight = false;
		});



		this.notes_de_frais_selectionnees.splice(id, 1);
	},

@endpush
