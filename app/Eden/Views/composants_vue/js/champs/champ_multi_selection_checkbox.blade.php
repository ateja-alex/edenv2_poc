<script>
    const champ_multi_selection_checkbox = Vue.component('champ-multi-selection-checkbox', {
        template: 
        `<div>
            <div style="display: flex;flex-wrap: wrap;gap: 10px;">
                <div v-for="element in elements" :name="type_element + '_' + nom_sql" :name_champ="nom_sql">
                    <input :disabled="lecture_seule" :id="type_element + '_' + nom_sql + '_' + element.id_valeur" type="checkbox" :value="element.id_valeur" :class="class_css_js" v-model="modele[nom_sql]" @change="changement_checkbox" :name="name + '[]'" :type-element-v-model="typeElementVModel"  style="display:none;"/>
                    <label :for="type_element + '_' + nom_sql + '_' + element.id_valeur" class="badge" :class="modele[nom_sql].includes(element.id_valeur) ? 'badge-success' : 'badge-default'" v-html="element.valeur"></label>
                </div>
                <input v-if="modele[nom_sql].length == 0" type="hidden" :name="name" />
                <template v-if="elements.length === 0">
                    <label style="margin-right: 20px;">
                        <div class="badge" :class="\'badge-default\'" style="cursor: not-allowed !important">Désolé, il n\'y a pas d\'élements.</div>
                    </label>
                </template>
            </div>
        </div>`,

        props: {
            typeElementVModel: '',
            name: '',
            type_element: '',
            nom_sql: '',
            liste_choix: '',
            id_cl: '', 
            modele: '',
            class_css_js: '',
            type_reference: null,
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
        },

        data: function() {
            return {
                changement: false,
            }
        },

        computed : {

            elements : function(){

                var elements = [];

                if(this.type_reference == 20){

                    if(!this.$root.valeurs_listes_formatees[this.liste_choix])
                        return [];

                    elements = Object.values(this.$root.valeurs_listes_formatees[this.liste_choix][this.type_element + '.' + this.nom_sql] 
                        ?? this.$root.valeurs_listes_formatees[this.liste_choix].standard 
                        ?? this.$root.valeurs_listes_formatees[this.liste_choix]);
                }
                else{

                    var liste_libre = this.$root.valeurs_listes_libres[this.id_cl];

                    if(!liste_libre)
                        return [];

                    if(liste_libre.sans_categorie)
                        elements = this.$root.affichage_valeur_liste_libre(this.modele ,liste_libre.sans_categorie,liste_libre.liaisons, this.type_element, this.nom_sql);

                    if(liste_libre.categorie){

                        for(nom_colonne in liste_libre){

                            if(['categorie', 'sans_categorie', 'liaisons'].includes(nom_colonne))
                                continue;

                            var elements_categorie = this.$root.affichage_valeur_liste_libre(this.modele ,liste_libre[nom_colonne],liste_libre.liaisons, this.type_element, this.nom_sql);

                            elements = elements.concat(elements_categorie);
                        }
                    }

                }

                elements = elements.filter(element => element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0));

                return elements;
            }
        },

        methods: {

            changement_checkbox(valeur) {
                
                if(this.type_reference == 1)
                    this.$root.changement_valeurs_liste(this.typeElementVModel, this.$root.valeurs_listes_libres[this.id_cl].liaisons, this.type_element, this.nom_sql);
            }

        }
    })

</script>