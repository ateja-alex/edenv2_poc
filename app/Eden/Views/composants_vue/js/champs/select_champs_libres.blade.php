<script>
    const select_champs_libres = Vue.component('select-champs-libres', {
        template: `
            <div class="select_champs_libres">
                <div class="select" v-if="champ_libre != null">
                    <div class="affichage" v-html="$root.traduction(champ_libre.index_traduction_type_element) +' > '+(champ_libre.index_traduction != null ? $root.traduction(champ_libre.index_traduction+'.nom') : champ_libre.nom)"></div>
                    <span @click="$emit('changement_select_champs_libres',{nom_sql : null,type_element : null,champ_liaison : null,modele_champ : null});recherche = '';">
                        <i class="fas fa-times css_pointer"></i>
                    </span>
                </div>
                <div class="select" v-click_outside="" v-else>
                    <input type="text" v-model="recherche" @input="liste_champ = true">
                    <span @click="liste_champ = !liste_champ">
                        <i :class="'fas fa-chevron-'+(liste_champ ? 'up' : 'down')"></i>
                    </span>
                    <div v-if="liste_champ" class="liste_champ">
                        <template v-for="donnees_type_element in champs_libres_par_type_element">
                            <div class="categorie" v-html="$root.traduction(donnees_type_element.index_traduction) + (donnees_type_element.champ_liaison && !donnees_type_element.questionnaire ? ' ('+$root.traduction('champs_libres.'+type_element_origine+'.'+donnees_type_element.champ_liaison.split('|')[0]+'.nom')+')' : '')"></div>
                            <div class="valeur" v-for="champ_libre_choix in donnees_type_element.champs_libres" :style="(champ_libre == champ_libre_choix? 'background:var(--background_navbar);color:white;' : '')" @click="selection_champ(donnees_type_element,champ_libre_choix)" v-html="'<b>'+(champ_libre_choix.index_traduction != null ? $root.traduction(champ_libre_choix.index_traduction+'.nom') : champ_libre_choix.nom)+'</b> ('+champ_libre_choix.nom_sql+')'">
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        `,
        props: {
            champs_libres : {
                type : Array,
                default: function(){
                    return [];
                }
            },
            type_element_origine:{
                type : String,
                default : null
            },
            type_element:{
                type : String,
                default : null
            },
            nom_sql:{
                type : String,
                default : null
            },
        },
        data : function(){
            return {
                recherche : null,
                liste_champ : false,
            };
        },
        methods : {
            selection_champ(donnees_type_element,champ_libre_choix){

                this.$emit('changement_select_champs_libres', {
                    nom_sql : champ_libre_choix.nom_sql,
                    type_element : donnees_type_element.type_element,
                    champ_liaison : donnees_type_element.champ_liaison,
                    modele_champ : champ_libre_choix,
                });

                this.liste_champ = !this.liste_champ;

                this.recherche = null;
            },
        },
        computed: {
            champ_libre : function(){

                var type_champ_libres = this.champs_libres.filter(type => type.type_element == this.type_element);

                if(type_champ_libres.length == 0)
                    return null;

                var champs_libres = type_champ_libres[0].champs_libres.filter(champ_libre => champ_libre.nom_sql == this.nom_sql);

                if(champs_libres.length == 0)
                    return null;

                return champs_libres[0];
            },
            champs_libres_par_type_element : function(){

                var champs_libres_par_type_element = [];

                var champs_libres_par_type = structuredClone(this.champs_libres);

                for(type_champ_libre of champs_libres_par_type) {

                    if(this.recherche != null && this.recherche != '') {

                        var recherche = this.$options.filters.retraite_caracteres_speciaux(this.recherche.toLowerCase());

                        type_champ_libre.champs_libres = type_champ_libre.champs_libres.filter(
                            champ_libre => this.$options.filters.retraite_caracteres_speciaux((champ_libre.index_traduction != null ? this.$root.traduction(champ_libre.index_traduction + '.nom') : champ_libre.nom).toLowerCase()).includes(recherche) || this.$options.filters.retraite_caracteres_speciaux(champ_libre.nom_sql).includes(recherche)
                        );
                    }

                    if(type_champ_libre.champs_libres.length > 0)
                        champs_libres_par_type_element.push(type_champ_libre);
                }

                return champs_libres_par_type_element;
            },
        },
        mounted : async function() {},
        directives: {
            click_outside: {
                bind: function (el, binding, vnode) {
                    el.clickOutsideEvent = function (event) {
                        if (!(el.contains(event.target)))
                            vnode.context.liste_champ = false;
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