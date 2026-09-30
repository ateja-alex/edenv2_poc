<script>
    const filtre_liste_questionnaire = Vue.component('filtre-liste-questionnaire', {
        template: `<div>
                <div v-if="affichage">
                    @traduction('filtres.champ_liste_formatee.contenu')
                    <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
                </div>
                <div v-else class="css_liste_checkbox_popover">
                  <div class="form-check form-check-inline css_checkbox_popover" v-for="element in filtre.modele.options">
                    <label class="d-flex align-items-center" style="gap:5px;">

                      <input
                          class="form-check-input"
                          type="checkbox"
                          :value="element.id_valeur"
                          v-model="valeur_checkbox"
                          name="valeurs[]"
                      >
                      <div class="d-flex flex-column" v-html="element.format_affichage ? element.format_affichage : element.valeur"></div>

                    </label>
                  </div>
                </div>
            </div>
        `,
        props: {
            filtre : {
                type : Object,
                default: function(){
                    return {};
                }
            },
            valeurs : {
                type : Array,
                default: function(){
                    return [];
                }
            },
            affichage: {
                type : Boolean,
                default: false
            },
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed: {
            valeur_checkbox : {
                get(){
                    return this.valeurs;
                },
                set(valeur){
                    if(valeur.length == 0)
                        valeur = null;

                    this.changement_filtre(valeur);
                },
            },
            valeurs_selectionnes : function(){

                var valeurs = this.valeurs.map((x) => {return isNaN(x) ? x : parseInt(x)});

                return Object.values(this.filtre.modele.options).filter(x => valeurs.includes(x.id_valeur)).map((x) => {return x.valeur});
            },
        },
        mounted : function(){},
    });
</script>