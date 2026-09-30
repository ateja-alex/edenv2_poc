<template v-if="type_element">
    <template v-if="notification_manuelle.type_notification == 2">
        <div class="row" v-if="modeles_emails_type_element.length > 0">
            <div class="col-sm-2">
                @traduction('formulaire.choix_modele_email.modele_email')
            </div>

            <div class="col-sm-4">
                <select name="modele_email" @change="modifier_contenu_email" v-model="notification_manuelle.modele_email">
                    <option value=0>{{ traduction('formulaire.choix_modele_email.sans_valeur') }}</option>
                    <option v-for="modele_email in modeles_emails_type_element" :value="modele_email.id">@{{modele_email.nom}}</option>
                </select>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                {!! management('notification_manuelle')->champ('sujet')->nom_vue() !!}
            </div>
            <div class="col-sm-10">
                <champ-publipostage :type_element="type_element"
                    :modele="notification_manuelle" nom_sql="sujet" ></champ-publipostage>
                <input type="hidden" name="sujet" :value="notification_manuelle.sujet">
            </div>
        </div>
        <div class="row">
            <div class="col-sm-2">
                {!! management('notification_manuelle')->champ('contenu_email')->nom_vue() !!}
            </div>
            <div class="col-sm-10">
                <champ-publipostage :type_element="type_element"
                    :modele="notification_manuelle" nom_sql="contenu_email" type_champ="textarea-wysiwyg-vue"></champ-publipostage>
                <input type="hidden" name="contenu_email" :value="notification_manuelle.contenu_email">
            </div>
        </div>
    </template>
    <template v-else>
        <div class="row">
            <div class="col-sm-2">
                {!! management('notification_manuelle')->champ('message_notification')->nom_vue() !!}
            </div>
            <div class="col-sm-10">
                <champ-publipostage :type_element="type_element"
                    :modele="notification_manuelle" nom_sql="message_notification" type_champ="textarea-wysiwyg-vue"></champ-publipostage>
                <input type="hidden" name="message_notification" :value="notification_manuelle.message_notification">
            </div>
        </div>
    </template>
</template>

@push('donnees_pour_vuejs_data')
    modeles_emails : {!! modele('modele_email')->get() !!},
    tables_libres : {!! \App\Eden\Models\Table_libre::get() !!},
@endpush

@push('donnees_pour_vuejs_computed')
    modeles_emails_type_element : function(){
        return this.modeles_emails.filter(m => m.type_element_id == this.notification_manuelle.type_element_id);
    },
    type_element : function(){
        return this.tables_libres.find(t => t.id == this.notification_manuelle.type_element_id)?.type_element ?? null;
    },
@endpush

@push('donnees_pour_vuejs_methods')

    modifier_contenu_email(){

        var modele_email = this.modeles_emails_type_element.find(m => m.id == this.notification_manuelle.modele_email);

        if(modele_email != null){
            this.notification_manuelle.contenu_email = modele_email.modele;
            this.notification_manuelle.sujet = modele_email.sujet_modele;
        }
        else{
            this.notification_manuelle.contenu_email = '';
            this.notification_manuelle.sujet = '';
        }

    },
@endpush