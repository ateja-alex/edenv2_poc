<span>
	<a class="css_action_icon primaire fa fa-fw fa-truck"
	href="{{ route('document.expedier', [$management->_type_element, $management->modele->id]) }}"
	onclick="loading(true);"
	:title="traduction('document.actions.commande_vente_expediee.expedier')"
	data-toggle="tooltip"></a>
</span>
