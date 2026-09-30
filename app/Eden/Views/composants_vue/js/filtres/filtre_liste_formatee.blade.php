<script>
    const filtre_liste_formatee = Vue.component('filtre-liste-formatee', {
        template: `<div>
                <div v-if="affichage">
                    @traduction('filtres.champ_liste_formatee.contenu')
                    <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
                </div>
                <div v-else class="css_liste_checkbox_popover">
                  <div class="form-check form-check-inline css_checkbox_popover" v-for="element in valeurs_listes">
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
            }
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed: {
            valeurs_listes : function(){
                if(!this.$root.valeurs_listes_formatees[this.filtre.modele.liste_choix])
                    return {};

                var valeurs_listes = this.$root.valeurs_listes_formatees[this.filtre.modele.liste_choix];

                if(valeurs_listes.standard){

                    if(valeurs_listes[this.filtre.type_element+'.'+this.filtre.nom_sql])
                        valeurs_listes = valeurs_listes[this.filtre.type_element+'.'+this.filtre.nom_sql];
                    else
                        valeurs_listes = valeurs_listes.standard;
                }

                return valeurs_listes;
            },
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

                var valeurs_selectionnes = [];

                var valeurs = this.valeurs.map((x) => {return parseInt(x)});

                return Object.values(this.valeurs_listes).filter(x => valeurs.includes(x.id_valeur)).map((x) => {return x.valeur});
            },
        },
        mounted : function(){},
    });
</script>