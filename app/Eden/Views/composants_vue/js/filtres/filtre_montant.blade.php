<script>
    const filtre_montant = Vue.component('filtre-montant', {
        template: `<div>
          <div v-if="affichage">
            <span class="variable" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_montant.'+variable)"></span>
            <span class="valeur" v-if="montant != null" v-html="montant"></span>
          </div>
          <div v-else class="css_block_popover_standard">
              <div class="d-flex align-items-center">
                <span style="width: 40%">@traduction('filtres.cree_filtre_pour_liste.champ_texte.comparatif')</span>
                <select v-model="variable" style="width: 60%;margin: 5px 0px;height: 24px;border: 1px solid #e4e0e0;">
                  <option v-for="variable_actuel in variables" :value="variable_actuel" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_montant.'+variable_actuel)"></option>
                </select>
              </div>
              <div class="d-flex align-items-center">
                <span style="width: 40%">@traduction('filtres.cree_filtre_pour_liste.champ_montant.montant')</span>
                <input type="text" v-model="montant" style="width: 60%;margin: 5px 0px;height: 24px;border: 1px solid #e4e0e0;" />
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
                type : Object,
                default: function(){
                    return {
                        variable : 'egal_a',
                        montant: null
                    };
                }
            },
            affichage: {
                type : Boolean,
                default: false
            }
        },
        data:function(){
            return {
                variable_tmp : null,
                variables : [
                    'egal_a','superieur','superieur_egal','inferieur','inferieur_egal'
                ]
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed : {
            variable : {
                get(){
                    if(this.variable_tmp)
                        return this.variable_tmp;

                    return this.valeurs.variable;
                },
                set(valeur){

                    if(this.valeurs.montant == null)
                        this.variable_tmp = valeur;
                    else {
                        this.variable_tmp = null;

                        this.changement_filtre({
                            variable: valeur,
                            montant: this.valeurs.montant,
                        });
                    }
                },
            },
            montant : {
                get(){
                    return this.valeurs.montant;
                },
                set(valeur){

                    if(valeur == '')
                        valeur = null;

                    if(valeur == null) {

                        this.variable_tmp = this.valeurs.variable;

                        this.changement_filtre(null);
                    }
                    else {
                        this.changement_filtre({
                            variable: this.variable_tmp ? this.variable_tmp : this.valeurs.variable,
                            montant: valeur,
                        });

                        this.variable_tmp = null;
                    }
                },
            },
        }
    });
</script>