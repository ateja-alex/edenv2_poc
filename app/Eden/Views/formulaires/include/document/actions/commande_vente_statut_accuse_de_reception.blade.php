<span>
	<a class="css_action_icon primaire fas fa-file-invoice"
	href="{{ route('document.vente.commande.accuse_de_reception_envoye', [$management->modele->id]) }}"
	onclick="loading(true);"
	   :title="traduction('accuse_de_reception', 'AR envoyé ? cliquez ici', false)"
	   data-toggle="tooltip"></a>
</span>
