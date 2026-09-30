<span class="css_action_icon primaire fa fa-fw fa-send" title="Envoyer la signature" @click="envoyer_signature" data-placement="left" data-toggle="tooltip"></span>

@push('donnees_pour_vuejs_methods')

    envoyer_signature : function(){

        $.ajax({
            url: '{{URL::to('eden/fiche/docusign_enveloppe')}}/'+this.docusign_enveloppe.id+'/envoyer_signature',
            dataType:'json',
        }).done((donnees) => {

            loading(false);
            if(donnees.retour !== true){
                toastr.error(donnees.retour);
                return;
            }

            toastr.success(this.$root.traduction('interface.docusign.envoi_signature_succes'));

            document.location = donnees.redirection;
        });
    },

@endpush