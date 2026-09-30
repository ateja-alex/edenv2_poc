<a class="css_action_icon primaire fa fa-fw fa-dollar-sign"
	href="{{ route('document.valide_reglement', [$management->_type_element, $management->modele->id]) }}"
	onclick="loading(true);"
	:title="traduction('document.actions.statut_regle.passer_en_statut_regle')"
	data-toggle="tooltip"></a>