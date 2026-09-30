<script>
    const champ_liste_formatee = Vue.component('champ-liste-formatee', {
        template: /*template*/`
        <select v-model="modele[nom_sql]" @change="$parent.$emit('changement_valeur',{nom_sql : nom_sql, valeur : modele[nom_sql]})" :disabled="lecture_seule" :name="name" :class="class_css_js">
            <option v-for="element in elements" :value="element.id_valeur" v-html="element.valeur"></option>
        </select>`,
        
        props: {
            modele: '',
            nom_sql: '',
            name: '',
            class_css_js: '',
            modele_obligatoire: '',
            type_element: '',
            liste_choix: '',
            valeur: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
            zero_possible : false,
        },

        computed : {

            elements : function(){

                var elements = this.$root.valeurs_listes_formatees[this.liste_choix] ?? [];

                elements = Object.values(elements[this.type_element + '.' + this.nom_sql] ?? elements.standard ?? elements);

                if(!this.zero_possible)
                    elements = elements.filter(element => (element.id_valeur == 0  && !this.modele_obligatoire) || element.id_valeur != 0);
                
                return elements;
            }
        }
    })

</script>