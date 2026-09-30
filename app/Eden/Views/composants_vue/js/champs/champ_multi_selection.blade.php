<script>
const champ_multi_selection = Vue.component('champ-multi-selection', {
    template: `<div>
            <div style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 10px 5px;" class="champ_selection_element_multiple">
                <i :class="'fa fa-search css_pointer css_input_ajout_selection_element css_background_couleur_primaire' "
                   style="padding: 10px;text-align: center;width: 35px;" v-if="!lecture_seule" @click="selection_element = true"></i>
                <span class="css_selection_element_multiple" v-for="(element,index) in liste_valeurs_choisies">
                    <span v-if="!lecture_seule" @click="deselection_valeur(index);" class="css_selection_element_multiple_icone">
                        <i class="fa fa-times"></i>
                    </span>
                    <span style="margin-left: 5px;" v-html="element.valeur"></span>
				</span>
                <select v-if="Array.isArray(modele[nom_sql]) && modele[nom_sql].length > 0" :name="name+'[]'" v-model="modele[nom_sql]" multiple style="display: none;">
                   <option v-for="valeur in liste_valeurs" :value="valeur.id_valeur">@{{valeur.valeur}}</option>
                </select>
                <input v-else type="hidden" :name="name" />
            </div>
		<!-- la modale -->
        <template v-if="selection_element">
            <transition name="modal">
                <div class="modal-mask">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">@traduction('composant.champ_multi_selection.selection_des_valeurs')</h5>
                                <button type="button" @click="selection_element = false" aria-label="Close"
                                        class="close">
                                    <span aria-hidden="true">×</span>
                                </button>
                            </div>

                            <div class="modal-body css_form">

                                <div class="row">
                                    <div class="champ_selection_element_multiple col-md-12" style="display: inline-flex; align-items: center; flex-wrap: wrap; gap: 10px 5px;">

                                        <span class="css_selection_element_multiple" v-for="(element,index) in liste_valeurs_choisies">
                                            <span v-if="!lecture_seule" @click="deselection_valeur(index);" class="css_selection_element_multiple_icone">
                                                <i class="fa fa-times"></i>
                                            </span>
                                            <span style="margin-left: 5px;" v-html="element.valeur"></span>
                                        </span>

                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 champ_recherche">
                                        <i class="fa fa-search"></i>
                                        <input type="text" v-model="recherche"/>
                                    </div>

                                    <template v-if="liste_sans_categorie.length > 0">
                                        <div class="col-md-12">

                                            <div class="row">
                                                <div class="col-md-12" style="padding-left: 50px;">
                                                    <div
                                                         v-for="element in liste_sans_categorie"
                                                         @click="selection_valeur(element.id_valeur)">
                                                        <span style="cursor: pointer;">@{{ element.valeur }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="col-md-12" v-for="nom_categorie in categories" v-if="listes_categorie(nom_categorie).length > 0">
                                        <b style="text-decoration: underline;" v-html="$root.traduction(nom_categorie)"></b><br/>
                                        <div class="row">
                                            <div class="col-md-12" style="padding-left: 50px;">
                                                <div
                                                     v-for="element in listes_categorie(nom_categorie)"
                                                     @click="selection_valeur(element.id_valeur)">
                                                    <span style="cursor: pointer;">@{{ element.valeur }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" @click="selection_element = false">@traduction('interface.modales.fermer')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
        </div>`,
  props: {

            modele: {},
            nom_sql: '',
            name: '',
            type_element: '',
            url: false,
            champ_libre : null,
            lecture_seule :{
                type: Boolean | Number,
                default: false,
            },
        },
        data: function () {
            return {
                liste_valeurs: {},
                recherche: '',
                selection_element: false,
                categories: [],
                liaisons: {},
            }
        },
        computed: {

            id_random: function () {

                length = 15;

                var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

                if (!length) {
                    length = Math.floor(Math.random() * chars.length);
                }

                var str = '';
                for (var i = 0; i < length; i++) {
                    str += chars[Math.floor(Math.random() * chars.length)];
                }

                return str;
            },

            liste_valeurs_choisies: function () {

                var valeurs = this.modele[this.nom_sql];

                var liste_valeurs_choisies = [];

                if(valeurs == null)
                    return liste_valeurs_choisies;

                for(valeur of valeurs){

                    for(valeur_disponible of Object.values(this.liste_valeurs)){
                        if(valeur_disponible.id_valeur == valeur)
                            liste_valeurs_choisies.push(valeur_disponible);
                    }
                }

                return liste_valeurs_choisies;
            },

            liste_sans_categorie : function(){

                var liste_sans_categorie = [];

                for(valeur of Object.values(this.liste_a_afficher)){

                    if(valeur.categorie == undefined || valeur.categorie == '')
                        liste_sans_categorie.push(valeur);
                }

                return liste_sans_categorie;
            },

            liste_a_afficher : function(){

                var liste_a_afficher = [];

                var recherche = this.recherche.toUpperCase();

                for(element of Object.values(this.liste_valeurs)){

                    var valeur_affichage = element.valeur.toUpperCase();

                    if(!this.modele[this.nom_sql].includes(element.id_valeur) && valeur_affichage.indexOf(recherche) >= 0)
                        liste_a_afficher.push(element);
                }

                if(Object.values(this.liaisons).length > 0)
                    liste_a_afficher = this.$root.affichage_valeur_liste_libre(this.modele,liste_a_afficher,this.liaisons,this.type_element,this.nom_sql);

                return liste_a_afficher;
            }
        },
        methods: {

            selection_valeur: function (valeur) {

                this.modele[this.nom_sql].push(valeur);

                if(Object.values(this.liaisons).length > 0)
                    this.$root.changement_valeurs_liste(this.modele,this.liaisons,this.type_element,this.nom_sql);

                this.recherche = "";
            },

            deselection_valeur: function (index) {

                this.modele[this.nom_sql].splice(index,1);

                if(Object.values(this.liaisons).length > 0)
                    this.$root.changement_valeurs_liste(this.modele,this.liaisons,this.type_element,this.nom_sql);

            },

            listes_categorie : function(categorie){

                var listes_categorie = [];

                for(valeur of Object.values(this.liste_a_afficher)){

                    if(valeur.categorie == categorie)
                        listes_categorie.push(valeur);
                }

                return listes_categorie;
            },

        },
        created: function () {

            var liste_valeurs = {};
            var categories = [];

            var type = this.champ_libre.type;
            var type_reference = this.champ_libre.type_reference;
            var type_element = this.type_element;
            var nom_sql = this.nom_sql;

            if(type == 10 && type_reference == 20) {

                var valeurs = this.$root.valeurs_listes_formatees[this.champ_libre.liste_choix];

                if(valeurs != undefined){

                    if(valeurs.standard != undefined){

                        if(valeurs[type_element+'.'+nom_sql] != undefined)
                            liste_valeurs = valeurs[type_element+'.'+nom_sql];
                        else
                            liste_valeurs = valeurs.standard;

                    }
                    else
                        liste_valeurs = valeurs;
                }
            }
            else{

                var id_cl = this.champ_libre.id_cl;

                if(this.champ_libre.liste_choix > 0)
                    id_cl = this.champ_libre.liste_choix;

                var valeurs = this.$root.valeurs_listes_libres[id_cl];

                if(valeurs != undefined) {

                    this.liaisons = valeurs.liaisons;

                    if (valeurs.sans_categorie != undefined)
                        liste_valeurs = valeurs.sans_categorie;

                    if (valeurs.categorie != undefined) {

                        for (const [nom_categorie, listes] of Object.entries(valeurs)) {
                            if (nom_categorie != 'categorie' && nom_categorie != 'sans_categorie' && nom_categorie != 'liaisons') {

                                categories.push(nom_categorie);

                                liste_valeurs = liste_valeurs.concat(listes);
                            }
                        }
                    }
                }
            }

            for(const[index,element] of Object.entries(liste_valeurs)){

                if(element.id_valeur == 0 || element.inactif == 1) {
                    if(Array.isArray(liste_valeurs))
                        liste_valeurs.splice(index,1);
                    else
                        delete liste_valeurs[index];
                }
            }

            this.liste_valeurs = liste_valeurs;
            this.categories = categories;

        },
        watch: {

            modele: {
                handler: function (newVal, oldVal) {

                    var composant = this;

                    if (typeof composant.modele[composant.nom_sql] == 'undefined' || composant.modele[composant.nom_sql] == null) {
                        composant.modele[composant.nom_sql] = {};
                    }
                },
                deep: true
            },
        },

});
</script>