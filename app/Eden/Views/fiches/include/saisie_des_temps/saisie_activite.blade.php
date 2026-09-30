@extends('eden::fiches.include.saisie_des_temps.template.saisie',['type_saisie' => 'activite'])

@section('saisie')
    <div v-if="loader_element" class="text-center">
        <img class="loader" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
    </div>
    <div class="row saisie" v-else v-for="(element, index_element) in elements">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="titre" v-html="element.affichage"></h4>
                    <div v-if="enregistrement_en_cours == element.id" class="enregistrement">
                        <img src="/eden/images/loader.svg" />
                        <span class="texte">@traduction('interface.index_traduction.enregistrement_en_cours')</span>
                    </div>
                    <div v-else-if="enregistrement_en_cours == element.id*-1" class="enregistrement">
                        <i class="fas fa-check"></i>
                        <span class="texte">@traduction('interface.index_traduction.enregistrement_effectue')</span>
                    </div>
                    <i class="fas fa-trash" v-if="informations_dates.statut_saisie_terminee == 0" @click="desactiver_element(element,index_element)"></i>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th scope="col" class="titre">@traduction('tables_libres.activite.nom_table')</th>
                                <th scope="col" class="titre" v-if="gestion_commentaires">@traduction('interface.saisie_des_temps.commentaire')</th>
                                <th v-for="date in informations_dates.dates_pour_saisie"
                                    :class="(date.date == $root.aujourdhui ? 'jour_aujourdhui' : '')+' '+(date.delimiteur && mode_affichage == 3 ? 'delimiteur' : '')+' '+(date.jour_indisponibilite !== false ? 'jour_indisponibilite' : '')"
                                >
                                    <div class="titre_date">
                                        @{{ date.affichage }}
                                        <i class="far fa-calendar-times" v-if="date.jour_indisponibilite !== false" :title="date.jour_indisponibilite.map((jour) => jour.chaine_affichage).join(', ')"></i>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <template v-for="categorie in element.activites_par_categorie">
                                <tr>
                                    <td colspan="100%" class="categorie">
                                        @{{ categorie.affichage }}
                                    </td>
                                </tr>
                                <tr v-for="activite in categorie.activites">
                                    <td>
                                        <i v-if="activite.prioritaire" class="fas fa-star"></i>
                                        @{{ activite.affichage }}
                                    </td>
                                    <td v-if="gestion_commentaires">
                                        <input @change="enregistre_commentaire_activite(element,activite)" v-model="element.feuilles_de_temps[activite.id].commentaire.commentaire" type="text">
                                    </td>

                                    <td v-for="date in informations_dates.dates_pour_saisie" :class="date.delimiteur && mode_affichage == 3 ? 'delimiteur' : ''">
                                        <champ-montant
                                                style="width:100%"
                                                :lecture_seule="date.jour_indisponibilite !== false || date_statut(date.date) > 0"
                                                :class_input="(
                                                date.jour_indisponibilite == false && element.feuilles_de_temps[activite.id].durees[date.date].type_element_transforme != null ?
                                                'temps_transformee' : (date.jour_indisponibilite == false && date_statut(date.date) == 2 ? 'temps_validee' : ''))"
                                                :modele="element.feuilles_de_temps[activite.id].durees[date.date]"
                                                :nom_sql="champ_de_duree"
                                                :valeur_uniquement_positive="true"
                                                nombre_decimale="2"
                                                :parametres_emit="{
                                                    element: element,
                                                    date : date.date,
                                                    activite : activite
                                                }"
                                        ></champ-montant>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
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
        this.enregistre_temps_activite(donnees.element,donnees.date,donnees.activite);
    });
@endpush

@push('donnees_pour_vuejs_methods')

    traitement_retour_chargement_donnees : function(donnees,element_id){

        if(element_id == null){

            this.feuille_de_temps.elements_ids = [];

            for(index_element in donnees.elements){

                element = donnees.elements[index_element];

                if(element.feuilles_de_temps.length == 0)
                    element.feuilles_de_temps = {};

                for(categorie of element.activites_par_categorie){

                    for(activite of categorie.activites){

                        var commentaire = this.commentaires[element.id] &&
                                this.commentaires[element.id][activite.id] &&
                                this.commentaires[element.id][activite.id][this.informations_dates.debut]
                                ? this.commentaires[element.id][activite.id][this.informations_dates.debut] :
                                {commentaire : null};

                        if(element.feuilles_de_temps[activite.id] == undefined)
                            element.feuilles_de_temps[activite.id] = {
                                durees : {},
                                commentaire : commentaire
                            };
                        else
                            element.feuilles_de_temps[activite.id] = {
                                durees : element.feuilles_de_temps[activite.id],
                                commentaire : commentaire
                            };

                        for(date of this.informations_dates.dates_pour_saisie){

                            if(element.feuilles_de_temps[activite.id].durees[date.date] == undefined){
                                element.feuilles_de_temps[activite.id].durees[date.date] = {};
                                element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree] = 0;
                            }
                            else
                                element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree] =
                                        parseFloat(element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree]);
                        }
                    }

                }

                this.feuille_de_temps.elements_ids.push(element.id);

                donnees.elements[index_element] = element;
            }

            this.$set(this,'elements',donnees.elements)

        }
        else{

            var element = donnees.element;

            if(element.feuilles_de_temps.length == 0)
                element.feuilles_de_temps = {};

            for(categorie of element.activites_par_categorie){

                for(activite of categorie.activites){

                    if(element.feuilles_de_temps[activite.id] == undefined)
                        element.feuilles_de_temps[activite.id] = {
                            durees : {},
                            commentaire : {
                                commentaire : null,
                            }
                        };
                    else
                        element.feuilles_de_temps[activite.id] = {
                            durees : element.feuilles_de_temps[activite.id],
                            commentaire : {
                                commentaire : null,
                            }
                        };

                    for(date of this.informations_dates.dates_pour_saisie){

                        if(element.feuilles_de_temps[activite.id].durees[date.date] == undefined){
                            element.feuilles_de_temps[activite.id].durees[date.date] = {};
                            element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree] = 0;
                        }
                        else
                            element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree] =
                                    parseFloat(element.feuilles_de_temps[activite.id].durees[date.date][this.champ_de_duree]);
                    }
                }
            }

            this.feuille_de_temps.elements_ids.push(element.id);

            this.elements.push(element);

            this.$forceUpdate();
        }

    },

    enregistre_temps_activite : async function(element,date,activite){

        var feuille_de_temps = element.feuilles_de_temps[activite.id].durees[date];

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : date,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
            activite_id : activite.id,
        };

        donnees_pour_enregistrement[this.champ_de_duree] = feuille_de_temps[this.champ_de_duree];

        var retour = await this.enregistre_temps(feuille_de_temps,donnees_pour_enregistrement);

        if(retour){
            retour[this.champ_de_duree] = parseFloat(retour[this.champ_de_duree]);
            this.$set(element.feuilles_de_temps[activite.id].durees,date,retour);

            this.enregistrement_en_cours = element.id * -1;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },

    enregistre_commentaire_activite : async function(element,activite){

        this.enregistrement_en_cours = element.id;

        var commentaire = element.feuilles_de_temps[activite.id].commentaire;

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : this.informations_dates.debut,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
            activite_id : activite.id,
            mode_affichage : this.mode_affichage,
            commentaire : commentaire.commentaire,
        };

        var retour = await this.enregistre_commentaire(commentaire,donnees_pour_enregistrement);

        if(retour){
            this.$set(element.feuilles_de_temps[activite.id],'commentaire',retour);

            this.enregistrement_en_cours = element.id * -1;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },
@endpush
