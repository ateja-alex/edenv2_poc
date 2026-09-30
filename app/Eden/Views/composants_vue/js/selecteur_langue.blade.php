<script>
    const selecteur_langue = Vue.component('selecteur-langue', {
        template: `<div class="authentification_choix_langue" v-if="langues_disponibles.length > 1">
                <div class="authentification_langue" @click="dropdown_choix_langue = !dropdown_choix_langue" v-clique_en_dehors="{ func: () => {dropdown_choix_langue = false}}">
                    <span :class="'authentification_drapeau fi-' + langue_selectionnee.code"></span>
                    <span v-text="langue_selectionnee.nom"></span>
                    <span :class="'fa fa-chevron-' + (dropdown_choix_langue ? 'up' : 'down')"></span>
                </div>
                <div :class="'authentification_conteneur_langues ' + (dropdown_choix_langue ? 'authentification_conteneur_langues_ouvert' : '')">
                    <div class="authentification_langue" @click="changement_langue(langue)" v-for="langue in langues_disponibles" v-if="langue_selectionnee.id !== langue.id">
                        <span :class="'authentification_drapeau fi-' + langue.code"></span>
                        <span v-text="langue.nom"></span>
                    </div>
                </div>
            </div>`,
        props: {

            langue_traduction_erp_defaut: {},
            langues_traduction_erp: [],
        },
        data: function(){
            return {
                langue_selectionnee: {},
                dropdown_choix_langue: false,
            }
        },
        methods : {

            changement_langue : async function(langue){
						
                let contenu_fichier_traductions = await fetch('/storage/traductions/langue_'+langue.code+'.json');
                contenu_fichier_traductions = await contenu_fichier_traductions.text();

                this.$set(this, 'langue_selectionnee', langue);
                this.$set(this, 'dropdown_choix_langue', false);
                this.$root.$emit('changement_langue_traduction', JSON.parse(contenu_fichier_traductions));
                localStorage.setItem('langue_traduction_erp_selectionnee', JSON.stringify(langue));
            },
        },
        computed: {
            langues_disponibles: function(){

                if(this.langues_traduction_erp === undefined) 
                    return [];
                
                return this.langues_traduction_erp.filter((langue) => langue.disponible_interface); 
            },
        },
        mounted : async function(){

            this.$set(this, 'langue_selectionnee',
                localStorage.getItem('langue_traduction_erp_selectionnee') !== null
                    ? JSON.parse(localStorage.getItem('langue_traduction_erp_selectionnee')) : this.langue_traduction_erp_defaut
            );

            if(this.langue_selectionnee.id !== this.langue_traduction_erp_defaut.id){
            
                let contenu_fichier_traductions = await fetch('/storage/traductions/langue_'+this.langue_selectionnee.code+'.json');
                contenu_fichier_traductions = await contenu_fichier_traductions.text();
                this.$root.$emit('changement_langue_traduction', JSON.parse(contenu_fichier_traductions));
            }
        },
    });
</script>
