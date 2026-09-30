@extends('eden::templates.template')

@php
	$client_id = config('services.budget_insight.client_id');

    if(config('fonctionnalites_integrations.budget_insight_client_id') != null)
        $client_id = config('fonctionnalites_integrations.budget_insight_client_id');

@endphp

@if(!isset($management_element))
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<div class="row">
				<div class="col-lg-9 col-md-12">

					<h2>@traduction('interface.budget_insight.aucune_synchro_en_cours')
							<a
								href="https://easydeveloppement.biapi.pro/2.0/auth/webview/connect/select?client_id={{ $client_id }}&redirect_uri={{route('budget_insight.index')}}"
								class="css_ajouter_element css__lien">

								<button class="btn btn-secondary col-12" @click="synchroniser"><i class="fa fa-fw fa-plus-square"></i>@traduction('interface.budget_insight.ajouter_un_compte')</button>

							</a>

					</h2>
				</div>
			</div>
		</div>
	</div>
@else
	@section('content')

		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				<div class="row">
					<div class="col-lg-9 col-md-12">
						<liste-libre-{{$id_liste}}
								ref="liste_libre_{{$id_liste}}"

						:filtres_pour_fiche="filtres_pour_fiche"

						@if(super_admin() || mode_parametrage())
							:mode_parametrage=1
						@endif
						>
						</liste-libre-{{$id_liste}}>
					</div>

					<div class="col-lg-3 col-md-12">
						<div class="card mb-3 row">
							<div class="card-header">
								<h4>
									@traduction('interface.budget_insight.entites.titre')
								</h4>
							</div>
							<div class="card-body">
								@foreach($entites as $entite)
									<div class="row selectCompte"
										 :class="{budgetInsightCompteSelected: entite_selectionnee == {{$entite->id}}}"
										 id="selectEntite{{ $entite->id }}"
										 @click="budget_insight_selectionne_entite('{{ $entite->id }}')">
										<div class="col-12"><b>{{ $entite->nom }}</b></div>
									</div>
								@endforeach
							</div>
						</div>
						<div class="card mb-3 row">
							<div class="card-header">
								<h4>
									@traduction('interface.budget_insight.comptes.titre')<br/>
								</h4>
							</div>
							<div class="card-body">
								<div class="row selectCompte" v-for="compte in comptes" v-show="entite_selectionnee == compte.entite_id">
									<div class="col-md-11" :class="{budgetInsightCompteSelected: id_account_selectionne == compte.id}" @click="budget_insight_selectionne_compte_bancaire(compte.id)">
										<b>@{{ compte.original_name }}</b><br/>
										<div style="font-weight: 300; color: black; margin-bottom: 20px;">
										@traduction('interface.budget_insight.comptes.banque') : @{{ compte.banque }}<br/>
										@traduction('interface.budget_insight.comptes.solde') : @{{ compte.balance | montant }}<br/>
										@traduction('interface.budget_insight.comptes.derniere_synchro') : @{{ compte.last_update | datetime_relatif }}<br/>
										</div>
									</div>
									<div class="col-md-1" v-show="compte.state !== null">
										<a :href="'{{ URL::to('eden/budget_insight/mise_a_jour_connexion') }}/'+compte.synchro_id"><img src="{{ asset('eden/images/erreur.png') }}" width="20px" :title="compte.error" /></a>
									</div>
								</div>
							</div>
						</div>
						<div class="mb-3">

							<button class="btn btn-secondary col-12" @click="synchroniser"><i class="fas fa-sync-alt"></i>@traduction('interface.budget_insight.bouton_synchronisation')</button><br><br>

							<a
								href="https://webview.powens.com/connect?domain=easydeveloppement.biapi.pro&client_id={{ $client_id }}&redirect_uri={{route('budget_insight.index')}}"
								class="css_ajouter_element css__lien">

								<button class="btn btn-secondary col-12"><i class="fa fa-fw fa-plus-square"></i>@traduction('interface.budget_insight.bouton_ajouter_un_compte')</button>

							</a><br><br>
							
							<hr/><br/>
							
							<div v-show="budget_insight_nombre_transactions_selectionnees == 1 && $refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee] && $refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee].element.reporte_eden != 1" v-if=" $refs['liste_libre_{{$id_liste}}'] != undefined">
								<div v-if="$refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee] && $refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee].element.statut_eden == 2">
									<button class="btn btn-secondary col-12" @click="budget_insight_affiche_separation"><i class="fas fa-minus-square"></i> &laquo; @traduction('interface.budget_insight.bouton_de_rappocher') &raquo;</button><br><br>
								</div>
								<div v-else>
									<button class="btn btn-secondary col-12" data-toggle="modal" data-target="#modale_choix_type_rapprochement"><i class="fas fa-plus-square"></i> @traduction('interface.budget_insight.bouton_rappocher')</button><br><br>
									<button class="btn btn-secondary col-12" data-toggle="modal" data-target="#modale_choix_type_paiement"><i class="fas fa-plus-square"></i> @traduction('interface.budget_insight.bouton_enregistrer_paiement')</button><br><br>
									<button class="btn btn-secondary col-12" @click="budget_insight_operation_treso"><i class="fas fa-plus-square"></i> @traduction('interface.budget_insight.bouton_operation_treso')</button><br><br>
									<button class="btn btn-secondary col-12" @click="budget_insight_affiche_separation" v-if="$refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee] && $refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee].element.statut_eden == 1"><i class="fas fa-minus-square"></i> &laquo; @traduction('interface.budget_insight.bouton_de_rappocher') &raquo;</button><br><br>
								</div>
							</div>
								<div v-show="budget_insight_nombre_transactions_selectionnees >= 1 && (!transaction_selectionnee || ($refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee] && $refs['liste_libre_{{$id_liste}}'].liste.lignes[transaction_selectionnee].element.statut_eden != 2) )">
									<textarea name="commentaire" id="budget_insight_commentaire" cols="30" rows="6" placeholder="Veuillez saisir un commentaire pour reporter les transactions sélectionnées" v-model="budget_insight_commentaire" class="form-control" style="height: 90px;"></textarea>
									<button class="btn btn-secondary col-12" :disabled="budget_insight_commentaire === ''" @click="budget_insight_reporter"><i class="fas fa-plus-square"></i>@traduction('interface.budget_insight.bouton_reporter')</button><br><br>
								</div>
						</div>
					</div>
				</div>
				
				<!-- enregistrer paiement sur une facture -->
				@include('eden::rapprochement.factures')
				
				<!-- enregistrer paiement sur une facture achat -->
				@include('eden::rapprochement.factures_achat')
				
				<!-- enregistrer paiement sur une commande  -->
				@include('eden::rapprochement.commandes')
				
				<!-- enregistrer paiement sur un acompte  -->
				@include('eden::rapprochement.acomptes')

				<!-- enregistrer paiement sur un avoir  -->
				@include('eden::rapprochement.avoirs')

				<!-- enregistrer paiement sur un avoir achat  -->
				@include('eden::rapprochement.avoirs_achat')

				<!-- enregistrer paiement sur une note de frais  -->
				@include('eden::rapprochement.note_de_frais')
				
				<!-- rapprocher paiement -->
				@include('eden::rapprochement.paiements')
				
				<!-- rapprocher paiement -->
				@include('eden::rapprochement.bordereaux')
				
			</div>
		</div>

		<!-- Modale rapprochement -->
		<div class="modal fade" id="modale_choix_type_paiement" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">@traduction('interface.budget_insight.modale_rapprochement.titre')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div>
							<div class="row">
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.factures_clients')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_factures')
									</div>
								</div>
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_commandes">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.commandes_clients')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_commandes')
									</div>
								</div>
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_acomptes">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.acomptes_clients')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_acomptes')
									</div>
								</div>
							</div>
							<br/>
							<div class="row">
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_avoirs">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.avoirs_clients')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_avoirs')
									</div>
								</div>
							</div>
							<br/>
							<div class="row">
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_factures_achat">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.factures_fournisseurs')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_factures_fournisseurs')
									</div>
								</div>
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_avoirs_achat">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.avoirs_fournisseurs')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_avoirs_fournisseurs')
									</div>
								</div>
							</div>
							<br/>
							<div class="row">
								<div class="col-sm-4">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_paiement_note_de_frais">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_rapprochement.note_de_frais')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_paiement_note_de_frais')
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Modale enregistrer paiement -->
		<div class="modal fade" id="modale_choix_type_rapprochement" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">@traduction('interface.budget_insight.modale_paiement.titre')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div>
							<div class="row">
								<div class="col-sm-6">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_rapprocher">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_paiement.paiements')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_rapprocher_ligne_paiements')
									</div>
								</div>
								<div class="col-sm-6">
									<div class="css_option_choix_sur_formulaire" @click="budget_insight_rapprocher_bordereau">
										<span class="css_option_choix_sur_formulaire_titre">@traduction('interface.budget_insight.modale_paiement.bordereau')</span>
										@traduction('interface.budget_insight.modale_rapprochement.texte_enregistrement_rapprocher_ligne_bordereau')
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>



		<div class="modal fade" id="modal_separation_paiement" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog modal-lg" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">@traduction('interface.budget_insight.modale_separation_paiement.titre')</h5>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div style="color:red">
							<i class="fas fa-exclamation-triangle"></i>
							@traduction('interface.budget_insight.modale_separation_paiement.avertissement')<bR><br>
						</div>
						<div>
							<div class="row" v-for="paiement in paiements_a_separer">
								<div class="col-sm-6">@{{ paiement.titre }}</div>
								<div class="col-sm-3" style="text-align: right;">@{{ paiement.montant }} {!! maquette('devise_application_symbole') !!}</div>
								<div class="col-sm-3" style="text-align: right;"><a :href="'{{URL::to('/eden/document/')}}'+'/'+paiement.type_element+'/'+paiement.id_document" target="_blank">@traduction('interface.budget_insight.modale_separation_paiement.afficher_document')</a></div>
							</div><br/>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.budget_insight.modale_separation_paiement.bouton_fermer')</button>
						<button type="button" class="btn btn-primary" data-dismiss="modal" @click="budget_insight_separer()" >@traduction('interface.budget_insight.modale_separation_paiement.bouton_supprimer')</button>
					</div>
				</div>
			</div>
		</div>

	<div class="modal fade" id="modal_saisie_operation_treso" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">@traduction('interface.budget_insight.modale_saisie_operation.titre')</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<form action="#" method="post" class="css_form" id="formulaire_saisie_operation_treso" enctype="multipart/form-data">
						{!! formulaire('paiement') !!}
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal" >@traduction('interface.budget_insight.modale_saisie_operation.bouton_fermer')</button>
					<button type="button" class="btn btn-primary" @click="budget_insight_enregistrer_operation()">@traduction('interface.budget_insight.modale_saisie_operation.bouton_enregistrer')</button>
				</div>
			</div>
		</div>
	</div>
	


	@endsection

	@push('composants_vue')
        <script type="text/javascript" src="{{ asset('storage/composants/liste_libre_'.$id_liste.'.js') }}"></script>
	@endpush

	{{-- <script> --}}
	@section('donnees_pour_vuejs_data')
	
		transaction_selectionnee_texte: '',
		comptes: {!! $comptes !!},
		entites: {!! $entites !!},
		budget_insight_commentaire : '',
		entite_selectionnee : 0,
		id_account_selectionne : 0,

		paiements_a_separer: [],
		mode_de_paiement : {!! $mode_de_paiement !!},
		
		transaction_selectionnee:false,
		
		// les différentes fenêtres de rapprochement
		budget_insight_paiement_affichage: false,
		budget_insight_paiement_commandes_affichage: false,
		budget_insight_paiement_acomptes_affichage: false,
		budget_insight_paiement_avoirs_affichage: false,
		budget_insight_paiement_factures_achat_affichage: false,
		budget_insight_paiement_avoirs_achat_affichage: false,
		budget_insight_paiement_note_de_frais_affichage: false,

		budget_insight_rapprochement_affichage: false,
		budget_insight_rapprochement_bordereau_affichage: false,
		
		// les filtres
		budget_insight_recherche_paiement: '',
		budget_insight_recherche_rapprochement: '',
		
		filtres_rapprochement : {
			mode_de_paiement_choisi : 0,
			date_de_debut : null,
			date_de_fin : null
		},
		
		budget_insight_nombre_transactions_selectionnees: 0,
		
		transaction_id: 0,
		
		// pour la saisie d'un paiement à la volée
		paiement: {!! management('paiement')->modele_par_defaut() !!},
	@endsection

	@push('donnees_pour_vuejs_methods')


		

		/**
		 * 
		 * ???????????????
		 * 
		modal_previsualisation_fichier: function(fichier) {
			
			this.fichier = fichier;
			$('#modal_previsualisation_fichier').modal('show');
		},
		 */
		
		/**
		 * 
		 * Permet d'ouvrir la modale pour saisir une opération de tréso
		 * 
		 */
		budget_insight_operation_treso: function() {
			
			$('#modal_saisie_operation_treso').modal('show');
		},
		
		/**
		 * 
		 * Enregistre l'opération de tréso que l'on vient de saisir
		 * 
		 */
		budget_insight_enregistrer_operation: function() {
			
			$.post({

				url: "{{ route('budget_insight.saisie_operation_treso') }}",
				dataType: "json",
				data: {
					
					paiement: vue_instance.paiement,
				}
			}).done(async function(resultat) {
				
				// il n'y a pas d'erreur, du coup on actualise la liste
				if(resultat.erreur === false) {
					
					// on ferme la modale
					$('#modal_saisie_operation_treso').modal('hide');
					
					// on actualise la liste
					vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();

				}
				else {

					await alerte_eden(resultat.message);
				}
			});
		},

		/**
		 * 
		 * Filtres spécifiques à cette page, sélection du compte bancaire
		 * 
		 */
		budget_insight_selectionne_compte_bancaire: async function(id_account) {

			vue_instance.id_account_selectionne = id_account;

			await this.$nextTick();

			vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();
			
			this.change_fenetre_affichee(false);
		},

		/**
		 * 
		 * Filtres spécifiques à cette page, sélection de l'entité
		 * 
		 */
		budget_insight_selectionne_entite: async function (entite_id) {
			
			var compte_id = false;

			vue_instance.entite_selectionnee = entite_id;
			this.change_fenetre_affichee(false);

			var compte_id = null;

			// on sélectionne un compte
			this.comptes.forEach(function(compte) {
				if(compte_id == null && compte.entite_id == entite_id)
					compte_id = compte.id;
			})

            if(compte_id != null)
				vue_instance.budget_insight_selectionne_compte_bancaire(compte_id);
			else{

				await this.$nextTick(() => {
					this.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();
				});
			}
		},
		
		/**
		 * 
		 * Après une sélection de ligne, met à jour les informations nécessaires
		 * 
		 */
		budget_insight_maj_nombre_transactions_selectionnees: function() {
			
			this.budget_insight_nombre_transactions_selectionnees = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').length;
			
			this.total_ttc_a_rapprocher_acomptes = parseFloat(0);
			this.total_ttc_a_rapprocher_commandes = parseFloat(0);
			this.total_ttc_a_rapprocher_factures = parseFloat(0);
			this.total_ttc_a_rapprocher_factures_achat = parseFloat(0);
			
			this.total_ttc_a_rapprocher_paiements = parseFloat(0);
			this.total_ttc_a_rapprocher_bordereau = parseFloat(0);

			this.total_ttc_a_rapprocher_notes_de_frais = parseFloat(0);

			$('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').each(function() {
				
				var ligne = vue_instance.$refs['liste_libre_{{$id_liste}}'].liste.lignes[$(this).attr('index_ligne')];
				
				vue_instance.total_ttc_a_rapprocher_acomptes += parseFloat(ligne.element.a_rapprocher);
				vue_instance.total_ttc_a_rapprocher_commandes += parseFloat(ligne.element.a_rapprocher);
				vue_instance.total_ttc_a_rapprocher_factures += parseFloat(ligne.element.a_rapprocher);
				
				vue_instance.total_ttc_a_rapprocher_factures_achat -= parseFloat(ligne.element.a_rapprocher);

				vue_instance.total_ttc_a_rapprocher_notes_de_frais -= parseFloat(ligne.element.a_rapprocher);

				vue_instance.total_ttc_a_rapprocher_paiements += parseFloat(ligne.element.a_rapprocher);
				vue_instance.total_ttc_a_rapprocher_bordereau += parseFloat(ligne.element.a_rapprocher);
				
				// on met à jour le texte de la ligne en cours de rapprochement
				vue_instance.transaction_selectionnee_texte = ligne.element.original_wording;
				
				// ????? présaisie du formulaire de paiement je suppose
				vue_instance.paiement.date = ligne.element.date;
				
				if(ligne.element.a_rapprocher < 0) {
					
					vue_instance.paiement.montant_saisi = ligne.element.a_rapprocher * -1;
					vue_instance.paiement.type = 1;
				}
				else {
					
					vue_instance.paiement.montant_saisi = ligne.element.a_rapprocher;
					vue_instance.paiement.type = 0;
				}
				
				vue_instance.paiement.titre = ligne.element.original_wording;
				vue_instance.paiement.transaction_id = ligne.element.id;
				// Je commente car les data n'ont pas l'air bonnes en base sur les transactions, de cette manière on peut sélectionner le bon compte bancaire
				// vue_instance.paiement.compte_bancaire_id = ligne.element.id_account;
			});
			
			vue_instance.total_ttc_a_rapprocher_acomptes = Math.round(vue_instance.total_ttc_a_rapprocher_acomptes * 100) / 100;
			vue_instance.total_ttc_a_rapprocher_commandes = Math.round(vue_instance.total_ttc_a_rapprocher_commandes * 100) / 100;
			vue_instance.total_ttc_a_rapprocher_factures = Math.round(vue_instance.total_ttc_a_rapprocher_factures * 100) / 100;
			vue_instance.total_ttc_a_rapprocher_factures_achat = Math.round(vue_instance.total_ttc_a_rapprocher_factures_achat * 100) / 100;
			vue_instance.total_ttc_a_rapprocher_notes_de_frais = Math.round(vue_instance.total_ttc_a_rapprocher_notes_de_frais * 100) / 100;

			vue_instance.total_ttc_a_rapprocher_paiements = Math.round(vue_instance.total_ttc_a_rapprocher_paiements * 100) / 100;
			vue_instance.total_ttc_a_rapprocher_bordereau = Math.round(vue_instance.total_ttc_a_rapprocher_bordereau * 100) / 100;
			
			
			if ( this.budget_insight_nombre_transactions_selectionnees == 1 ) {

				this.transaction_selectionnee = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('index_ligne');

			} else {

				this.transaction_selectionnee = false;
			}
			
		},
		
		/**
		 *
		 * Reporte une ou plusieurs transactions
		 *
		 */
		budget_insight_reporter: function() {
			
			loading(true);
			
			var transactions = [];
			var commentaire = this.budget_insight_commentaire
			
			$('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').each(function() {
				
				transactions.push($(this).attr('element_id'));
			});
			
			
			$.post({

				url: "{{ route('budget_insight.reporter') }}",
				dataType: "json",
				data: {
					
					transactions: transactions,
					commentaire: commentaire,
				}
			}).done(function(factures) {

				vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();
				vue_instance.$refs['liste_libre_{{$id_liste}}'].deselectionner_toutes_les_lignes();
				vue_instance.budget_insight_commentaire = '';
				vue_instance.budget_insight_nombre_transactions_selectionnees = 0;
				vue_instance.transaction_selectionnee = false;
				loading(false);
				setTimeout(function() {

					$('[data-toggle="tooltip"]').tooltip();
				},1000);
			});
		},
		
		
		/**
		 * 
		 * Permet de dé rapprocher une opération
		 * 
		 */
		budget_insight_affiche_separation: function() {
			
			var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');
			
			loading(true);
			
			$.ajax({

				url: "{{ route('budget_insight.recupere_paiements') }}",
				dataType: "json",
				method: 'post',
				data: {transaction_id:transaction_id}
			}).done(function(paiements) {
				
				vue_instance.paiements_a_separer = paiements ;

				$('#modal_separation_paiement').modal();

				//123456
				
				loading(false);
			});
		},
		
		/**
		 * 
		 * Permet de dé rapprocher une opération
		 * 
		 */
		budget_insight_separer: function() {

			// on regarde si la ligne sélectionnée n'est pas déjà reportée ou rapprochée
			if($('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').length > 1) {

				toastr.error("Il n'est pas possible de dé rapprocher plus d'une ligne à la fois");
				return;
			}
			if($('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').length == 0) {

				toastr.error("Vous devez sélectionner au moins une ligne pour faire un dé rapprochement");
				return;
			}
			
			var transaction_id = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('element_id');
			var transaction_index = $('#liste_{{$id_liste}} .css_liste_ligne_selectionnee').attr('index_ligne');

			loading(true);
			
			$.ajax({

				url: "{{ route('budget_insight.separe_transaction') }}",
				dataType: "json",
				method: 'post',
				data: {transaction_id:transaction_id}
			}).done(async function(retour) {
				
				vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();
				
				$.each(retour.erreurs, async function(erreur, index) {
					
					await alerte_eden('Erreur (x' + index + ') : ' + erreur);
				});
				
				
				loading(false);
			});

			//console.log( vue_instance.lignes[vue_instance.transaction_selectionnee] );
		},
		
		/**
		 * 
		 * Change ce qui est actuellement affiché
		 * 
		 */
		change_fenetre_affichee: function(fenetre) {
			
			// on vide la recherche quoi qu'il en soit
			this.budget_insight_recherche_rapprochement = '';
			
			
			this.budget_insight_rapprochement_bordereau_affichage = false;
			this.budget_insight_rapprochement_affichage = false;
			
			this.budget_insight_paiement_affichage = false;
			this.budget_insight_paiement_commandes_affichage = false;
			this.budget_insight_paiement_acomptes_affichage = false;
			this.budget_insight_paiement_avoirs_affichage = false;
			this.budget_insight_paiement_factures_achat_affichage = false;
			this.budget_insight_paiement_avoirs_achat_affichage = false;
			this.budget_insight_paiement_note_de_frais_affichage = false;

			// on veut tout fermer
			if(fenetre === false) {
				
				return;
			}
			
			
			if(fenetre == 'rapprocher_bordereau') {
				
				this.budget_insight_rapprochement_bordereau_affichage = true;
			}
			if(fenetre == 'rapprocher_paiement') {
				
				this.budget_insight_rapprochement_affichage = true;
			}
			if(fenetre == 'paiement_facture') {
				
				this.budget_insight_paiement_affichage = true;
			}
			if(fenetre == 'paiement_commande') {
				
				this.budget_insight_paiement_commandes_affichage = true;
			}
			if(fenetre == 'paiement_acompte') {
				
				this.budget_insight_paiement_acomptes_affichage = true;
			}
			if(fenetre == 'paiement_avoir') {

				this.budget_insight_paiement_avoirs_affichage = true;
			}
			if(fenetre == 'paiement_factures_achat') {
				
				this.budget_insight_paiement_factures_achat_affichage = true;
			}
			if(fenetre == 'paiement_avoirs_achat') {

				this.budget_insight_paiement_avoirs_achat_affichage = true;
			}
			if(fenetre == 'paiement_note_de_frais') {

				this.budget_insight_paiement_note_de_frais_affichage = true;
			}
			
			return;
		},
		
		
		/**
		 * 
		 * Lance une synchro
		 * 
		 */
		synchroniser: function() {
			
			loading(true);

			$.ajax({

				url: "{{ route('budget_insight.synchro') }}",
				dataType: "json",
				method: 'get',
				data: this.$refs.liste_libre_{{$id_liste}}.parametres_liste
			}).done(async function(donnees) {

				if(donnees.success !== true) {

					await alerte_eden("{{ traduction('messages.js.budget_insight.erreur_synchro') }}");
					loading(false);
					return;
				}

				vue_instance.$refs['liste_libre_{{$id_liste}}'].actualisation_filtres();
				vue_instance.comptes = donnees.comptes;
				
				loading(false);
			}).fail(async function(jqXHR, textStatus, errorThrown ) {

				await alerte_eden("{{ traduction('messages.js.budget_insight.erreur_synchro') }}");
				
				loading(false);
			});
		},

		
		

	@endpush

	@push('donnees_pour_vuejs_created')

		var entite_id = null;
		var account_id = null;

		for(entite of this.entites){
			if(entite_id == null)
				entite_id = entite.id;
		}

		for(compte of this.comptes){
			if(account_id == null && compte.entite_id == entite_id)
				account_id = compte.id;
		}

		this.entite_selectionnee = entite_id;
		this.id_account_selectionne = account_id;
	@endpush

	@push('donnees_pour_vuejs_computed')

		filtres_pour_fiche : function(){
			return {
				entite_id : this.entite_selectionnee,
				id_account : this.id_account_selectionne,
			}
		},
	@endpush
	{{-- </script> --}}

	@section('scripts')

		<script type="text/javascript">
		
			$('body').on('click', '#liste_{{$id_liste}} tr', function() { 
			
				setTimeout(function() { 
				
					vue_instance.budget_insight_maj_nombre_transactions_selectionnees() 
				}, 250); 
			});
			
			$('document').ready(function(){
				
				vue_instance.budget_insight_paiement_recuperer();
				vue_instance.budget_insight_paiement_recuperer_commandes();
				vue_instance.budget_insight_paiement_recuperer_acomptes();
				vue_instance.budget_insight_paiement_recuperer_factures_achat();

				$('body').on('change', '.js_datepicker', function(e) {

					var date_de_debut = $('.js_date_de_debut').val();
					var date_de_fin = $('.js_date_de_fin').val();

					if(date_de_debut != "")
						date_de_debut = moment(date_de_debut, 'DD/MM/YYYY').format('YYYY-MM-DD');
					else
						date_de_debut = null;

					if(date_de_fin != "")
						date_de_fin = moment(date_de_fin, 'DD/MM/YYYY').format('YYYY-MM-DD');
					else
						date_de_fin = null;

					vue_instance.$data.filtres_rapprochement.date_de_debut = date_de_debut;
					vue_instance.$data.filtres_rapprochement.date_de_fin = date_de_fin;

					vue_instance.filtrer_rapprochements();
				})
			});
		
		</script>

	@endsection
@endif
