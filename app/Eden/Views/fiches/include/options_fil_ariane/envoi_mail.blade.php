<i class="css_action_icon primaire fa fa-fw fa-envelope" @click="eden_envoyer_un_email()" :title="$root.traduction('module_sur_fiche.fiche.envoyer_mail')" data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    eden_envoyer_un_email: function() {

        var parametres = {};
        this.modale_choix_champs_email = false;

        parametres.type_element = this.$root.type_element;
        parametres.id_element = this.$root.element_id;
        parametres.client_id = this.$root.type_element == 'client' ? this.$root.element_id : (this.$root.element.client_id ?? undefined);

        @if(!empty($campagne_de_prospection_en_cours))
            parametres.campagne_de_prospection = {{$campagne_de_prospection_en_cours->id}};
        @endif

        this.$root.$emit('envoie_email',parametres);
    },
@endpush
