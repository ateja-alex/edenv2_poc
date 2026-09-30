@php
    $background_navbar = maquette('background_navbar');

    list($r, $g, $b) = sscanf($background_navbar, "#%02x%02x%02x");

    $rgba = $r.', '.$g.', '.$b.',0.3';
@endphp

<script>
const traduction_table = Vue.component('traduction-table', {
    template: `
            <table style="width: 100%;" :class="'table table-bordered table-hover '+(vertical ? 'table_vertical' : '')" id="liste_champs_libres" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th :style="(vertical === false ? 'width:30%' : '')" scope="col">@traduction('composant.traduction_table.index')</th>
                        <th :style="(vertical === false ? 'width:'+(70/langues_affichage_tableau.length)+'%' : '')" v-for="langue in langues_affichage_tableau" scope="col">@{{ langue.nom }}</th>
                    </tr>
                </thead>
                <tbody>
                    <template v-if="traductions_par_sous_categorie.length > 0" v-for="traduction_par_sous_categorie in traductions_par_sous_categorie">
                        <template v-if="traduction_par_sous_categorie[index_tableau_traduction] !== undefined && traduction_par_sous_categorie[index_tableau_traduction].length > 0">
                            <tr v-if="filtrage_index == ''">
                                <td colspan="5">
                                    <h5>@{{ traduction_par_sous_categorie.sous_categorie }}</h5>
                                </td>
                            </tr>
                            <tr v-for="(traduction,index_tableau) in traduction_par_sous_categorie[index_tableau_traduction]">
                                <td :style="(vertical === false ? 'width:30%' : '')"  scope="col">
                                    <div class="row" style="margin: unset;display: inline-flex;align-items: center;width: 100%;">
                                        <span>@{{ traduction.index }}</span>
                                        <div @click="supprimer_traduction(traduction,traduction_par_sous_categorie.traductions,index_tableau)" style="cursor:pointer" class="ml-auto" v-if="traduction.type == 'specifique' || {{ (env('BASE_TRADUCTION') === true ? 'true' : 'false') }} === true">
                                            <i class="fas fa-trash"></i>
                                        </div>
                                    </div>
                                </td>
                                <td :style="(vertical === false ? 'width:'+(70/langues_affichage_tableau.length)+'%' : '')" v-for="langue in langues_affichage_tableau" scope="col">
                                    @if(env('BASE_TRADUCTION') === true)
                                        <textarea v-if="traduction.textarea === true" style="width: 100%;height: 150px;" @change="enregistrement_changement_traduction(traduction,langue)" v-model="traduction[langue.code].traduction_standard" ></textarea>
                                        <input v-else style="width: 100%;" type="text" @change="enregistrement_changement_traduction(traduction,langue)" v-model="traduction[langue.code].traduction_standard" />
                                    @else
                                        <div v-if="traduction.type == 'specifique'">
                                            <textarea v-if="traduction[langue.code].textarea === true" @dblclick="traduction[langue.code].textarea = false;$forceUpdate();" style="width:100%;height: 150px;background-color:rgba({{$rgba}});"  @change="enregistrement_changement_traduction(traduction,langue)" v-model="traduction[langue.code].traduction_specifique"></textarea>
                                            <input v-else style="width:100%;background-color:rgba({{$rgba}});" @dblclick="traduction[langue.code].textarea = true;$forceUpdate();"  @change="enregistrement_changement_traduction(traduction,langue)" type="text" v-model="traduction[langue.code].traduction_specifique" />
                                        </div>
                                        <div v-else-if="traduction[langue.code].traduction_specifique == null || traduction[langue.code].traduction_specifique == ''">
                                            <textarea v-if="traduction[langue.code].textarea === true" @dblclick="traduction[langue.code].textarea = false;$forceUpdate();" style="width: 100%;height: 150px;"  @change="affectation_valeur_specifique($event,traduction,langue)" :value="traduction[langue.code].traduction_standard" ></textarea>
                                            <input v-else style="width: 100%;" @dblclick="traduction[langue.code].textarea = true;$forceUpdate();"  @change="affectation_valeur_specifique($event,traduction,langue)" type="text" :value="traduction[langue.code].traduction_standard" />
                                        </div>
                                        <div style="display: inline-flex;width: 100%;" v-else>
                                            <textarea  v-if="traduction[langue.code].textarea === true" @dblclick="traduction[langue.code].textarea = false;$forceUpdate();" style="width:100%;background-color:rgba({{$rgba}});height: 150px;" @change="enregistrement_changement_traduction(traduction,langue)" v-model="traduction[langue.code].traduction_specifique" ></textarea>
                                            <input v-else style="width:100%;background-color:rgba({{$rgba}});" @dblclick="traduction[langue.code].textarea = true;$forceUpdate();" @change="enregistrement_changement_traduction(traduction,langue)" type="text" v-model="traduction[langue.code].traduction_specifique" />
                                            <span style="width: 30px;background-color: red;height: 30px;cursor: pointer;display: inline-flex;align-items: center;justify-content: center;"  @click="traduction[langue.code].traduction_specifique = null;enregistrement_changement_traduction(traduction,langue)">
                                                <i class="fas fa-times" style="color: white;"></i>
                                            </span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        </template>
                    </template>
                    <template v-if="traductions_par_sous_categorie.length == 0 && chargement_table_traductions == false">
                        <tr>
                            <td style="text-align:center;padding: 15px;" :colspan="langues_affichage_tableau.length+1">
                               @traduction('composant.traduction_table.aucune_traduction_disponible')  <traduction-action :categorie="categorie" :index_traduction_par_defaut="filtrage_index" ></traduction-action>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
       `,
   props: {
       filtrage_index:{
           type: String,
           default: '',
       },
       langues_affichage_tableau : {
           type: Array,
           default: function() {
               return this.$root.langues_traduction_erp;
           },
       },
       recherche: {
           type: String,
           default: '',
       },
       filtres_types: {
           type: Object,
           default: function() {
               return {
                   'standard' : true,
                   'specifique': true,
               }
           },
       },
       filtres_valeurs_vides:{},
       filtres_recherches: {},
       categorie:0,
       vertical:{
           type: Boolean,
           default: false,
       },
    },
    data:function(){
        return {
            traductions_par_sous_categorie:[],
            requete_recuperation_traductions : null,
            chargement_table_traductions: false,
            index_tableau_traduction : 'traductions',
        }
    },
    methods:{

        affectation_valeur_specifique(event,traduction,langue){

            traduction[langue.code].traduction_specifique = $(event.target).val();

            this.enregistrement_changement_traduction(traduction,langue);
        },

        enregistrement_changement_traduction: function(traduction,langue){

            var vue_instance = this;

            vue_instance.$root.$emit('enregistrement_en_cours');
            var traduction_langue = {'index' : traduction.index,'type' : traduction.type};
            traduction_langue[langue.code] = traduction[langue.code];

            $.post({
                url : '/eden/parametrage/traduction/enregistrer',
                dataType : 'json',
                data : traduction_langue,
            }).done(function(donnees){

                if(donnees.retour == false){
                    toastr.error(donnees.message);
                    return;
                }

                if(traduction[langue.code].traduction_specifique == null)
                    vue_instance.$root.traductions_valeurs[traduction.index] = traduction[langue.code].traduction_standard;
                else
                    vue_instance.$root.traductions_valeurs[traduction.index] = traduction[langue.code].traduction_specifique;

                vue_instance.$root.$emit('enregistrement_termine');

                @if(env('BASE_TRADUCTION') !== true)
                    traduction_langue[langue.code].type = 'specifique';
                @endif

            });
        },

        async supprimer_traduction(traduction,traductions,index_tableau){

            var vue_instance = this;

            if(!await confirm_eden(vue_instance.$root.traduction('interface.listes.etes_vous_certain')))
                return false;

            loading(true);

            $.post({
                url : '/eden/parametrage/traduction/supprimer',
                dataType : 'json',
                data : traduction,
            }).done(async function(donnees){

                loading(false);

                if(donnees.retour == false){
                    toastr.error(donnees.message);
                    return;
                }
                vue_instance.charger_traductions();
            });

        },

        charger_traductions(){

            var vue_instance = this;

            if(vue_instance.categorie === null)
                return;

            if(vue_instance.requete_recuperation_traductions !== null)
                vue_instance.requete_recuperation_traductions.abort();

            vue_instance.$root.$emit('chargement_table_traductions');
            vue_instance.chargement_table_traductions = true;

            vue_instance.requete_recuperation_traductions = $.ajax({
                url : '/eden/parametrage/traduction/categorie/'+vue_instance.categorie+'?filtrage_index='+vue_instance.filtrage_index,
                dataType : 'json',
            }).done(function(traductions_par_sous_categorie){

                vue_instance.requete_recuperation_traductions = null;

                vue_instance.traductions_par_sous_categorie = traductions_par_sous_categorie;

                vue_instance.calcul_traductions_a_afficher();
            });
        },

        calcul_traductions_a_afficher:async function(){

            var vue_instance = this;

            var recherche = this.recherche.toLowerCase();

            this.$root.$emit('chargement_table_traductions');
            vue_instance.chargement_table_traductions = true;

            var traductions_par_sous_categorie = [];

            traductions_par_sous_categorie = JSON.parse(JSON.stringify(vue_instance.traductions_par_sous_categorie));

            setTimeout(async function(){

                var filtres_valeurs_vides_false = true;

                $.each(vue_instance.filtres_valeurs_vides,function(osef,valeur){
                    if(valeur === true)
                        filtres_valeurs_vides_false = false;

                });

                if(vue_instance.recherche == '' && vue_instance.filtres_types.standard && vue_instance.filtres_types.specifique && filtres_valeurs_vides_false){
                    vue_instance.index_tableau_traduction = 'traductions';
                    vue_instance.$root.$emit('fin_chargement_table_traductions');
                    vue_instance.chargement_table_traductions = false;
                    vue_instance.$forceUpdate();
                    return;
                }

                vue_instance.traductions_par_sous_categorie = [];

                vue_instance.index_tableau_traduction = 'traductions_a_afficher';

                await traductions_par_sous_categorie.forEach(function(sous_categorie){

                    sous_categorie.traductions_a_afficher = [];

                    sous_categorie.traductions.forEach(function(traduction, index){

                        var correspondance_type = vue_instance.filtres_types[traduction.type];

                        var correspondance_valeur_vide = true;

                         $.each(traduction,function(index_information,information){

                             if(index_information == 'index' || index_information == 'index_categorie')
                                 return;

                             if(vue_instance.filtres_valeurs_vides[index_information] === true && ((information.traduction_specifique != null && information.traduction_specifique != '') || (information.traduction_standard != null && information.traduction_standard != '')))
                                 correspondance_valeur_vide = false;
                         });

                        if(correspondance_type && correspondance_valeur_vide){

                            if(vue_instance.recherche == '')
                                sous_categorie.traductions_a_afficher.push(traduction);
                            else{

                                var correspondance_recherche = false;

                                $.each(traduction,function(index_information,information){

                                    if(vue_instance.filtres_recherches[index_information] === false)
                                        return;

                                    if(index_information !== 'index_categorie' && index_information !== 'index'){

                                        if(information.traduction_specifique != null)
                                            information = information.traduction_specifique;
                                        else
                                            information = information.traduction_standard;
                                    }

                                    if(correspondance_recherche == true || typeof information !== 'string')
                                        return;

                                    if(vue_instance.filtres_recherches.type_recherche == 'contient')
                                        correspondance_recherche = information.toLowerCase().includes(recherche);


                                    else if(vue_instance.filtres_recherches.type_recherche == 'egal')
                                        correspondance_recherche = information.toLowerCase().localeCompare(recherche) == 0;

                                    else if(vue_instance.filtres_recherches.type_recherche == 'commence_par')
                                        correspondance_recherche = information.toLowerCase().startsWith(recherche);


                                });

                                if(correspondance_recherche)
                                    sous_categorie.traductions_a_afficher.push(traduction);
                            }

                        }
                    });

                });

                vue_instance.traductions_par_sous_categorie = traductions_par_sous_categorie;

                vue_instance.$root.$emit('fin_chargement_table_traductions');
                vue_instance.chargement_table_traductions = false;
            },50);
            vue_instance.$forceUpdate();

        },
    },
    mounted: function(){

         this.charger_traductions();

         var vue_instance = this;

         vue_instance.$root.$on('enregistrement_traduction',function(){
            vue_instance.charger_traductions();
         });
    },
    watch:{
        categorie:function(){
            this.charger_traductions();
        },
    }
});
</script>
