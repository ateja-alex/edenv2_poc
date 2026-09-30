<script>
const recherche_avancee = Vue.component('recherche-avancee', {
    template: `<div class="bloc_recherche_avancee card mb-3">

                  <div class="card-header titre" v-if="bloc_unitaire && !enregistrement_desactive">
                    <h4>@traduction('composant.recherche_avancee.filtres_appliques.titre')</h4>
                    <span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Enregistrer" @click="enregistrer_recherche_avancee">
                        <i class="css_action_icon far fa-save css_font_16" style="width: 30px;"></i>
                    </span>
                  </div>
                  <div class="card-header titre" v-else-if="!bloc_unitaire">
                    <h4>@traduction('composant.recherche_avancee.titre')</h4>
                    <div v-if="recherches_avancees.length > 0" class="choix_element">
                        <select v-model="recherche_avancee.id" @change="charger_recherche_avancee">
                            <optgroup v-if="categorie.recherches_avancees.length > 0" v-for="categorie in recherches_par_categories" :label="categorie.nom">
                              <option v-for="element_recherche_avancee in categorie.recherches_avancees" :value="element_recherche_avancee.id" v-html="element_recherche_avancee.nom"></option>
                            </optgroup>
                        </select>
                        <i class="fas fa-times" v-if="recherche_avancee.id > 0" @click="annuler_modele"></i>
                    </div>
                    <div class="options">
                        <div> @traduction('composant.recherche_avancee.nom')</div>
                        <div> <input :disabled="!recherche_modifiable" v-model="recherche_avancee.nom" />  </div>
                    </div>
                  </div>

                <div class="card-body recherche_avancee" ref="recherche_avancee">

                    <div v-if="!chargement_champs" class="text-center">
                        <img src="/eden/images/ajax_loader.gif" style="width:60px"/>
                    </div>
                    <div class="bloc_conditions" v-else>
                        <recherche-avancee-bloc v-for="(bloc,index_bloc) in recherche_avancee.structure" :key="index_bloc" :bloc="bloc" :index_bloc="index_bloc" :type_element="parametres_recherche_avancee.type_element" :filtres="filtres"></recherche-avancee-bloc>
                        <div class="css_action_icon ajout_bloc_global" @click="ajouter_bloc()" :title="$root.traduction('composant.blocs_recherche_avancee.nouveau_groupe_de_conditions')">
                            ( )
                            <span class="icone_secondaire">
                                <i class="fas fa-plus"></i>
                            </span>
                        </div>
                    </div>

                  <div v-if="ajout_element" class="ajout_element">
                    <div>
                      <h5>@traduction('composant.recherche_avancee.condition')</h5>
                    </div>
                    <div>
                      <div class="champs">
                        <div>@traduction('composant.recherche_avancee.champ')</div>
                        <div>
                          <select-champs-libres @changement_select_champs_libres="changement_select_champs_libres($event)" :champs_libres="champs_libres" :type_element_origine="parametres_recherche_avancee.type_element" :type_element="condition_en_cours.type_element" :nom_sql="condition_en_cours.nom_sql"></select-champs-libres>
                        </div>
                      </div>
                      <div class="champs" v-if="filtres[condition_en_cours.type_element +'_'+condition_en_cours.nom_sql]">
                        <div>@traduction('composant.recherche_avancee.condition')</div>
                        <div class="condition">
                          <component :is="filtres[condition_en_cours.type_element +'_'+condition_en_cours.nom_sql].type_filtre" :valeurs="condition_en_cours.valeurs" :filtre="filtres[condition_en_cours.type_element +'_'+condition_en_cours.nom_sql]" :type_element_source="type_element_source"></component>
                        </div>
                      </div>
                    </div>
                    <div class="boutons">
                      <button type="button" class="btn btn-secondary" @click="ajout_element = false" data-dismiss="modal">
                        @traduction('composant.recherche_avancee.fermer')
                       </button>
                      <button type="button" class="btn btn-primary" @click="enregistrer_condition">
                        @traduction('composant.recherche_avancee.enregistrer')
                      </button>
                    </div>
                    <div class="chevron" v-if="position_chevron != null" :style="'top:'+position_chevron+'px'">
                    </div>
                  </div>
                </div>
                  <div class="card-footer options_bas" v-if="!bloc_unitaire && !enregistrement_desactive">
                    <span class="css_action_icon secondaire" v-if="recherche_avancee.nom != '' && recherche_modifiable" @click="enregistrer_recherche_avancee">
                        @traduction('composant.recherche_avancee.enregistrer_modele') : @{{ recherche_avancee.nom }}
                    </span>
                    <span class="css_action_icon suppression" v-if="recherche_modifiable" @click="supprimer_recherche_avancee">
                        @traduction('composant.recherche_avancee.supprimer_modele')
                    </span>
                    <template v-if="recherche_avancee.id > 0">
                      <span class="css_action_icon duplication" @click="dupliquer_recherche_avancee">
                        @traduction('composant.recherche_avancee.dupliquer_modele')
                      </span>
                    </template>
                  </div>
        </div>`,
    props:{
        parametres_recherche_avancee: {
            type:Object,
            default:function(){
                return {};
            }
        },
        chargement_externe : {
            type : Boolean,
            default: false,
        },
        bloc_unitaire : {
            type : Boolean,
            default: false,
        },
        enregistrement_desactive: {
            type : Boolean,
            default: false,
        },
        informations_complementaires: {
            type:Object,
            default:function(){
                return {};
            }
        },
        type_element_source : {
            type : String,
            default : null,
        },
    },

    data : function(){
        return {
            ajout_element : false,
            condition_en_cours : {},
            champs_libres: [],
            chargement_champs: false,
            chargement_champs_en_cours: false,
            position_chevron : null,
            instance_filtre : null,
            index_filtre: null,
            recherche_avancee : {
                nom : '',
                structure : [],
            },
            recherches_par_categories : [],
        }
    },
    methods: {

        ajouter_bloc() {

            var nouveau_bloc = {operateur: 0, filtres: [], blocs: [], exclu : 0};

            var blocs = this.recherche_avancee.structure;

            blocs.push(nouveau_bloc);

            this.$parent.$emit('changement_recherche_avancee',{
                recherche_avancee: this.recherche_avancee,
                actualisation : false,
                informations_complementaires : this.informations_complementaires,
            });
        },

        ajouter_condition(parametres) {

            this.condition_en_cours = {
                nom_sql : null,
                type_element : null,
                champ_liaison : null,
                operateur : 0,
            };

            this.position_chevron = parametres.instance.$refs.bloc.getBoundingClientRect().top - this.$refs.recherche_avancee.getBoundingClientRect().top - 15;

            this.instance_filtre = parametres.instance;

            this.ajout_element = true;
        },

        modifier_condition : async function(parametres) {

            this.condition_en_cours = parametres.filtre;

            this.position_chevron = parametres.instance.$refs.filtres[parametres.index_filtre].getBoundingClientRect().top - this.$refs.recherche_avancee.getBoundingClientRect().top -20;

            this.instance_filtre = parametres.instance;
            this.index_filtre = parametres.index_filtre;

            this.ajout_element = true;
        },

        enregistrer_condition : function(){

            if(this.condition_en_cours.nom_sql == '' || this.condition_en_cours.nom_sql == null)
                return;

            this.instance_filtre.enregistrement_condition(this.condition_en_cours,this.index_filtre);

            this.condition_en_cours = {
                nom_sql : null,
                type_element : null,
                champ_liaison : null,
                operateur:0,
            };

            this.position_chevron = this.instance_filtre.$refs.bloc.getBoundingClientRect().top - this.$refs.recherche_avancee.getBoundingClientRect().top - 15;

            this.index_filtre = null;
        },

        enregistrer_recherche_avancee : function(){

            if(this.recherche_avancee.nom == '' && !this.bloc_unitaire){

                alerte_eden(this.$root.traduction('composant.recherche_avancee.indiquer_nom'))
                return;
            }

            var recherche_avancee = structuredClone(this.recherche_avancee);

            for(index_parametre in this.parametres_recherche_avancee){
                recherche_avancee[index_parametre] = this.parametres_recherche_avancee[index_parametre];
            }

            var modification = recherche_avancee.id ? true : false;

            $.post({
                url : '{{route('base_eden.recherche_avancee.enregistrer', [], false)}}',
                data : recherche_avancee
            }).done((retour) => {
                this.recherche_avancee = retour;

                if(modification){

                    for(categorie of this.recherches_par_categories){

                        for(index_recherche_avancee in categorie.recherches_avancees){

                            var liste_recherche_avancee = categorie.recherches_avancees[index_recherche_avancee];

                            if(liste_recherche_avancee.id == retour.id)
                                categorie.recherches_avancees[index_recherche_avancee] = structuredClone(retour);

                        }
                    }
                }
                else {

                    var categorie_id = this.parametres_recherche_avancee.utilisateur_id > 0 ? 'personnel' : 'eden';

                    for(categorie of this.recherches_par_categories){

                        if(categorie.id == categorie_id)
                            categorie.recherches_avancees.push(structuredClone(retour));
                    }
                }

                toastr.success(this.$root.traduction('composant.recherche_avancee.modele_enregistre'));
            });
        },

        supprimer_recherche_avancee : async function(){

            if(!await confirm_eden(this.$root.traduction('interface.modales.confirmation_suppression')))
                return false;

            if(!(this.recherche_avancee.id > 0)){

                this.recherche_avancee = {
                    nom : '',
                    structure : [],
                };

                this.$parent.$emit('changement_recherche_avancee',{
                    recherche_avancee: this.recherche_avancee,
                    actualisation : true,
                    informations_complementaires : this.informations_complementaires,
                });

                this.ajout_element = false;

                return;
            }

            loading(true);

            // on fait un appel ajax pour supprimer
            $.get({

                url: "eden/recherche_avancee/"+this.recherche_avancee.id+"/supprimer",
                dataType: "json",
            }).done(async (donnees) => {

                loading(false);

                if(donnees.retour !== true) {

                    await erreur(donnees.retour);
                    return;
                }

                for(categorie of this.recherches_par_categories){

                    for(index_recherche_avancee in categorie.recherches_avancees) {

                        var liste_recherche_avancee = categorie.recherches_avancees[index_recherche_avancee];

                        if (liste_recherche_avancee.id == this.recherche_avancee.id)
                            categorie.recherches_avancees.splice(index_recherche_avancee, 1);
                    }

                }

                this.recherche_avancee = {
                    nom : '',
                    structure : [],
                };

                this.$parent.$emit('changement_recherche_avancee',{
                    recherche_avancee: this.recherche_avancee,
                    actualisation : true,
                    informations_complementaires : this.informations_complementaires,
                });

                this.ajout_element = false;

                toastr.success(this.$root.traduction('composant.recherche_avancee.modele_supprime'));
            });
        },

        chargement_initial : async function(donnees = {}, differer_champs_libres = false){

            if(differer_champs_libres !== true)
                this.charger_champs_libres(donnees.champs_libres ?? false);

            this.charger_listes_recherches_avancees(donnees.recherches_avancees ?? donnees.recherches_par_categories ?? false);
        },

        charger_listes_recherches_avancees : async function(recherches_par_categories = false){

            if(recherches_par_categories === false)
                recherches_par_categories = await $.post({
                    url : '{{route('base_eden.recherche_avancee.listes', [], false)}}',
                    data : this.parametres_recherche_avancee
                });

            if(recherches_par_categories) {
                if (this.bloc_unitaire) {
                    this.recherche_avancee = recherches_par_categories[0]['recherches_avancees'][0] ?? {
                        nom: '',
                        structure: [],
                    };

                    if (this.recherche_avancee.id != null)
                        await this.charger_recherche_avancee();
                } else
                    this.recherches_par_categories = recherches_par_categories;
            }
        },

        charger_champs_libres : async function(champs_libres = false){

            if(champs_libres === false && (this.chargement_champs === true || this.chargement_champs_en_cours === true))
                return;

            this.chargement_champs_en_cours = true;

            if(champs_libres === false)
                champs_libres = await $.post({
                    url : '{{route('base_eden.recherche_avancee.champs_libres', [], false)}}',
                    data : this.parametres_recherche_avancee
                });

            this.champs_libres = champs_libres;
            this.chargement_champs = true;
            this.chargement_champs_en_cours = false;
        },

        charger_recherche_avancee : async function(){

            var recherche_avancee = await $.ajax({
                url : 'eden/recherche_avancee/'+this.recherche_avancee.id,
            });

            this.recherche_avancee = recherche_avancee;

            if(this.ajout_element)
                this.ajout_element = false;

            this.$parent.$emit('changement_recherche_avancee',{
                recherche_avancee : this.recherche_avancee,
                actualisation : true,
                informations_complementaires : this.informations_complementaires,
            });
        },

        dupliquer_recherche_avancee : function(){

            this.recherche_avancee.nom += '_2';

            this.$delete(this.recherche_avancee,'id');
        },

        annuler_modele : function(){

            this.recherche_avancee = {
                nom : '',
                structure : [],
            };

            if(this.ajout_element)
                this.ajout_element = false;

            this.$parent.$emit('changement_recherche_avancee',{
                recherche_avancee: this.recherche_avancee,
                actualisation : true,
                informations_complementaires : this.informations_complementaires,
            });
        },
        changement_select_champs_libres : function(valeur){
    
            this.condition_en_cours.nom_sql = valeur.nom_sql;
            this.condition_en_cours.type_element = valeur.type_element;
            this.condition_en_cours.champ_liaison = valeur.champ_liaison;

            this.$delete(this.condition_en_cours, 'valeurs');
        },
    },
    computed: {

        filtres : function(){

            var filtres = {};

            for(type of this.champs_libres) {
                for (champ_libre of type.champs_libres) {

                    if(!filtres[champ_libre.type_element + '_' + champ_libre.nom_sql])
                        filtres[champ_libre.type_element + '_' + champ_libre.nom_sql] = {
                            nom_sql: champ_libre.nom_sql,
                            type_element: champ_libre.type_element,
                            type_filtre: champ_libre.type_filtre,
                            modele: champ_libre,
                        }
                }
            }

            return filtres;
        },

        recherche_modifiable : function(){

            if(this.recherche_avancee.id > 0 && !(this.recherche_avancee.utilisateur_id > 0) && this.parametres_recherche_avancee.utilisateur_id > 0)
                return false;

            return true;
        },

        recherches_avancees : function(){

            var recherches_avancees = [];

            for(categorie of this.recherches_par_categories){

                recherches_avancees = recherches_avancees.concat(categorie.recherches_avancees);
            }

            return recherches_avancees;
        },
    },
    mounted : function(){

        this.$on('ajouter_condition',(parametres) => {
            this.ajouter_condition(parametres);
        });

        this.$on('modifier_condition',(parametres) => {
            this.modifier_condition(parametres);
        });

        this.$on('supprimer_bloc',(parametres) => {

            var blocs = this.recherche_avancee.structure;

            blocs.splice(parametres.index_bloc,1);

            this.$parent.$emit('changement_recherche_avancee', {
                recherche_avancee: this.recherche_avancee,
                actualisation : parametres.non_actualise !== true,
                informations_complementaires : this.informations_complementaires,
            });
        });

        this.$on('changement_bloc',(parametres) => {

            var blocs = this.recherche_avancee.structure;

            blocs[parametres.index_bloc] = parametres.bloc;

            this.$parent.$emit('changement_recherche_avancee',{
                recherche_avancee: this.recherche_avancee,
                actualisation : parametres.non_actualise !== true,
                informations_complementaires : this.informations_complementaires,
            });
        });

        this.$on('changement_filtre',(filtre) => {

            if(filtre.valeurs == null)
                this.$delete(this.condition_en_cours,'valeurs');
            else
                this.$set(this.condition_en_cours,'valeurs',filtre.valeurs);
        });

        if(this.chargement_externe !== true)
            this.chargement_initial();
    },

});
</script>
