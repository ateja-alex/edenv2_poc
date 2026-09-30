<a @click="annulation_totale_commande_vente()" :title="traduction('document.actions.commande_vente_annuler.annuler')" data-toggle="tooltip">
	<span class="css_action_icon primaire fa fa-fw fa-times"></span>
</a>

@push('donnees_pour_vuejs_methods')

	annulation_totale_commande_vente: function(){

		this.modale_verification_annulation_totale = true;

	},

@endpush