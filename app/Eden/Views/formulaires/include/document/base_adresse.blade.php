<div class="card mb-3">
	<div class="card-header @if(!isset($onglet) && $management->existe()) js_fermeture_bloc @endif">
		<h4>
			<span>@traduction('document.blocs.{{ $type_adresse }}.titre')</span>

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

		<formulaire ref="formulaire_{{$type_adresse}}" :nom_formulaire="type_element + '_{{$type_adresse}}'" :form="false" :uniquement_champs_editables="{!! $management->champ_modifiable($type_adresse) ? 'false' : 'true'!!}"></formulaire>
	</div>
</div>

@push('donnees_pour_vuejs_mounted')

	this.$on('formulaire_charger',(nom_formulaire) => {

		if(nom_formulaire == this.type_element + '_{{$type_adresse}}')
			this.$set(this.$refs.formulaire_{{$type_adresse}},'element',this.document);
	});
@endpush