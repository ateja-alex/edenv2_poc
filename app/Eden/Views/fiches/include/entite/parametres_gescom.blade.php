<div class="row">
	<div class="col-md-12">
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('module_sur_fiche.client.parametres_gescom.titre')
				</h4>
			</div>
			<div class="card-body">
				<form id="parametres_gestion_commerciale" class="css_form">
					<div class="row">
						<div class="col-sm-12 css_form_ligne_titre">@traduction('module_sur_fiche.client.parametres_gescom.cgv_cga')</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_devis_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_devis_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_commandes_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_commande_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_factures_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_facture_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_bl_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_bl_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_acomptes_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_acompte_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_avoirs_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_avoir_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_bons_retours_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_bon_retour_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cgv_bons_preparation_vente')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cgv_sur_bon_preparation_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_devis_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_devis_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_commandes_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_commande_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_factures_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_facture_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_bls_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_bl_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_acomptes_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_acompte_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-9">@traduction('module_sur_fiche.client.parametres_gescom.joindre_cga_avoirs_achat')</div>
						<div class="col-md-3">
							<select v-model="parametres[{{$management_element->modele->id}}].cga_sur_avoir_vente">
								<option value="0">{{traduction('module_sur_fiche.client.parametres_gescom.non')}}</option>
								<option value="1">{{traduction('module_sur_fiche.client.parametres_gescom.oui')}}</option>
							</select>
						</div>
					</div>

					<div class="row">
						<div class="col-sm-12 css_form_ligne_titre">@traduction('module_sur_fiche.client.parametres_gescom.numerotation_documents')</div>
					</div>
					
					@if(fonctionnalite('gescom_document')['devis_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.devis_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_devis_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['commande_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.commande_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_commande_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['bl_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.bl_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_bl_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['acompte_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.acompte_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_acompte_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['facture_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.facture_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_facture_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['avoir_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.avoir_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_avoir_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['bon_retour_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.br_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_bon_retour_vente" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['bon_preparation_vente'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.bon_preparation_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_bon_preparation_vente" />
							</div>
						</div>
					@endif
					
					@if(fonctionnalite('gescom_document')['devis_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.devis_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_devis_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['commande_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.commande_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_commande_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['bl_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.bl_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_bl_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['acompte_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.acompte_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_acompte_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['facture_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.facture_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_facture_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['avoir_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.avoir_achat')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_avoir_achat" />
							</div>
						</div>
					@endif
					@if(fonctionnalite('gescom_document')['bon_retour_achat'])
						<div class="row">
							<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.brf_vente')</div>
							<div class="col-md-6">
								<input type="text" v-model="parametres[{{$management_element->modele->id}}].numerotation_bon_retour_achat" />
							</div>
						</div>
					@endif
					
					<div class="row">
						<div class="col-md-6">@traduction('module_sur_fiche.client.parametres_gescom.compte_bancaire')</div>
						<div class="col-md-6">
							<select v-model="parametres[{{$management_element->modele->id}}].compte_bancaire_defaut">
								<option value="">{{traduction('module_sur_fiche.client.parametres_gescom.non_precise')}}</option>
								@foreach(modele('compte_bancaire')->get() as $compte_bancaire)
								
									<option value="{{ $compte_bancaire->id }}">{{ $compte_bancaire->nom }}</option>
								@endforeach
							</select>
						</div>
					</div>
					
					<div class="row">
						<div class="col-md-6"></div>
						<div class="col-md-6">
							<div class="d-flex flex-row justify-content-end">
								<div class="btn btn-primary" @click="enregistre_parametres()">{{traduction('interface.modales.enregistrer')}}</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>

@push('donnees_pour_vuejs_data')
	parametres: {!! $parametres !!},
@endpush

@push('donnees_pour_vuejs_methods')
	enregistre_parametres: function() {
		
		loading(true);
					
		// on enregistre les infos du champ libre
		$.ajax({
			
			url: "{{ route('base_eden.parametres_erp.enregistrer') }}",
			dataType: "json",
			type: "post",
			data: {parametres: vue_instance.parametres}
		}).done(async function(donnees) {
			
			if(donnees.retour !== true) {
				
				await erreur(donnees.retour);
				return;
			}
			
			loading(false);
		});
	},
	
@endpush