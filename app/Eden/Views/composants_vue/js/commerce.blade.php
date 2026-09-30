<script>
    const commerce = Vue.component('commerce', {
        template: 
            `<div class="css_module_onglets">
                <ul class="nav nav-tabs liste_onglets onglets_principaux">
                    <li v-if="categorie.listes.length > 0" v-for="(categorie,index) in structure">
                        <a v-html="categorie.nom_type_flux" class="css_background_couleur_primaire_active css_pointer" :class="onglet_selectionne.type_flux == categorie.type_flux ? 'active' : ''" @click="onglet_selectionne.type_flux = categorie.type_flux"></a>
                    </li>
                </ul>

                <div class="tab-content">
                    <div v-for="(categorie,index) in structure" v-if="categorie.listes.length > 0" v-show="onglet_selectionne.type_flux == categorie.type_flux">
                        <div class="css_module_onglets">
                            <ul class="nav nav-tabs liste_onglets liste-documents-commerce">
                                <li v-for="(liste, index) in categorie.listes">
                                    <a v-html="affichage_nombre_elements[liste.liste_libre.id]" class="css_background_couleur_primaire_active css_pointer" :class="onglet_selectionne[categorie.type_flux] == liste.liste_libre.type_element ? 'active' : ''" @click="onglet_selectionne[categorie.type_flux] = liste.liste_libre.type_element"></a>
                                </li>
                            </ul>
                            <div class="tab-content">
                                <div v-for="(liste, index) in categorie.listes" v-show="onglet_selectionne[categorie.type_flux] == liste.liste_libre.type_element">
                                    <component
                                        :is="'liste-libre-'+liste.liste_libre.id"
                                        :ref="'liste_libre_'+liste.liste_libre.id"
                                        :filtres_pour_fiche="filtres_pour_fiche[liste.liste_libre.id]"
                                        :modele_par_defaut="modele_par_defaut[liste.liste_libre.id]"
                                        :session="{}"
                                        :seulement_inactif="0"
                                        :mode_parametrage="mode_parametrage"
                                    >
                                    </component>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>`,

        props: {
            structure: {
                type: Array,
                default: [],
            },
            mode_parametrage: {
                type: Number,
                default: 0,
            },
            filtres_pour_fiche: {
                type: Object,
                default: {},
            },
            modele_par_defaut: {
                type: Object,
                default: {},
            },
        },

        data: function(){
            return {
                onglet_selectionne : {
                    type_flux: null,
                },
            }
        },

        computed: {

            affichage_nombre_elements(){
                var nombres_elements = [];
                for(type_flux of this.structure){
                    for(liste of type_flux.listes){
                        var cle = 'liste_libre_'+liste.liste_libre.id;
                        nombres_elements[liste.liste_libre.id] = this.$refs[cle] !== undefined && this.$refs[cle][0].liste.nombre_elements != '' ? this.$refs[cle][0].liste.nombre_elements : this.$root.traduction('tables_libres.'+liste.liste_libre.type_element+'.nom_table');
                    }
                }
                return nombres_elements;
            },
        },
        mounted : function(){

            var onglet_par_defaut = '';
            this.onglet_selectionne.type_flux = this.structure[0].listes.length === 0 ? this.structure[1].type_flux : this.structure[0].type_flux;

            for(categorie of this.structure){
                if(categorie.listes.length > 0){
                    onglet_par_defaut = categorie.onglet_defaut == '' ? categorie.listes[0].liste_libre.type_element : categorie.onglet_defaut; 
                    this.$set(this.onglet_selectionne,categorie.type_flux,onglet_par_defaut);
                }
            }
        }
    });
</script>