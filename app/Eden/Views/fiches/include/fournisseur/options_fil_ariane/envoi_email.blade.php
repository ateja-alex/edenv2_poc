<i class="css_action_icon primaire fa fa-fw fa-envelope" @click="eden_envoyer_un_email()" title="{{ traduction('module_sur_fiche.fiche.fournisseur.envoyer_un_mail') }}" data-toggle="tooltip"></i>

@push('donnees_pour_vuejs_methods')

    eden_envoyer_un_email: function() {

        var parametres = {};

        parametres.type_element = 'fournisseur';
        parametres.id_element = vue_instance.fournisseur.id;
        parametres.fournisseur_id = vue_instance.fournisseur.id;

        this.$root.$emit('envoie_email',parametres);
    },

@endpush