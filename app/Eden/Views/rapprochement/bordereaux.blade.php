<div class="row" v-show="budget_insight_rapprochement_bordereau_affichage === true && budget_insight_nombre_transactions_selectionnees == 1">
	<div class="col-lg-12 col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<div class="row">
					<div class="d-flex" style="flex-direction:column">
						<h4 class="col-xs-12 col-md-10">
							@traduction('interface.budget_insight.rapprocher_bordereau') <span style="font-size: 16px;">(@{{ transaction_selectionnee_texte }})</span>
						</h4>
					</div>
				</div>
			</div>
			<div class="card-body">
				
				<div class="row">
					<div class="col-md-3"><b>@traduction('interface.budget_insight.montant_a_rapprocher') : @{{ total_restant_a_rapprocher_bordereau }} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b></div>
					<div class="col-md-6" v-show="bordereaux_selectionnes.length > 0">
						<b>@traduction('interface.budget_insight.bordereaux_selectionnes') : @{{ total_ttc_selectionne_rapprochement_bordereau | montant }} {!! maquette('devise_application_symbole') !!} @traduction('interface.budget_insight.ttc') </b><br/>
						<span v-for="(bordereau, id) in bordereaux_selectionnes">
							@{{ bordereau.numero }}, @{{ bordereau.montant | montant }} {!! maquette('devise_application_symbole') !!} <span class="fa fa-times" @click="supprime_borderau_selectione(id, paiement)"></span><br/>
						</span>
						<br/>
						<br/>
					</div>
					<div class="col-md-3" v-show="bordereaux_selectionnes.length > 0">
						<b>@traduction('interface.budget_insight.actions') : </b></br>
						<span class="css__lien" v-show="total_restant_a_rapprocher_bordereau <= 0.05 && total_restant_a_rapprocher_bordereau >= -0.05" @click="budget_insight_enregistrer_rapprochement_bordereau">@traduction('interface.budget_insight.enregistrer_rapprochements')</span>
					</div>
				</div>
				

				<div class="table-responsive">
					<table class="table table-bordered table-hover" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th>#</th>
								<th>@traduction('interface.budget_insight.tableau_bordereau.numero_bordereau')</th>
								<th>@traduction('interface.budget_insight.tableau_bordereau.nombre_cheques')</th>
								<th>@traduction('interface.budget_insight.tableau_bordereau.montant_ttc')</th>
								<th>@traduction('interface.budget_insight.tableau_bordereau.date')</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="bordereau in bordereaux_a_rapprocher" :class="{'css_liste_ligne_selectionnee': bordereau.selectionnee_budget_insight === true}" @click="ajoute_bordereau_pour_rapprochement(bordereau)">
								<td>@{{ bordereau.id }}</td>
								<td>@{{ bordereau.numero }}</th>
								<td>@{{ bordereau.nombre_de_cheques }}</th>
								<td>@{{ bordereau.montant }}</th>
								<td>@{{ bordereau.date }}</th>
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
	
	bordereaux_a_rapprocher: {},
	bordereaux_selectionnes: [],
	total_ttc_a_rapprocher_bordereau: 0,
	
@endpush

@push('donnees_pour_vuejs_computed')
	
	total_ttc_selectionne_rapprochement_bordereau: function() {
		
		var total = parseFloat(0);
		
		this.bordereaux_selectionnes.forEach(function(element) {
			
			total += parseFloat(element.montant);
		});
		
		return total;
	},
	
	total_restant_a_rapprocher_bordereau: function() {
			
			return Math.round((this.total_ttc_a_rapprocher_bordereau - this.total_ttc_selectionne_rapprochement_bordereau) * 100) / 100;
		},
		
@endpush


@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Action appelée pour ouvrir la liste des bordereaux de chèques pour un rapprochement
	 *
	 */
	budget_insight_rapprocher_bordereau: function() {
		
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
			
			// on ferme les fenêtre de rapprochement
			this.change_fenetre_affichee(false);
			
			return;
		}
		
		// on récupère la liste des paiements non rapprochés
		loading(true);
		
		this.change_fenetre_affichee('rapprocher_bordereau');
		
		$.ajax({

			url: "{{ route('budget_insight.recupere_bordereaux_non_rapproches') }}",
			dataType: "json",
			method: 'post',
			data: {
				transaction_id:transaction_id
			},
		}).done(function(bordereaux) {
			
			vue_instance.bordereaux_a_rapprocher = bordereaux;
			vue_instance.transaction_selectionnee = false;

			loading(false);
		});
	},
	
	/**
	 * 
	 * Sélectionne un bordereau pour le rapprochement
	 * 
	 */
	ajoute_bordereau_pour_rapprochement: function(bordereau) {
			
		var ligne_trouvee = false;
		
		// on vérifie que la facture n'est pas déjà sélectionnée
		this.bordereaux_selectionnes.forEach(function(element, index) {
			
			if(element.id == bordereau.id) {
				
				ligne_trouvee = true;
				bordereau.selectionnee_budget_insight = false;
				vue_instance.bordereaux_selectionnes.splice(index,1);
			}
		});
		
		if(ligne_trouvee === true) {
			
			return;
		}
		
		bordereau.selectionnee_budget_insight = true;
		
		this.bordereaux_selectionnes.push(bordereau);
	},
	
	/**
	 * 
	 * 
	 * 
	 */
	supprime_borderau_selectione: function(id, bordereau) {
			
		this.bordereaux_a_rapprocher.forEach(function(element) {
			
			if(element.id == bordereau.id)
				bordereau.selectionnee_budget_insight = false;
		});
		
		
		
		this.bordereaux_selectionnes.splice(id, 1);
	},
	
	/**
	 *
	 * Rapproche la ligne du relevé bancaire avec un paiement
	 *
	 */
	budget_insight_enregistrer_rapprochement_bordereau: function() {
		
		loading(true);
		
		var context = this;
		var bordereaux_rapproches = [];
		
		this.bordereaux_selectionnes.forEach(function(element) {
			
			bordereaux_rapproches.push(element.id);
		});
		
		var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');
		
		$.post({

			url: "{{ route('budget_insight.enregistre_rapprochement_bordereau') }}",
			dataType: "json",
			data: {
				
				bordereaux_rapproches: bordereaux_rapproches,
				transaction_id: transaction_id
			}
		}).done(async function(retour) {
			
			// une erreur ?
			if(retour.resultat !== true) {
				
				loading(false);
				await alerte_eden(resultat.erreur);
				return;
			}
			
			vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();

			// on supprime la sélection de ligne
			vue_instance.$refs['liste_libre_{{$id_liste}}'].deselectionner_toutes_les_lignes();

			context.bordereaux_selectionnes = [];
			
			setTimeout(function() {
				
				context.budget_insight_maj_nombre_transactions_selectionnees();
				context.transaction_selectionnee = false;
			}, 250);
			
			context.change_fenetre_affichee(false);

			$('html, body').animate( { scrollTop: $('#base-content').offset().top }, 750 ); // Go
			
			loading(false);
		});
	},
@endpush