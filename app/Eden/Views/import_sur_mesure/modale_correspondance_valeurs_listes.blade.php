@push('modales')
<template v-if="affichage_modale_correspondance">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">@traduction('interface.import_sur_mesure.correspondance_valeurs')</h5>
                    </div>

                    <div class="modal-body css_form" v-show="correspondance_en_visionnage == correspondance.champ_import" v-for="correspondance in correspondances">

                        <div class="row" style="border-bottom: 1px solid lightgrey;padding: 10px;margin-bottom: 15px;">
                            <div class="col-sm-5" style="text-align:center;">
                                <h6>"@{{ correspondance.champ_import }}"</h6>
                            </div>
                            <div class="col-sm-2" style="text-align:center;font-size: 25px;">
                                <span><i class="fas fa-long-arrow-alt-right"></i></span>
                            </div>
                            <div class="col-sm-5" style="text-align:center;">
                                <span>@{{ nom_table(correspondance.element) }} > @{{ correspondance.champ_libre.nom }} (@{{correspondance.champ_libre.nom_sql}})</span>
                            </div>
                        </div>
                        <div class="row" v-for="valeur_import in correspondance.valeurs_import" :key="correspondance.champ_import+'_'+valeur_import" style="margin-bottom: 5px;">
                            <div class="col-sm-5" style="text-align:center;">
                                <span>@{{ valeur_import }}</span>
                            </div>
                            <div class="col-sm-2" style="text-align:center;font-size: 25px;">
                                <span><i class="fas fa-long-arrow-alt-right"></i></span>
                            </div>
                            <div class="col-sm-5" style="text-align:center;" v-if="affichage_filtres[correspondance.champ_import] && affichage_filtres[correspondance.champ_import][valeur_import]">
                                <component :key="correspondance.champ_import+'_'+valeur_import" :is="affichage_filtres[correspondance.champ_import][valeur_import]"></component>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" @click="affichage_modale_correspondance = false;" class="btn btn-default" >@traduction('interface.modales.fermer')</button>
                        <div class="btn btn-secondary" v-if="correspondances.length > 1">
                            <span style="cursor:pointer" v-if="ordre_correspondance_visionnage > 1" @click="correspondance_en_visionnage = correspondances[ordre_correspondance_visionnage-2].champ_import">
                                <i class="fas fa-arrow-circle-left"></i>
                            </span>
                            <span style="margin: 0 10px;">@{{ ordre_correspondance_visionnage }} / @{{ correspondances.length }}</span>
                            <span style="cursor:pointer" v-if="ordre_correspondance_visionnage < correspondances.length" @click="correspondance_en_visionnage = correspondances[ordre_correspondance_visionnage].champ_import">
                                <i class="fas fa-arrow-circle-right"></i>
                            </span>
                        </div>
                        <button type="button" v-if="ordre_correspondance_visionnage == correspondances.length" @click="valider_correspondances" class="btn btn-primary">@traduction('interface.import_sur_mesure.valider_correspondances')</button>
                    </div>

                </div>
            </div>
        </div>
    </transition>
</template>
@endpush

@push('donnees_pour_vuejs_data')
    correspondances: [],
    affichage_modale_correspondance : false,
    correspondance_en_visionnage: null,
    affichage_filtres: {},
@endpush

@push('donnees_pour_vuejs_methods')

    initialisation_component : function(correspondance,valeur_import){

        var champ_information = {};

        $.each(this.import_sur_mesure.champs,function(index,champ){
            if(champ.champ_import == correspondance.champ_import)
                champ_information = champ;
        });

        var data_composant = {};

        $.each(champ_information.correspondances_valeurs_liste,(index,data_correspondance) => {
            if(valeur_import == data_correspondance.valeur){
                data_composant = data_correspondance;
            }
        });

        if(this.affichage_filtres[correspondance.champ_import] == null)
            this.affichage_filtres[correspondance.champ_import] = {};

        var component = {
            template:correspondance.champ_erp,
            name: 'champ_correspondance_'+correspondance.champ_import+'_'+cle_import,
            methods:this.$options.methods,
            data : function(){
                return data_composant;
            }
        };

        this.$set(this.affichage_filtres[correspondance.champ_import],valeur_import,component);
    },


    valider_correspondances : async function(){

        await this.verifications_cles();
        this.affichage_modale_correspondance=false;
    },

@endpush

@push('donnees_pour_vuejs_computed')
    ordre_correspondance_visionnage : function(){

        var vue_instance = this;

        var ordre = 0;

        $.each(vue_instance.correspondances,function(index,correspondance){
            if(correspondance.champ_import == vue_instance.correspondance_en_visionnage){
                ordre = index + 1;
                return;
            }
        });

        return ordre;
    },
@endpush