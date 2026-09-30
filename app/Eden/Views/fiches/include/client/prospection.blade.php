<div class="card mb-3">
	<div class="card-header js_fermeture_bloc">
		
		<h4 class="d-flex align-items-center">
			<span>@traduction('module_sur_fiche.client.prospection')</span>
			@if($modification_possible)
				<span @click="enregistrer_prospection" class="css_ajouter_element ml-auto" data-toggle="tooltip" data-placement="top" title="{{traduction('interface.modales.enregistrer')}}">
					<i class="css_action_icon secondaire fa fa-fw fa-save"></i>
				</span>
			@endif
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

		<form id="formulaire_prospection" class="css_form">
			@include('eden::fiches.include.client.prospection_formulaire')
		</form>
	</div>
</div>

@push('donnees_pour_vuejs_methods')

	enregistrer_prospection: function() {
			
			var formulaire = $('#formulaire_prospection');
			
			$.ajax({
				method: 'POST',
				url: "{{ route('base_eden.element.enregistrer', ['client', $management_element->modele->id]) }}",
				dataType: "json",
				data: formulaire.serialize()
			}).done(async function(donnees) {
					
				if(donnees.retour !== true) {

					await erreur(donnees.retour);
					return;
				}

				info("{{traduction('module_sur_fiche.client.prospection.enregistrement_ok')}}");
			});
			
		},
@endpush

