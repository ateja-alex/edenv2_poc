@extends('eden::templates.template')

@section('title') Cartes @stop

@section('content')
    <div id="vue_app" >
        <div class="content-wrapper" >
            <div  id="base-content" class="container-fluid">

                @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
                array('nom' => 'Cartes')
            )])

                <div class="row">
                    <div class="col-md-12">
                        <div class="card mb-3">
                            <div class="card-header d-flex align-items-center">
                                <h4>Cartes</h4>
                                <span data-toggle="tooltip" data-placement="left" title="" class="css_ajouter_element ml-auto" data-original-title="Nouvelle carte" @click="ajouter">
                                    <i aria-hidden="true" class="css_action_icon fa fa-fw fa-plus-square"></i>
                                </span>
                                <input class='css_input_recherche_liste ml-3' style="padding-left: 5px;" type="text" name="recherche_cartes" v-model="recherche_cartes" placeholder="Recherche"/>
                                <div class="css_btn_recherche_liste">
                                    <i class="css_action_icon fa fa-search" aria-hidden="true"></i>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
                                        <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nom</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="carte in cartes" :key="carte.id" v-show="carte.id_rapport.indexOf(recherche_cartes) != -1 || carte.id_rapport.indexOf(recherche_cartes) != -1 || (carte.id_rapport !== null && carte.id_rapport.indexOf(recherche_cartes) != -1)">
                                                <td><a v-bind:href="'eden/parametrage/carte/'+ carte.id">@{{ carte.id }}</a></td>
                                                <td><a v-bind:href="'eden/parametrage/carte/'+ carte.id">Carte : @{{carte.id_rapport}}</a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ajout Carte -->
    <div class="modal fade" id="modal_ajout_element" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestion des cartes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="#" method="post" class="css_form" id="formulaire_carte">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">id_rapport *</div>
                            <div class="col-sm-4"><input type="text" name="id_rapport" v-model="carte.id_rapport" pattern="^\S*$" /></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">Titre *</div>
                            <div class="col-sm-4"><input type="text" name="titre" v-model="carte.titre" /></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-2">Description *</div>
                            <div class="col-sm-4"><input type="text" name="description" v-model="carte.description" /></div>
                        </div>
                        
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" @click="enregistrer" v-show="carte.id_rapport != '' && carte.titre != '' && carte.description != '' && !/\s/.test(carte.id_rapport)">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('donnees_pour_vuejs_data')
    
    cartes: {!! $cartes !!},
    recherche_cartes: "",
    carte: { },
    
@endsection

@section('donnees_pour_vuejs_methods')

    ajouter() {

        $('#modal_ajout_element').modal('show');
    
        // on réinitialise la table
        this.carte = {
            id_rapport: '',
            titre: '',
            description: '',
        };
    },

    enregistrer(){

        $('#modal_ajout_element').modal('show');
    
        var vue_contexte = this;
    
        var data = $('#formulaire_carte').serialize();
    
        loading(true);
        // on enregistre les infos de la carte
        $.post({
            url: "{{ URL::to("eden/parametrage/carte/enregistrer_nouvelle_carte") }}",
            dataType: "json",
            data: $('#formulaire_carte').serialize()
            }).done(async function(donnees) {
                loading(false);
                
                if(donnees.succes !== true) {
                    await erreur(donnees.succes);
                    return;
                }
    
            $('#modal_ajout_element').modal('hide');
            window.location.href = '{{ url('/eden/parametrage/carte/') }}/'+donnees.carte.id;
        });
    },
    
@endsection