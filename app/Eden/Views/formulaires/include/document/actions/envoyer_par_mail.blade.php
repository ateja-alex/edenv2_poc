<i class="css_action_icon primaire fa fa-fw fa-envelope"
	@if($management->modele->valide == 1)
		@click="eden_envoyer_document_par_email()"
	@else
   		@click="alerte_avant_envoie_mail()"
	@endif
 :title="traduction('document.actions.envoyer_par_mail.envoyer')"
 data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    /**
	 *
	 * Envoyer le document par mail
	 *
	 */
	eden_envoyer_document_par_email : function() {

		var parametres = {};

		@if($management->existe())

			parametres.type_element = '{!! $management->_type_element !!}';
			parametres.id_element = '{!! $management->modele->id !!}';

			parametres.documents = [{type_element: '{{ $management->_type_element }}', id_element: {{ $management->modele->id }}}];

		@endif

		this.$root.$emit('envoie_email',parametres);
	},

    alerte_avant_envoie_mail : async function () {

        const type_document = ['facture_vente', 'commande_vente', 'facture_achat', 'commande_achat'];

        if(type_document.includes(this.type_element))
            await alerte_eden(this.traduction('messages.js.documents.alerte_envoi_une') + ' ' + this.nom_type_element + ' ' + this.traduction('messages.js.documents.proforma'), '{{ traduction('interface.alerte.attention') }}');
        else
            await alerte_eden(this.traduction('messages.js.documents.alerte_envoi_un') + ' ' + this.nom_type_element + ' ' + this.traduction('messages.js.documents.proforma'), '{{ traduction('interface.alerte.attention') }}');

        this.eden_envoyer_document_par_email();
    },

@endpush