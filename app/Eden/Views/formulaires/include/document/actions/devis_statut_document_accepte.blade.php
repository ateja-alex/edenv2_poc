<span>

	<span class="css_action_icon primaire fa fa-fw fa-handshake" 
		@click="accepter_devis(type_element, document_id)"
		:title="traduction('document.actions.devis_statut_document_accepte.accepter')"
		data-toggle="tooltip"></a>
</span>

@push('donnees_pour_vuejs_data')
    type_element: "{{ $management->_type_element }}",
    document_id: {{ $management->modele->id }},
@endpush

@push('donnees_pour_vuejs_methods')

    accepter_devis: function(type_element, id){

		loading(true);

        $.get({
            url: 'eden/document/' + this.type_element + '/' + this.document_id + '/accepter',
            dataType: "json"
        }).done(async (retour) => {
            if(retour.retour !== true){
                await alerte_eden(retour.erreur);
				loading(false);
            } else {
                if(retour.redirection != undefined)
					window.location.href = retour.redirection;
				else {
					this.operations_document_post_modification(retour);
					loading(false);
				}
            }
        })
    },
@endpush