<a class="css_action_icon primaire fa fa-fw fa-pause" 
	href="{{ route('document.devis.mise_en_attente', [($management->est_une_vente() ? 'vente' : 'achat'),$management->modele->id]) }}"
	onclick="loading(true);"
	:title="traduction('document.actions.devis_statut_document_attente.mise_en_attente')"
	data-toggle="tooltip"></a>