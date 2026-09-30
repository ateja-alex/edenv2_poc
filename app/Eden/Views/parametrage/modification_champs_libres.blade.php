@extends('eden::templates.template')

@section('title') Champs libres @stop

@section('content')

    <div class="content-wrapper" >
        <div id="base-content" class="container-fluid">

            @if($erreur_droit !=null)
                <div class="alert alert-danger">{{ $erreur_droit }}</div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-3">
                        <div class="card-header">
                            <h4>
                                Champs libres
                            </h4>
                            <label class="mx-2" for="recherche_champs_libres">Recherche</label><input type="text" class="js_focus_input_recherche" name="recherche_champs_libres" v-model="recherche_champs_libres">
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th scope="col">Type_element</th>
                                        <th scope="col">Nom </th>
                                        <th scope="col">Modification</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="(champ_libre, key,index) in champs_libres" :key="champ_libre.nom+'_'+key" v-show="champ_libre.nom !== null && champ_libre.nom.indexOf(recherche_champs_libres.toLowerCase()) != -1 || champ_libre.nom_sql.indexOf(recherche_champs_libres.toLowerCase()) != -1">
                                        <td><p>@{{ champ_libre.type_element }}</p></td>
                                        <td><p >@{{ champ_libre.nom }}</p>
                                        </td>
                                        <td>
                                            <span class="css__lien" v-show="champ_libre.type == 1 || champ_libre.type == 12 || champ_libre.type == 10" @click="champs_libre_modifier_liste_libre(champ_libre)">Modifier la liste</span>
                                        </td>
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

    <!-- Modal liste libre -->
    @include('eden::parametrage.include.modal_champ_libre_liste_libre', ['vue_sql' => 0])

@endsection

@section('donnees_pour_vuejs_data')
    champs_libres: {!! $champs_libres !!},
    recherche_champs_libres: '',
    modal_liste_libre: false,
@endsection

@section('donnees_pour_vuejs_methods')

    charger_picker(){

		var vue_contexte = this;
		setTimeout(function() {
			iconpicker(vue_contexte);
		}, 500);
	},

@endsection



@push('scripts')
    <script>

		function iconpicker(vue_contexte){

			$('.icp-dd').iconpicker({
				defaultValue: false,
				placement: 'bottomLeft',
				hideOnSelect: false,
			});

			$('.icp').on('iconpickerSelected', function (e) {
				if($(this).hasClass('liste_libre_preenregistree')) {
					var id = e.target.id;
					id = id.replace('liste_libre_preenregistree_','');
					vue_contexte.liste_libre_preenregistree[id].icone = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('liste_libre')) {
					
					var id = e.target.id;
					id = id.replace('liste_libre_','');
					vue_contexte.liste_libre['valeurs'][id].icone = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('format_affichage_champ')){
					var id = e.target.id;
					id = id.replace('format_affichage_champ_','');
					vue_contexte.champ_libre.contenu[id] = e.iconpickerValue;
					vue_contexte.$forceUpdate();
				}
				if($(this).hasClass('icone_url')){
					vue_contexte.champ_libre.contenu[0] = e.iconpickerValue;
					vue_contexte.$forceUpdate();

				}
			});
		}

    </script>
@endpush

@push('scripts')
    <script>
        $('.js_focus_input_recherche').focus();
    </script>
@endpush