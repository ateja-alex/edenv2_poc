<script>
const questionnaire = Vue.component('questionnaire', {
    template: `<div class="questionnaire">
                    <div class="row" v-if="chargement_questionnaire">
                        <div class="col-md-12 loader">
                          <img src="/eden/images/ajax_loader.gif">
                        </div>
                    </div>
                    <div class="row" v-else-if="questionnaire_valide">
                        <div class="col-md-12">
                            <div class="card mb-3">
                                <div class="card-header" v-if="affichage_client === false">
                                    <div class="entete">
                                        <h4>
                                            @{{ questionnaire.nom }}
                                        </h4>
                                        <template v-if="$root.mode_parametrage == 1">
                                            <div class="ml-md-3 bouton_parametrage">
                                              <a target="_blank" :href="'/eden/fiche/questionnaire/'+questionnaire_id" data-toggle="tooltip" data-placement="right" :title="'Paramétrer questionnaire'" class="css_bouton_modifier_liste_primaire">
                                                <i class="css_action_icon fas fa-cog"></i>
                                              </a>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="ml-auto">
                                        <span v-if="questionnaire.reponses_multiples == 1" @click="affichage_tableau_reponses = !affichage_tableau_reponses" class="css_ajouter_element" data-toggle="tooltip" data-placement="left" title="Afficher les réponses">
                                            <i class="css_action_icon far fa-comments css_font_16"></i>
                                        </span>
                                        <span @click="enregistrer(false)" class="css_ajouter_element" data-toggle="tooltip" data-placement="left" id="formulaire_fiche_enregistrer" :title="$root.traduction('composant.formulaire_fiche.enregistrer')">
                                            <i class="css_action_icon far fa-save css_font_16"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="fiche_bloc_questionnaire">

                                        <template v-for="question in questions">

                                            <div v-if="verification_conditionnel(question.id)" :class="'col-md-'+question.taille+' question'">

                                                <label v-if="question.type != 7 && question.type != 9" :for="question.id" v-html="question.question+(question.obligatoire ? '*' : '')" ></label>

                                                <div v-if="question.type == 1" class="css_notation_questionnaire">

                                                    <!-- Boucle pour afficher les images NPS -->
                                                    <div v-if="question.format == 'net_promoter_score'" class="d-flex question_nps">
                                                        @for($i = 10 ; $i >= 0 ; $i--)
                                                            <label @click="$set(reponse_questionnaire,question.id,(reponse_questionnaire[question.id] == {{$i}} ? null : {{$i}}))" class="image_nps">
                                                                <img v-if="{{$i}} >= 0 && {{$i}} <= 6" src="/eden/images/pictos/nps_pas_content.png" alt="Grimace" :class="{ 'image-selected': reponse_questionnaire[question.id] == {{$i}} }">
                                                                <img v-else-if="{{$i}} >= 7 && {{$i}} <= 8" src="/eden/images/pictos/nps_moyen_content.png" alt="Neutre" :class="{ 'image-selected': reponse_questionnaire[question.id] == {{$i}} }">
                                                                <img v-else src="/eden/images/pictos/nps_content.png" alt="Sourire" :class="{ 'image-selected': reponse_questionnaire[question.id] == {{$i}} }">
                                                                <span :style="reponse_questionnaire[question.id] == {{$i}} ? 'font-weight : bold;' : ''">{{$i}}</span>
                                                            </label>
                                                        @endfor
                                                    </div>

                                                    <!-- Boucle pour afficher les étoiles -->
                                                    <div v-else>
                                                        <template v-for='etoile in parseInt(question.texte ?? 10)'>
                                                            <label @click="reponse_questionnaire[question.id] = (reponse_questionnaire[question.id] == etoile ? null : etoile);$forceUpdate();" :class="'etoile fa-star '+(Number.isInteger(parseInt(reponse_questionnaire[question.id])) && etoile <= reponse_questionnaire[question.id] ? 'fas' : 'far')"></label>
                                                            <input type="radio" :value="etoile" :name="'reponse['+question.id+']'" v-model="reponse_questionnaire[question.id]" class="d-none" required>
                                                        </template>
                                                    </div>

                                                    <input type="radio" value="{{$i}}" :name="'reponse['+question.id+']'" v-model="reponse_questionnaire[question.id]" class="d-none" required>
                                                </div>

                                                <div v-else-if="question.type == 2">
                                                    <input :id="question.id" type="text" class="form-control" :name="'reponse['+question.id+']'" v-model="reponse_questionnaire[question.id]" required>
                                                </div>
                                                <div class="css_notation_questionnaire" v-else-if="question.type == 5">
                                                    <select v-model="reponse_questionnaire[question.id]" :name="'reponse['+question.id+']'" class="form-control" required>
                                                        <option value="0" v-if="!question.obligatoire" v-html="$root.traduction('valeurs_listes_formatees.3.valeur_0')"></option>
                                                        <template v-for="option in options[question.id]">
                                                            <option :value="option.id">@{{option.option}}</option>
                                                        </template>
                                                    </select>
                                                </div>
                                                <div v-else-if="question.type == 6">
                                                    <input :id="question.id" type="date" class="form-control" :name="'reponse['+question.id+']'" v-model="reponse_questionnaire[question.id]" required>
                                                </div>
                                                <div v-else-if="question.type == 7" v-html="question.texte"></div>
                                                <div v-else-if="question.type == 8" class="question_checkbox">
                                                    <template v-for="checkbox in question.checkbox">
                                                        <div>
                                                            <input :id="question.id+'['+checkbox.valeur_checkbox+']'" @change="$forceUpdate()" type="checkbox" :name="'reponse['+question.id+'][]'" v-model="reponse_questionnaire[question.id]" :value="checkbox.valeur_checkbox">
                                                            <label :for="question.id+'['+checkbox.valeur_checkbox+']'">@{{checkbox.nom_checkbox}}</label><br>
                                                        </div>
                                                    </template>
                                                </div>
                                                <div v-else-if="question.type == 9" class="question_oui_non">
                                                    <label :for="question.id" v-html="question.question+(question.obligatoire ? '*' : '')" ></label>
                                                    <input :id="question.id" :true-value="1" :false-value="0" type="checkbox" :name="'reponse['+question.id+']'" v-model="reponse_questionnaire[question.id]" required>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <div v-if="affichage_tableau_reponses">
                                        <questionnaire-reponses ref="questionnaire_reponses" :questionnaire_id="questionnaire_id" :parametres="parametres" :questions="questions">
                                        </questionnaire-reponses>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
              </div>`,
    props:{
        questionnaire_id: {
            type: Number,
            default:0,
        },
        affichage_client:{
            type : Boolean,
            default:false,
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
            questionnaire : {},
            questions : {},
            options : {},
            reponse_questionnaire : {},
            chargement_questionnaire : true,
            affichage_tableau_reponses : false,
        }
    },
    mounted : function(){
        
        this.charger_questions();
    },
    methods: {
        charger_questions : function(){
            
            if(this.questionnaire_valide === false)
                return;

            this.chargement_questionnaire = true;

            $.ajax({
                url:'/eden/questionnaire/'+this.questionnaire_id+'/prepare_questionnaire',
                dataType:'json',
                data:this.parametres,
            }).done((donnees) => {

                this.questionnaire = donnees.questionnaire;
                this.questions = donnees.questions;
                this.options = donnees.options;

                if(donnees.reponse_questionnaire !== undefined)
                    this.reponse_questionnaire = donnees.reponse_questionnaire;

                if(Object.values(this.reponse_questionnaire).length == 0)
                    this.reponse_questionnaire = {};

                donnees.questions.forEach((question, index) => {

                    if (question.type == 8){
                        if(this.reponse_questionnaire[question.id] == undefined)
                            this.reponse_questionnaire[question.id] = [];
                        else
                            this.reponse_questionnaire[question.id] = JSON.parse(this.reponse_questionnaire[question.id])
                    }
                });

                this.chargement_questionnaire = false;
            });
        },
        enregistrer : async function(enregistrement_multiple = false){
            
            var reponses = this.reponse_questionnaire;

            var champs_obligatoires_non_remplis = []

            for(question of this.questions){

                if(question.obligatoire == 1 && ( reponses[question.id] == '' ||
                    reponses[question.id] == null || (question.type == 8 && (!reponses[question.id] || reponses[question.id].length == 0))))
                    champs_obligatoires_non_remplis.push(question.question);
            }

            if(champs_obligatoires_non_remplis.length > 0) {

                var erreur_texte = this.$root.traduction('messages.php.champs_obligatoires')+' ' + champs_obligatoires_non_remplis.join(', ');

                if(enregistrement_multiple === false)
                    erreur(erreur_texte);

                var retour = {
                    questionnaire : this.questionnaire,
                    erreur : erreur_texte
                };

                return retour;
            }

            if(enregistrement_multiple === false)
                loading(true);

            var retour = await $.post({
                url:'/eden/questionnaire/'+this.questionnaire_id+'/enregistrer',
                dataType:'json',
                data: {
                    parametres : this.parametres,
                    reponses : reponses
                },
            }).done(async (donnees) =>{

                if(enregistrement_multiple === false)
                    loading(false);

                if(donnees.retour !== true) {

                    if(enregistrement_multiple === false)
                        await erreur(donnees.erreur);

                    return donnees;
                }

                if(enregistrement_multiple === false)
                    info(this.$root.traduction('messages.js.enregistrement_succes'));

                if(this.questionnaire.reponses_multiples) {
                    this.reponse_questionnaire = {};
                    this.questions.forEach((question, index) => {

                        if (question.type == 8 && this.reponse_questionnaire[question.id] == undefined)
                            this.reponse_questionnaire[question.id] = [];
                    });
                    if(this.$refs.questionnaire_reponses !== undefined)
                        this.$refs.questionnaire_reponses.changement_page(1);
                }

                return donnees;
            });

            retour.questionnaire = this.questionnaire;

            return retour;
        },
        verification_conditionnel: function(question_a_tester_id){

            var variable_retour = false;

            var question_a_tester = {};

            this.questions.forEach((question, index) => {

                if(question.id == question_a_tester_id)
                    question_a_tester = question;
            });

            // Pas de condition sur l'affichage
            if(!question_a_tester.affichage_conditionnel)
                return true;

            var question_conditionnel = {};
            var question_conditionnel_id = question_a_tester.affichage_conditionnel.question_id;

            // On récupère la question à vérifier
            this.questions.forEach((question, index) => {

            if(question_conditionnel_id == question.id)
                question_conditionnel = question;
            });

            // Note
            if(question_conditionnel.type == 1){

                var operateur = question_a_tester.affichage_conditionnel.operateur;
                var valeur_voulu = question_a_tester.affichage_conditionnel.valeur_a_tester;
                var valeur_actuel = this.reponse_questionnaire[question_conditionnel.id];

                if(!valeur_actuel)
                    return false;

                var resultat_operation = window.eval(valeur_actuel + operateur + valeur_voulu);

                if(resultat_operation)
                    variable_retour = true;
            }

            // Liste ou article ou entité
            if(question_conditionnel.type == 5){

                var valeur_voulu = question_a_tester.affichage_conditionnel.valeur_a_tester;
                var valeur_actuel = this.reponse_questionnaire[question_conditionnel.id];

                if(!valeur_actuel)
                    return false;

                if(valeur_actuel == valeur_voulu)
                    variable_retour = true;
            }

            // Date
            if(question_conditionnel.type == 6){

                var valeur_voulu = question_a_tester.affichage_conditionnel.valeur_a_tester;
                var valeur_actuel = this.reponse_questionnaire[question_conditionnel.id];
                var operateur = question_a_tester.affichage_conditionnel.operateur;

                if(!valeur_actuel)
                    return false;

                if(operateur == "le" && valeur_voulu == valeur_actuel)
                    variable_retour = true;

                if(operateur == "apres_le" && valeur_voulu < valeur_actuel)
                    variable_retour = true;

                if(operateur == "avant_le" && valeur_voulu > valeur_actuel)
                    variable_retour = true;
            }

            // Choix multiple
            if(question_conditionnel.type == 8){

                var valeur_voulu = question_a_tester.affichage_conditionnel.valeur_a_tester;
                var valeur_actuel = this.reponse_questionnaire[question_conditionnel.id];
                var operateur = question_a_tester.affichage_conditionnel.operateur;

                if(!valeur_actuel && operateur == "contient")
                    return false;

                if(operateur == "tout_sauf" && !valeur_actuel)
                    return true;

                var verification_absent = false;

                valeur_voulu.forEach((checkbox, index) => {

                    if(!valeur_actuel.includes(checkbox) && operateur == "contient")
                        verification_absent = true;

                    if(valeur_actuel.includes(checkbox) && operateur == "tout_sauf")
                        verification_absent = true;
                });

                if(!verification_absent)
                    variable_retour = true;
            }

            if(question_conditionnel.type == 9){
                var valeur_voulu = question_a_tester.affichage_conditionnel.valeur_a_tester;
                var valeur_actuel = this.reponse_questionnaire[question_conditionnel.id];

                if(valeur_voulu == 'false')
                    return valeur_actuel == false || valeur_actuel == null;

                return eval(valeur_voulu) == valeur_actuel;
            }

            return variable_retour;
        },
    },
    computed: {

         questionnaire_valide : function(){

            return !isNaN(parseInt(this.questionnaire_id)) && parseInt(this.questionnaire_id) > 0;
         },
    },
});
</script>
