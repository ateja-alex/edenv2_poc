<script>
    const filtres = Vue.component('filtres', {
        template: `
            <div>
              <div v-show="$root.largeur_ecran >= 1330" class="css_filtres_listes container css_flex_liste_filtres_disponible" style="padding: 15px;">
                <span class="filtre_supprimer_tous_les_filtres" style="font-style: italic; cursor: pointer;" v-if="filtres_actifs.length > 0" @click="effacer_filtre()">
                    <i class="fas fa-times"></i> @traduction('interface.structure_filtre_liste.effacer')
                </span>

                <template v-if="desactiver_filtres == false" v-for="filtre in filtres">
                    <div :key="filtre.id"
                         class="css_conteneur_popover_filtre conteneur_filtres" v-click_outside >
                        <span
                            :class="'css_dropdown_filtres ' +(filtres_actifs.includes(filtre.id) ? 'bg-dark text-white' : 'bg-white text-dark')"
                            style="border:solid 1px" @click="filtre_actif = (filtre.id == filtre_actif) ? null : filtre.id">
                                @traduction('filtre.index_traduction',null,true)
                                <i :class="'fas fa-angle-' + (filtre.id == filtre_actif ? 'up' : 'down')"></i>
                        </span>
                      <div class="css_block_popover_filtre" v-if="filtre_actif == filtre.id">
                           <component :is="filtre.type_filtre" :valeurs="valeurs_filtres_par_filtre[filtre.id]" ref="filtres" :key="filtre.id" :filtre="filtre" />
                      </div>
                      <i class="fas fa-times" style="cursor: pointer;"
                         v-if="filtres_actifs.includes(filtre.id)" @click="effacer_filtre(filtre.id)"></i>
                    </div>
                </template>

                <template v-if="appliquer_recherche_avancee">

                    <div class="section_recherche_avancee_filtres">
                        <div v-if="$refs.recherche_avancee != null && $refs.recherche_avancee.recherches_avancees != null && $refs.recherche_avancee.recherches_avancees.length > 0" class="d-flex">
                            <select v-model="$refs.recherche_avancee.recherche_avancee.id" @change="$refs.recherche_avancee.charger_recherche_avancee" style="height: 23px;border: unset;">
                                <optgroup v-if="categorie.recherches_avancees.length > 0" v-for="categorie in $refs.recherche_avancee.recherches_par_categories" :label="categorie.nom">
                                  <option v-for="element_recherche_avancee in categorie.recherches_avancees" :value="element_recherche_avancee.id" v-html="element_recherche_avancee.nom"></option>
                                </optgroup>
                            </select>
                            <i class="fas fa-times" v-if="$refs.recherche_avancee.recherche_avancee.id > 0" @click="$refs.recherche_avancee.annuler_modele"></i>
                        </div>

                        <div @click="gestion_recherche_avancee()">
                            <i class="fab fa-searchengin"></i>
                        </div>
                    </div>
                    <recherche-avancee ref="recherche_avancee" :chargement_externe="true" v-show="affichage_recherche_avancee" :parametres_recherche_avancee="parametres_recherche_avancee"></recherche-avancee>
                </template>
              </div>
              <div  v-show="$root.largeur_ecran < 1330" class="css_filtres_liste_mobile">

                <div class="css_action_icon css_mobile_bouton_filtre_liste" @click="afficher_filtres_mobile = true">
                  <i class="fas fa-sort" aria-hidden="true" v-if="filtres_actifs.length === 0"></i>
                  <span v-else>@{{ filtres_actifs.length }}</span>
                </div>
                <div id="modal_filtres">
                    <template v-if="afficher_filtres_mobile">
                    <transition name="modal" >
                            <div class="modal-mask">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">@traduction('interface.listes.filtrer')</h5>
                                </div>
                                <div class="modal-body css_form js_selection_element" >

                                        <span class="filtre_supprimer_tous_les_filtres btn btn-danger css_mobile_effacer_filtres" v-if="filtres_actifs.length > 0" @click="effacer_filtre()">
                                            @traduction('interface.structure_filtre_liste.effacer')
                                        </span>

                                    <div v-for="filtre in filtres"
                                        class="css_conteneur_popover_filtre conteneur_filtres">
                                            <span class="css_mobile_dropdown_filtres" @click="filtre_actif = (filtre.id == filtre_actif) ? null : filtre.id">
                                                @traduction('filtre.index_traduction',null,true)
                                                <i :class="'fas fa-angle-' + (filtre.id == filtre_actif ? 'up' : 'down')"></i>
                                            </span>
                                    <div v-if="filtre_actif == filtre.id">
                                        <component :is="filtre.type_filtre" ref="filtres" :valeurs="valeurs_filtres_par_filtre[filtre.id]" :key="filtre.id" :filtre="filtre" />
                                    </div>
                                    <i class="fas fa-times css_mobile_effacer_filtre" v-if="filtres_actifs.includes(filtre.id)" @click="effacer_filtre(filtre.id)"></i>
                                    </div>

                                    <template v-if="appliquer_recherche_avancee && $refs.recherche_avancee != null && $refs.recherche_avancee.recherches_avancees.length > 0">

                                        <p>@traduction('composant.recherche_avancee.nom') : </p>
                                        <div v-if="$refs.recherche_avancee != null && $refs.recherche_avancee.recherches_avancees != null && $refs.recherche_avancee.recherches_avancees.length > 0" class="d-flex" style="justify-content: center; align-items: center;">

                                        <select v-model="$refs.recherche_avancee.recherche_avancee.id" @change="$refs.recherche_avancee.charger_recherche_avancee" style="height: 23px;width:100%;border: none;border-bottom: solid grey 1px;">
                                            <optgroup v-if="categorie.recherches_avancees.length > 0" v-for="categorie in $refs.recherche_avancee.recherches_par_categories" :label="categorie.nom">
                                            <option v-for="element_recherche_avancee in categorie.recherches_avancees" :value="element_recherche_avancee.id" v-html="element_recherche_avancee.nom"></option>
                                            </optgroup>
                                        </select>
                                        <i class="fas fa-times" style="font-size: 16px" v-if="$refs.recherche_avancee.recherche_avancee.id > 0" @click="$refs.recherche_avancee.annuler_modele"></i>
                                        </div>
                                    </template>
                                </div>
                                <div class="modal-footer">
                                    <span class="btn btn-secondary" @click="afficher_filtres_mobile = false">@traduction('interface.modales.fermer')</span>
                                </div>
                                </div>
                            </div>
                            </div>
                    </transition>
                    </template>
                </div>
              </div>
        </div>`,
        props: {
            filtres : {
                type : Array,
                default: function(){
                    return [];
                },
            },
            valeurs_filtres : {
                type : Array,
                default: function(){
                    return [];
                },
            },
            desactiver_filtres: {
                type : Boolean,
                default: false
            },
            appliquer_recherche_avancee : {
                type : Boolean,
                default: false
            },
            parametres_recherche_avancee: {
                type : Object,
                default: function(){
                    return {};
                },
            },
        },
        data: function(){
            return {
                afficher_filtres_mobile: false,
                filtre_actif : null,
                affichage_recherche_avancee: false,
                deplacement_recherche_avancee : false,
            }
        },
        created:function(){
            this.$on('changement_filtre',(nouvelles_valeurs,multiple = false) => {

                if(multiple === false)
                    nouvelles_valeurs = [nouvelles_valeurs];

                this.changement_filtres(nouvelles_valeurs);
            });

            this.$on('changement_recherche_avancee',(recherches_avancee) => {
                this.$parent.$emit('changement_recherche_avancee',recherches_avancee);
            })
        },
        mounted : function(){
            $('#stack_modales_composants').append($('#modal_filtres'));
        },
        methods:{
            effacer_filtre : function(filtre_id = null){

                var valeurs = [];

                if(filtre_id !== null){

                    valeurs = structuredClone(this.valeurs_filtres);

                    for (cle_valeur in valeurs) {

                        var valeur = valeurs[cle_valeur];

                        if (filtre_id == valeur.id)
                            valeurs.splice(cle_valeur, 1);
                    }
                }

                this.$parent.$emit('changement_filtres',valeurs);
            },
            changement_filtres : function(nouvelles_valeurs){

                var valeurs = structuredClone(this.valeurs_filtres);

                for(nouvelle_valeur of nouvelles_valeurs) {

                    var maj = false;

                    for (cle_valeur in valeurs) {

                        var valeur = valeurs[cle_valeur];

                        if (valeur.id == nouvelle_valeur.id) {
                            maj = true;

                            if (nouvelle_valeur.valeurs === null)
                                valeurs.splice(cle_valeur, 1);
                            else
                                this.$set(valeur, 'valeurs', nouvelle_valeur.valeurs);
                        }
                    }

                    if (!maj && nouvelle_valeur.valeurs !== null)
                        valeurs.push(nouvelle_valeur);
                }

                this.$parent.$emit('changement_filtres',valeurs);
            },
            gestion_recherche_avancee : async function(){

                this.affichage_recherche_avancee = !this.affichage_recherche_avancee;

                if(this.affichage_recherche_avancee && this.$refs.recherche_avancee)
                    this.$refs.recherche_avancee.charger_champs_libres();

                if(this.deplacement_recherche_avancee === false) {
                    var card_header = this.$refs.recherche_avancee.$el.closest('.card-header');
                    $(card_header).parent()[0].insertBefore(this.$refs.recherche_avancee.$el, card_header.nextSibling);
                    this.deplacement_recherche_avancee = true;
                }
            },
        },
        computed:{

            valeurs_filtres_par_filtre : function(){

                var valeurs_filtres_par_filtre = {};

                for(valeur_filtre of this.valeurs_filtres) {
                    if(!valeur_filtre)
                        continue;

                    valeurs_filtres_par_filtre[valeur_filtre.id] = valeur_filtre.valeurs;
                }

                return valeurs_filtres_par_filtre;
            },
            filtres_actifs: function(){
                return Object.keys(this.valeurs_filtres_par_filtre).map(x => isNaN(parseInt(x)) ? x : parseInt(x));
            },
        },
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target)) && vnode.context.filtre_actif == vnode.key && vnode.context.$root.largeur_ecran >= 1330)
                            vnode.context.filtre_actif = null;
                    };
                    document.body.addEventListener('click', el.clickOutsideEvent)
                },
                unbind: function (el) {
                    document.body.removeEventListener('click', el.clickOutsideEvent)
                },
            }
        }
    });
</script>