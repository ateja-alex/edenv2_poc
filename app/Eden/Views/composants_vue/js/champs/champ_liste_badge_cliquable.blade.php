<script>
const champ_liste_badge_cliquable = Vue.component('champ-liste-badge-cliquable', {
    template: /* html */ `
        <div>
            <span :class="'badge '+(modele[nom_sql] == 1 ? 'badge-success' : 'badge-default')" @click="changement_valeur()" 
                v-html="$root.traduction('champs_libres.'+type_element+'.'+nom_sql+'.nom')"></span>

            <select v-model="modele[nom_sql]" :name="name" style="display: none;">
                <option v-for="cle in [0,1]" :value="cle"></option>
            </select>
        </div>
    `,

    props: {
        modele: '',
        nom_sql: '',
        name: '',
        class_css_js: '',
        type_element: '',
        lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
    },

    methods : {

        changement_valeur : function(){
            if(!this.lecture_seule){
                this.modele[this.nom_sql] = this.modele[this.nom_sql] == 1 ? 0 : 1;
                this.$parent.$emit('changement_valeur',this.modele[this.nom_sql]);
            }
        }
    }
});
</script>