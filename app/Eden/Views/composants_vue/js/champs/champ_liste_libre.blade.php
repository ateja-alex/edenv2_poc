<script>
    const champ_liste_libre = Vue.component('champ-liste-libre', {
        template: /*template*/`
        <select v-model="modele[nom_sql]" @change="$parent.$emit('changement_valeur',modele[nom_sql])" :disabled="lecture_seule" :name="name" :class="class_css_js">
            <option v-for="element in elements.sans_categorie" :value="element.id_valeur" v-html="element.valeur"></option>
            <optgroup v-for="(listes,nom_categorie) in elements.categories" :label="nom_categorie">
                <option  v-for="element in listes" :value="element.id_valeur" v-html="element.valeur"></option>
            </optgroup>
        </select>`,
        
        props: {
            modele: '',
            nom_sql: '',
            name: '',
            class_css_js: '',
            modele_obligatoire: Boolean | Number,
            id_cl: '',
            type_element: '',
            valeur: '',
            afficher_sans_valeur: Boolean,
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
        },

        computed : {

            elements : function(){

                var elements = this.$root.valeurs_listes_libres[this.id_cl] ?? [];

                var elements_a_afficher = {
                    sans_categorie: [],
                    categories: {}
                };

                if(elements.sans_categorie)
                    elements_a_afficher.sans_categorie = this.$root.affichage_valeur_liste_libre(this.modele ,elements.sans_categorie,elements.liaisons, this.type_element, this.nom_sql)
                        .filter(element => element.id_valeur != 0 || (element.id_valeur == 0 && !this.modele_obligatoire && this.afficher_sans_valeur));

                if(elements.categorie){

                    for(nom_colonne in elements){

                        if(['categorie', 'sans_categorie', 'liaisons'].includes(nom_colonne))
                            continue;

                        elements_a_afficher.categories[nom_colonne] = this.$root.affichage_valeur_liste_libre(this.modele ,elements[nom_colonne],elements.liaisons, this.type_element, this.nom_sql)
                            .filter(element => element.id_valeur != 0 || (element.id_valeur == 0 && !this.modele_obligatoire && this.afficher_sans_valeur));
                    }
                }

                return elements_a_afficher;
            }
        }
    })

</script>