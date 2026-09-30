<?php

$formulaire_parametrable = App\Eden\Models\Formulaire::where('nom_formulaire', $module)->first();
?>

<div class="card mb-3 formulaire-libre-sur-fiche">
	<div class="card-header js_fermeture_bloc">

		<h4 class="d-flex align-items-center">
			@traduction('{{ $formulaire_parametrable->index_traduction }}','titre')
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
		<formulaire ref="formulaire_{{ $formulaire_parametrable->nom_formulaire }}" nom_formulaire="{{ $formulaire_parametrable->nom_formulaire }}"></formulaire>
	</div>
</div>

@push('donnees_pour_vuejs_mounted')

	this.$on('formulaire_charger',(formulaire) => {

		if(formulaire == '{{ $formulaire_parametrable->nom_formulaire }}'){
			this.$refs.formulaire_{{ $formulaire_parametrable->nom_formulaire }}.element = this.{{$formulaire_parametrable->type_element}};
		}
	});

	this.$root.$on('trigger_enregistre_formulaire_fiche',() => {
		this.$refs.formulaire_{{ $formulaire_parametrable->nom_formulaire }}.enregistrer();
	});
@endpush
