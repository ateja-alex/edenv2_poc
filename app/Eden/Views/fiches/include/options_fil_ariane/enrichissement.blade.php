<enrichissement :type_element="type_element" :element_id="parseInt(element_id)" @enregistrement="traitement_retour_enrichissement">
    <template v-slot:bouton="{parametrage_modale}">
        <span class="css_action_icon primaire" @click="parametrage_modale()" :title="traduction('composant.enrichissement')" data-toggle="tooltip">
            <svg style="margin-top: -2px;" fill="white"  viewBox="0 0 30 30" width="20" height="20"><path d="M13.95 6.805l.654 3.06c.593 2.773 2.759 4.939 5.532 5.532l3.06.654c1.024.219 1.024 1.68 0 1.899l-3.06.654c-2.773.593-4.939 2.759-5.532 5.532l-.654 3.06c-.219 1.024-1.68 1.024-1.899 0l-.654-3.06c-.593-2.773-2.759-4.939-5.532-5.532l-3.06-.654c-1.024-.219-1.024-1.68 0-1.899l3.06-.654c2.773-.593 4.939-2.759 5.532-5.532l.654-3.06C12.269 5.781 13.731 5.781 13.95 6.805zM23.641 2.525l.588 2.119c.152.547.58.975 1.127 1.127l2.119.588c.65.18.65 1.102 0 1.282l-2.119.588c-.547.152-.975.58-1.127 1.127l-.588 2.119c-.18.65-1.102.65-1.282 0l-.588-2.119c-.152-.547-.58-.975-1.127-1.127l-2.119-.588c-.65-.18-.65-1.102 0-1.282l2.119-.588c.547-.152.975-.58 1.127-1.127l.588-2.119C22.539 1.875 23.461 1.875 23.641 2.525z"/></svg>
        </span>
    </template>
</enrichissement>

@push('donnees_pour_vuejs_methods')

    traitement_retour_enrichissement : function(donnees){

        this.$set(this,'{{$type_element}}',donnees.element);

        if(this.$refs.formulaire_edition_element)
            this.$set(this.$refs.formulaire_edition_element.$refs.formulaire.$refs.formulaire,'{{$type_element}}',donnees.element);

        info(this.$root.traduction('interface.listes.element_enregistre_avec_succes'));
    },

@endpush