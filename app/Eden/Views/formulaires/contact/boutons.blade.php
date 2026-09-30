<div class="conteneur_boutons_formulaire_contact" v-if="contact.id != undefined">

    <button type="button" class="css_action_icon bouton_formulaire_contact_prioritaire_activer fas fa-exclamation" @click="contact_definir_comme_prioritaire(1)" v-if="contact.contact_prioritaire == 0 || contact.contact_prioritaire == null" :title="$root.traduction('composant.liste_contacts.contact_prioritaire')"></button>
    <button type="button" class="css_action_icon bouton_formulaire_contact_prioritaire_desactiver fas fa-exclamation" @click="contact_definir_comme_prioritaire(0)" v-else :title="$root.traduction('composant.liste_contacts.annuler_contact_prioritaire')"></button>
    <button type="button" class="css_action_icon bouton_formulaire_contact_npai_activer" @click="contact_npai(1)" v-if="contact.npai == 0 || contact.npai == null" :title="$root.traduction('composant.liste_contacts.npai')">@traduction('composant.liste_contacts.npai')</button>
    <button type="button" class="css_action_icon bouton_formulaire_contact_npai_desactiver" @click="contact_npai(0)" v-else :title="$root.traduction('composant.liste_contacts.annuler_npai')">@traduction('composant.liste_contacts.npai')</button>
    @if(fonctionnalite('utiliser_extranet'))
        <button type="button" 
            @click="gestion_utilisateur_extranet()"
            class="css_action_icon bouton_formulaire_contact_extranet_activer fas fa-user-cog" :title="$root.traduction('composant.liste_contacts.gestion_utilisateur_extranet')"></button>
    @endif
</div>


@includeWhen(fonctionnalite('utiliser_extranet'),'eden::formulaires.contact.include.utilisateur_extranet')

@push('donnees_pour_vuejs_methods')

    contact_definir_comme_prioritaire: function(valeur) {

        loading(true);

        var url = "eden/element/contact/" + this.contact.id + "/enregistrer";

        // on enregistre la modification
        $.post({

            url: url,
            dataType: "json",
            method: 'POST',
            data: {contact_prioritaire: valeur}
        }).done((donnees) => {

            // On retire le loader
            loading(false);
            this.contact.contact_prioritaire = donnees.element.contact_prioritaire;
        });
    },

    contact_npai: function(valeur) {

        loading(true);

        var url = "eden/element/contact/" + this.contact.id + "/enregistrer";

        // on enregistre la modification
        $.post({

            url: url,
            dataType: "json",
            method: 'POST',
            data: {npai: valeur}
        }).done((donnees) => {

            // On retire le loader
            loading(false);
            this.contact.npai = donnees.element.npai;
        });
    },
@endpush