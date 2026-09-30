Vue.component('affichage-famille', {
    template : `
        <div class="affichage_filtre_famille">
            <div>
                <div class="form-check form-check-inline css_checkbox_popover css_checkbox_famille" style="gap: 5px;">
                    <label class="filtre_famille_ligne_donnee">
                        <input
                                class="form-check-input"
                                type="checkbox"
                                :value="famille.id"
                                v-model="valeurs_checkbox"
                                name="valeurs[]"
                        >
                        <div class="d-flex flex-column" v-html="famille.nom"></div>
                    </label>
                    <i v-if="famille.enfants.length > 0" :class="'fas fa-chevron-'+(affichage_enfants ? 'up' : 'down')" @click="affichage_enfants = !affichage_enfants"></i>
                </div>
            </div>
            <template v-if="affichage_enfants && famille.enfants.length > 0">
                <div class="ml-4 selection_tous">
                    <a @click="selection_enfants()">@traduction('filtres.cree_filtre_pour_liste.champ_liste.tout_selectionner')</a>
                    <a @click="deselection_enfants()">@traduction('filtres.cree_filtre_pour_liste.champ_liste.tout_deselectionner')</a>
                </div>
                <div class="ml-4" v-for="famille_enfant in famille.enfants">
                    <affichage-famille ref="enfants" :key="famille_enfant.id" :famille="famille_enfant" :valeurs="valeurs"></affichage-famille>
                </div>
            </template>
        </div>
    `,
    props: {
        famille : {},
        valeurs : {
            type : [String, Array],
            default: function(){
                return [];
            }
        },
    },
    data: function(){
        return{
            affichage_enfants : true,
        };
    },
    methods : {
        changement_filtre : function(valeurs){

            if(valeurs.length == 0)
                valeurs = null;

            this.$parent.$emit('changement_filtre',valeurs);
        },
        selection_enfants : function(enfant = false,valeurs = null){

            if(valeurs == null)
                valeurs = structuredClone(this.valeurs_checkbox);

            for(famille_enfant of this.famille.enfants){
                if(!valeurs.includes(famille_enfant.id))
                    valeurs.push(famille_enfant.id);
            }

            if(this.$refs.enfants){
                for(enfant_ref of this.$refs.enfants){
                    valeurs = enfant_ref.selection_enfants(true,valeurs);
                }
            }

            if(!enfant)
                this.changement_filtre(valeurs);
            else
                return valeurs;
        },
        deselection_enfants : function(enfant = false,valeurs = null){

            if(valeurs == null)
                valeurs = structuredClone(this.valeurs_checkbox);

            for(famille_enfant of this.famille.enfants){
                if(valeurs.includes(famille_enfant.id))
                    valeurs.splice(valeurs.indexOf(famille_enfant.id),1);
            }

            if(this.$refs.enfants){
                for(enfant_ref of this.$refs.enfants){
                    valeurs = enfant_ref.deselection_enfants(true,valeurs);
                }
            }

            if(!enfant)
                this.changement_filtre(valeurs);
            else
                return valeurs;
        },
    },
    mounted : function(){
        this.$on('changement_filtre',(valeurs) => {
            this.$parent.$emit('changement_filtre',valeurs);
        });
    },
    computed : {
        valeurs_checkbox : {
            get(){

                if(!Array.isArray(this.valeurs))
                    return [];

                return this.valeurs.map((x) => {return parseInt(x)});
            },
            set(valeur){
                this.changement_filtre(valeur);
            },
        },
    },
});