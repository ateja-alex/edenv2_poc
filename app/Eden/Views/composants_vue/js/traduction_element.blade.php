<script>
const traduction_element = Vue.component('traduction-element', {
    template: `
            <span class="traduction_element" :style="'cursor:pointer;'+ (affichage_modifiable ? 'color:var(--background_navbar)' : '' )"  @click="gestion_traduction" @mouseover="affichage_modifiable = true" @mouseleave="affichage_modifiable = false" >
                <span v-html="traduction"></span>
                <i v-if="affichage_modifiable && traduction != '' && traduction != null" class="fas fa-pen"></i>
                <i v-else-if="traduction == '' || traduction == null" class="fas fa-pen"></i>
            </span>
       `,
   props: {
		index_traduction: {
            type: String,
            default: ''
        },
        champ: {
            type: String,
            default: null
        },
        parametres: {
            type: Array,
            default: function() {
                return [];
            }
        },
    },
    data:function(){
        return {
            affichage_modifiable: false,
        }
    },
    methods:{
        gestion_traduction :function(event){

            var vue_instance = this;

            event.stopPropagation();
            event.preventDefault();

            var modale = this.$root.$refs.traduction_modale;

            modale.index_traduction = this.index_traduction;
            modale.champ = this.champ;
            modale.chargement_traduction();
        },
    },
    computed: {

        traduction: function() {

            return this.$root.traduction(this.index_traduction,this.champ,this.parametres);
        },

    },
});
</script>