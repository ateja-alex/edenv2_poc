<div class="card mb-3 formulaire-libre-sur-fiche" v-if="modele_de_document_concerne_facturation_electronique">
	<div class="card-header js_fermeture_bloc">

		<h4 class="d-flex align-items-center">
			@traduction('module_sur_fiche.fiche.modele_de_document.modele_de_document_facturation_electronique.titre')
			@if(isset($afficher_par_defaut))
				@if($afficher_par_defaut === true)
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
	<div class="card-body" @if(isset($afficher_par_defaut) && $afficher_par_defaut === false) style="display: none;" @endif>
		<formulaire ref="formulaire_modele_de_document_facturation_electronique" nom_formulaire="modele_de_document_facturation_electronique"></formulaire>
	</div>
</div>

@push('donnees_pour_vuejs_computed')

	modele_de_document_concerne_facturation_electronique(){

		var selection = this.modele_de_document.type_element || [];

		var ids_selectionnes = (Array.isArray(selection) ? selection : Object.keys(selection)).map(Number);

		return Object.keys(this.ids_facturation_electronique_type_element)
			.some(type_element => ids_selectionnes.includes(Number(this.ids_facturation_electronique_type_element[type_element]))
				|| this.modele_de_document.type_element_autres == type_element);
	},
@endpush

@push('donnees_pour_vuejs_data')
	ids_facturation_electronique_type_element: {!! json_encode(\App\Eden\Models\Table_libre::whereIn('type_element', ['facture_vente', 'avoir_vente'])->pluck('id', 'type_element')) !!},
@endpush

@push('donnees_pour_vuejs_mounted')

	this.$on('formulaire_charger',(formulaire) => {

		if(formulaire == 'modele_de_document_facturation_electronique'){
			this.$refs.formulaire_modele_de_document_facturation_electronique.element = this.modele_de_document;
		}
	});

	this.$root.$on('trigger_enregistre_formulaire_fiche',() => {
		this.$refs.formulaire_modele_de_document_facturation_electronique.enregistrer();
	});
@endpush
