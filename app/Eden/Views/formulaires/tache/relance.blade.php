<div class="row">
    <div class="col-sm-2">
        @traduction('formulaire.taches.formulaire_guide.relance_par_mail')
    </div>
    <div class="col-sm-1">
        {!! management('tache')->champ('notification_email_active')->cree() !!}
    </div>

    <div class="col-sm-9">
        <div class="row" v-show="tache.notification_email_active">
            <div style="width: 50px;">
                {!! management('tache')->champ('notification_email_combien')->cree() !!}
            </div>
            <div class="col-sm-3">
                <select name="notification_email_unite" v-model="tache.notification_email_unite">
                    <option value="M" v-if="tache.notification_email_combien>1">{{ traduction('formulaire.taches.formulaire_guide.minutes') }}</option>
                    <option value="M" v-else>{{ traduction('formulaire.taches.formulaire_guide.minutes') }}</option>
                    <option value="H" v-if="tache.notification_email_combien>1">{{ traduction('formulaire.taches.formulaire_guide.heures') }}</option>
                    <option value="H" v-else>{{ traduction('formulaire.taches.formulaire_guide.heures') }}</option>
                    <option value="J" v-if="tache.notification_email_combien>1">{{ traduction('formulaire.taches.formulaire_guide.jours') }}</option>
                    <option value="J" v-else>{{ traduction('formulaire.taches.formulaire_guide.jours') }}</option>
                </select>
            </div>
            <span v-if="this.type_taches_a_gerer == 'rdv'">{{ traduction('formulaire.taches.formulaire_guide.avant_le_debut_du_rdv') }}</span>
            <span v-else>{{ traduction('formulaire.taches.formulaire_guide.avant_le_debut_de_la_tache') }}</span>
        </div>
    </div>
</div>
<div class="row">

     <div class="col-sm-2">
        @traduction('formulaire.taches.formulaire_guide.relance_par_notification')
    </div>
    <div class="col-sm-1">
        {!! management('tache')->champ('notification_visuelle_active')->cree() !!}
    </div>
    <div class="col-sm-9">
        <div class="row" v-show="tache.notification_visuelle_active">
            <div style="width: 50px;">
                {!! management('tache')->champ('notification_visuelle_combien')->cree() !!}
            </div>
            <div class="col-sm-3">
                <select name="notification_visuelle_unite" v-model="tache.notification_visuelle_unite">
                    <option value="M" v-if="tache.notification_visuelle_combien>1">{{ traduction('formulaire.taches.formulaire_guide.minutes') }}</option>
                    <option value="M" v-else>{{ traduction('formulaire.taches.formulaire_guide.minutes') }}</option>
                    <option value="H" v-if="tache.notification_visuelle_combien>1">{{ traduction('formulaire.taches.formulaire_guide.heures') }}</option>
                    <option value="H" v-else>{{ traduction('formulaire.taches.formulaire_guide.heures') }}</option>
                    <option value="J" v-if="tache.notification_visuelle_combien>1">{{ traduction('formulaire.taches.formulaire_guide.jours') }}</option>
                    <option value="J" v-else>{{ traduction('formulaire.taches.formulaire_guide.jours') }}</option>
                </select>
            </div>
            <span v-if="this.type_taches_a_gerer == 'rdv'">{{ traduction('formulaire.taches.formulaire_guide.avant_le_debut_du_rdv') }}</span>
            <span v-else>{{ traduction('formulaire.taches.formulaire_guide.avant_le_debut_de_la_tache') }}</span>
        </div>
    </div>
</div>