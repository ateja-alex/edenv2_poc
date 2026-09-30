<script>
const questionnaire_reponses = Vue.component('questionnaire-reponses', {
    template: `<div>
                    <div style="display: flex;flex-direction: column;gap: 10px;">
                        <div class="ml-auto" style="display: flex;gap: 10px;align-items: center;">
                            <a :href="'/eden/questionnaire/'+this.questionnaire_id+'/export?'+parametres_export" class="css_action_icon" style="background: var(--background_navbar);">
                                <i class="fas fa-file-export"></i>
                            </a>
                            <div style="display: flex;">
                                <input class='css_input_recherche_liste js_input_recherche_liste' v-on:keyup.enter="changement_page(1)" :placeholder="$root.traduction('interface.listes.recherche')" style="border: 1px solid var(--background_navbar);padding-left: 5px;" type="text" name="recherche" v-model="recherche" />
                                <div style="background: var(--background_navbar);" class="css_btn_recherche_liste" @click="changement_page(1)">
                                    <i class="fa fa-search" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div style="width: 100%; overflow: auto; max-height: 400px;">
                            <table style="padding-left:2px;padding-right:2px;min-width: 100%;" class="table table-bordered table-hover" :class="(chargement_reponses === true ? 'css_actualisation_ajax_en_cours' : '')">
                                <thead>
                                    <tr>
                                      <th v-for="colonne in colonnes_sources" v-html="$root.traduction('champs_libres.questionnaire_element_repondant.'+colonne+'.nom')">
                                      <th v-for="colonne in colonnes_questions" v-html="colonne.question_brut"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="groupe_reponses_questionnaires in groupes_reponses_questionnaires">
                                        <td v-for="colonne in colonnes_sources" v-html="groupe_reponses_questionnaires[colonne]"></td>
                                        <td v-for="colonne in colonnes_questions" v-html="groupe_reponses_questionnaires['question_'+colonne.id]"></td>
                                    </tr>
                                    <tr v-if="Object.values(groupes_reponses_questionnaires).length == 0">
                                        <td :colspan="colonnes_questions.length +5">@traduction('interface.listes.aucun_element')</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <nav class='css_nav_pagination_listes' >
                            <ul class="pagination css_pagination_perso">
                                <li class="page-item" v-show="page_active > 1">
                                    <a class="page-link" @click="changement_page(1)"><span>@traduction('composant.timeline.premiere_page')</span></a>
                                </li>

                                <li :class="{'page-item':true, 'active':(page === page_active)}" v-for="page in nombre_de_pages" v-show="page >= page_active-4 && page <= page_active+4">
                                    <a class="page-link" @click="changement_page(page)">@{{ page }}</a>
                                </li>

                                <li class="page-item" v-show="page_active < nombre_de_pages">
                                    <a class="page-link" @click="changement_page(nombre_de_pages)"><span>@traduction('composant.timeline.derniere_page')</span></a>
                                </li>
                            </ul>
                        </nav>
                        <div class="row">
                            <div class="col-md-4 offset-md-4" v-html="nombre_de_pages + ' pages'" style="text-align: center;"></div>
                        </div>
                    </div>
              </div>`,
    props:{
        questionnaire_id: {
            type: Number,
            default:0,
        },
        questions : {
            type: Array,
            default:[],
        },
        parametres: {
            type: Object,
            default:function(){
                return {};
            },
        }
    },
    data : function(){

        return {
            groupes_reponses_questionnaires : {},
            chargement_reponses : true,
            page_active : 1,
            nombre_de_pages : 1,
            recherche : ''
        }
    },
    mounted : function(){

        var vue_instance = this;

        vue_instance.charger_reponses();
    },
    methods: {
        charger_reponses : function(){

            var vue_instance = this;

            this.chargement_reponses = true;

            $.ajax({
                url:'/eden/questionnaire/'+vue_instance.questionnaire_id+'/reponses',
                dataType:'json',
                data:{
                    parametres : vue_instance.parametres,
                    parametres_liste : {
                        page: vue_instance.page_active,
                        recherche: vue_instance.recherche,
                    }
                },
            }).done(function(donnees){

                vue_instance.groupes_reponses_questionnaires = donnees.reponses;

                vue_instance.nombre_de_pages = donnees.nombre_de_pages;

                vue_instance.chargement_reponses = false;
            });
        },
        changement_page : function(page){

            this.page_active = page;

            this.charger_reponses();
        },
    },
    computed: {

         colonnes_sources : function(){

            var colonnes_source = [
                'cree_le',
                'type_element',
                'element_id_affichage',
                'type_element_origine',
                'element_origine_id_affichage'
            ];

            var colonnes_source_a_afficher = [];

            for(const [index,colonne] of colonnes_source.entries()){

                if(this.parametres[colonne] == undefined)
                    colonnes_source_a_afficher.push(colonne);
            }

            return colonnes_source_a_afficher;
         },
        colonnes_questions : function(){

             var colonnes = [];

             for(question of this.questions){

                 if(question.type != 7)
                     colonnes.push(question);
             }

             return colonnes;
        },
        parametres_export : function(){

            var vue_instance = this;

            var parametres = 'parametres='+encodeURIComponent(JSON.stringify(vue_instance.parametres));
            var parametres_liste = 'parametres_liste='+encodeURIComponent(JSON.stringify({recherche: vue_instance.recherche}));

            return parametres+'&'+parametres_liste;
        },
    },
});
</script>
