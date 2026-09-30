<a v-if="ligne.element.recue != 1 && ligne.element.recu != 1" @click="reception_fractionnee(ligne.element)">
    <span class="btn btn-mini btn-xs btn-default css_icone_option_dans_liste">
        <span class="fas fa-columns" data-toggle="tooltip"
              :title="$root.traduction('interface.listes.reception_fractionnee')">
        </span>
    </span>
</a>

@push('modales')
    <template v-if="modale_reception_fractionnee">
        <transition name="modal">
            <div class="modal-mask">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">@traduction('interface.listes.reception_partielle')</h5>
                            <button type="button" class="close" @click="modale_reception_fractionnee = false">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <h4>@traduction('interface.listes.historique_reception')</h4>

                            <div class="row" v-for="ligne_bl_achat in lignes_bl_achat">
                                <div class="col-md-12">@traduction('interface.listes.br_du') @{{ ligne_bl_achat.bl_achat.date | date_relatif_sans_heure }}, @traduction('interface.listes.quantite_receptionnee') : @{{ ligne_bl_achat.quantite }}</div>
                            </div>
                            <div class="row" v-if="lignes_bl_achat.length == 0">
                                <div class="col-md-12">@traduction('interface.listes.aucune_reception_pour_cette_ligne')</div>
                            </div>
                            <br/>
                            <br/>
                            <br/>
                            <h4>@traduction('interface.listes.saisie_reception_partielle')</h4>
                            <div class="row">
                                <div class="col-md-3">@traduction('interface.listes.quantite_receptionnee')</div>
                                <div class="col-md-3"><input type="number" v-model="quantite" @wheel.prevent @keydown.up.prevent @keydown.down.prevent></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="btn btn-primary" @click="enregistrer_reception_fractionnee()">@traduction('interface.listes.enregistrer')</div>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')
    lignes_bl_achat: {},
    modale_reception_fractionnee:false,
    quantite : 0,
@endpush

@push('donnees_pour_vuejs_methods')

    /**
    *
    * Permet d'ouvrir la modale pour la réception d'une ligne de commande fournisseur (partiellement)
    *
    */
    reception_fractionnee: function(element){

        this.ligne_a_receptionner = element;

        // on va chercher l'historique des lignes
        loading(true);

        $.ajax({

            url: "eden/document/achat/commande/retourne_lignes_bl_achat/"+element.id,
            dataType: "json"
        }).done((donnees) => {

            loading(false);

            this.modale_reception_fractionnee = true;

            this.lignes_bl_achat = donnees;

            this.quantite = element.quantite - (this.lignes_bl_achat.reduce((acc, ligne) => acc + ligne.quantite, 0));
        });

    },

    /**
    *
    * Permet d'enregistrer la réception d'une ligne de commande fournisseur (partiellement)
    *
    */
    enregistrer_reception_fractionnee: function() {

        loading(true);

        // on enregistre toutes les nouvelles infos
        $.post({

            url: "eden/document/achat/commande/reception_partielle_ligne/"+this.ligne_a_receptionner.id,
            dataType: "json",
            method: "post",
            data: {
                quantite : this.quantite
            }
        }).done(async (donnees) => {

            loading(false);

            if(donnees.retour !== true) {

                await erreur(donnees.retour);
                return;
            }

            this.actualisation_filtres();
            this.modale_reception_fractionnee = false;
        });

    },

@endpush