<div class="card mb-3">
	<div class="card-header">
		<h4>
			@if(!isset($pied_de_page))
				<span>@traduction('document.blocs.recap.titre')</span>
			@else
				<span>@traduction('document.blocs.recap.pied_de_page')</span>

			@endif
		</h4>
	</div>

	<div class="card-body">
		@if(!isset($pied_de_page))
			@if(strpos($management->_type_element, 'vente') !== false)
				@php $champ_tiers = 'client' @endphp
			@else
				@php $champ_tiers = 'fournisseur' @endphp
			@endif
			<!-- fournisseur -->
			<div class="row" v-if="{{ $champ_tiers }}.modele != null">
				<div class="col-sm-12">
					<div style="margin-top: 5px; margin-bottom: 5px; padding: 5px 15px; background: rgb(246, 245, 245); text-align: center;">
						{{ management($management->_type_element)->champ($champ_tiers.'_id')->modele->nom }} :
						<span v-html="{{ $champ_tiers }}.modele.affiche_lien"></span>
					</div>
				</div>
			</div>

			<!-- Adresse de facturation -->
			<div class="row" v-if="{{ $champ_tiers }}.adresses_facturation != null || (document.adresse_de_facturation_texte !== null && document.adresse_de_facturation_texte != '')">

				@if($management->management_fiche()->presence_module('adresse_de_facturation'))
					<!-- adresse saisie -->
						<div class="col-sm-6" v-if="document.adresse_de_facturation_texte !== null && document.adresse_de_facturation_texte != ''">
							<div style="margin-top: 5px; margin-bottom: 5px; padding: 5px 15px; background: rgb(246, 245, 245); text-align: center;">
								<b>{{ management($management->_type_element)->champ('adresse_de_facturation')->modele->nom }}</b></br>
								<span style="line-height: 15px; padding-top: 7px;" v-html="nl2br(document.adresse_de_facturation_texte)"></span>
							</div>
						</div>
					<!-- adresse sélectionnée -->
					<div class="col-sm-6" v-if="document.adresse_de_facturation_texte === null || document.adresse_de_facturation_texte == ''">
						<div style="margin-top: 5px; margin-bottom: 5px; padding: 5px 15px; background: rgb(246, 245, 245); text-align: center;">
							<b>{{ management($management->_type_element)->champ('adresse_de_facturation')->modele->nom }}</b>
							<template v-for="adresse_facturation in {{ $champ_tiers }}.adresses_facturation">
								<div style="line-height: 15px; padding-top: 7px;" v-show="adresse_facturation.id == document.adresse_de_facturation">
									<p v-show="adresse_facturation.societe != null" style="line-height: 15px;">@{{ adresse_facturation.societe }}<br/></p>
									<p v-show="adresse_facturation.nom != null" style="line-height: 15px;">@{{ adresse_facturation.prenom }} @{{ adresse_facturation.nom }}<br/></p>
									<p v-show="adresse_facturation.adresse != null" style="line-height: 15px;">@{{ adresse_facturation.adresse }}<br/></p>
									<p v-show="adresse_facturation.adresse_complement != null" style="line-height: 15px;">@{{ adresse_facturation.adresse_complement }}<br/></p>
									<p v-show="adresse_facturation.code_postal != null" style="line-height: 15px;">@{{ adresse_facturation.code_postal }} @{{ adresse_facturation.ville }}<br/></p>
								</div>
							</template>
						</div>
					</div>
				@endif


				@if($management->management_fiche()->presence_module('adresse_de_livraison'))


					<!-- Adresse de livraison -->
					<!-- adresse saisie -->
					<div class="col-sm-6" v-if="document.adresse_de_livraison_texte !== null && document.adresse_de_livraison_texte != ''">
						<div style="margin-top: 5px; margin-bottom: 5px; padding: 5px 15px; background: rgb(246, 245, 245); text-align: center;">
							<b>{{ management($management->_type_element)->champ('adresse_de_livraison')->modele->nom }}</b></br>
							<span style="line-height: 15px; padding-top: 7px;" v-html="nl2br(document.adresse_de_livraison_texte)"></span>
						</div>
					</div>

					<!-- adresse sélectionnée -->
					<div class="col-sm-6" v-if="document.adresse_de_livraison_texte === null || document.adresse_de_livraison_texte == ''">
						<div style="margin-top: 5px; margin-bottom: 5px; padding: 5px 15px; background: rgb(246, 245, 245); text-align: center;">
							<b>{{ management($management->_type_element)->champ('adresse_de_livraison')->modele->nom }}</b>
							<template v-for="adresse_livraison in {{ $champ_tiers }}.adresses_livraison">
								<div style="line-height: 15px; padding-top: 7px;" v-show="adresse_livraison.id == document.adresse_de_livraison">
									<p v-show="adresse_livraison.societe != null" style="line-height: 15px;">@{{ adresse_livraison.societe }}<br/></p>
									<p v-show="adresse_livraison.nom != null" style="line-height: 15px;">@{{ adresse_livraison.prenom }} @{{ adresse_livraison.nom }}<br/></p>
									<p v-show="adresse_livraison.adresse != null" style="line-height: 15px;">@{{ adresse_livraison.adresse }}<br/></p>
									<p v-show="adresse_livraison.adresse_complement != null" style="line-height: 15px;">@{{ adresse_livraison.adresse_complement }}<br/></p>
									<p v-show="adresse_livraison.code_postal != null" style="line-height: 15px;">@{{ adresse_livraison.code_postal }} @{{ adresse_livraison.ville }}<br/></p>
								</div>
							</template>
							@if($management->management_fiche()->presence_module('projet'))
							<template v-if="projet != undefined && projet !== null" v-for="adresse_livraison in projet.adresses_livraison" >
								<div style="line-height: 15px; padding-top: 7px;" v-show="adresse_livraison.id == document.adresse_de_livraison">
									<p v-show="adresse_livraison.societe != null" style="line-height: 15px;">@{{ adresse_livraison.societe }}<br/></p>
									<p v-show="adresse_livraison.nom != null" style="line-height: 15px;">@{{ adresse_livraison.prenom }} @{{ adresse_livraison.nom }}<br/></p>
									<p v-show="adresse_livraison.adresse != null" style="line-height: 15px;">@{{ adresse_livraison.adresse }}<br/></p>
									<p v-show="adresse_livraison.adresse_complement != null" style="line-height: 15px;">@{{ adresse_livraison.adresse_complement }}<br/></p>
									<p v-show="adresse_livraison.code_postal != null" style="line-height: 15px;">@{{ adresse_livraison.code_postal }} @{{ adresse_livraison.ville }}<br/></p>
								</div>
							</template>
							@endif
						</div>
					</div>


				@endif

			</div>
			<div class="row">
				<div class="col-md-12">
					<div id="liste_des_articles">
						@include('eden::formulaires.include.document.includes.tableau_des_articles', ['recapitulatif' => true])
					</div>
				</div>
			</div>


		@endif
		<div class="row">
			<!-- marge totale sur le document -->
			<div class="col-sm-6 recap_bloc">
				@if($fonctionnalite_colonnes['prix_achat'] === true && $fonctionnalite_colonnes['marge'] === true)
					<div :class="'alert ' +  (la_marge_est_elle_bonne_critique === false ? 
						'alert-danger recap_bloc_marge_critique' : la_marge_est_elle_bonne_critique === true && la_marge_est_elle_bonne_alerte === false ?
						'alert-warning recap_bloc_marge_mauvaise' : 'alert-success recap_bloc_marge_bonne')">
						<div class="recap_bloc_conteneur">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.prix_achat_total') :</span>
							<span class="recap_bloc_marge_montant">@{{totaux.somme_pa_articles | montant}} </span>
						</div>
						<div class="recap_bloc_conteneur">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_brute_montant') :</span>
							<span class="recap_bloc_marge_montant">@{{totaux.marge_brute_montant | montant}} </span>
						</div>
						<div class="recap_bloc_conteneur">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_nette_montant') :</span>
							<span class="recap_bloc_marge_montant">@{{totaux.marge_nette_montant | montant}} </span>
						</div>
						<div class="recap_bloc_conteneur">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_brute_pourcentage') :</span>
							<span class="recap_bloc_marge_montant">@{{totaux.marge_brute_pourcentage}} %</span>
						</div>
						<div class="recap_bloc_conteneur">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_nette_pourcentage') :</span>
							<span class="recap_bloc_marge_montant">@{{totaux.marge_nette_pourcentage}} %</span>
						</div>
						<div class="recap_bloc_conteneur" v-if="la_marge_est_elle_bonne_critique === false">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_inferieure_marge_mini')</span>
							<span class="recap_bloc_marge_montant">({{ montant(fonctionnalite('marge_mini_sur_documents_commerciaux'), 2) }} %) !</span>
						</div>
						<div class="recap_bloc_conteneur" v-if="la_marge_est_elle_bonne_critique === true && la_marge_est_elle_bonne_alerte === false">
							<span class="recap_bloc_marge_titre">@traduction('document.blocs.recap.marge_inferieure_marge_recommandee')</span>
							<span class="recap_bloc_marge_montant">({{ montant(fonctionnalite('marge_recommandee_sur_documents_commerciaux'), 2) }} %) !</span>
						</div>
					</div>
				@endif

				<template v-if="totaux.total_option && Object.values(totaux.total_option).length > 0">
					<div class="tableau_recap_options_titre">
						<h6>@traduction('document.recap.tableau_options')</h6>
					</div>

					<table class="tableau_recap_options">
						<tr>
							<td>
								@traduction('document.colonnes.option')
							</td>
							<td>
								@traduction('document.colonnes.total.titre')
							</td>
							<td>
								@traduction('document.colonnes.total_ttc.titre')
							</td>
						</tr>
						<tr v-for="(total, regroupement_id) in totaux.total_option" v-if="total.ht != 0">
							<td>@{{ articles_du_document.filter((article) => { return article.type_ligne == 'regroupement' && article.id ==regroupement_id})[0].nom  }}</td>
							<td>@{{ total.ht | montant }} </td>
							<td>@{{ total.ttc | montant }} </td>
						</tr>
					</table>
				</template>

                <!-- tableau de récap des TVA -->
                <table class="tableau_recap_tva">
					<div class="tableau_recap_tva_titre">
						<h6>@traduction('document.recap.tableau_tva')</h6>
					</div>

                    <tr>
                        <td class="tableau_recap_tva_titre_colonne">
                            @traduction('document.colonnes.taux')
                        </td>
                        <td class="tableau_recap_tva_titre_colonne">
                            @traduction('document.colonnes.total.titre')
                        </td>
                        <td class="tableau_recap_tva_titre_colonne">
                            @traduction('document.colonnes.tva.titre')
                        </td>
                    </tr>
                    <tr v-for="(taux, valeur) in tableau_de_tva_computed" v-if="taux.ht">
                        <td class="tableau_recap_tva_montant">@{{ parseFloat(valeur) }} %</td>
                        <td class="tableau_recap_tva_montant">@{{ taux.ht | montant }} </td>
                        <td class="tableau_recap_tva_montant">@{{ taux.tva | montant }} </td>
                    </tr>
                </table>
			</div>

			<!-- Totaux du document -->
			<div class="col-sm-6">

				<!-- totaux -->
				@if(fonctionnalite('frais_de_port_sur_documents_commerciaux') === true)
					<div class="row">
						<div class="col-8 col-sm-8 recap_frais_livraison_titre">
							<big>
								<span>@traduction('document.blocs.recap.frais_de_livraison')</span>
							</big>
						</div>
						<div class="col-4 col-sm-4 recap_frais_livraison_montant">
							<big>
								@{{ totaux_affichage.total_frais_livraison | montant }}
							</big>
						</div>
					</div>

					<div class="row">
						<div class="col-8 col-sm-8 recap_frais_livraison_saisie_titre">
							<big>
								<span>@traduction('document.blocs.recap.frais_de_livraison_a_saisir')</span>
							</big>
						</div>
						<div class="col-4 col-sm-4 recap_frais_livraison_saisie_montant">
							<champ-montant
								name="frais_de_port_saisie"
								:valeur_non_vide="true"
								:modele="document"
								nom_sql="frais_de_port_saisie">
							</champ-montant>
						</div>

					</div>
				@endif
				@if(fonctionnalite('recap_saisie_document_afficher_total_ht') !== false)
					<div class="row">
						<div class="col-8 col-sm-8 recap_total_ht_titre">
							<big>
								<span>@traduction('document.blocs.recap.total_ht')</span>
							</big>
						</div>
						<div class="col-4 col-sm-4 eco_contribution_inclu_bloc">
							<big>@{{ totaux_affichage.total_ht | montant }}</big>
							<span class="eco_contribution_inclu recap" v-if="totaux_affichage.eco_contribution_inclus > 0" v-html="traduction('document.eco_contribution_inclus',null,[$options.filters.montant(totaux_affichage.eco_contribution_inclus)])"></span>
						</div>
					</div>
					<div class="row" v-if="totaux_affichage.eco_contribution > 0">
						<div class="col-8 col-sm-8 recap_eco_contribution_titre"><big>
								<span>@traduction('document.blocs.recap.eco_contribution')</span>
							</big></div>
						<div class="col-4 col-sm-4 recap_eco_contribution_montant"><big>@{{ totaux_affichage.eco_contribution | montant }}</big></div>
					</div>
				@endif
				@if(fonctionnalite('recap_saisie_document_afficher_total_ttc') !== false)
				<div class="row">
					<div class="col-8 col-sm-8 recap_total_ttc_titre">
						<big>
							<span>@traduction('document.blocs.recap.total_ttc')</span>
						</big>
					</div>
					<div class="col-4 col-sm-4 recap_total_ttc_montant">
						<big>
							<span>@{{ totaux_affichage.total_ttc | montant }}</span>
						</big>
					</div>
				</div>
				@endif

				@if(fonctionnalite('gescom_document_remises_pied_de_page') !== false)

					<div class="row">
						<div class="col-12 col-sm-8 recap_remise_2_titre">
							<big>
								<span>@traduction('document.blocs.recap.remise')</span> {{ fonctionnalite('type_remise_globale_en_montant') }}
							</big>
						</div>
						<div class="col-12 col-sm-4 recap_remise_2_montant">
							<div style="display:flex;align-items:center;">
								<champ-montant :modele="document" name="remise_globale" @if(!$articles_modifiables) :lecture_seule="true" @endif 
									nom_sql="remise_globale" :valeur_non_vide="true" :modele="document" style="width:66%">
								</champ-montant>
								<select style="width:33%" @change="supprime_coupon_reduction();mise_a_jour_total_document_vue()" v-model="document.remise_globale_type" @if(!$articles_modifiables) disabled @endif name="remise_globale_type">
									<option value="1" v-text="traduction('document.recap.remise_globale.type_1')"></option>
									<option value="2" v-text="traduction('document.recap.remise_globale.type_2', null, ['{{ maquette('devise_application_symbole') }}'])"></option>
								</select>
							</div>
						</div>
					</div>
					<input type="hidden" :value="document.remise_globale_type" name="remise_globale_type">
				@endif
				@if(fonctionnalite('gescom_document_coupon_reduction') !== false)
					<div class="row">
						<div class="col-12 col-sm-8 recap_coupon_reduction_titre">
							<big>
								<span>@traduction('document.blocs.recap.coupon_reduction')</span>
							</big>
						</div>
						<div class="col-12 col-sm-4 recap_coupon_reduction_montant">
							<div style="display:flex;align-items:center;">
								<input type="text" v-model="coupon_reduction_code" name="input_coupon_reduc_document">
								<input type="hidden" :value="coupon_reduction.id" name="coupon_reduction">
								<i class="css_background_couleur_primaire fa fa-check" style="height: 34px;width:34px;text-align:center;line-height:34px;cursor:pointer" 
									v-if="!coupon_reduction.id" @click="verifie_coupon_reduction();">
								</i>
								<i class="fa fa-times" style="background-color:var(--danger);color:white;height: 34px;width:34px;text-align:center;line-height:34px;cursor:pointer" 
									v-else @click="supprime_coupon_reduction(true);">
								</i>
							</div>
						</div>
						<span class="recap_coupon_reduction_message" v-if="coupon_reduction_message.message != ''"
							:style="{'background-color' : coupon_reduction_message.succes ? '#6d9c3a' : '#ee7767'}">
							<i :class="'fas ' +  (coupon_reduction_message.succes ? 'fa-check-circle' : 'fa-times-circle')"></i>
							@{{ coupon_reduction_message.message }}
						</span>
					</div>
				@endif

				@if(fonctionnalite('ecart_gestion_ttc')[$management->_type_element] == true)
					<div class="row">
						<div class="col-12 col-sm-8 recap_remise_1_titre">
							<big>
								<span v-html="traduction('champs_libres.{{$management->_type_element}}.ecart_gestion_ttc.nom')"></span>
							</big>
						</div>
						<div class="col-12 col-sm-4 recap_remise_1_montant">
							<div class="input-group">
								<champ-montant
									:modele="document"
									:valeur_non_vide="true"
									name="ecart_gestion_ttc"
									@if(!$articles_modifiables)
										:lecture_seule="true"
									@endif
									nom_sql="ecart_gestion_ttc">
								</champ-montant>
								<div class="input-group-addon" style="border-left: 0px;">€</div>
							</div>
						</div>
					</div>
				@endif
				
				@if(fonctionnalite('recap_saisie_document_afficher_total_ht') !== false && fonctionnalite('gescom_document_remises_pied_de_page') !== false)

					<div class="row">
						<div class="col-8 col-sm-8 recap_total_ht_apres_remise_titre">
							<big>
								<span>@traduction('document.blocs.recap.total_ht_apres_remise')</span>
							</big>
						</div>
						<div class="col-4 col-sm-4 recap_total_ht_apres_remise_montant">
							<big>
								@{{ totaux_affichage.total_ht_apres_remise | montant }}
							</big>
						</div>
					</div>
				@endif
				@if(fonctionnalite('recap_saisie_document_afficher_total_ttc') !== false && fonctionnalite('gescom_document_remises_pied_de_page') !== false)
				<div class="row">
					<div class="col-8 col-sm-8 recap_total_ttc_apres_remise_titre">
						<big>
							<span>@traduction('document.blocs.recap.total_ttc_apres_remise')</span>
						</big>
					</div>
					<div class="col-4 col-sm-4 recap_total_ttc_apres_remise_montant">
						<big>@{{ totaux_affichage.total_ttc_apres_remise | montant }}</big>
					</div>
				</div>
				@endif

				@if($management->_type_element == 'acompte_vente')
                    @foreach([1,3,2] as $type_acompte)
                        <div class="row">
                            <div class="col-12 col-sm-8 recap_acompte_titre">
                                <big>
                                    <span>
                                        @if($type_acompte == 1)
                                            @traduction('document.blocs.recap.acompte')
                                        @elseif($type_acompte == 2)
                                            @traduction('document.blocs.recap.acompte_ttc')
                                        @elseif($type_acompte == 3)
                                            @traduction('document.blocs.recap.acompte_ht')
                                        @endif
                                    </span>
                                </big>
                            </div>
                            <div class="col-12 col-sm-4 recap_acompte_montant">
                                <div class="input-group">
                                    <input type="text"
                                       @if(!empty($management->modele))
                                           @if($management->modele->acompte_type == $type_acompte)
                                               value="{{ $management->modele->acompte }}"
                                           @else
                                               @if(in_array($management->modele->acompte_type, array_diff([1,3,2],[$type_acompte])))
                                                   disabled=""
                                               @endif
                                               value="0"
                                           @endif
                                       @else
                                           value="0"
                                       @endif
                                       name="acompte_{{$type_acompte}}" @change="on_change_acompte();">
                                        @if($type_acompte == 1)
                                            <div class="input-group-addon" style="border-left: 0px;">%</div>
                                        @else
                                            <div class="input-group-addon" style="border-left: 0px;">{{ maquette('devise_application_symbole') }}</div>
                                        @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
				@endif

				@if( strpos($management->_type_element, '_vente') !== false && fonctionnalite('gescom_document_cacher_totaux_sur_pdf') === true)
				<div class="row recap_cacher_totaux">
					<div class="col-md-12">
						<div class="form-check">
							<input class="form-check-input css_pointer" style="left: 20px;top: 3px;" type="checkbox" value="1" v-model="document.cacher_totaux_sur_pdf" name="cacher_totaux_sur_pdf" id="cacher_totaux_sur_pdf">
							<label class="form-check-label css_pointer" for="cacher_totaux_sur_pdf">

								<span>@traduction('document.blocs.recap.cacher_totaux_pdf')</span>
							</label>
						</div>
					</div>
				</div>
				@endif
			</div>
		</div>
		@include('eden::formulaires.include.document.vues_a_surcharger.informations_bas_de_recap')
	</div>
</div>

@push('donnees_pour_vuejs_data')

	coupon_reduction : {!! collect($coupon_reduction) !!},
	coupon_reduction_code : '{!! $coupon_reduction->code ?? '' !!}',
	coupon_reduction_message : {

		message : '{!! $coupon_reduction->message ?? '' !!}',
		succes : true,
	},
@endpush

@push('donnees_pour_vuejs_mounted')

    this.$on('maj_champ_montant',(donnees) => {

        if(!['frais_de_port_saisie','remise_globale','ecart_gestion_ttc'].includes(donnees.nom_sql))
            return;

		if(donnees.nom_sql == 'ecart_gestion_ttc'){
			var seuil_ecart_gestion_ttc = {!! fonctionnalite('seuil_ecart_gestion_ttc') !!};

			if(this.document.ecart_gestion_ttc > seuil_ecart_gestion_ttc){
				alerte_eden(this.$root.traduction('messages.js.ecart_gestion_ttc.trop_eleve',null,[this.$options.filters.montant(seuil_ecart_gestion_ttc)]));
				this.document.ecart_gestion_ttc = seuil_ecart_gestion_ttc;
			}
		}

		if(donnees.nom_sql === 'remise_globale')
			this.supprime_coupon_reduction();

        this.mise_a_jour_total_document_vue();
    });

	this.verifie_coupon_reduction();
@endpush

@push('donnees_pour_vuejs_methods')

	/**
	 *
	 * Mise à jour des zones de saisies des acomptes
	 *
	 */
	on_change_acompte : function() {

		// on initialise les champs à zéro, si l'input est vide
		let champs_vides = true;
		let types_acompte = [1, 2, 3];

		for (let type_acompte of types_acompte) {
			$(`[name=acompte_${type_acompte}]`).attr('disabled', '');

			if($(`[name=acompte_${type_acompte}]`).val() == "")
				$(`[name=acompte_${type_acompte}]`).val(0)

			if($(`[name=acompte_${type_acompte}]`).val() != 0) {
				$(`[name=acompte_${type_acompte}]`).removeAttr('disabled');
				champs_vides = false;
			}
		}

		if (champs_vides) {
			for (let type_acompte of types_acompte) {
				$(`[name=acompte_${type_acompte}]`).removeAttr('disabled');
			}
		}
	},

	/**
	 *
	 * Retourne la marge au fondction du prix achat et du tarif
	 *
	 */

	calcule_marge : function(prix_achat, tarif) {

		return parseInt(tarif) - parseInt(prix_achat)
	},

@endpush

@push('donnees_pour_vuejs_methods')

	verifie_coupon_reduction : function(){

		if(!this.coupon_reduction_code)
			return;

		donnees = {
			coupon: this.coupon_reduction_code,
			document: this.document,
			articles: this.articles_du_document,
		}

		// On fait l'appel pour vérifier que le coupon de réduction est bien valide
		$.ajax({

			url: "{{ route('coupon_reduction.calcule') }}",
			dataType: "json",
			method: "post",
			data: donnees
		}).done((retour) => {

			if(retour.succes == true) {

				this.$set(this, 'coupon_reduction', retour.coupon_reduction);
				
				// on renseigne la valeur du coupon réduction
				this.$set(this.document, 'remise_globale_type', retour.coupon_reduction.type_de_reduction == 1 ? 2 : 1);
				this.$set(this.document, 'remise_globale', parseFloat(retour.coupon_reduction.valeur));
			} else {

				this.$set(this.document, 'remise_globale_type', 1);
				this.$set(this.document, 'remise_globale', 0);
			}

			this.$set(this.coupon_reduction_message, 'message', retour.message);
			this.$set(this.coupon_reduction_message, 'succes', retour.succes);

			this.mise_a_jour_total_document_vue();
		});
	},

	supprime_coupon_reduction(supprimer_remise_globale = false) {

		this.$set(this, 'coupon_reduction', {});
		this.$set(this, 'coupon_reduction_code', '');
		this.$set(this.coupon_reduction_message, 'message', '');

		if(supprimer_remise_globale){

			this.$set(this.document, 'remise_globale', 0);
			this.$set(this.document, 'remise_globale_type', 1);
		}

		this.mise_a_jour_total_document_vue();
	},
@endpush
