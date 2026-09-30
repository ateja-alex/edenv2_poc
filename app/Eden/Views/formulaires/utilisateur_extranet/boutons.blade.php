<div class="conteneur_boutons_formulaire_contact" v-if="utilisateur_extranet.id != undefined">
    <a type="button" class="css_action_icon fas fa-people-arrows" :href="url_usurpation_extranet" :title="$root.traduction('composant.liste_contacts.usurpation_extranet')"></a>
    <span class="css_action_icon fas fa-envelope-open-text" @click="renvoi_mail_inscription" :title="$root.traduction('interface.modales.renvoi_mail_inscription')" ></span>
</div>

@push('donnees_pour_vuejs_methods')
    renvoi_mail_inscription : function(){

        loading(true);

        $.post({
            url : 'eden/utilisateur_extranet/envoi_mail_inscription',
            dataType : 'json',
            data:{
                id : this.utilisateur_extranet.id
            }
        }).done(async (retour) => {

            loading(false);

            if(retour.retour !== true) {
                await erreur(retour.retour);
                return;
            }

            toastr.success(this.$root.traduction('messages.php.extranet.mail_renvoye'));
        });
    },

@endpush

@push('donnees_pour_vuejs_computed')

    url_usurpation_extranet : function(){

        var url = `/extranet/usurpation/${this.utilisateur_extranet.id}?retour_url=${window.location.href}`;

        if(this.$parent.options.contact_id_source > 0)
            url += `&contact_id_source=${this.$parent.options.contact_id_source}`;

        return url;
    },
@endpush