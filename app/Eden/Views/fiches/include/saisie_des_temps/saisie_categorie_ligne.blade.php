@extends('eden::fiches.include.saisie_des_temps.template.saisie',['type_saisie' => 'categorie'])

@section('saisie')
    <div v-if="loader_element" class="text-center">
        <img class="loader" src="<?php echo e('eden/images/ajax_loader.gif'); ?>">
    </div>
    <div class="row saisie" v-else>
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <table class="table table-bordered table-hover css_form css_table_fin_padding" width="100%" cellspacing="0">
                        <thead>
                        <tr>
                            <th scope="col" class="titre">@traduction('interface.saisie_des_temps.options')</th>
                            <th scope="col" class="titre">@traduction('tables_libres.categorie_activite.nom_table')</th>
                            <th scope="col" class="titre" v-html="$root.traduction('tables_libres.'+feuille_de_temps.type_element+'.element')"></th>
                            @yield('entete_colonnes_supplementaires')
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
                            <template v-for="(element,index_element) in elements">
                                <tr v-for="(ligne,index_ligne) in element.lignes">
                                    <td>
                                        <i class="supprimer fas fa-trash" v-if="informations_dates.statut_saisie_terminee == 0" @click="desactiver_categorie(element,index_element,ligne,index_ligne)"></i>
                                        <i v-if="categories_a_afficher(element).length > 0 && informations_dates.statut_saisie_terminee == 0" class="supprimer fas fa-copy" @click="dupliquer_categorie(element,ligne)"></i>
                                    </td>
                                    <td>
                                        <select v-model="ligne.categorie_activite_id" :disabled="temps_saisie_categorie(ligne)">
                                            <option :value="categorie.id" v-for="categorie in categories_a_afficher(element,ligne)">@{{ categorie.affichage }}</option>
                                        </select>
                                    </td>
                                    <td v-html="element.affichage"></td>
                                    @yield('valeur_colonnes_supplementaires')
                                    <td v-if="gestion_commentaires">
                                        <input :disabled="!(ligne.categorie_activite_id > 0)" @change="enregistre_temps_commentaire(element,ligne)" v-model="ligne.commentaire.commentaire" type="text">
                                    </td>
                                    <td v-for="date in informations_dates.dates_pour_saisie" :class="(date.delimiteur && mode_affichage == 3 ? 'delimiteur' : '')">
                                        <champ-montant
                                            :lecture_seule="date.jour_indisponibilite !== false || date_statut(date.date) > 0 || !(ligne.categorie_activite_id > 0)"
                                            :class_input="(date.jour_indisponibilite == false && date_statut(date.date) == 2 ? 'temps_validee' : '')"
                                            :modele="ligne.durees[date.date]"
                                            :nom_sql="champ_de_duree"
                                            :valeur_uniquement_positive="true"
                                            nombre_decimale="2"
                                            :parametres_emit="{
                                                element: element,
                                                date : date.date,
                                                ligne : ligne
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

@push('donnees_pour_vuejs_data')
    categories:[],
@endpush

@push('donnees_pour_vuejs_mounted')
    this.$on('ajout_element_existant',(element_id) => {

        var element_existant = null;

        for(element of this.elements){
            if(element.id == element_id)
                element_existant = element;
        }

        if(element_existant != null){

            if(this.categories_a_afficher(element).length > 0){

                var date_par_categorie = {};

                for(date of this.informations_dates.dates_pour_saisie){

                    date_par_categorie[date.date] = {};

                    date_par_categorie[date.date][this.champ_de_duree] = 0;
                }

                element_existant.lignes.push({
                    categorie_activite_id : null,
                    durees : date_par_categorie,
                    commentaire : {
                        commentaire : null,
                    },
                });
            }
        }
    });

    this.$on('maj_champ_montant',(donnees) => {

        var valeur = donnees.modele[donnees.nom_sql];

        if(valeur == '' || isNaN(valeur))
            donnees.modele[donnees.nom_sql] = 0;

        this.corrige_virgule(donnees.modele,donnees.nom_sql);
        this.enregistre_temps_categorie(donnees.element,donnees.date,donnees.ligne)
    });
@endpush

@push('donnees_pour_vuejs_methods')

    categories_a_afficher : function(element,ligne = null){

        var categories_a_afficher = [];

        for(categorie of this.categories){

            categorie_a_ajouter = true;

            if(ligne == null || ligne.categorie_activite_id != categorie.id){

                for(element_ligne of element.lignes){

                    if(element_ligne.categorie_activite_id == categorie.id)
                        categorie_a_ajouter = false;
                }

            }

            if(categorie_a_ajouter)
                categories_a_afficher.push(categorie);

        }

        return categories_a_afficher;
    },

    temps_saisie_categorie : function(ligne){

        var temps_saisie_categorie = false;

        for(date of Object.values(ligne.durees)){

            if(date[this.champ_de_duree] > 0)
                temps_saisie_categorie = true;
        }

        return temps_saisie_categorie;
    },

    dupliquer_categorie : function(element,ligne){

        var date_par_categorie = {};

        for(date of this.informations_dates.dates_pour_saisie){

            date_par_categorie[date.date] = {};
            date_par_categorie[date.date][this.champ_de_duree] = 0;
        }

        element.lignes.push({
            categorie_activite_id : null,
            durees : date_par_categorie,
            commentaire : {
                commentaire : null,
            },
        });
    },

    traitement_retour_chargement_donnees : function(donnees,element_id = null){

        if(element_id == null){

            this.categories = donnees.categories;

            this.feuille_de_temps.elements_ids = [];

            for(index_element in donnees.elements){

                element = donnees.elements[index_element];

                element.lignes = [];

                if(element.feuilles_de_temps.length == 0){

                    var date_par_categorie = {};

                    element.lignes = [];

                    for(date of this.informations_dates.dates_pour_saisie){

                        date_par_categorie[date.date] = {};
                        date_par_categorie[date.date][this.champ_de_duree] = 0;
                    }

                    element.lignes.push({
                        categorie_activite_id : null,
                        durees : date_par_categorie,
                        commentaire : {
                            commentaire : null,
                        },
                    });

                }
                else{

                    for(categorie in element.feuilles_de_temps){

                        var date_par_categorie = element.feuilles_de_temps[categorie];

                        for(date of this.informations_dates.dates_pour_saisie){

                            if(date_par_categorie[date.date] == undefined){
                                date_par_categorie[date.date] = {};
                                date_par_categorie[date.date][this.champ_de_duree] = 0;
                            }
                            else
                                date_par_categorie[date.date][this.champ_de_duree] = parseFloat(date_par_categorie[date.date][this.champ_de_duree]);
                        }

                        var commentaire = this.commentaires[element.id] &&
                            this.commentaires[element.id][categorie] &&
                            this.commentaires[element.id][categorie][this.informations_dates.debut]
                            ? this.commentaires[element.id][categorie][this.informations_dates.debut] :
                            {commentaire : null};

                        element.lignes.push({
                            categorie_activite_id : categorie,
                            durees : date_par_categorie,
                            commentaire : commentaire,
                        });
                    }
                }

                this.feuille_de_temps.elements_ids.push(element.id);

                donnees.elements[index_element] = element;
            }

            this.$set(this,'elements',donnees.elements)

        }
        else{

            var element = donnees.element;

            var date_par_categorie = {};

            element.lignes = [];

            for(date of this.informations_dates.dates_pour_saisie){

                date_par_categorie[date.date] = {};
                date_par_categorie[date.date][this.champ_de_duree] = 0;
            }

            element.lignes.push({
                categorie_activite_id : null,
                durees : date_par_categorie,
                commentaire : {
                    commentaire : null
                }
            });

            this.feuille_de_temps.elements_ids.push(element.id);

            this.elements.push(element);

            this.$forceUpdate();
        }
    },

    enregistre_temps_categorie : async function(element,date,ligne){

        var feuille_de_temps = ligne.durees[date];

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : date,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
            categorie_id : ligne.categorie_activite_id,
        };

        donnees_pour_enregistrement[this.champ_de_duree] = feuille_de_temps[this.champ_de_duree];

        var retour = await this.enregistre_temps(feuille_de_temps,donnees_pour_enregistrement);

        if(retour){
            retour[this.champ_de_duree] = parseFloat(retour[this.champ_de_duree]);
            this.$set(ligne.durees,date,retour);

            this.enregistrement_en_cours = element.id * -1;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },

    desactiver_categorie : async function(element,index_element, ligne, index_ligne){

        if(element.lignes.length > 1){

            if(!this.temps_saisie_categorie(ligne)){

                element.lignes.splice(index_ligne,1);
                return;
            }

            if(!await confirm_eden(this.$root.traduction('interface.saisie_des_temps.confirmation_desactivation')))
                return;

            var elements_ids = [];

            var data = {
                element_id : element.id,
                elements_ids : elements_ids,
                parametres : this.feuille_de_temps,
                date_debut : this.informations_dates.debut,
                date_fin : this.informations_dates.fin,
                mode_affichage : this.mode_affichage,
                type_saisie : 'categorie',
                categorie_id : ligne.categorie_activite_id,
            };

            $.post({
                url : '{{route('saisie_des_temps.desactiver_element', [], false)}}',
                data: data,
            }).done((donnees) => {
                element.lignes.splice(index_ligne,1);
                this.$emit('changement_valeur');
            });
        }
        else
            this.desactiver_element(element,index_element);
    },

    enregistre_temps_commentaire : async function(element,ligne){

        this.enregistrement_en_cours = element.id;

        var commentaire = ligne.commentaire;

        var donnees_pour_enregistrement = {
            type_element : this.feuille_de_temps.type_element,
            element_id : element.id,
            date : this.informations_dates.debut,
            utilisateur_id : this.feuille_de_temps.utilisateur_id,
            categorie_id : ligne.categorie_activite_id,
            mode_affichage : this.mode_affichage,
            commentaire : commentaire.commentaire,
        };

        var retour = await this.enregistre_commentaire(commentaire,donnees_pour_enregistrement);

        if(retour){
            this.$set(ligne,'commentaire',retour);

                this.enregistrement_en_cours = element.id * -1;

            setTimeout(() => {
                this.enregistrement_en_cours = null;
            },5000);
        }
    },

@endpush
