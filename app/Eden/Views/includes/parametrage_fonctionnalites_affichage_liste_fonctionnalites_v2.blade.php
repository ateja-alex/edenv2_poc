@extends('eden::templates.template')

@section('title') {!! traduction('interface.parametrage_fonctionnalites.configuration') !!} {{$type_module}} @stop

@section('styles')
<style>
    .ligne_fonctionnalite:target{

        animation: background-fade-fonctionnalite 3s forwards;
    }

    @keyframes background-fade-fonctionnalite {
        0% {
            background:#fff;
        }
        50% {
            background: var(--background_navbar);
        }
        100% {
            background:#fff;
        }
    }
</style>
@stop
@section('options_fil_ariane')
    <span data-toggle="tooltip" data-placement="left" :title="traduction('interface.modales.enregistrer')"
          class="css_ajouter_element ml-2" @click="enregistrer_fonctionnalites">
        <i class="css_action_icon far fa-save css_font_16"></i>
    </span>
@endsection
@section('content')

    <div class="content-wrapper">
        <div id="base-content" class="container-fluid">
            @include('eden::includes.fil_ariane', ['fil_ariane' => array(
                array('route' => 'parametrage.index', 'nom' => traduction('interface.fil_ariane.parametrage')),
                array('route' => 'parametrage.fonctionnalites.index', 'nom' => traduction('interface.fil_ariane.fonctionnalites')),
                array('nom' => $nom_module)
            )])

            @if(!empty($fonctionnalites_manquantes))
                <div class="alert alert-danger">
                    <h4>@traduction('interface.parametrage_fonctionnalites.fonctionnalites_manquantes')</h4><br><br>
                    @foreach($fonctionnalites_manquantes as $categorie_manquante => $fonctionnalites_manquante)
                        <h5>{!! $categorie_manquante !!} :</h5>
                        @foreach($fonctionnalites_manquante as $fonctionnalite_manquante)
                            - {!! $fonctionnalite_manquante !!} <br>
                        @endforeach
                        <br>
                    @endforeach
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div id="parametrage_module" class="card-header d-flex align-items-center">
                            <h4>
                                @traduction('interface.parametrage_fonctionnalites.parametrage_module') &laquo; {{$type_module}} &raquo;
                            </h4>
							<label for="recherche_module" class="css_recherche_module_parametrage_gauche  ml-auto">
                                <input type="text" name="recherche_module" :placeholder="traduction('interface.parametrage_fonctionnalites.recherche')" v-model="recherche_fonctionnalite">
                                <i class="fas fa-search"></i>
                            </label>

                        </div>
                        <div class="card-body" style="overflow: auto !important;min-height: 60vh;">

                            {{-- New table --}}
                            <div class="dropdown dropdown_hover d-inline-flex css_dropdown_tableau_parametrage">
                                <div class="css_dropdown_changement_liste">
                                    <span>{{$type_module}}</span>
                                    <i class="fas fa-angle-down"></i>
                                </div>
                                @include('eden::includes.parametrage_fonctionnalites_dropdown_onglet')
                            </div>

                            <table class="table table-bordered table-hover css_form css_table_parametrage">
                                <thead class="css_thead_parametrage">
                                    <th>@traduction('interface.parametrage_fonctionnalites.nom_parametre')</th>
                                    <th>@traduction('interface.parametrage_fonctionnalites.valeur')</th>
                                </thead>
                                <tbody>
                                    <template v-for="(fonctionnalites_de_la_categorie, categorie) in fonctionnalites">
                                        <tr>
                                            <td colspan="2" class="css_form_ligne_titre">
                                                @{{ categorie }}
                                            </td>
                                        </tr>
                                        <template v-for="fonctionnalite in fonctionnalites_de_la_categorie">
                                            <tr v-show="( recherche_fonctionnalite == ''
                                            || fonctionnalite.nom.toUpperCase().indexOf(recherche_fonctionnalite.toUpperCase()) >= 0 )
                                            && (( fonctionnalite.hasOwnProperty('fonctionnalite_mere') && verification_fonctionnalite_mere_remplie(fonctionnalite))
                                            || fonctionnalite.hasOwnProperty('fonctionnalite_mere') == false) "
                                                 class="ligne_fonctionnalite" :id="'ancre_'+fonctionnalite.fonctionnalite">
                                                <td>
                                                    <div class="d-flex align-items-center" style="gap:5px;justify-content: space-between;">
                                                        <div class="d-flex align-items-center" style="gap:10px;">
                                                            <span><span v-html="fonctionnalite.nom"></span> <i style="font-style: italic;color: #c7c6c6;font-size: 12px;">@{{fonctionnalite.fonctionnalite}}</i></span>
                                                            <span class="css_action_icon fa fa-question" style="background: #fbc428;font-size: 14px;height: 20px;width: 20px;line-height: 21px;" :title="traduction('interface.parametrage_fonctionnalites.a_tester')" data-toggle="tooltip" v-show="fonctionnalite.valide!==true && fonctionnalite.valide!==false"></span>
                                                            <span class="css_action_icon fa fa-check" style="background: rgb(41, 148, 28);font-size: 14px;height: 20px;width: 20px;line-height: 21px;" :title="traduction('interface.parametrage_fonctionnalites.fonctionnel')" data-toggle="tooltip"  v-show="fonctionnalite.valide===true"></span>
                                                            <span class="css_action_icon fa fa-times" style="background: #d62626;font-size: 14px;height: 20px;width: 20px;line-height: 21px;" :title="traduction('interface.parametrage_fonctionnalites.non_fonctionnel')" data-toggle="tooltip"  v-show="fonctionnalite.valide===false"></span>
                                                            <span class="css_action_icon fa fa-arrow-up" style="background: #d66515;font-size: 14px;height: 20px;width: 20px;line-height: 21px;" :title="traduction('interface.parametrage_fonctionnalites.a_ameliorer')" data-toggle="tooltip" v-show="fonctionnalite.a_ameliorer===true"></span>
                                                        </div>
                                                        <div class="d-flex align-items-center" style="gap:10px;">
                                                            <span class="css_toggle_aide_parametrage" v-if="fonctionnalite.gestion_profil">
                                                                <select v-model="fonctionnalite.profil">
                                                                    <option :value="null">Pour tous les profils</option>
                                                                    <option v-for="profil in profils" :value="profil.id" v-html="traduction('profil.'+profil.id+'.nom')"></option>
                                                                </select>
                                                            </span>
                                                            <span class="css_toggle_aide_parametrage js_toggle_infos_module"><i
                                                                    class="far fa-question-circle"
                                                                    v-if="fonctionnalite.description != null && fonctionnalite.description != ''"></i></span>
                                                        </div>
                                                    </div>
                                                    <div class="css_infos_module_parametrage js_block_infos_module">
                                                        @{{ fonctionnalite.description }}
                                                    </div>
                                                </td>
                                                <template v-if="fonctionnalite.profil > 0">
                                                    @include('eden::includes.champ_fonctionnalites',['modele' => "parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite.fonctionnalite][fonctionnalite.profil]"])
                                                </template>
                                                <template v-else>
                                                    @include('eden::includes.champ_fonctionnalites',['modele' => "parametres_fonctionnalites[fonctionnalite.fonctionnalite]"])
                                                </template>
                                            </tr>
                                        </template>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@foreach($fonctionnalites_type_connexion as $fonctionnalite)
    @if(view()->exists('eden::parametrage.fonctionnalites.connexion.modale_' . $fonctionnalite))
        @include('eden::parametrage.fonctionnalites.connexion.modale_' . $fonctionnalite)
    @endif
@endforeach

@foreach($fonctionnalites_modules as $fonctionnalite)
    @if(!empty($fonctionnalite['include']) && view()->exists('eden::parametrage.fonctionnalites.includes.' . $fonctionnalite['include']))
        @include('eden::parametrage.fonctionnalites.includes.' . $fonctionnalite['include'])
    @endif
@endforeach

@push('scripts')
    <script>
        $('.js_toggle_infos_module').click(function () {

            $(this).parent().parent().next('.js_block_infos_module').slideToggle(150);
            $(this).find('i').toggleClass('fas fa-times');
        });
    </script>
@endpush

@push('donnees_pour_vuejs_data')
    parametres_fonctionnalites: {!! collect($parametres_fonctionnalites) !!},
    fonctionnalites: {!! collect($fonctionnalites) !!},
    fonctionnalites_modules: {!! collect($fonctionnalites_modules) !!},
    recherche_fonctionnalite: '',
    fonctionnalites_type_connexion: {!! collect($fonctionnalites_type_connexion) !!},
    profils: {!! collect($profils) !!},
    @foreach($fonctionnalites_type_connexion as $fonctionnalite)
        modale_{!! $fonctionnalite !!} : false,
    @endforeach
@endpush

<script>
    @push('donnees_pour_vuejs_mounted')

        var vue_instance = this;

        if(window.location.hash.substr(1) != ''){

            position = $('#'+window.location.hash.substr(1)).offset().top;
            if($('#content-wrapper').height()){
                position = position - $('#content-wrapper').height();
            }
            position = position - 200;
            $('html,body').animate({
                scrollTop: position
            }, 'slow');
        }

        this.mise_en_place_profils();

    @endpush
    @push('donnees_pour_vuejs_methods')

        enregistrer_fonctionnalites: async function() {

            loading(true);

            var parametres_fonctionnalites = this.parametres_fonctionnalites;

            var vue_instance = this;

            var nom_module = "{{ $nom_module_lien }}";
            // On va vérifier les champs obligatoires si fonctionnalités mère activée
            var retour_verification_fonctionnalite_mere = this.verification_fonctionnalite_mere(parametres_fonctionnalites);

            if(retour_verification_fonctionnalite_mere.length > 0){

                await alerte_eden(vue_instance.traduction('interface.parametrage_fonctionnalites.alerte_fonctionnalites_a_remplir')+' :\r\n\r\n'+retour_verification_fonctionnalite_mere.join('\r\n'), '{{ traduction('interface.alerte.attention') }}');
                loading(false);
                return false;
            }

            parametres_fonctionnalites = this.gestion_profil(parametres_fonctionnalites);

            try {
                await $.post({

                    @if($type_module !== "Fonctionnalités spécifiques" )
                        url: '{{ route('parametrage.fonctionnalites.enregistrer') }}',
                    @else
                        url: '{{ route('parametrage.fonctionnalites.specifiques.enregistrer') }}',
                    @endif
                    data: {parametres_fonctionnalites},
                });
            } catch (error) {

                loading(false);
                await alerte_eden(vue_instance.traduction('interface.parametrage_fonctionnalites.erreur_enregistrement_message', null, [error.responseJSON.exception, error.responseJSON.message, error.responseJSON.file, error.responseJSON.line]), vue_instance.traduction('interface.parametrage_fonctionnalites.erreur_enregistrement'));
                return false;
            }

            $.post({

                url: '{{ route('parametrage.fonctionnalites.regenere_composants_lie') }}',
                data: {parametres_fonctionnalites, nom_module},
                success: (data) => {
                    this.fonctionnalites = data;

                    this.mise_en_place_profils();

                    vue_instance.$forceUpdate();
                    loading(false);
                }
            });
        },

        verification_fonctionnalite_mere : function(parametres_fonctionnalites){

            var vue_instance = this;
            var fonctionnalites_a_remplir = [];

            // On parcourt les fonctionnalités du module
            $.each( vue_instance.fonctionnalites, function( categorie, fonctionnalites ) {

                $.each( fonctionnalites, function( index, fonctionnalite ) {
                    var verification_mere = null;

                    // Si la fonctionnalité à une fonctionnalité mère et est obligatoire si mère activé, on fait la vérification
                    if(fonctionnalite.hasOwnProperty('obligatoire_si_mere_remplie') && fonctionnalite.obligatoire_si_mere_remplie === true){

                        verification_mere = vue_instance.verification_fonctionnalite_mere_remplie(fonctionnalite);


                        // Parent rempli, on vérifie maintenant que la fonctionnalité présente est rempli également
                        if(verification_mere == true && (parametres_fonctionnalites[fonctionnalite.fonctionnalite] == false
                            || parametres_fonctionnalites[fonctionnalite.fonctionnalite] == null
                            || parametres_fonctionnalites[fonctionnalite.fonctionnalite] == ""))
                            fonctionnalites_a_remplir.push(fonctionnalite.fonctionnalite);
                    }
                });
            });

            return fonctionnalites_a_remplir;
        },

        recupere_fonctionnalite : function(fonctionnalite_nom){

            var vue_instance = this;
            var fonctionnalite_retour = null;

            // On parcourt les fonctionnalités du module
            $.each( vue_instance.fonctionnalites, function( categorie, fonctionnalites ) {

                $.each( fonctionnalites, function( index, fonctionnalite ) {

                    if(fonctionnalite_nom === fonctionnalite.fonctionnalite) {

                        fonctionnalite_retour = fonctionnalite;
                        return false;
                    }
                });
            });

            return fonctionnalite_retour;
        },

        verification_fonctionnalite_mere_remplie: function(fonctionnalite_a_verifier){

            var fonctionnalite_recherche = fonctionnalite_a_verifier.fonctionnalite_mere;
            var valeur_cible = fonctionnalite_a_verifier.fonctionnalite_mere_valeur_cible;
            var valeur_parent = true;
            var vue_instance = this;

            if(fonctionnalite_recherche !== undefined) {

                valeur_parent = this.verification_fonctionnalite_mere_remplie(this.recupere_fonctionnalite(fonctionnalite_recherche));
            }

            if(valeur_parent != null && valeur_parent != false && valeur_parent != '' && valeur_parent != []) {

                $.each(vue_instance.parametres_fonctionnalites, function (fonctionnalite, valeur) {

                    if (fonctionnalite_recherche === fonctionnalite) {
                        valeur_parent = valeur;
                        return false;
                    }
                });
            }

            if(valeur_cible !== undefined)
                return valeur_parent == valeur_cible;
            if(typeof valeur_parent === 'object')
                return Object.values(valeur_parent).filter(v => v === true).length > 0;

            return valeur_parent;
        },

        //On vide juste la valeur contenue dans la fonctionnalité, car c'est le jeton d'authentification qui permet d'être connecté.
        deconnexion_api: function(fonctionnalite_type_connexion){

            var vue_instance = this;

            if(confirm(vue_instance.traduction('messages.js.mfiles.confirmer_deconnexion'))){

                loading(true);

                $.ajax({

                    url: "{{ route('parametrage.fonctionnalites.deconnexion_api') }}",
                    dataType: "json",
                    method: 'post',
                    data: {
                        fonctionnalite: fonctionnalite_type_connexion
                    }
                }).done(function(donnees) {

                    if(donnees.succes === true){

                        toastr.success(vue_instance.traduction('messages.js.mfiles.deconnexion'));
                        vue_instance.$data.parametres_fonctionnalites[`${fonctionnalite_type_connexion}`] = false;
                    }
                    else
                        toastr.error(donnees.erreur);

                    loading(false);
                });
            }
        },

        changement_valeur : function(fonctionnalite){

            if(fonctionnalite.methode_changement != null)
                this[fonctionnalite.methode_changement](fonctionnalite);
        },

        gestion_profil : function(parametres_fonctionnalites){

            for(fonctionnalite in parametres_fonctionnalites.fonctionnalites_par_profil){

                for(profil in parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite]){

                    if(JSON.stringify(parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite][profil]) == JSON.stringify(parametres_fonctionnalites[fonctionnalite]))
                        delete parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite][profil];
                }
            }

            return parametres_fonctionnalites;
        },

        mise_en_place_profils : function(){

            if(this.parametres_fonctionnalites.fonctionnalites_par_profil == null)
                this.$set(this.parametres_fonctionnalites,'fonctionnalites_par_profil',{});

            for(categorie in this.fonctionnalites){

                var fonctionnalites = this.fonctionnalites[categorie];

                for(fonctionnalite of fonctionnalites) {

                    if (fonctionnalite.gestion_profil) {
                        this.$set(fonctionnalite, 'profil', null);

                        if(this.parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite.fonctionnalite] == null || this.parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite.fonctionnalite] == false)
                            this.$set(this.parametres_fonctionnalites.fonctionnalites_par_profil,fonctionnalite.fonctionnalite,{});

                        for(profil of this.profils){

                            if(this.parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite.fonctionnalite][profil.id] == null)
                                this.$set(this.parametres_fonctionnalites.fonctionnalites_par_profil[fonctionnalite.fonctionnalite],profil.id,structuredClone(this.parametres_fonctionnalites[fonctionnalite.fonctionnalite]));
                        }
                    }
                }
            }
        },
    @endpush
</script>