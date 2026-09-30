<a class="css_action_icon primaire fa fa-fw fa-check-square"
		href="{{ route('document.vente.commande.commande_fournisseur_recue', [$management->modele->id]) }}"
		onclick="loading(true);"
		:title="traduction('document.actions.commande_vente_commande_fournisseur_recue.recevoir')"
		data-toggle="tooltip"></a>