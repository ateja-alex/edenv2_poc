@if(!$verification_document_concerne_par_approbation)
	<a class="css_action_icon primaire fa fa-fw fa-hourglass" 
		href="{{ route('base_eden.element.demander_approbation_manuelle', [$management->_type_element, $management->modele->id]) }}"
		onclick="loading(true);"
		:title="traduction('document.actions.approbation_manuelle.demander')"
		data-toggle="tooltip"></a>
@else
	<a class="css_action_icon primaire fa fa-fw fa-hourglass" 
		:title="traduction('document.actions.approbation_manuelle.approbation_existante')"
		data-toggle="tooltip" style="color: #28a745"></a>
@endif