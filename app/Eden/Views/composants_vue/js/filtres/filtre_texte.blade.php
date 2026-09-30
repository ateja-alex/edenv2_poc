<script>
    const filtre_texte = Vue.component('filtre-texte', {
        template: `<div>
           <div v-if="affichage">
             <span v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_texte.'+variable)"></span>
             <span v-if="texte != null" v-html="texte"></span>
           </div>
           <div class="css_block_popover_standard" v-else>
              <div class="d-flex align-items-center">
                <span style="width: 40%">@traduction('filtres.cree_filtre_pour_liste.champ_texte.comparatif')</span>
                <select v-model="variable" class="ml-auto" style="width: 60%;margin: 5px 0px;height: 24px;border: 1px solid #e4e0e0;">
                  <option :value="variable_actuel" v-for="variable_actuel in variables" v-html="$root.traduction('filtres.cree_filtre_pour_liste.champ_texte.'+variable_actuel)"></option>
                </select>
              </div>
              <div class="d-flex align-items-center" v-if="!['vide','non_vide'].includes(variable)">
                <span style="width: 40%">@traduction('filtres.cree_filtre_pour_liste.champ_texte.mots_cles')</span>
                <input type="text" v-model="texte" style="width: 60%;margin: 5px 0px;height: 24px;border: 1px solid #e4e0e0;" />
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
                        variable : 'contient',
                        texte: null
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
                    'contient','egal_a','commence_par','vide','non_vide','ne_contient_pas','non_egal_a'
                ]
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',{id : this.filtre.id,valeurs : valeurs});
            },
        },
        computed: {
            variable : {
                get(){

                    if(this.variable_tmp)
                        return this.variable_tmp;

                    return this.valeurs.variable;
                },
                set(valeur){

                    if(!['vide','non_vide'].includes(valeur) && this.valeurs.texte == null) {
                        this.variable_tmp = valeur;

                        if(['vide','non_vide'].includes(this.valeurs.variable))
                            this.changement_filtre(null);
                    }
                    else {
                        this.variable_tmp = null;

                        this.changement_filtre({
                            variable: valeur,
                            texte: ['vide', 'non_vide'].includes(valeur) ? null : this.valeurs.texte,
                        });
                    }
                },
            },
            texte : {
                get(){
                    return this.valeurs.texte;
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
                            texte: valeur,
                        });

                        this.variable_tmp = null;
                    }
                },
            },
        }
    });
</script>