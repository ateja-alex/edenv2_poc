<span>
	<a class="css_action_icon primaire fas fa-file-invoice"
	href="{{ route('document.vente.facture.indique_document_comme_envoye', [$management->modele->id]) }}"
	target="_blank"
	onclick="loading(true);"
	   :title="traduction('document.actions.facture_vente_statut_a_envoyer.indiquer_comme_envoyee')"
	   data-toggle="tooltip"></a>
</span>
