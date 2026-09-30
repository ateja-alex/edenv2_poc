<div class="modal-header">
    <h5 class="modal-title">@traduction('formulaire.modele_de_calculateur.calculateur') - @{{ modele_de_calculateur.calculateur.quantite.designation }}</h5>
</div>

<div class="modal-body css_form js_selection_element" >

    <div>
        <h6 style="font-weight:bold;">@traduction('formulaire.modele_de_calculateur.quantite')</h6>
        <div class="row">
            <span class="col-md-2">@traduction('formulaire.modele_de_calculateur.designation')</span>
            <input type="text" class="col-md-4" v-model="modele_de_calculateur.calculateur.quantite.designation" :name="'calculateur[quantite][designation]'"/>
            <input type="hidden" class="col-md-4" v-model="modele_de_calculateur.id" name="id"/>
        </div>
    </div>

    <div style="margin-top:15px;">
        <h6 style="font-weight:bold;">@traduction('formulaire.modele_de_calculateur.creation_de_variable')</h6>
        <table class="table table-bordered table-hover">

            <thead>

            <tr class="css_tableau_titre">
                <th></th>
                <th>@traduction('formulaire.modele_de_calculateur.categorie')</th>
                <th>@traduction('formulaire.modele_de_calculateur.titre')</th>
                <th>@traduction('formulaire.modele_de_calculateur.valeur')</th>
                <th>@traduction('formulaire.modele_de_calculateur.resultat')</th>
                <th>@traduction('formulaire.modele_de_calculateur.variable')</th>
                <th>@traduction('formulaire.modele_de_calculateur.options')</th>
            </tr>

            </thead>
            <tbody>

                <template v-for="(variable,variable_index) in modele_de_calculateur.calculateur.variables">

                    <tr >
                        <td>
                            <span >@traduction('formulaire.modele_de_calculateur.article')</span>

                        </td>
                        <td><input type="text" v-model="variable.categorie" :name="'calculateur[variables][' + variable_index + '][categorie]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);"/></td>
                        <td><input type="text" v-model="variable.titre" :name="'calculateur[variables][' + variable_index + '][titre]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);" /></td>
                        <td>
                            <div class="calculateur_input_valeur">
                                <input type="text" :style="'background:' + variable.couleur" v-model="variable.valeur" :name="'calculateur[variables][' + variable_index + '][valeur]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);" />
                                <input type="color" v-model="variable.couleur" :name="'calculateur[variables][' + variable_index + '][couleur]'"/>
                            </div>
                        </td>
                        <td><input type="text" v-model="variable.resultat" disabled/></td>
                        <td><input type="text" v-model="variable.nom" :name="'calculateur[variables][' + variable_index + '][nom]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);"/></td>
                        <td><span class="far fa-trash-alt" @click="supprimer_element_calculateur(modele_de_calculateur.calculateur.variables,variable_index);" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur)"></span></td>
                    </tr>

                </template>

            </tbody>

        </table>

        <span class="css_ajouter_ligne_nomenclature" @click="ajouter_variable_article(modele_de_calculateur);">+ <span>@traduction('formulaire.modele_de_calculateur.variable')</span></span>
    </div>

    <div style="margin-top:15px;">
        <h6 style="font-weight:bold;">@traduction('formulaire.modele_de_calculateur.ponderations_conditionnelles')</h6>

            <div v-for="(ponderation,ponderation_index) in modele_de_calculateur.calculateur.ponderation" style="border: solid #bababa 1px;padding:10px; margin: 20px 0px;">
                <div class="row">
                    <span class="col-md-2">@traduction('formulaire.modele_de_calculateur.variable')</span>
                    <input type="text" class="col-md-4" :name="'calculateur[ponderation][' + ponderation_index + '][designation]'" v-model="ponderation.designation" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);"/>
                    <span style="line-height: 30px;" class="col-md-4 far fa-trash-alt" @click="supprimer_element_calculateur(modele_de_calculateur.calculateur.ponderation,ponderation_index);"></span>
                </div>
                <table class="table" style="border:none;">
                    <thead style="border:none;">

                    <tr style="border:none;">
                        <th style="border:none;font-weight:bold;">@traduction('formulaire.modele_de_calculateur.condition')</th>
                        <th style="border:none;font-weight:bold;"></th>
                        <th style="border:none;font-weight:bold;"></th>
                        <th style="border:none;font-weight:bold;">@traduction('formulaire.modele_de_calculateur.critere')</th>
                        <th style="border:none;font-weight:bold;">@traduction('formulaire.modele_de_calculateur.comp')</th>
                        <th style="border:none;font-weight:bold;">@traduction('formulaire.modele_de_calculateur.valeur')</th>
                        <th style="border:none;"></th>
                        <th style="border:none;"></th>
                        <th style="border:none;"></th>
                        <th style="border:none;font-weight:bold;">@traduction('formulaire.modele_de_calculateur.resultat')</th>
                        <th style="border:none;"></th>
                    </tr>

                    </thead>
                    <tbody style="border:none;">

                    <tr v-for="(condition,condition_index) in ponderation.conditions" style="border:none;">

                        <td style="border:none;" class="align-top">
                            <select v-model="condition.type" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][type]'" @change="check_condition(ponderation, condition);calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);">

                                <option value="0">@traduction('formulaire.modele_de_calculateur.sinon')</option>
                                <option value="1">@traduction('formulaire.modele_de_calculateur.si')</option>

                            </select>
                        </td>

                        <td style="border: none;">
                            <template v-for="(donnees,index) in condition.donnees">
                                <span style="display: block;line-height: 30px" @click="supprimer_element_calculateur(condition.donnees, index);calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);" class="far fa-trash-alt"></span>
                            </template>
                        </td>

                        <td style="border: none;">
                        </td>

                        <td style="border:none;">
                            <template v-for="(donnees,index) in condition.donnees">
                                <input type="text" v-model="donnees.critere" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][donnees][' + index + '][critere]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);"></input>
                            </template>
                        </td>
                        <td style="border:none;">
                            <template v-for="(donnees,index) in condition.donnees">
                                <select v-model="donnees.comp" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][donnees][' + index + '][comp]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);">

                                    <option value=">">></option>
                                    <option value="<"><</option>
                                    <option value=">=">>=</option>
                                    <option value="<="><=</option>
                                    <option value="==">=</option>

                                </select>
                            </template>
                        </td>
                        <td style="border:none;">

                            <template v-for="(donnees,index) in condition.donnees">
                                <input type="text" v-model="donnees.valeur" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][donnees][' + index + '][valeur]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);"></input>
                            </template>

                        </td>

                        <td style="border:none;" class="align-top">

                            <template v-for="(donnees,index) in condition.donnees" v-if="condition.donnees.length - 1 > index" >
                                <select v-model="donnees.cond" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][donnees][' + index + '][cond]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);">
                                    <option value="&&">@traduction('formulaire.modele_de_calculateur.et')</option>
                                    <option value="||">@traduction('formulaire.modele_de_calculateur.ou')</option>
                                </select>
                            </template>

                        </td>

                        <td style="border:none;" class="align-bottom">
                            <span v-if="condition.type == 1" class="far fa-plus-square" @click="ajouter_condition_conditions(ponderation, condition)"></span>
                        </td>
                        <td style="border:none;" class="align-bottom">@traduction('formulaire.modele_de_calculateur.alors')</td>
                        <td style="border:none;" class="align-bottom"><input type="text" v-model="condition.resultat" :name="'calculateur[ponderation][' + ponderation_index + '][conditions][' + condition_index + '][resultat]'" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);$forceUpdate()"></input></td>
                        <td style="border:none;" class="align-bottom">
                            <span class="far fa-trash-alt" @click="supprimer_condition_article(ponderation, condition_index);calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);$forceUpdate();"></span>
                        </td>


                    </tr>

                    </tbody>
                </table>
                <span class="css_ajouter_ligne_nomenclature" @click="ajouter_condition_article(ponderation)">+ <span>@traduction('formulaire.modele_de_calculateur.condition')</span></span>
            </div>

            <span class="css_ajouter_ligne_nomenclature" @click="ajouter_ponderation_article(modele_de_calculateur)">+ <span>@traduction('formulaire.modele_de_calculateur.ponderation')</span></span>
    </div>

    <div style="margin-top:15px;" v-if="modele_de_calculateur.type_ligne == undefined">
        <h6 style="font-weight:bold;">@traduction('formulaire.modele_de_calculateur.calcul_de_la_quantite')</h6>
        <div class="row">
            <span class="col-md-2">@traduction('formulaire.modele_de_calculateur.formule')</span>
            <input type="text" class="col-md-4" v-model="modele_de_calculateur.calculateur.calcul.formule" name="calculateur[calcul][formule]" @change="calculer_total_calculateur_par_article(modele_de_calculateur.calculateur, modele_de_calculateur);$forceUpdate()"/>
            <span class="col-md-2">@traduction('formulaire.modele_de_calculateur.total')</span>
            <input class="col-md-3" v-model="modele_de_calculateur.calculateur.resultat_calcul" readonly/>
        </div>
    </div>

</div>


@push('donnees_pour_vuejs_data')
    variables: {},
@endpush
@push('donnees_pour_vuejs_methods')

    supprimer_element_calculateur : async function(tableau, index){

        var vue_composant = this;

        if(await confirm_eden('{!! traduction('formulaire.modele_de_calculateur.etes_vous_sur_de_vouloir_supprimer') !!}')){
            tableau.splice(index,1);
            vue_composant.$forceUpdate();
        }

    },

    ajouter_variable_article : function(article) {

        var vue_composant = this;

        var calculateur = article.calculateur;

        if(calculateur == undefined || calculateur.length == 0){

            article.calculateur = [{
                calcul: {},
                ponderation: [],
                variables: [],
                quantite: {},
                erreur: false,
            }];

        }

        article.calculateur.variables.push({

            nom: "",
            titre: "",
            resultat: 0,
            valeur: "",
            ordre: article.calculateur.variables.length,

        });

        vue_instance.$forceUpdate();
    },

    ajouter_condition_conditions : function(ponderation, condition) {

        var vue_instance = this;
    
        condition.donnees.push({

            critere: "",
            comp: 0,
            valeur: "",

        });

        vue_instance.taille_tableau(ponderation);
        vue_instance.$forceUpdate();

    },

    ajouter_ponderation_article : function(article) {

        var vue_composant = this;

        var calculateur = article.calculateur;

        if(calculateur == undefined || calculateur.length == 0){

            article.calculateur = [{
                calcul: {},
                ponderation: [],
                variables: [],
                quantite: {},
                erreur: false,
            }];

        }

        article.calculateur.ponderation.push({

            designation: "",
            conditions: [],
            taille_tableau: [],

        });

        vue_instance.$forceUpdate();
    },

    ajouter_condition_article : function(ponderation) {
    
        var vue_instance = this;

        ponderation.conditions.push({

            type: 0,
            donnees: [],

        });

        vue_instance.taille_tableau(ponderation);

        vue_instance.$forceUpdate();
    },

    supprimer_condition_article : function(ponderation, condition_index) {

        var vue_instance = this;
    
        ponderation.conditions.splice(condition_index,1);

        vue_instance.taille_tableau(ponderation)

    },

    taille_tableau : function(ponderation){

        var max = 0;

        ponderation.conditions.forEach(function(conditions, index){

            if(conditions.donnees.length > max){

                max = conditions.donnees.length;

            }

        });

        ponderation.taille_tableau = [];

        if(max > 0){
            for(let i = 0 ; i < max ; i++){

                ponderation.taille_tableau[i] = i;

            }
        }

    },

    check_condition(ponderation, condition){

        var vue_instance = this;
    
        if(condition.type == 1 && condition.donnees.length == 0){

            condition.donnees.push({

                critere: "",
                comp: 0,
                valeur: "",

            });

        }

        else if(condition.type == 0 && condition.donnees.length > 0){

            condition.donnees = [];

        }

        vue_instance.taille_tableau(ponderation)
        vue_instance.$forceUpdate();

    },

    retourne_border_input_quantite_pour_calculateur: function(detail_calculateur) {

        if(detail_calculateur.calculateur == undefined)
            return '';

        if(detail_calculateur.calculateur.resultat_calcul == undefined)
            return '';

        if(detail_calculateur.calculateur.resultat_calcul == '')
            return '';

        if(detail_calculateur.calculateur.resultat_calcul != detail_calculateur.quantite)
            return 'border: 3px solid #e98a1df5;';

        return '';
    },

    /**
    *
    * Calcul une chaine de caractère donnée
    *
    */
    calculer: async function(calcul, article, variable_source = false, alert_ou_non = false, premier_alerte = true, ponderation = false){

        var vue_instance = this;
        var calcul_tmp = calcul;
        var erreur = false;

        if(calcul_tmp == null){
            return {
                'erreur': true,
            };
        }

        var index_debut = calcul_tmp.indexOf('[');
        var index_fin = calcul_tmp.indexOf(']');
        if(calcul_tmp == "[]"){
    
            variable_source.erreur = true;
            article.calculateur.erreur = true;
        
            erreur = true;
    
        }
        if(variable_source != false && variable_source.boucle == undefined){
    
            variable_source.boucle = 0;
    
        }
    
        var regex = new RegExp('[a-zA-Z]','g');
    
        if(variable_source.nom == '' || variable_source.designation == ''){
    
            variable_source.erreur = true;
            article.calculateur.erreur = true;
        
            erreur = true;
    
        }
    
        if(index_fin == -1 && regex.test(calcul_tmp) == true){
    
            if(variable_source.nom != undefined && variable_source.resultat != false){
        
                if(alert_ou_non != "no_alerte" && premier_alerte == true){
                    await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.nom]),'{{ traduction('interface.alerte.attention') }}');
                }
                vue_instance.variables[variable_source.nom] = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
        
            }
            else if(variable_source.designation != undefined && variable_source.resultat != false){
        
                if(alert_ou_non != "no_alerte" && premier_alerte == true){
                    await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.designation]) + ' ' + traduction('messages.js.documents.critere_condition_errone'),'{{ traduction('interface.alerte.attention') }}');
                }
                vue_instance.variables[variable_source.designation] = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
    
            }
    
            variable_source.resultat = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
            variable_source.erreur = true;
            article.calculateur.erreur = true;
        
            erreur = true;
    
        }
    
        if(variable_source != false && variable_source.boucle > 2){
    
            if(alert_ou_non != "no_alerte" && premier_alerte == true){
                await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variables_boucle_infinie', null, [variable_source.nom]), '{{ traduction('interface.alerte.attention') }}');
            }
            erreur = true;
            variable_source.erreur = true;
            calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
        }
    
        if(index_fin != -1 && erreur == false){
    
            var index_debut = 0;
            var index_fin = 0;
            var variable = "";
            var erreur = false;
    
            while(index_fin != -1){
        
                index_debut = calcul_tmp.indexOf('[');
                index_fin = calcul_tmp.indexOf(']');
                variable = calcul_tmp.substring(index_debut, index_fin).replace('[','').replace(']','');

                if(variable == variable_source.nom || variable == variable_source.designation){
                    if(alert_ou_non != "no_alerte" && premier_alerte == true){
                        await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_boucle_infinie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
                    }
                    erreur = true;
                    variable_source.erreur = true;
                    calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
            
                }
                if(erreur == true){
            
                    return{
                    erreur : true
                    };
            
                }
        
                else if(variable != "" && variable.length > 0){
                    if(typeof vue_instance.variables[variable] === 'number'){
                        calcul_tmp = calcul_tmp.replace("[" + variable + "]",vue_instance.variables[variable]);
                    }
                    else{
                        var variable_a_definir = article.calculateur.variables.filter(variable_tmp => variable_tmp.nom == variable);
                        if(variable_a_definir.length > 1){
                            if(alert_ou_non != "no_alerte" && premier_alerte == true){
                                await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_deja_definie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
                            }
                            erreur = true;
                            variable_source.erreur = true;
                            calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
                        }
                        else if(variable_a_definir.length == 1){
                            variable_source.boucle += 1;
                            if(premier_alerte == true)
                                premier_alerte = false;
                        
                            var calcul_test = await vue_instance.calculer(variable_a_definir[0].valeur,article,variable_a_definir[0], false, premier_alerte);
                            if(calcul_test.erreur == false){
                        
                                calcul_tmp = calcul_tmp.replace("[" + variable + "]", calcul_test.resultat);
                        
                            }
                            else{
                        
                                if(alert_ou_non != "no_alerte" && premier_alerte == true){
                                    await alerte_eden(vue_instance.$root.traduction('messages.js.documents.erreur_calcul', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
                                }
                                erreur = true;
                                variable_source.erreur = true;
                        
                            }
                    
                        }
                        else{
                            if(alert_ou_non != "no_alerte" && premier_alerte == true){
                                await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_non_definie', null, [variable]), '{{ traduction('interface.alerte.attention') }}');
                            }
                            erreur = true;
                            variable_source.erreur = true;
                            calcul_tmp = calcul_tmp.replace("[" + variable + "]", '');
                        }
                
                    }
                }
        
                if(index_fin == -1 && regex.test(calcul_tmp) == true && variable_source != false){
            
                    if(variable_source.nom && vue_instance.variables[variable_source.nom] == undefined){
                
                        if(alert_ou_non != "no_alerte" && premier_alerte == true){
                            await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.nom]),'{{ traduction('interface.alerte.attention') }}');
                        }
                        vue_instance.variables[variable_source.nom] = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
                
                    }
                    else if(variable_source.designation && vue_instance.variables[variable_source.designation] == undefined){
                
                        if(alert_ou_non != "no_alerte" && premier_alerte == true){
                            await alerte_eden(vue_instance.$root.traduction('messages.js.documents.variable_non_utilisable', null, [variable_source.designation]), '{{ traduction('interface.alerte.attention') }}');
                        }
                        vue_instance.variables[variable_source.designation] = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
                
                    }
                
                    variable_source.resultat = vue_instance.$root.traduction('formulaire.modele_de_calculateur.erreur');
                    variable_source.erreur = true;
                    article.calculateur.erreur = true;
                
                    erreur = true;
            
                }
        
        
            }
    
        }
    
        if(erreur != true){
    
            if(ponderation === true){
                return {
                    'resultat': calcul_tmp,
                    'erreur': erreur,
                };
            }
    
            calcul_tmp = calcul_tmp.replace(/[^-()\d/*+.<>=]/g, '');

            try{
                calcul_tmp = eval(calcul_tmp);
            }
            catch(exception){
                return {
                    'erreur': false,
                };
            }
    
            if(variable_source != false && calcul_tmp != false)
                variable_source.resultat = calcul_tmp.toFixed(2);
        
            vue_instance.variables[variable_source.nom] = calcul_tmp;
            variable_source.boucle = 0;
            variable_source.erreur = false;
        }
    
        if(calcul_tmp != undefined){
            calcul_tmp = Math.round(calcul_tmp * 100) / 100;
        }
    
        return {
            'resultat': calcul_tmp,
            'erreur': erreur,
        };

    },

    /**
    *
    * Récupère les lignes liés à la ligne données de manière hiérarchique et traite leur calculateur
    *
    */
    calculer_total_calculateur_par_article : async function(calculateur, article_mere, article_nomenclature_index = 0){

        var vue_instance = this;

        if(article_mere.calculateur != undefined && article_mere.calculateur != null && Object.keys(article_mere.calculateur).length < 4)
            return;

        article_mere.calculateur.resultat_calcul = null;

        vue_instance.variables = [];

        var article_a_traite = [];

        article_a_traite.push(article_mere);

        var erreur = false;

        for(let [article_index, article] of Object.entries(article_a_traite)){

            if(article.calculateur.variables != null){

                for(let [variable_index, variable] of Object.entries(article.calculateur.variables)){

                    if(variable.boucle && variable.boucle != 0){

                        variable.boucle = 0;

                    }

                }

                for(let [variable_index, variable] of Object.entries(article.calculateur.variables)){

                    if(variable.valeur !== null){

                        var calcul = await vue_instance.calculer(variable.valeur, article, variable);

                        if(calcul.erreur == false)
                            vue_instance.variables[variable.nom] = calcul.resultat;
                        else{

                            article.calculateur.erreur = true;
                            erreur = true;
                            return;

                        }
                    }

                }
            }

            if(article.calculateur.ponderation != null){

                for(let [ponderation_index, ponderation] of Object.entries(article.calculateur.ponderation)){

                    var condition_remplie = false;

                    for(let [condition_index, condition] of Object.entries(ponderation.conditions)){

                        if(condition.type == 1 && condition_remplie == false && erreur != true){

                            var condition_complete = [];

                            for(let [donnee_index, donnee] of Object.entries(condition.donnees)){

                                if(donnee.comp == 0){
                                    article.calculateur.erreur = true;
                                    erreur = true;
                                    return;
                                }

                                var calcul = await vue_instance.calculer(donnee.critere + donnee.comp + donnee.valeur, article, donnee, "no_alert", true, true);

                                if(calcul.erreur == false)
                                    condition_complete.push(calcul.resultat);
                                else{

                                    article.calculateur.erreur = true;
                                    erreur = true;
                                    return;

                                }

                                if(donnee.cond){

                                    condition_complete.push(donnee.cond);

                                }

                            }

                            var test_final = "";

                            for(let resultat of condition_complete){
                                test_final += resultat;
                            }

                            if(eval(test_final) == true){

                                var calcul = await vue_instance.calculer(condition.resultat, article);

                                if(calcul.erreur == false){
                                    vue_instance.variables[ponderation.designation] = parseFloat(calcul.resultat);
                                    ponderation.resultat = parseFloat(calcul.resultat);
                                    condition_remplie = true;
                                }
                                else{

                                    article.calculateur.erreur = true;
                                    erreur = true;
                                    return;
                                }

                            }

                        }
                        else if(condition_remplie == false && erreur != true){

                            var calcul = await vue_instance.calculer(condition.resultat, article);

                            if(calcul.erreur == false){
                                vue_instance.variables[ponderation.designation] = parseFloat(calcul.resultat);
                                ponderation.resultat = parseFloat(calcul.resultat);
                                condition_remplie = true;
                            }
                            else{

                                article.calculateur.erreur = true;
                                erreur = true;
                                return;
                            }

                        }

                        else if(erreur == true){

                            vue_instance.variables[ponderation.designation] = 1;
                            ponderation.resultat = 1;
                            condition_remplie = true;

                        }

                    }

                }

            }

            if(erreur == false){
                if(article.type_ligne == undefined){
                    var calcul = await vue_instance.calculer(article.calculateur.calcul.formule, article);

                    if(calcul.erreur == false && typeof calcul.resultat == 'number'){

                        if(article.resultat_force != true)
                            article.quantite = calcul.resultat;
                            article.calculateur.resultat_calcul = calcul.resultat;
                        if(erreur == false){
                            article.calculateur.erreur = false;
                            article.calculateur.calcul.erreur = false;
                        }

                    }
                    else{

                        article.calculateur.erreur = true;
                        article.calculateur.calcul.erreur = true;
                        article.quantite = 0;

                    }
                }
                else{
                    article.calculateur.erreur = false;
                }
            }
            else{
                var calcul = await vue_instance.calculer(article.calculateur.calcul.formule, article);

            if(typeof calcul.resultat == 'number'){

                article.calculateur.resultat_calcul = calcul.resultat;
            }

            article.calculateur.erreur = true;
            article.calculateur.calcul.erreur = true;
            }
            if(article_nomenclature_index >= 0){

                article_mere.erreur = article.calculateur.erreur;

            }


        }

        vue_instance.$forceUpdate();
    },

@endpush