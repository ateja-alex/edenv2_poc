<script>
    const champ_multi_selection_select = Vue.component('champ-multi-selection-select', {
        template: /*html*/`
        <div> 
            <select :disabled="lecture_seule" multiple style="height:150px;" v-model="modele[nom_sql]" :name="name+'[]'">
                
                <template v-if="$root.valeurs_listes_formatees[liste_choix] != undefined">
                    <template v-if="$root.valeurs_listes_formatees[liste_choix]['standard'] != undefined">
                        <option v-if="$root.valeurs_listes_formatees[liste_choix][type_element + '.' + nom_sql] != undefined" v-show="element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0)"  v-for="element in $root.valeurs_listes_formatees[liste_choix][type_element + '.' + nom_sql]" :value="element.id_valeur" v-html="element.valeur"></option>
                        <option v-if="$root.valeurs_listes_formatees[liste_choix][type_element + '.' + nom_sql] === undefined" v-for="element in $root.valeurs_listes_formatees[liste_choix]['standard']" :value="element.id_valeur" v-html="element.valeur" v-show="element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0)" ></option>
                    </template>
                    <template v-else>
                        <option v-for="element in $root.valeurs_listes_formatees[liste_choix]" :value="element.id_valeur" v-show="element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0)" v-html="element.valeur"></option>
                    </template>
                </template>
                
                <template v-else>
                    <template v-if="$root.valeurs_listes_libres[id_cl] != undefined">
                        <option v-if="$root.valeurs_listes_libres[id_cl]['sans_categorie'] != undefined" v-show="element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0)" v-for="element in $root.affichage_valeur_liste_libre(modele,$root.valeurs_listes_libres[id_cl]['sans_categorie'],$root.valeurs_listes_libres[id_cl].liaisons,'type_element','nom_sql')" :value="element.id_valeur" v-html="element.valeur" ></option>
                        <template v-if="$root.valeurs_listes_libres[id_cl].categorie != undefined && $root.valeurs_listes_libres[id_cl].categorie">
                            <optgroup v-for="(listes,nom_categorie) in $root.valeurs_listes_libres[id_cl]" v-if="nom_categorie != 'categorie' && nom_categorie != 'sans_categorie' && nom_categorie != 'liaisons'" :label="nom_categorie">
                                <option v-for="element in $root.affichage_valeur_liste_libre(modele,listes,$root.valeurs_listes_libres[id_cl].liaisons,'type_element','nom_sql')" :value="element.id_valeur" v-show="element.id_valeur != 0 && (element.inactif == undefined || element.inactif == 0)" v-html="element.valeur" ></option>
                            </optgroup>
                        </template>
                    </template>
                </template>
            </select>
        </div> `,

        props: {
            type_element: '',
            nom_sql: '',
            liste_choix: '',
            id_cl: '', 
            modele: '',
            name: '',
            lecture_seule : {
                type: Boolean | Number,
                default: false,
            },
        },
    });

</script>