<i class="css_action_icon primaire fa fa-envelope" @click="eden_envoyer_un_email()" data-placement="left" title="{{traduction('module_sur_fiche.fiche.lead.envoyer_email')}}" data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    eden_envoyer_un_email: function() {
        var parametres = {};
    
        parametres.type_element = 'lead';
        parametres.id_element = vue_instance.proposition_commerciale.lead_id;
        parametres.lead_id = vue_instance.proposition_commerciale.lead_id;
    
        this.$root.$emit('envoie_email',parametres);
    },

@endpush
