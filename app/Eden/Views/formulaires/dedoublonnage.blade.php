<div class="dedoublonnage" v-if="table_libre.dedoublonnage == 1">
    <transition name="slide-fade-left">
        <div class="doublon_potentiel" v-if="doublons_potentiels.length > 0">
            <div class="affichage_doublon">
                <transition name="expand-panel-left" mode="out-in">
                    <div v-if="!affichage_doublons" @mouseenter="affichage_doublons = true">
                        <span v-html="doublons_potentiels.length" class="nombre_doublons"></span>
                        <i style="font-size: 14px;" class="fa fa-exclamation-triangle"></i>
                    </div>
                    <span v-else @mouseleave="affichage_doublons = false">
                        <span v-html="doublons_potentiels.length" class="nombre_doublons"></span>
                        <div class="titre"> 
                            <i class="fa fa-exclamation-triangle"></i>
                            @traduction('interface.formulaires.dedoublonnage.titre')
                        </div>
                        <div class="tableau_doublons">
                            <div v-for="doublon in doublons_potentiels" :key="doublon.id" v-html="doublon.lien"></div>
                        </div>
                    </span>
                </transition>
            </div>
        </div>
    </transition>
</div>


@push('donnees_pour_vuejs_data')
    doublons_potentiels: {},
    requete_recherche_doublon:null,
    affichage_doublons: false,
@endpush

@push('donnees_pour_vuejs_mounted')

    await this.$nextTick();
    if(this.table_libre.dedoublonnage == 1 && this.element.id == null){
        for(champ_libre of Object.values(this.champs_type_element).filter(c => c.recherche == 1)){
            this.$watch('element.'+champ_libre.nom_sql, () => {
                this.recherche_doublon_potentiel(champ_libre.nom_sql);
            });
        }
    }
@endpush

@push('donnees_pour_vuejs_methods')

    recherche_doublon_potentiel : function(nom_sql){

        if(this.requete_recherche_doublon !== null)
            this.requete_recherche_doublon.abort();

        var valeurs_recherches = {};

        for(champ_libre of Object.values(this.champs_type_element).filter(c => c.recherche == 1)){
            valeurs_recherches[champ_libre.nom_sql] = this.element[champ_libre.nom_sql];
        }

        this.requete_recherche_doublon = $.post({
            url: '/eden/recherche/dedoublonnage/'+this.type_element,
            dataType : 'json',
            data : {
                valeurs_recherches : valeurs_recherches,
            }
        }).done((doublons) => {
            this.doublons_potentiels = doublons;
            this.requete_recherche_doublon = null;
            if(this.doublons_potentiels.length > 0){
                this.affichage_doublons = true;
            }
        });
    },
@endpush
