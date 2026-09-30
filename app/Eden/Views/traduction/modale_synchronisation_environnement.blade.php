<span data-toggle="tooltip" @click="synchronisation_environnement" data-placement="left" :data-original-title="traduction('interface.index_traduction.modale_synchronisation_environnement.titre')" class="css_ajouter_element ml-2">
    <i aria-hidden="true" class="css_action_icon fas fa-history css_font_16"></i>
</span>

@push('modales')
    <template v-if="modale_synchronisation_environnement">
        <transition name="modal" >
            <div class="modal-mask">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                @traduction('interface.index_traduction.modale_synchronisation_environnement.titre')
                                <span v-if="etape_synchronisation == 1"> : @traduction('interface.index_traduction.modale_synchronisation_environnement.ajout')</span>
                                <span v-if="etape_synchronisation == 2"> : @traduction('interface.index_traduction.modale_synchronisation_environnement.comparaison')</span>
                                <span v-if="ajout_disponible && comparaison_disponible"> ( @{{ etape_synchronisation }} / 2 ) </span>
                            </h5>
                        </div>

                        <div class="modal-body css_form js_selection_element" :style="(etape_synchronisation == 2 ? 'overflow:unset' : '')">

                            <template v-if="etape_synchronisation == 0">
                                <div class="row">
                                    <div class="col-sm-12" style="text-align:center">
                                        @traduction('interface.index_traduction.modale_synchronisation_environnement.aucune_difference_trouvee')
                                    </div>
                                </div>
                            </template>
                            <template v-else-if="etape_synchronisation == 1">
                                <div class="row">
                                    <div class="col-sm-12">
                                        @traduction('interface.index_traduction.modale_synchronisation_environnement.explication_ajout')
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12" style="text-align: end;">
                                        <template v-if="suppression_en_masse === false">
                                            <span @click="activation_suppression_en_masse" class="badge badge-default">
                                                @traduction('interface.index_traduction.modale_synchronisation_environnement.suppression_en_masse')
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span @click="valider_suppression_en_masse" class="badge badge-danger">
                                                @traduction('interface.index_traduction.modale_synchronisation_environnement.suppression_valider')
                                            </span>
                                            <span @click="suppression_en_masse = false" class="badge badge-default">
                                                @traduction('interface.index_traduction.modale_synchronisation_environnement.suppression_annuler')
                                            </span>
                                        </template>
                                    </div>
                                </div>
                                <table style="width: 100%;" class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th style="width: 35%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.index')</th>
                                            <th style="width: 40%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.valeur')</th>
                                            <th style="width: 15%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.categorie')</th>
                                            <th style="width: 5%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.langue')</th>
                                            <th style="width: 5%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.options')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-if="traductions.length > 0" v-for="(traductions,environnement) in traductions_synchronisations.ajout">
                                            <tr>
                                                <td colspan="5" style="background-color:#eeeeee;text-transform:uppercase">
                                                    <span v-if="environnement == 'preprod'">@traduction('interface.index_traduction.modale_synchronisation_environnement.prod') -> @traduction('interface.index_traduction.modale_synchronisation_environnement.preprod')</span>
                                                    <span v-else>@traduction('interface.index_traduction.modale_synchronisation_environnement.preprod') -> @traduction('interface.index_traduction.modale_synchronisation_environnement.prod')</span>
                                                </td>
                                            </tr>
                                            <tr v-for="(traduction,index_traduction) in traductions">
                                                <td>@{{ traduction.index }}</td>
                                                <td>@{{ traduction.valeur }}</td>
                                                <td v-html="valeur_categorie(traduction.categorie)"></td>
                                                <td>@{{ traduction.langue }}</td>
                                                <td>
                                                    <i v-if="suppression_en_masse === false" @click="suppression_ajout(traductions,index_traduction)" class="css_pointer fas fa-trash"></i>
                                                    <input type="checkbox" v-model="elements_a_supprimer[environnement][index_traduction]" v-else />
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </template>

                            <template v-else>
                                <div class="row">
                                    <div class="col-sm-12">
                                        @traduction('interface.index_traduction.modale_synchronisation_environnement.explication_comparaison')
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <span @click="selectionner_valeurs('prod')" :class="'badge badge-'+(selection_unique == 'prod'? 'success' : 'default')">
                                            @traduction('interface.index_traduction.modale_synchronisation_environnement.selection_valeurs') @traduction('interface.index_traduction.modale_synchronisation_environnement.prod')
                                        </span>
                                        <span @click="selectionner_valeurs('preprod')" :class="'badge badge-'+(selection_unique == 'preprod'? 'success' : 'default')">
                                            @traduction('interface.index_traduction.modale_synchronisation_environnement.selection_valeurs') @traduction('interface.index_traduction.modale_synchronisation_environnement.preprod')
                                        </span>
                                    </div>
                                </div>
                                <div style="display: block;overflow: auto;max-height: 440px;">
                                    <table style="width: 100%" class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th style="width: 30%">@traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.index')</th>
                                                <th style="width: 35%">
                                                    @traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.valeur') @traduction('interface.index_traduction.modale_synchronisation_environnement.prod')
                                                </th>
                                                <th style="width: 35%">
                                                    @traduction('interface.index_traduction.modale_synchronisation_environnement.colonnes.valeur') @traduction('interface.index_traduction.modale_synchronisation_environnement.preprod')
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template v-for="(traductions,langue) in traductions_synchronisations.comparaison">
                                                <tr style="background-color:#eeeeee;text-transform:uppercase">
                                                    <td colspan="3">@{{ nom_langue(langue) }}</td>
                                                </tr>
                                                <tr v-for="(valeurs,index_traduction) in traductions">
                                                    <td>@{{ index_traduction }}</td>
                                                    <td :style="couleur_cellule(index_traduction,'prod')" class="css_pointer" @click="choix_traduction_comparaison(index_traduction,'prod')">@{{ valeurs.prod }}</td>
                                                    <td :style="couleur_cellule(index_traduction,'preprod')" class="css_pointer" @click="choix_traduction_comparaison(index_traduction,'preprod')">@{{ valeurs.preprod }}</td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </template>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modale_synchronisation_environnement = false">@traduction('interface.modales.fermer')</button>
                            <button type="button" class="btn btn-default" @click="etape_synchronisation = 2" v-if="etape_synchronisation == 1 && comparaison_disponible">@traduction('interface.index_traduction.modale_synchronisation_environnement.suivant')</button>
                            <button type="button" class="btn btn-default" @click="etape_synchronisation = 1" v-if="etape_synchronisation == 2 && ajout_disponible">@traduction('interface.index_traduction.modale_synchronisation_environnement.precedent')</button>
                            <button type="button" class="btn btn-primary" v-if="validation_disponible" @click="valider">@traduction('interface.index_traduction.modale_synchronisation_environnement.valider')</button>
                        </div>
                    </div>
                </div>
            </div>
        </transition>
    </template>
@endpush

@push('donnees_pour_vuejs_data')

    modale_synchronisation_environnement : false,
    traductions_synchronisations : {},
    etape_synchronisation: 0,
    choix_comparaisons:{
        prod : [],
        preprod : [],
    },
    suppression_en_masse : false,
    elements_a_supprimer : {
        prod : {},
        preprod : {},
    },

@endpush

@push('donnees_pour_vuejs_methods')

    synchronisation_environnement : function(){

        var vue_instance = this;

        loading(true);

        vue_instance.etape_synchronisation = 0;

        $.ajax({
            url:'{{route('maintenance.traduction.synchronisation_environnement')}}',
            dataType:'json',
        }).done(function(donnees){

            loading(false);

            if(donnees.retour !== true){

                erreur_toast(donnees.message);
                return;
            }

            vue_instance.choix_comparaisons = {
                prod : [],
                preprod : [],
            };

            vue_instance.traductions_synchronisations = donnees.traductions_a_traiter;

            if(vue_instance.ajout_disponible)
                vue_instance.etape_synchronisation = 1;

            else if(vue_instance.comparaison_disponible)
                vue_instance.etape_synchronisation = 2;

            vue_instance.modale_synchronisation_environnement = true;

            vue_instance.$forceUpdate();
        });
    },

    choix_traduction_comparaison: function(index_traduction,environnement){

        var environnement_inverse = environnement == "prod" ? "preprod" : "prod";

        if(this.choix_comparaisons[environnement].includes(index_traduction))
            return;

        if(this.choix_comparaisons[environnement_inverse].includes(index_traduction)){

            var index_tableau = this.choix_comparaisons[environnement_inverse].indexOf(index_traduction);
            this.choix_comparaisons[environnement_inverse].splice(index_tableau,1);
        }

        this.choix_comparaisons[environnement].push(index_traduction);

        this.$forceUpdate();
    },

    couleur_cellule : function(index_traduction,environnement){

        var environnement_inverse = environnement == "prod" ? "preprod" : "prod";

        if(this.choix_comparaisons[environnement].includes(index_traduction))
            return 'background-color:limegreen;color:white;';

        else if(this.choix_comparaisons[environnement_inverse].includes(index_traduction))
            return 'background-color:lightgrey';

        return;
    },

    nom_langue : function(langue){

        var nom_langue = '';

        this.langues_traduction_erp.forEach(function(langue_traduction){

            if(langue_traduction.code == langue)
                nom_langue = langue_traduction.nom;
        });

        return nom_langue;
    },

    selectionner_valeurs : function(environnement){

        if(this.selection_unique == environnement)
            return;

        var environnement_inverse = environnement == "prod" ? "preprod" : "prod";

        this.choix_comparaisons[environnement_inverse] = [];

        var choix_comparaisons = [];

        $.each(this.traductions_synchronisations.comparaison,function(langue,traductions){
            $.each(traductions,function(index_traduction,osef){
                choix_comparaisons.push(index_traduction);
            });
        });

        this.choix_comparaisons[environnement] = choix_comparaisons;

        this.$forceUpdate();
    },

    suppression_ajout : async function(traductions,index_traduction){

        if(!await confirm_eden(this.traduction('interface.index_traduction.modale_synchronisation_environnement.confirmation'))){
            return;
        }

        traductions.splice(index_traduction,1);
    },

    valider : async function(){

        if(!await confirm_eden(this.traduction('interface.index_traduction.modale_synchronisation_environnement.confirmation'))){
            return;
        }

        loading(true);

        var vue_instance = this;

        $.post({
            url : '{{ route('maintenance.traduction.validation_synchronisation_environnement') }}',
            dataType : 'json',
            data : {
                traductions_synchronisations : vue_instance.traductions_synchronisations,
                choix_comparaisons : vue_instance.choix_comparaisons
            }
        }).done(async function(donnees){

            loading(false);

            if(donnees.retour !== true){

                erreur_toast(donnees.message);
                return;
            }

            info(vue_instance.traduction('messages.js.enregistrement_succes'));

            if(vue_instance.$refs.traduction_table != undefined)
                await vue_instance.$refs.traduction_table.charger_traductions();

            vue_instance.modale_synchronisation_environnement = false;
        });
    },

    valeur_categorie : function(categorie_id){

        var vue_instance = this;

        var valeur = '';

        for(const [cle, categorie] of Object.entries(vue_instance.valeurs_listes_formatees[590])) {

            if(categorie.id_valeur == categorie_id)
                valeur = categorie.valeur;

        }

        return valeur;

    },

    activation_suppression_en_masse : function(){

        this.suppression_en_masse = true;
        this.elements_a_supprimer = {
            prod : {},
            preprod : {},
        };
    },

    valider_suppression_en_masse : async function(){

        if(!await confirm_eden(this.traduction('interface.index_traduction.modale_synchronisation_environnement.confirmation'))){
            return;
        }

        var vue_instance = this;

        $.each(vue_instance.elements_a_supprimer,function(environnement,index_traductions){

            index_a_supprimer = [];

            $.each(index_traductions,function(index_traduction,valeur){

                if(valeur === true)
                    index_a_supprimer.push(index_traduction);
            });

            index_a_supprimer.sort(function(a,b){ return b - a; });

            index_a_supprimer.forEach(function(index){

                vue_instance.traductions_synchronisations.ajout[environnement].splice(index,1);

            });

        });

        this.suppression_en_masse = false;

    },

@endpush

@push('donnees_pour_vuejs_computed')

    ajout_disponible : function(){

        return this.traductions_synchronisations.ajout !== undefined &&
            ((this.traductions_synchronisations.ajout.preprod != undefined &&
            this.traductions_synchronisations.ajout.preprod.length > 0) ||
            (this.traductions_synchronisations.ajout.prod != undefined &&
            this.traductions_synchronisations.ajout.prod.length > 0));
    },

    comparaison_disponible : function(){

        return this.traductions_synchronisations.comparaison !== undefined &&
            Object.keys(this.traductions_synchronisations.comparaison).length > 0;
    },

    validation_disponible : function(){

        return (this.etape_synchronisation == 1 && this.comparaison_disponible === false) ||
            (this.etape_synchronisation == 2 && this.comparaison_termine);

    },

    nombres_de_comparaison : function(){

        var nombres_de_comparaison = 0;

        $.each(this.traductions_synchronisations.comparaison,function(langue,traductions){
            nombres_de_comparaison += Object.keys(traductions).length;
        });

        return nombres_de_comparaison;
    },

    selection_unique : function(){

        if(this.choix_comparaisons.prod.length == this.nombres_de_comparaison)
            return 'prod';

        else if(this.choix_comparaisons.preprod.length == this.nombres_de_comparaison)
            return 'preprod';

        return null;
    },

    comparaison_termine : function(){

        return this.nombres_de_comparaison == this.choix_comparaisons.prod.length + this.choix_comparaisons.preprod.length;
    },
@endpush