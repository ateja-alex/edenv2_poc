<div class="card mb-3">
	<div class="card-header js_fermeture_bloc" style="display:flex;">
		<h4>
			@traduction('module_sur_fiche.questionnaire.reponses.titre')
		</h4>
	    <div class="ml-auto" style="display: flex;gap: 15px;align-items: center;">
			<span class="css_pointer">
				@if($afficher_par_defaut === true)
					<span class="fa fa-chevron-up"></span>
				@else
					<span class="fa fa-chevron-down"></span>
				@endif
			</span>
		</div>
	</div>

	<div class="card-body" style="{{ isset($afficher_par_defaut) && $afficher_par_defaut === false ? 'display: none;' : '' }}">
		<questionnaire-reponses :questions="questions" :questionnaire_id="{{$id_element}}">
	</div>
</div>

@push('donnees_pour_vuejs_data')
	questions: {!! collect($questions) !!},
@endpush
