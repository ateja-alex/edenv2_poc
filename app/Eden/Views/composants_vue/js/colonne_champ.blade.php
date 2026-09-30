<script>
    const colonne_champ = Vue.component('colonne-champ', {
        template: `<div>
            <div v-if="$root.largeur_ecran > 576" v-click_outside="valider" @click="modifier($event)" class="colonne_champ" @mouseover="affichage_icone = true" @mouseleave="affichage_icone = false">
                <div v-if="modification" class="css_champ_modification_valeur_a_la_volee_dans_listes" :style="{ alignItems: correspondance_alignement[colonne.alignement_colonne] || '' }">
                    <component ref="composant_modifier" :key="ligne.id" :is="composant"></component>
                </div>
                <div style="min-width:100px;" v-else>
                    <slot name="contenu"></slot>
                    <i v-if="affichage_icone" class="fa fa-pencil-alt"></i>
                </div>
            </div>
            <div v-else class="colonne_champ" style="user-select: none;" v-click_outside="valider" @touchstart="touche_debut($event)" @touchend="touche_fin()">
                <div v-if="modification" class="css_champ_modification_valeur_a_la_volee_dans_listes" :style="{ alignItems: correspondance_alignement[colonne.alignement_colonne] || '' }">
                    <component ref="composant_modifier" :key="ligne.id" :is="composant"></component>
                </div>
                <div style="min-width:100px;" v-else>
                    <slot name="contenu"></slot>
                </div>
            </div>
        </div>
        `,
        props:{
            colonne : {
                type: Object,
                default : function(){
                    return {};
                }
            },
            ligne : {
                type: Object,
                default : function(){
                    return {};
                }
            },
        },
        data: function(){

            return {
                affichage_icone : false,
                modification : false,
                composant: {},
                temps_touche:null,
                correspondance_alignement: {
                    right: 'flex-end',
                    left: 'flex-start',
                    center: 'center'
                }
            }

        },

        methods:{

            mise_en_place_champ : function(){

                var composant = this;

                this.composant = {
                    template : this.donnees.modification,
                    data : function(){

                        var data = {};

                        var element = structuredClone(composant.ligne.element);

                        if(composant.donnees.type_champ == 42)
                            element[composant.parametres_champ.champ] = null;

                        data[composant.parametres_champ.v_model] = element;

                        return data;
                    },
                    mounted: function(){

                        this.$on('changement_valeur',() => {
                            this.$parent.valider();
                        });
                    }
                }
            },

            modifier : function(event){

                if(this.modification)
                    return;

                event.stopImmediatePropagation();

                this.mise_en_place_champ();
                this.modification = true;
            },

            valider : function(){

                var data = {};

                if(this.$refs.composant_modifier[this.parametres_champ.v_model][this.colonne.champ] == this.ligne.element[this.colonne.champ]){
                    if(this.champ_modifiable_par_defaut)
                        return;

                    this.modification = false;
                    this.affichage_icone = false;
                    return;
                }

                data[this.parametres_champ.champ] = this.$refs.composant_modifier[this.parametres_champ.v_model][this.colonne.champ];

                if(this.parametres_champ.element_id == null){

                    data = Object.assign(data, this.parametres_champ.parametres_creation);

                    url = "eden/element/" + this.parametres_champ.type_element + "/creer";
                }
                else
                    var url = "eden/element/" + this.parametres_champ.type_element + "/" + this.parametres_champ.element_id + "/enregistrer";

                $.post({
                    url: url,
                    dataType: "json",
                    data: data
                }).done(async (donnees) => {

                    if(!this.champ_modifiable_par_defaut){
                        this.modification = false;
                        this.affichage_icone = false;
                    }

                    if (donnees.retour !== true) {

                        await erreur(donnees.retour);
                        this.$refs.composant_modifier[this.parametres_champ.v_model][this.colonne.champ] = this.ligne.element[this.colonne.champ];
                        return;
                    }

                    if(this.$parent.detail_liste_element_id != undefined){
                        await this.$parent.$parent.actualisation_filtres();
                        this.$parent.$parent.zoom_ligne(this.$parent.detail_liste_element_id);
                    }
                    else
                        this.$parent.actualisation_filtres();
                });
            },

            touche_debut : function($event){
                this.temps_touche = setTimeout(() => {
                    this.modifier($event)
                }, 500);
            },

            touche_fin : function(){

                if(this.temps_touche) {
                    clearTimeout(this.temps_touche)
                    this.temps_touche = null;
                }
            },
        },

        computed:{
            donnees : function(){
                return this.ligne[this.colonne.id] ?? [];
            },
            parametres_champ : function(){
                return this.donnees.parametres;
            },
            champ_modifiable_par_defaut : function(){
                return this.donnees.type_champ == 20 && this.donnees.liste_choix == 14 && this.donnees.format_champ != null && this.donnees.format_champ != '';
            },
        },

        mounted: function(){

            if(this.champ_modifiable_par_defaut){
                this.mise_en_place_champ();
                this.modification = true;
            }

            this.$parent.$on('actualisation_liste', () => {
                if(this.modification)
                    this.mise_en_place_champ();
            });
        },

        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (event.target.parentElement != null && !(el.closest('td').contains(event.target)) && vnode.context.modification)
                            vnode.context[binding.expression]();
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