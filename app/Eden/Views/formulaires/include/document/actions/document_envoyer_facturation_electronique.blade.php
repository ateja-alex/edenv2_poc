<span>
	<i class="css_action_icon primaire fa fa-fw fa-file-export" @click="ouvrir_previsualisation_facturation_electronique()"
	   :title="traduction('document.actions.facture_vente_envoyer_facturation_electronique.envoyer')"
	   data-toggle="tooltip"></i>
</span>

@push('modales')

	<template v-if="modale_previsualisation_facturation_electronique">
		<transition name="modal" >
			<div class="modal-mask">
				<div class="modal-dialog modal-xl" style="height:92vh;max-width:95vw;">
					<div class="modal-content" style="height:100%;">

						<div class="modal-header" style="flex:none;">
							<h5 class="modal-title">@traduction('document.modale_previsualisation_facturation_electronique.titre')</h5>
						</div>

						<div v-if="erreurs_facturx_facturation_electronique.length" class="alert alert-danger" style="margin: 10px 15px 0; flex:none;">
							<strong>@traduction('document.modale_previsualisation_facturation_electronique.erreurs_titre')</strong>
							<ul style="margin-bottom:0;">
								<li v-for="erreur in erreurs_facturx_facturation_electronique" style="cursor:pointer;text-decoration:underline;" @click="aller_a_erreur_facturx(erreur.cle)">@{{ erreur.message }}</li>
							</ul>
						</div>

						<ul class="nav nav-tabs" style="margin: 0 15px; flex:none;">
							<li class="nav-item">
								<a class="nav-link" :class="{active: onglet_previsualisation_facturation_electronique == 'pdf'}" href="javascript:;" @click="onglet_previsualisation_facturation_electronique = 'pdf'" style="color:#495057 !important;">@traduction('document.modale_previsualisation_facturation_electronique.onglet_pdf')</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" :class="{active: onglet_previsualisation_facturation_electronique == 'lisible'}" href="javascript:;" @click="onglet_previsualisation_facturation_electronique = 'lisible'" style="color:#495057 !important;">@traduction('document.modale_previsualisation_facturation_electronique.onglet_lisible')</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" :class="{active: onglet_previsualisation_facturation_electronique == 'xml'}" href="javascript:;" @click="onglet_previsualisation_facturation_electronique = 'xml'" style="color:#495057 !important;">@traduction('document.modale_previsualisation_facturation_electronique.onglet_xml')</a>
							</li>
						</ul>

						<iframe v-if="onglet_previsualisation_facturation_electronique == 'pdf'" class="modal-body" :src="chemin_pdf_facturation_electronique" style="flex:1 1 auto;padding:unset;height:auto;max-height:unset;" width='100%' align='middle'></iframe>

						<div v-if="onglet_previsualisation_facturation_electronique == 'lisible'" class="modal-body" v-html="xml_facturx_lisible" style="flex:1 1 auto;height:auto;overflow:auto;max-height:unset;"></div>

						<pre v-if="onglet_previsualisation_facturation_electronique == 'xml'" class="modal-body" style="flex:1 1 auto;height:auto;overflow:auto;max-height:unset;">@{{ xml_facturx }}</pre>

						<div class="modal-footer" style="flex:none;">
							<button type="button" class="btn btn-secondary" @click="modale_previsualisation_facturation_electronique = false">@traduction('interface.modales.fermer')</button>
							<button type="button" class="btn btn-primary" :disabled="erreurs_facturx_facturation_electronique.length > 0" @click="confirmer_envoi_facturation_electronique()">@traduction('document.modale_previsualisation_facturation_electronique.confirmer_envoi')</button>
						</div>

					</div>
				</div>
			</div>
		</transition>
	</template>
@endpush

@push('donnees_pour_vuejs_data')
	modale_previsualisation_facturation_electronique : false,
	onglet_previsualisation_facturation_electronique : 'pdf',
	chemin_pdf_facturation_electronique : '',
	xml_facturx : '',
	xml_facturx_lisible : '',
	erreurs_facturx_facturation_electronique : [],
@endpush

@push('donnees_pour_vuejs_methods')
	ouvrir_previsualisation_facturation_electronique : async function() {

		loading(true);

		$.get({
			url : "{{ route('document.previsualisation_facturation_electronique', [$management->_type_element, $management->modele->id]) }}",
			dataType : 'json',
		}).done((donnees) => {

			loading(false);

			let erreur_modele_inactif = donnees.erreurs_facturx.find(erreur => erreur.cle == 'xml_facturx_absent');

			if(erreur_modele_inactif) {
				toastr.error(erreur_modele_inactif.message);
				return;
			}

			this.chemin_pdf_facturation_electronique = donnees.chemin_pdf;
			this.xml_facturx = donnees.xml_facturx;
			this.xml_facturx_lisible = donnees.xml_facturx_lisible;
			this.erreurs_facturx_facturation_electronique = donnees.erreurs_facturx;
			this.onglet_previsualisation_facturation_electronique = 'pdf';
			this.modale_previsualisation_facturation_electronique = true;

		}).fail(() => loading(false));
	},

	aller_a_erreur_facturx : function(cle) {

		this.onglet_previsualisation_facturation_electronique = 'lisible';

		this.$nextTick(() => {

			const cible = document.querySelector('[data-verification-facturx="'+cle+'"]');

			if(!cible) return;

			cible.scrollIntoView({block : 'center', behavior : 'smooth'});
			cible.classList.add('css_verification_facturx_clignote');

			setTimeout(() => cible.classList.remove('css_verification_facturx_clignote'), 1500);
		});
	},

	confirmer_envoi_facturation_electronique : async function() {

		if(this.erreurs_facturx_facturation_electronique.length) return false;

		if(!await confirm_eden()) return false;

		loading(true);

		$.post({
			url : "{{ route('base_eden.element.enregistrer', [$management->_type_element, $management->modele->id]) }}",
			data : { statut_facturation_electronique : 1 },
			dataType : 'json',
		}).done(async (donnees) => {

			loading(false);

			if(donnees.retour !== true) {
				await erreur(donnees.retour);
				return;
			}

			this.document.statut_facturation_electronique = donnees.element.statut_facturation_electronique;

			this.modale_previsualisation_facturation_electronique = false;

			info("{{ traduction('document.actions.facture_vente_envoyer_facturation_electronique.envoye') }}");

		}).fail(() => loading(false));
	},
@endpush
