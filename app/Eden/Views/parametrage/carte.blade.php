@extends('eden::templates.template')

@section('title') Carte @stop

@section('content')
    <div id="vue_app" >
        <div class="content-wrapper" >
            <div id="base-content" class="container-fluid">
                
                @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                    array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
                    array('route' => 'parametrage.carte.index','nom' => 'Cartes'),
                    array('nom' => $rapport->titre)
                )])
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center">
                                <h4>
                                   Carte 
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive css_form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <traduction-table :sans_categorie_afficher="true" categorie="10" :filtrage_index="carte.index_traduction+'.'"></traduction-table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center">
                                <h4>Filtres</h4>
                                <span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouveau filtre" @click="ajouter_filtre_carte">
                                    <i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus-square"></i>
                                </span>
                                <input class='css_input_recherche_liste ml-3' style="padding-left: 5px;" type="text" name="recherche_filtres" v-model="recherche_filtres" placeholder="Recherche"/>
                                <div class="css_btn_recherche_liste">
                                    <i class="css_action_icon fa fa-search" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nom</th>
                                        </tr>
                                        </thead>
                                        <tbody class="sortable" type_element='filtre'>
                                        <tr v-for="filtre in filtres" :key="filtre.id" v-show="filtre.nom_sql.indexOf(recherche_filtres) != -1 || filtre.nom_sql.indexOf(recherche_filtres) != -1 || (filtre.nom_sql !== null && filtre.nom_sql.indexOf(recherche_filtres) != -1)" :data-ordre="filtre.ordre" :data-id_colonne_sortable="filtre.id">
                                            <td><span class="css_ajouter_element css__lien"  @click="modifier_filtre_carte" :id_filtre="filtre.id">@{{ filtre.id }}</span></td>
                                            <td v-html="filtre.nom_sql" class="css_ajouter_element css__lien"  @click="modifier_filtre_carte" :id_filtre="filtre.id"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal filtre -->
            <div class="modal fade" id="modal_ajout_filtre" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Gestion des filtres cartes</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form action="#" method="post" class="css_form" id="formulaire_filtre">
                                {{ csrf_field() }}
                                <input type="hidden" name="id" v-model="filtre.id" />
                                <input type="hidden" name="rapport_id" value="{{ $id_rapport }}" />                                
                                <div class="row">
                                    <div class="col-sm-2">Valeur</div>
                                    <div class="col-sm-10">
                                        <select name="nom_sql" v-model="filtre.nom_sql">
                                            <option :value="nom_sql" v-for="(nom,nom_sql) in champs_libres">
                                                @{{ nom }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Ordre</div>
                                    <div class="col-sm-10"><input type="text" name="ordre" v-model="filtre.ordre" /></div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="button" class="btn btn-danger" data-dismiss="modal" @click="supprimer_filtre_carte" v-show="filtre.id != ''">Supprimer</button>
                            <button type="button" class="btn btn-primary" @click="enregistrer_filtre_carte({{ $id_rapport }})">Enregistrer</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@push('donnees_pour_vuejs_data')
    filtres: {!! $filtres !!},
    filtre: {},
    rapport_id: '{!! $id_rapport !!}',
    champs_libres: {!! $champs_libres !!},
    recherche_filtres: "",
    carte: {!! $rapport !!},
@endpush

@push('donnees_pour_vuejs_methods')
    ajouter_filtre_carte(event) {

        $('#modal_ajout_filtre').modal('show');
    
        this.filtre = {
            id: '',
            rapport_id: this.rapport_id,
            nom_sql: '',
            type_filtre: '',
            ordre: '',
            emplacement : 3,
        };
    },

    enregistrer_filtre_carte() {
        var vue_contexte = this;
        var data =$('#formulaire_filtre').serialize();
        loading(true);
    
        // on enregistre les infos du champ libre
        $.post({
            url: "{{ URL::to("eden/parametrage/carte/enregistrer_filtre") }}/"+$('#formulaire_filtre input[name=id]').val(),
            dataType: "json",
            data: $('#formulaire_filtre').serialize()
        }).done(async function(donnees) {
            loading(false);
        
            if(donnees.retour !== true) {
                await erreur(donnees.retour);
                return;
            }
    
            $('#modal_ajout_filtre').modal('hide');
            vue_contexte.filtres = donnees.filtres;
        });

    },

    modifier_filtre_carte(event) {

        $('#modal_ajout_filtre').modal('show');
    
        var event = $(event.target);
        var vue_contexte = this;
        
    // on récupère les infos du champ libre
        $.ajax({
            url: "{{ URL::to("eden/parametrage/carte/filtre") }}/"+event.attr('id_filtre'),
            dataType: "json"
        }).done(function(filtre) {
            vue_contexte.filtre = filtre;
        });
    },
    
    supprimer_filtre_carte() {

        loading(true);
        var vue_contexte = this;
    
        // on enregistre les infos du champ libre
        $.ajax({
            url: "{{ URL::to("eden/parametrage/carte/filtre") }}/"+$('#formulaire_filtre input[name=id]').val()+'/supprimer',
            dataType: "json"
        }).done(function(donnees) {
            loading(false);
            vue_contexte.filtres = donnees.filtres;
        });
    
        return false;
    },
    
@endpush

@section('scripts')
    <script>
        $(".sortable").sortable({
            update : function (event, ui) {

                var position = $(ui.originalPosition['top']);
                var position_origine = $(ui.position['top']);
                var item = ui.item[0];
                var id_pour_drop=item.dataset.id_colonne_sortable;
                var ordre_origine = item.dataset.ordre;
                var difference = position[0] - position_origine[0];
                var element = $(this).attr('type_element');
                var tableaux_nouveaux_ordres = {};

                var tableau_sortable = $(this);
                // tableau_sortable.sortable("disable");

                if (difference > 0) {

                    var ordre_nouveau = ui.item[0].nextSibling.attributes[0].nodeValue;
                    var nb_element_a_modifier = ordre_origine - ordre_nouveau;
                    var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) + 1;
                    var element_a_modifier = ui.item[0].nextSibling;
                    var id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;

                    tableaux_nouveaux_ordres[id_pour_drop] = ordre_nouveau;

                    for (var i = 1; i <= nb_element_a_modifier; i++) {

                        tableaux_nouveaux_ordres[id_pour_bouclage] = nouveau_ordre_element_suivant;

                        nouveau_ordre_element_suivant++;
                        if(element_a_modifier != null){
                            element_a_modifier = element_a_modifier.nextElementSibling;
                            if(element_a_modifier != null){
                                id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;
                            }
                        }
                    }
                }

                else{

                    var ordre_nouveau = ui.item[0].previousSibling.attributes[0].nodeValue;
                    var nb_element_a_modifier = ordre_nouveau - ordre_origine;
                    var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) - 1;
                    var element_a_modifier = ui.item[0].previousSibling;
                    var id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;

                    tableaux_nouveaux_ordres[id_pour_drop]=ordre_nouveau;

                    for (var i = 0; i < nb_element_a_modifier; i++) {

                        tableaux_nouveaux_ordres[id_pour_bouclage]=nouveau_ordre_element_suivant;

                        nouveau_ordre_element_suivant--;
                        if(element_a_modifier != null){
                            element_a_modifier = element_a_modifier.previousElementSibling;
                            if(element_a_modifier != null){
                                id_pour_bouclage = element_a_modifier.dataset.id_colonne_sortable;
                            }
                        }
                    }

                }

                $.post({

                    url: "{{ URL::to("/eden/parametrage/carte/changement_ordre_filtre") }}",
                    dataType: "json",
                    data: {
                        tableaux_nouveaux_ordres: tableaux_nouveaux_ordres,
                        element: element,
                    }
                }).done(function(donnees) {

                    vue_instance[donnees.type_element]=donnees.elements;
                    vue_instance.$forceUpdate();
                    tableau_sortable.sortable("enable");
                });

            }
        });
    </script>
@endsection