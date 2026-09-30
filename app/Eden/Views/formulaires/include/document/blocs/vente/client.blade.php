<div class="card mb-3">
	<div class="card-header @if(!isset($onglet)) js_fermeture_bloc @endif">
		<h4>
			<span v-html="traduction('document.blocs.client.titre')"></span>
			@if(!isset($onglet) && $management->existe())
				@if(fonctionnalite('gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document'))
					<span class="ml-2 css_toggle_card_panel">
						<span class="fa fa-chevron-up"></span>
					</span>
				@else
					<span class="ml-2 css_toggle_card_panel">
						<span class="fa fa-chevron-down"></span>
					</span>
				@endif
			@endif
		</h4>
	</div>
	<div class="card-body" @if(!isset($onglet) && $management->existe() && !fonctionnalite('gescom_ne_pas_plier_par_defaut_les_blocs_sur_saisie_document')) style="display: none;" @endif>
		<div class="row">
			<div class="col-sm-12">
				<!-- le client -->
				<div class="row">
					@if($management->champ_modifiable('client_id'))
						<div class="col-sm-12">
							<b>@traduction('champs_libres.facture_vente.client_id.nom')</b>
							@if(fonctionnalite('afficher_encours_saisie_documents') !== false)
								<div class="badge" :class="{'badge-success': client.encours == 0, 'badge-warning': client.encours >= 0}" v-show="document.client_id != 0 && document.client_id != null">Encours actuel : @{{ new Intl.NumberFormat('de-DE', { style: 'currency', currency: '{!! maquette('devise_application_iso') !!}' }).format(client.encours) }}</div>
							@endif
							{!! $management->champ('client_id')->cree() !!}
						</div>
					@else
						<div class="col-md-2"><b>{!! $management->champ('client_id')->nom_vue() !!}</b></div>
						<div class="col-md-4">{!! $management->champ('client_id')->cree_affichage() !!}</div>
					@endif
				</div>
				<!-- les contacts -->
				@if(fonctionnalite('lien_contacts_sur_documents'))
				<div class="row" v-show="document.client_id != null && document.client_id != undefined && document.client_id != ''">
					<div class="col-sm-12" {!! !$management->champ_modifiable('contacts_ids') ? 'v-if="document.contacts_ids.length !== 0"' : '' !!}>
						<b>@traduction('document.blocs.client.contacts_du_client_associes_au_document')</b>
					</div>

					@if($management->champ_modifiable('contacts_ids'))
						<div class="col-sm-12">
							{!! $management->champ('contacts_ids')->cree() !!}
						</div>
					@else
						<div class="col-md-4" v-if="document.contacts_ids.length !== 0">{!! $management->champ('contacts_ids')->cree_affichage() !!}</div>
						<div class="col-sm-12" v-if="document.contacts_ids.length === 0">
							<b>@traduction('document.blocs.client.aucun_contact_selectionne')</b>
						</div>

					@endif
				</div>
				@endif

				<!-- les paiements non rattache -->
				@if(fonctionnalite('afficher_paiements_non_rattache_sur_documents'))
					@if(!$management->existe())
						<div class="row" v-show="document.client_id != null && document.client_id != undefined && document.client_id != ''">
							<template v-if="client.paiements_non_rattache && client.paiements_non_rattache.length">
								<div class="col-sm-12">
									<b>@traduction('document.blocs.client.paiements_non_rattache_du_client')</b>
								</div>
								<div class="table-responsive">
									<table class="table table-bordered table-hover" width="100%" cellspacing="0">
										<thead>
										<tr>
											<td></td>
											<td>@traduction('document.blocs.client.date')</td>
											<td>@traduction('document.blocs.client.titre_paiement')</td>
											<td>@traduction('document.blocs.client.montant')</td>
											<td>@traduction('document.blocs.client.mode_de_paiement')</td>
										</tr>
										</thead>
										<tbody>
										<tr v-for="paiement_non_rattache in client.paiements_non_rattache">
											<td style="cursor:pointer;text-align:center;">
												<a @click="rattache_paiement_client(paiement_non_rattache)" v-if="paiement_non_rattache.selectionne != true">
													<i class="fas fa-arrow-alt-circle-right"></i>
												</a>

												<a @click="retirer_paiement_client(paiement_non_rattache)" v-if="paiement_non_rattache.selectionne == true">
													<i class="fas fa-times"></i>
												</a>
											</td>
											<td>@{{ paiement_non_rattache.date | date }}</td>
											<td>@{{ paiement_non_rattache.titre }}</td>
											<td>@{{ paiement_non_rattache.montant }}</td>
											<td>@{{ paiement_non_rattache.mode_paiement_nom }}</td>
										</tr>
										</tbody>
									</table>
								</div>
								<input type="hidden" name="paiement_rattache" v-model="paiement_document">
							</template>
						</div>
					@endif
				@endif
			</div>

		</div>
	</div>
</div>
