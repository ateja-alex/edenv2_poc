<div class="tooltip-vue-tache-boutons" v-if="tache.id && ((!tache.statut_participant && !tache.annulee) || modification_statut)">
    <template v-if="tache.parent_id && tache.parent_id > 0">
        <div>
            <button type="button" class="btn btn-primary css_btn_responsive bouton_dropdown_enregistrer"
                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-check"></i>
                <span v-text="$root.traduction('composant.tooltip_tache.accepter')"></span>
                <i class="fas fa-angle-down"></i>
            </button>
            <div class="dropdown-menu">
                <span @click="changer_statut(1)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.cet_evenement')"></span>
                <span @click="changer_statut(1,1)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.tous_les_evenements')"></span>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-danger css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-times"></i>
                <span v-text="$root.traduction('composant.tooltip_tache.refuser')"></span>
                <i class="fas fa-angle-down"></i>
            </button>
            <div class="dropdown-menu">
                <span @click="changer_statut(2)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.cet_evenement')"></span>
                <span @click="changer_statut(2,1)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.tous_les_evenements')"></span>
            </div>
        </div>
        <div>
            <button type="button" class="btn btn-secondary css_btn_responsive bouton_dropdown_enregistrer" data-toggle="dropdown"
                    aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-question"></i>
                <span v-text="$root.traduction('composant.tooltip_tache.provisoire')"></span>
                <i class="fas fa-angle-down"></i>
            </button>
            <div class="dropdown-menu">
                <span @click="changer_statut(3)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.cet_evenement')"></span>
                <span @click="changer_statut(3,1)" class="dropdown-item" v-text="$root.traduction('composant.tooltip_tache.tous_les_evenements')"></span>
            </div>
        </div>
    </template>
    <template v-else>
        <button type="button" @click="changer_statut(1)" class="btn btn-primary css_btn_responsive">
            <i class="fas fa-check"></i>
            <span v-text="$root.traduction('composant.tooltip_tache.accepter')"></span>
        </button>
        <button type="button" @click="changer_statut(2)" class="btn btn-danger css_btn_responsive">
            <i class="fas fa-times"></i>
            <span v-text="$root.traduction('composant.tooltip_tache.refuser')"></span>
        </button>
        <button type="button" @click="changer_statut(3)" class="btn btn-secondary css_btn_responsive">
            <i class="fas fa-question"></i>
            <span v-text="$root.traduction('composant.tooltip_tache.provisoire')"></span>
        </button>
    </template>
</div>
<div class="tooltip-vue-tache-statut-actuel" v-show="tache.id && tache.statut_participant && !modification_statut">
    <div v-if="tache.statut_participant === 1">
        <i class="fas fa-check icone_accepte"></i>
        <span v-text="$root.traduction('composant.tooltip_tache.accepte')"></span>
    </div>
    <div v-else-if="tache.statut_participant === 2">
        <i class="fas fa-times icone_refuse"></i>
        <span v-text="$root.traduction('composant.tooltip_tache.refuse')"></span>
    </div>
    <div v-else-if="tache.statut_participant === 3">
        <i class="fas fa-question icone_provisoire"></i>
        <span v-text="$root.traduction('composant.tooltip_tache.provisoire')"></span>
    </div>
    <span @click="modification_statut = true" class="tooltip-vue-tache-modifier-statut">Modifier</span>
</div>

@push('donnees_pour_vuejs_data')
    modification_statut: false,
@endpush

@push('donnees_pour_vuejs_watch')

    tache_selectionnee : function(nouvelle_valeur){

        this.$set(this, 'tache', this.tache_selectionnee ? this.tache_selectionnee : {});
    },
@endpush

@push('donnees_pour_vuejs_methods')

    changer_statut: function(statut, toute_la_serie = 0){

        loading(true);

        var donnees = {'statut_participant' : statut};

        if(toute_la_serie === 1)
            donnees = {...donnees, 'modifier_recurrence' : 2};

        var url = "eden/element/tache/"+this.tache.id+"/enregistrer";

        $.post({

            url: url,
            dataType: "json",
            method: 'POST',
            data: donnees
        }).done(async (donnees) => {

            // On retire le loader
            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            this.actualisation_affichage();
        });
    },
@endpush