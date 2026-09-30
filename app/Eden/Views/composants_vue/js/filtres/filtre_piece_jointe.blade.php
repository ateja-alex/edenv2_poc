<script>
    const filtre_piece_jointe = Vue.component('filtre-piece-jointe', {
        template: `
        <div>
            <div v-if="affichage">
                <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_piece_jointe.'+presence)"></span>
                @traduction('filtres.champ_piece_jointe.affichage')
            </div>
            <div v-else class="css_liste_checkbox_popover">
              <div class="form-check form-check-inline css_checkbox_popover" v-for="element in ['sans','avec']">
                <label class="d-flex align-items-center" style="gap:5px">
                  <input
                      class="form-check-input"
                      type="radio"
                      :value="element"
                      v-model="presence"
                  />
                  <div class="d-flex flex-column" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_piece_jointe.'+element)"></div>
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
                type : String,
                defulat : null,
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
            presence : {
                get(){
                    return this.valeurs;
                },
                set(valeur){
                    this.changement_filtre(valeur);
                },
            }
        },
    });
</script>