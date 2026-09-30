@php
 	
	// @note frédéric 29/03/2023 je commente ça ci dessous, car ça bloque pour l'aana, et je ne sais pas à quoi ça sert
	// c'est bloquant car on ne peut pas cumuler une appro manuelle et une appro de validation (alors que je ne vois pas de raison de bloquer cela)
	// $workflow_approbation = modele('approbation_workflow')->where('type_element', $management->_type_element)->where('action', 3)->first();
	$workflow_approbation = null;
	
 	$workflow_approbation_validation = modele('approbation_workflow')->where('type_element', $management->_type_element)->where('action', 1)->first();
@endphp
@if($workflow_approbation === null)
	@if(!$verification_document_concerne_par_approbation)
		@if(!$verification_approbation)
			@if($management->verification_document_approbation_refuse())
				<a class="css_action_icon primaire fa fa-fw fa-hourglass-half js_valider_document"
					@click="validation_document" href="javascript:void(0)"
					:title="traduction('document.actions.valider.faire_demande_approbation')"
					data-toggle="tooltip"></a>
			@else
				<a class="css_action_icon primaire fa fa-fw fa-check js_valider_document"
					@click="validation_document" href="javascript:void(0)"
					:title="traduction('document.actions.valider.valider')"
					data-toggle="tooltip"></a>
			@endif
		@else
			@if($workflow_approbation_validation === null)
				<a class="css_action_icon primaire fa fa-fw fa-hourglass-half js_valider_document"
					@click="validation_document" href="javascript:void(0)"
					:title="traduction('document.actions.valider.faire_demande_approbation')"
					data-toggle="tooltip"></a>
			@else
				<a class="css_action_icon primaire fa fa-fw fa-check js_valider_document"
					@click="validation_document" href="javascript:void(0)"
					:title="traduction('document.actions.valider.valider')"
					data-toggle="tooltip"></a>
			@endif
		@endif
	@else
		@if(!$verification_demande_approbation_concerne)

			<a class="css_action_icon primaire fa fa-fw fa-hourglass"
			   		:title="traduction('document.actions.valider.demande_approbation_deja_faite')"
					data-toggle="tooltip" style="color: #28a745"></a>

		@else
			<a class="css_action_icon primaire fa fa-fw fa-hourglass-half js_liste_valider"
				onclick="loading(true);"
				:title="traduction('document.actions.valider.valider_approbation')"
				data-toggle="tooltip"
				element_id="{{ $management->modele->id }}"
				type_element="{{ $management->_type_element }}"
				action="1"
				approbation_id="0"></a>
		@endif
	@endif
@endif

@include('eden::formulaires.include.document.modale_previsualisation_pdf_pre_validation')

@push('donnees_pour_vuejs_methods')

	validation_document : async function(){

		loading(true);

		var vue_instance = this;

		@php
			$generer_document = fonctionnalite('type_document_generer_pdf');
		@endphp

		await this.enregistre_document_avec_verification();

		@if(isset($generer_document[$management->_type_element]) && $generer_document[$management->_type_element] == false)
			window.location.href = "{{ route('document.valider', [$management->_type_element, $management->modele->id]) }}";
		@else

			var fonctionnalite_previsualisation_pdf_validation = {!! fonctionnalite('gescom_visionnage_pdf_pre_validation') ? 'true' : 'false' !!};

			if(fonctionnalite_previsualisation_pdf_validation == true){

				$.ajax({
					url: "{{route('document.previsualisation_pdf',[$management->_type_element, $management->modele->id])}}",
					postData: 'json'
				}).done(function(chemin_pdf){

					vue_instance.chemin_pdf = chemin_pdf;

					vue_instance.modale_previsualisation_pdf = true;

					loading(false);
				});
			}
			else
				window.location.href = "{{ route('document.valider', [$management->_type_element, $management->modele->id]) }}";
		@endif

	},

@endpush