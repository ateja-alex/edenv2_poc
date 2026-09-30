<script>
const traduction_action = Vue.component('traduction-action', {
        template: `<div>
                <span data-toggle="tooltip" @click="gestion_traduction" data-placement="left" :data-original-title="tooltip_titre" class="css_ajouter_element ml-2">
                    <i aria-hidden="true" :class="'css_action_icon css_font_16 '+icone"></i>
                </span>
            </div>`,
        props:{
            icone: {
                type: String,
                default: 'fas fa-language'
            },
            tooltip_titre: {
                type: String,
                default: 'Traduction'
            },
            categorie : 0,
            index_traduction: {
                type: String,
                default: ''
            },
            champ: {
                type: String,
                default: ''
            },
            index_traduction_par_defaut:{
                type: String,
                default: ''
            },
        },
        methods: {
            gestion_traduction :function(){

                var modale = this.$root.$refs.traduction_modale;

                modale.index_traduction = this.index_traduction;
                modale.categorie = this.categorie;
                modale.champ = this.champ;
                modale.modele_traduction.index = this.index_traduction_par_defaut;
                modale.chargement_traduction();
            },
        },
    });
</script>