@extends('eden::fiches.include.saisie_des_temps.template.saisie',['type_saisie' => 'element'])

@section('saisie')
    <div class="row saisie">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="titre">
                        @{{ $root.traduction('tables_libres.'+feuille_de_temps.type_element+'.element_pluriel') }}
                    </h4>
                    <div class="enregistrement" v-if="enregistrement_en_cours == 1">
                        <img src="/eden/images/loader.svg" />
                        <span class="texte">@traduction('interface.index_traduction.enregistrement_en_cours')</span>
                    </div>
                    <div class="enregistrement" v-else-if="enregistrement_en_cours == 2" >
                        <i class="fas fa-check"></i>
                        <span class="texte">@traduction('interface.index_traduction.enregistrement_effectue')</span>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="loader_element" class="text-center">
                        <img class="loader" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
                    </div>
                    <form v-else>
                        <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th scope="col" class="titre">@{{ $root.traduction('tables_libres.'+feuille_de_temps.type_element+'.element_pluriel') }}</th>
                                    <th scope="col" class="titre" v-if="gestion_commentaires">@traduction('interface.saisie_des_temps.commentaire')</th>
                                    <th v-for="date in informations_dates.dates_pour_saisie"
                                        :class="(date.date == $root.aujourdhui ? 'jour_aujourdhui' : '')+' '+(date.delimiteur && mode_affichage == 3 ? 'delimiteur' : '')+' '+(date.jour_indisponibilite !== false ? 'jour_indisponibilite' : '')"
                                    >
                                        <div class="titre_date">
                                            @{{ date.affichage }}
                                            <i class="far fa-calendar-times" v-if="date.jour_indisponibilite !== false" :title="date.jour_indisponibilite.map((jour) => jour.chaine_affichage).join(', ')"></i>
                                        </div>
                                    </th>
                                    <th>@traduction('interface.saisie_des_temps.total')</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(element, index_element) in elements">
                                    <td><span v-html="element.affichage"></span></td>

                                    <td v-if="gestion_commentaires">
                                        <input @change="enregistre_commentaire_element(element)" v-model="element.feuilles_de_temps.commentaire.commentaire" type="text">
                                    </td>

                                    <td v-for="date in informations_dates.dates_pour_saisie"
                                        :class="(date.delimiteur && mode_affichage == 3 ? 'delimiteur' : '')">
                                        <champ-montant
                                                :lecture_seule="date.jour_indisponibilite !== false || date_statut(date.date) > 0"
                                                :class_input="(date.jour_indisponibilite == false && date_statut(date.date) == 2 ? 'temps_validee' : '')"
                                                :modele="element.feuilles_de_temps.durees[date.date]"
                                                :nom_sql="champ_de_duree"
                                                :valeur_uniquement_positive="true"
                                                nombre_decimale="2"
                                                :parametres_emit="{
                                                    element: element,
                                                    date : date.date
                                                }"
                                        ></champ-montant>
                                    </td>

                                    <td>
                                        <b>
                                            @{{ totaux_par_element[element.id] }}

                                            @{{ $root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)}}
                                        </b>
                                    </td>

                                    <td v-if="informations_dates.statut_saisie_terminee == 0">
                                        <i class="fas fa-trash" @click="desactiver_element(element,index_element)"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">@traduction('interface.saisie_des_temps.total')</td>
                                    <td v-if="gestion_commentaires"></td>
                                    <td v-for="date in informations_dates.dates_pour_saisie"
                                        :class="(date.delimiteur && mode_affichage == 3 ? 'delimiteur' : '')">
                                        <b>
                                            @{{ totaux_par_date[date.date] }}

                                            @{{ $root.traduction('module_sur_fiche.projet.indicateurs.unite_'+unite_de_saisie)}}
                                        </b>
                                    </td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('donnees_pour_vuejs_mounted')
    this.$on('maj_champ_montant',(donnees) => {

        var valeur = donnees.modele[donnees.nom_sql];

        if(valeur == '' || isNaN(valeur))
            donnees.modele[donnees.nom_sql] = 0;

        this.corrige_virgule(donnees.modele,donnees.nom_sql);
        this.enregistre_temps_element(donnees.element,donnees.date);
    });
@endpush

@push('donnees_pour_vuejs_computed')
    totaux_par_date : function(){

        var totaux_par_date = {};

        for(date of this.informations_dates.dates_pour_saisie){

            totaux_par_date[date.date] = 0;

            for(element of this.elements){

                totaux_par_date[date.date] += parseFloat(element.feuilles_de_temps.durees[date.date][this.champ_de_duree]);
            }

            totaux_par_date[date.date] = Math.round(totaux_par_date[date.date] * 100)/100;
        }

        return totaux_par_date;
    },
    totaux_par_element : function(){

        var totaux_par_element = {};

        for(element of this.elements){

            totaux_par_element[element.id] = 0;

            for(feuille_de_temps of Object.values(element.feuilles_de_temps.durees)){

                totaux_par_element[element.id] += parseFloat(feuille_de_temps[this.champ_de_duree]);
            }

            totaux_par_element[element.id] = Math.round(totaux_par_element[element.id] * 100)/100;
        }

        return totaux_par_element;
    },
@endpush

@push('donnees_pour_vuejs_methods')

    traitement_retour_chargement_donnees : function(donnees,element_id = null){

        if(element_id == null){

            this.feuille_de_temps.elements_ids = [];

            for(index_element in donnees.elements){

                element = donnees.elements[index_element];

                var commentaire = this.commentaires[element.id] &&
                    this.commentaires[element.id][this.informations_dates.debut]
                    ? this.commentaires[element.id][this.informations_dates.debut] :
                    {commentaire : null};

                if(element.feuilles_de_temps.length == 0)
                    element.feuilles_de_temps = {
                        durees : {},
                        commentaire : commentaire
                    };
                else
                    element.feuilles_de_temps = {
                        durees : element.feuilles_de_temps,
                        commentaire : commentaire
                    };

                for(date of this.informations_dates.dates_pour_saisie){

                    if(element.feuilles_de_temps.durees[date.date] == undefined){
                        element.feuilles_de_temps.durees[date.date] = {};
                        element.feuilles_de_temps.durees[date.date][this.champ_de_duree] = 0;
                    }
                    else
                        element.feuilles_de_temps.durees[date.date][this.champ_de_duree] = parseFloat(element.feuilles_de_temps.durees[date.date][this.champ_de_duree]);
                }

                this.feuille_de_temps.elements_ids.push(element.id);

                donnees.elements[index_element] = element;
            }

            this.$set(this,'elements',donnees.elements)

        }
        else{

            var element = donnees.element;

            if(element.feuilles_de_temps.length == 0)
                element.feuilles_de_temps = {
                    durees : {},
                    commentaire : {
                        commentaire : null,
                    }
                };
            else
                element.feuilles_de_temps = {
                    durees : element.feuilles_de_temps,
                    commentaire : {
                        commentaire : null,
                    }
                };

            for(date of this.informations_dates.dates_pour_saisie){

                if(element.feuilles_de_temps.durees[date.date] == undefined){
                    element.feuilles_de_temps.durees[date.date] = {};
                    element.feuilles_de_temps.durees[date.date][this.champ_de_duree] = 0;
                }
                else
                    element.feuilles_de_temps.durees[date.date][this.champ_de_duree] = parseFloat(element.feuilles_de_temps.durees[date.date][this.champ_de_duree]);
            }

            this.feuille_de_temps.elements_ids.push(element.id);

            this.elements.push(element);

            this.$forceUpdate();
        }
    },

    enregistre_temps_element : async function(element,date){

        var feuille_de_temps = element.feuilles_de_temps.durees[date];

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : date,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
        };

        donnees_pour_enregistrement[this.champ_de_duree] = feuille_de_temps[this.champ_de_duree];

        var retour = await this.enregistre_temps(feuille_de_temps,donnees_pour_enregistrement);

        if(retour){
            retour[this.champ_de_duree] = parseFloat(retour[this.champ_de_duree]);
            this.$set(element.feuilles_de_temps.durees,date,retour);

            this.enregistrement_en_cours = 2;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },

    enregistre_commentaire_element : async function(element){

        this.enregistrement_en_cours = 1;

        var commentaire = element.feuilles_de_temps.commentaire;

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : this.informations_dates.debut,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
            mode_affichage : this.mode_affichage,
            commentaire : commentaire.commentaire,
        };

        var retour = await this.enregistre_commentaire(commentaire,donnees_pour_enregistrement);

        if(retour){
            this.$set(element.feuilles_de_temps,'commentaire',retour);

            this.enregistrement_en_cours = 2;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },
@endpush
