@if(!empty($email_facture_fournisseur->pieces_jointes))
	@foreach(json_decode($email_facture_fournisseur->pieces_jointes) as $id => $piece_jointe)
		<div class="card mb-3">
			<div class="card-header js_fermeture_bloc">
				<h4>{{ $piece_jointe->nom }}</h4>
			</div>
			<div class="card-body">
			
				<div class="row">
					<div class="col-md-6"><iframe src="storage/email_recus/{{ $piece_jointe->fichier }}" width="80%" height="800px"></iframe></div>
					<div class="col-md-6">
						@if(session()->has('erreur_facture_achat_'.$id))
							<div class="alert alert-danger">
								{!! session()->get('erreur_facture_achat_'.$id) !!}
							</div>
						@endif
						
						@if(empty($piece_jointe->facture_achat_id) && empty($piece_jointe->avoir_achat_id))
							<form class="css_form js_saisie_facture_fournisseur_via_pdf" action="{{ route('base_eden.fiche.index_post', ['email_facture_fournisseur', $email_facture_fournisseur->id, 'cree_facture_achat']) }}" method="post">

								<div class="row">
									<div class="col-md-4">
										@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.type_element')
									</div>
									<div class="col-md-8">
										<select name="type_element_pour_email_facture_fournisseur">
											<option value="facture_achat">{{traduction('module_sur_fiche.client.liste_des_campagnes_emailing.facture_achat')}}</option>
											<option value="avoir_achat">{{traduction('module_sur_fiche.client.liste_des_campagnes_emailing.avoir_achat')}}</option>
										</select>
									</div>
								</div>

								{!! formulaire('facture_achat', 'email_facture_fournisseur_', 'factures.facture_achat_'.$id) !!}
								
								<div class="row" v-show="factures.facture_achat_{{$id}}.dernieres_factures != undefined">
									<div class="col-sm-12 css_form_ligne_titre">
										@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.dernieres_factures_saisies')
									</div>
									<div class="col-sm-12">
										<div v-for="facture_saisie in factures.facture_achat_{{$id}}.dernieres_factures" v-html="facture_saisie.affiche_lien"><br/></div>
									</div>
								</div>
								<div class="row">
									<div class="col-sm-12 css_form_ligne_titre">
										@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.articles')
									</div>
								</div>
								
								<div class="row">
									<div class="col-md-6">@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.article')</div>
									<div class="col-md-3">@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.ht')</div>
									<div class="col-md-3">@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.tva')</div>
								</div>
								@for($i=0; $i<5; $i++)
									<div class="row">
										<div class="col-md-6">
											<select name="articles[{{$i}}][article_id]" class="js_article_id_{{$id}}">
												<option value="">{{traduction('module_sur_fiche.client.liste_des_campagnes_emailing.choisissez_article')}}</option>
												<?php
												$derniere_famille_id = false;
												?>
												@foreach(modele('article')->orderBy('famille_id')->orderBy('designation')->get() as $article)
												
													@if($article->famille_id != $derniere_famille_id)
														@if($derniere_famille_id !== false)
														</optgroup>
														@endif
														<optgroup label="{{ modele('famille', $article->famille_id)->nom }}">
														<?php
														$derniere_famille_id = $article->famille_id;
														?>
													@endif
													<option value="{{ $article->id }}">{{ $article->designation }}</option>
												@endforeach
												</optgroup>
											</select>
										</div>
										<div class="col-md-3"><input type="text" name="articles[{{$i}}][tarif]" class="js_article_tarif_ht" placeholder="HT" /></div>
										<div class="col-md-3">
											<select name="articles[{{$i}}][tva]" class="js_taux_tva_{{$id}}">
												<option value="0">0%</option>
												<option value="5.5">5.5%</option>
												<option value="10">10%</option>
												<option value="20" selected>20%</option>
											</select>
										</div>
									</div>
								@endfor
								
								<input type="hidden" name="piece_jointe" value="{{$id}}" />
								<span class="btn btn-primary" onClick="return valide_formulaire_saisie_facture_via_banette(this)">{{traduction('interface.modales.enregistrer')}}</span>
								
							</form>
						@else
							<div class="alert alert-success">
								@if(!empty($piece_jointe->facture_achat_id))
									@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.piece_jointe_traitee') <a href="{{ route('document.afficher', ['facture_achat', $piece_jointe->facture_achat_id]) }}">@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.lien_facture')</a>.
								@else
									@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.piece_jointe_traitee') <a href="{{ route('document.afficher', ['avoir_achat', $piece_jointe->avoir_achat_id]) }}">@traduction('module_sur_fiche.client.liste_des_campagnes_emailing.lien_avoir')</a>.
								@endif
							</div>
						@endif
					</div>
				</div>			
			</div>
		</div>

	@endforeach
@endif

@push('scripts')
	<script>
	
		function valide_formulaire_saisie_facture_via_banette(span) {
			
			var formulaire = $(span).closest('form');
			
			if(formulaire.find('.js_article_tarif_ht').eq(0).val() == '') {

				toastr.error('{{traduction('module_sur_fiche.client.liste_des_campagnes_emailing.tarif_obligatoire')}}');
				return false;
			}
			
			// on on submit
			formulaire.submit();
		}
	
	</script>
@endpush


@push('donnees_pour_vuejs_data')

	factures: {
		@for($i=0; $i<=19; $i++)
			facture_achat_{{$i}}: {!! management('facture_achat')->modele_par_defaut() !!}, 
		@endfor
	},
	
	derniers_ids_fournisseurs: {
		
		@for($i=0; $i<=19; $i++)
			facture_achat_{{$i}}: '',
		@endfor
	},
	
@endpush

@push('donnees_pour_vuejs_watch')
	factures:  {
		handler: function(factures) {
			
			@for($i=0; $i<=19; $i++)
				if(this.derniers_ids_fournisseurs.facture_achat_{{$i}} != factures.facture_achat_{{$i}}.fournisseur_id) {
					
					this.derniers_ids_fournisseurs.facture_achat_{{$i}} = factures.facture_achat_{{$i}}.fournisseur_id;
					
					loading(true);
					
					$.ajax({

						url: "{{ URL::to('eden/budget_insight/informations_pour_fournisseur') }}/"+factures.facture_achat_{{$i}}.fournisseur_id,
						dataType: "json",
						method: 'get'
					}).done(function(retour) {
						
						var automatisation = retour.automatisation;
						
						factures.facture_achat_{{$i}}.dernieres_factures = retour.dernieres_factures;
						
						if(automatisation === null) {
							
							loading(false);
							return;
						}
						
						// l'article
						if(automatisation.article_id) {
							
							$('.js_article_id_{{$i}}').val(automatisation.article_id);
						}
						
						// le taux de TVA
						if(automatisation.taux_tva) {
							
							$('.js_taux_tva_{{$i}}').val(automatisation.taux_tva);
						}
						
						loading(false);

					});
				}
			@endfor
		},
		deep: true,
	}, 
@endpush
