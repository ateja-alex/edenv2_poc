<a class="css_action_icon primaire fa fa-fw fa-ban" 
	href="{{ route('document.annule_reglement', [$management->_type_element, $management->modele->id]) }}"
	onclick="loading(true);"
	:title="traduction('document.actions.statut_non_regle.annuler_statut_reglee')"
	data-toggle="tooltip"></a>