<div class="row" v-if="type_element">
    <div class="col-sm-2">
        @traduction('interface.notification_manuelle.destinataire.type_de_valeur')
    </div>
    <div class="col-sm-4">
        <select name="type_expediteur" v-model="notification_manuelle.type_expediteur" >
            <option value="compte_email_id" v-html="$root.traduction('champs_libres.notification_manuelle.compte_email_id.nom')"></option>
            <option value="utilisateur_expediteur_id" v-html="$root.traduction('champs_libres.notification_manuelle.utilisateur_expediteur_id.nom')"></option>
            <option value="champ" v-html="$root.traduction('interface.notification_manuelle.destinataire.champ')"></option>
        </select>
    </div>
    <template v-if="notification_manuelle.type_expediteur == 'champ'">
        <parametrage-lien-champ
                :lien_champ="notification_manuelle.lien_champ_expediteur"
                @changement_lien_champ="notification_manuelle.lien_champ_expediteur = $event"
                :type_element="type_element"
                :filtres_valeur_final="{
                    champs : [
                        {type_element_ajax : 'compte_email', type : 42},
                        {type_element_ajax : 'utilisateur', type : 42}
                    ]
                }"
                :valeur_unique="true"></parametrage-lien-champ>
    </template>
    <template v-else-if="notification_manuelle.type_expediteur == 'utilisateur_expediteur_id'">
        @champ('notification_manuelle','utilisateur_expediteur_id',2,4)
    </template>
    <template v-else>
        @champ('notification_manuelle','compte_email_id',2,4)
    </template>
</div>

<input type="hidden" name="lien_champ_expediteur" :value="notification_manuelle.type_expediteur == 'champ' ? notification_manuelle.lien_champ_expediteur : null">

@push('donnees_pour_vuejs_data')
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
@endpush

@push('donnees_pour_vuejs_computed')

    type_element : function(){
        return this.tables_libres.filter(table_libre => table_libre.id == this.notification_manuelle.type_element_id)[0]?.type_element ?? null;
    },

@endpush