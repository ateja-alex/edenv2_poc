<script>
    const filtre_liste_libre = Vue.component('filtre-liste-libre', {
        template: `<div>
          <div v-if="affichage">
            @traduction('filtres.champ_liste_libre.contenu')
            <span class="valeur" v-html="valeurs_selectionnes.join(', ')"></span>
          </div>
          <div class="filtre_liste_libre" ref="filtre_liste_libre" v-else>
              <div class="d-flex align-items-center justify-content-between col-12 my-2" v-if="overflow">
                  <span>@traduction('filtres.cree_filtre_pour_liste.champ_recherche_element.recherche')</span>
                  <input v-model="recherche" class="mx-1" type="text" style="height: 24px; border: 1px solid #e4e0e0;">
              </div>
              <div v-if="valeurs_listes.sans_categorie != undefined"
                   v-for="element in $root.affichage_valeur_liste_libre(valeurs_filtres_liaisons,valeurs_listes.sans_categorie,valeurs_listes.liaisons,filtre.type_element,filtre.nom_sql)"
                   class="form-check form-check-inline css_checkbox_popover css_checkbox_utilisateur js_utilisateurs_eden" >
                <label class="d-flex align-items-center" style="gap:5px;">

                  <input
                      class="form-check-input filtre_sur_liste_liste_libre"
                      type="checkbox"
                      :value="element.id_valeur"
                      v-model="valeur_checkbox"
                      name="valeurs[]"
                  >
                  <div class="d-flex flex-column" v-html="element.valeur"></div>
                </label>
              </div>
              <template v-if="valeurs_listes.categorie != undefined && valeurs_listes.categorie">
                <template v-for="(listes,nom_categorie) in valeurs_listes" v-if="!['categorie','sans_categorie','liaisons'].includes(nom_categorie)">
                  <div class="css_sous_titre_filtre_utilisateurs js_sous_titre_filtre_utilisateurs"
                       @click="onglets_non_affiches.includes(nom_categorie) ? onglets_non_affiches.splice(onglets_non_affiches.indexOf(nom_categorie),1) : onglets_non_affiches.push(nom_categorie)">
                    <span v-html="nom_categorie"></span>
                    <span class="css_arrow_down"><i class="fas fa-angle-down"></i></span>
                  </div>
                  <div
                      v-for="element in $root.affichage_valeur_liste_libre(valeurs_filtres_liaisons,listes,valeurs_listes.liaisons,filtre.type_element,filtre.nom_sql)"
                      class="'form-check form-check-inline css_checkbox_popover css_checkbox_utilisateur" v-show="!onglets_non_affiches.includes(nom_categorie)">
                    <label class="d-flex align-items-center" style="gap:5px;">

                      <input
                          class="form-check-input filtre_sur_liste_liste_libre"
                          type="checkbox"
                          :value="element.id_valeur"
                          v-model="valeur_checkbox"
                          name="valeurs[]"
                      >
                      <div class="d-flex flex-column" v-html="element.valeur">
                      </div>
                    </label>
                  </div>
                </template>
              </template>
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
        data : function(){
            return {
                onglets_non_affiches:[],
                recherche:'',
                isMounted: false
            };
        },
        methods : {
            changement_filtre : function(valeurs){
                this.$parent.$emit('changement_filtre',valeurs,true);
            },
        },
        computed: {
            valeurs_listes : function(){

                var id_liste = this.filtre.modele.liste_choix > 0 ? this.filtre.modele.liste_choix : this.filtre.modele.id_cl;

                if(!this.$root.valeurs_listes_libres[id_liste])
                    return {};

                var valeurs_listes = structuredClone(this.$root.valeurs_listes_libres[id_liste]);

                if(this.recherche != '') {

                    var recherche = this.$options.filters.retraite_caracteres_speciaux(this.recherche.toLowerCase());

                    for(index_liste in valeurs_listes) {

                        if(index_liste != 'categorie' && index_liste != 'liaisons') {

                            var liste = valeurs_listes[index_liste];

                            valeurs_listes[index_liste] = liste.filter((valeur_liste) => {
                                var valeur = valeur_liste.index_traduction ? this.$root.traduction(valeur_liste.index_traduction) : valeur_liste.valeur;
                                return this.$options.filters.retraite_caracteres_speciaux(valeur.toLowerCase()).includes(recherche);
                            });
                        }
                    }

                }

                return valeurs_listes;
            },
            valeur_checkbox : {
                get(){
                    return this.valeurs;
                },
                async set(valeur){

                    if(this.$parent.$options.name != 'filtres')
                        return this.$parent.$emit('changement_filtre',{valeurs : valeur});

                    var valeurs_liaisons = this.valeurs_filtres_liaisons;
                    valeurs_liaisons[this.filtre.nom_sql] = valeur;

                    await this.$root.changement_valeurs_liste(valeurs_liaisons,this.valeurs_listes.liaisons,this.filtre.type_element,this.filtre.nom_sql);

                    var valeurs_actuelles = this.$parent.valeurs_filtres;

                    for(nom_sql of Object.keys(valeurs_liaisons)){

                        var id_filtre = null;

                        if(this.$parent.$options.name == 'filtres') {

                            for (filtre of this.$parent.filtres) {
                                if (filtre.type_element == this.filtre.type_element && filtre.nom_sql == nom_sql)
                                    id_filtre = filtre.id;
                            }
                        }

                        if(id_filtre !== null){

                            var maj = false;

                            for(valeur_actuelle of valeurs_actuelles){
                                if(valeur_actuelle.id == id_filtre) {
                                    maj = true;
                                    valeur_actuelle.valeurs = valeurs_liaisons[nom_sql].length == 0 ? null : valeurs_liaisons[nom_sql];
                                }
                            }

                            if(maj === false)
                                valeurs_actuelles.push({id : id_filtre,valeurs:valeurs_liaisons[nom_sql].length == 0 ? null : valeurs_liaisons[nom_sql]})
                        }
                    }

                    this.changement_filtre(valeurs_actuelles);
                },
            },
            valeurs_filtres_liaisons : function(){

                var valeurs_filtres_liaisons = {};

                valeurs_filtres_liaisons[this.filtre.nom_sql] = this.valeurs;

                var champs_libres_ajout = [];

                if(this.valeurs_listes.liaisons[this.filtre.type_element] && this.valeurs_listes.liaisons[this.filtre.type_element][this.filtre.nom_sql]){

                    var liaisons = this.valeurs_listes.liaisons[this.filtre.type_element][this.filtre.nom_sql];

                    if(liaisons.parent)
                        champs_libres_ajout.push(liaisons.parent.champ_liste_libre_parent);

                    if(liaisons.enfants){
                        for(enfant of liaisons.enfants){
                            champs_libres_ajout.push(enfant.champ_liste_libre_enfant);
                        }
                    }
                }

                if(this.$parent.$options.name == 'filtres') {

                    for (filtre of this.$parent.filtres) {

                        if (filtre.type_element == this.filtre.type_element && champs_libres_ajout.includes(filtre.nom_sql))
                            valeurs_filtres_liaisons[filtre.nom_sql] = this.$parent.valeurs_filtres_par_filtre[filtre.id] ? this.$parent.valeurs_filtres_par_filtre[filtre.id].map(x => parseInt(x)) : [];
                    }
                }

                return valeurs_filtres_liaisons;
            },
            valeurs_selectionnes : function(){

                var valeurs_selectionnes = [];

                var valeurs = this.valeurs.map((x) => {return parseInt(x)});

                for(index of Object.keys(this.valeurs_listes)){

                    if(!['categorie','liaisons'].includes(index)){

                        valeurs_selectionnes = valeurs_selectionnes.concat(this.valeurs_listes[index].filter(x => valeurs.includes(x.id_valeur)).map((x) => {return x.valeur}))
                    }
                }

                return valeurs_selectionnes;
            },

            overflow : function(){

                if(!this.isMounted)
                    return false;

                return this.$refs.filtre_liste_libre.clientHeight < this.$refs.filtre_liste_libre.scrollHeight;
            }
        },
        mounted : function(){

            this.isMounted = true;
        },
    });
</script>