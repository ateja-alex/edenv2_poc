@extends('eden::templates.template')

@section('title') Paramétrage formulaire @stop

@section('styles')
<style type="text/css">
	/* Firefox */
	input[type=number] {
		-moz-appearance: textfield;
	}

	/* Chrome */
	input::-webkit-inner-spin-button,
	input::-webkit-outer-spin-button {
		-webkit-appearance: none;
		margin:0;
	}

	/* Opéra*/
	input::-o-inner-spin-button,
	input::-o-outer-spin-button {
		-o-appearance: none;
		margin:0
	}

	tr.js_scroll{
		background-color: rgb(238, 238, 238);
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	tr.js_scroll td.css_nom_champ {
		font-size: 1.2em;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	tr {
		background-color: white;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

	td.css_nom_champ {
		font-size: 1em;
		transition-duration:1.5s;
		transition-timing-function: ease-in;
	}

</style>

@endsection

@section('content')

<div class="content-wrapper" >
	<div id="base-content" class="container-fluid">

		@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('route' => 'parametrage.table_libre.principales', 'nom' => 'Elements paramétrables'),
				array('route' => 'parametrage.table_libre.zoom','arguments' => [table_libre($type_element)->type_element] , 'nom' => table_libre($type_element)->element),
				array('nom' => 'Formulaire')
			)])

		<div class="row">
			<div class="col-md-12">
				<div class="card mb-3">
					<div class="card-header">
						<h4>
							Paramétrage du formulaire <span class="css_ajouter_element css__lien" @click="modifier_champ()"><i class="fa fa-fw fa-plus-square"></i> Ajouter un champ</span>
							<span class="css_ajouter_element css__lien" @click="modifier_formulaire()"><i class="fa fa-pencil-square-o"></i> Modifier le formulaire</span>
						</h4>
					</div>
					<div class="card-body css_form css_parametrage_formulaire" id="sortable">
						<div class="row">
							<div class="col-md-12">
								<h2><small>Valeurs</small></h2>
								<hr style="width: 15%;margin-bottom: 2%;margin-left: 0;">
								<table style="width: 100%;">
									<tr style="border-bottom: solid 1px black;">
										<th style="width: 20%;text-align: center;">Intitulé</th>
										<th style="width: 20%;text-align: center;">Espace avant intitulé</th>
										<th style="width: 20%;text-align: center;">Taille de l'intitulé</th>
										<th style="width: 20%;text-align: center;">Taille du champ</th>
										<th style="width: 20%;text-align: center;">Taille après le champ</th>
									</tr>
									<template v-for="champ in champs_libres">
										<tr :id="champ.nom_sql">
											<td style="padding-left: 1%;" class="css_nom_champ"><i @click="supprimer_le_champ(champ)" style="margin-right: 5%;" class="fas fa-trash"></i> @{{ champ.nom_sql }}{{--  <i @click="afficher_modale(champ.nom_sql)" class="fas fa-cogs"></i> --}}</td>
											<td style="text-align: center;">
												<input type="number" v-model.number="champ.taille_avant" @change="enregistre_un_champ(champ)" style="width: 20px; height: 20px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</td>
											<td style="text-align: center;">
												<input type="number" v-model.number="champ.taille_libelle" @change="enregistre_un_champ(champ)" style="width: 20px; height: 20px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</td>
											<td style="text-align: center;">
												<input type="number" v-model.number="champ.taille_champ" @change="enregistre_un_champ(champ)" style="width: 20px; height: 20px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</td>
											<td style="text-align: center;">
												<input type="number" v-model.number="champ.taille_apres" @change="enregistre_un_champ(champ)" style="width: 20px; height: 20px;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/>
											</td>
										</tr>
									</template>
								</table>
							</div>
						</div>
					</div>

					<hr style="width: 98%;margin-left: 1%;">

					<div class="card-body css_form css_parametrage_formulaire" id="sortable">
						<div class="row">
							<div class="col-md-12">
								<h2><small>Aperçus affichage</small></h2>
								<hr style="width: 35%;margin-bottom: 3%;margin-left: 0;">
							</div>
						</div>
						<div class="row js_parametrage_formulaire">
							<template v-for="champ in champs_libres">
								<div :class="'js_ancrage col-sm-'+((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))+(parseInt(champ.taille_champ)))" v-if="champ.taille_libelle != 0 && champ.taille_champ !=0 && champ.type != -1 && champ.type != -2" :id="champ.ordre" :data-nomsql="champ.nom_sql" :data-typeelement="champ.type_element" >

									<div class="row">

										<div v-if="champ.taille_avant > 0 && champ.taille_libelle != 0"  :style="'background: #fff;width:'+((parseInt(champ.taille_avant)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))+(parseInt(champ.taille_champ))))+'%'"></div>

										<div class="js_champ" v-if="champ.taille_libelle != 0 "  :style="'background: #009688; color: #f3f3f3;width:'+((parseInt(champ.taille_libelle)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))+(parseInt(champ.taille_champ))))+'%'" :nom_sql="champ.nom_sql">
											<a style="margin-right: 4%; color: white;" :href="'eden/parametrage/formulaire/client#'+champ.nom_sql"><i class="fas fa-arrow-up"></i></a>
											@{{ champ.nom_sql }}

										</div>
										<div class="js_connect_champ" v-if="champ.taille_champ != 0" :style="'background: #f1f1f1; color: #aaa;width:'+((parseInt(champ.taille_champ)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))+(parseInt(champ.taille_champ))))+'%'">
											CHAMP

										</div>
										<div v-if="champ.type >= 0 && champ.taille_apres > 0 && champ.taille_champ != 0"  :style="'background: #fff;width:'+((parseInt(champ.taille_apres)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))+(parseInt(champ.taille_champ))))+'%'"></div>
									</div>
								</div>
								<div v-if="champ.taille_libelle == 0 || champ.taille_champ == 0 && champ.type != -1 && champ.type != -2" class="col-sm-12" :id="champ.ordre" >
									<div class="row">
										<div class="js_champ col-sm-12 css_form_ligne_titre" v-if="champ.type == -1 && champ.taille_libelle == 0" :nom_sql="champ.nom_sql" taille_libelle="12" taille_champ="0">@{{ champ.nom_sql }}</div>

										<div class="col-sm-6" v-if="champ.type >= 0 && champ.taille_avant > 0 && champ.taille_libelle == 0"  style="background: #fff;"></div>

										<div class="js_champ col-sm-6" v-if="champ.type >= 0 && champ.taille_libelle == 0"  style="background: #009688; color: #f3f3f3;" :nom_sql="champ.nom_sql">

											@{{ champ.nom_sql }}

										</div>
										<div class="js_connect_champ col-sm-6" v-if="champ.type >= 0 && champ.taille_champ == 0 && champ.type != -1" style="background: #f1f1f1; color: #aaa;">
											CHAMP

										</div>
										<div :class="'col-sm-'+(champ.taille_apres)" v-if="champ.type >= 0 && champ.taille_apres > 0 && champ.taille_champ == 0"  style="background: #fff;"></div>
									</div>
								</div>
								<div v-if="champ.type == -1 || champ.type == -2" :class="'js_ancrage col-sm-'+((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle)))" :id="champ.ordre" :data-nomsql="champ.nom_sql" :data-typeelement="champ.type_element">
									<div class="row">

										<div v-if="champ.taille_avant > 0"  :style="'background: #fff;width:'+((parseInt(champ.taille_avant)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))))+'%'"></div>

										<div class="js_champ" :style="'background: #69767b; color: #f3f3f3;width:'+((parseInt(champ.taille_libelle)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))))+'%'" :nom_sql="champ.nom_sql">
											<a style="margin-right: 4%; color: white;" :href="'eden/parametrage/formulaire/client#'+champ.nom_sql"><i class="fas fa-arrow-up"></i></a>
											@{{ champ.nom_sql }}

										</div>
										<div v-if="champ.taille_apres > 0" :style="'background: #fff;width:'+((parseInt(champ.taille_apres)*100)/((parseInt(champ.taille_avant))+(parseInt(champ.taille_apres))+(parseInt(champ.taille_libelle))))+'%'"></div>
									</div>
								</div>
							</template>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<!-- Modal modification du formulaire -->
<div class="modal fade" id="modal_modification_formulaire" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Gestion du formulaire</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<label for="nom_formulaire">Forcer l'utilisation du formulaire paramétrable</label>
					</div>
					<div class="col-sm-4">
						<select name="surcharger_la_vue" id="surcharger_la_vue" v-model="formulaire.surcharger_la_vue">
							<option value="0">Non</option>
							<option value="1">Oui</option>
						</select>
					</div>
				</div>

				<div class="row">
					<div class="col-sm-2">
						Data Vuejs
					</div>
					<div class="col-sm-4">
						<input type="text" name="vuejs_data" v-model="formulaire.vuejs_data">
					</div>
					<div class="col-sm-2">
						Méthodes Vuejs
					</div>
					<div class="col-sm-4">
						<input type="text" name="vuejs_methods" v-model="formulaire.vuejs_methods">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_formulaire">Enregistrer</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal ajout élément -->
<div class="modal fade" id="modal_modification_champ" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Gestion des formulaires</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<form action="#" method="post" class="css_form" id="formulaire_champ_libre">
					{{ csrf_field() }}
					<div class="row">
						<div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
					</div>
					<div class="row">
						<div class="col-sm-3">
							Nom sql
						</div>
						<div class="col-sm-9">
							<select name="nom_sql" id="nom_sql">
								@foreach($champs_libres as $champ)
								<option value="{{ $champ['nom_sql'] }}">{{ $champ['nom'] }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-sm-4">Taille avant le libellé</div>
						<div class="col-sm-2">
							<select name="taille_avant" id="taille_avant">
								@for($i=0; $i<=12; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille après le champ</div>
						<div class="col-sm-2">
							<select name="taille_apres" id="taille_apres">
								@for($i=0; $i<=12; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille du libellé</div>
						<div class="col-sm-2">
							<select name="taille_libelle" id="taille_libelle">
								@for($i=0; $i<=12; $i++)
								@if($i == 2)
								<option value="{{ $i }}" selected="">{{ $i }}</option>
								@else
								<option value="{{ $i }}">{{ $i }}</option>
								@endif
								@endfor
							</select>
						</div>
						<div class="col-sm-4">Taille du champ</div>
						<div class="col-sm-2">
							<select name="taille_champ" id="taille_champ">
								@for($i=0; $i<=12; $i++)
								@if($i == 4)
								<option value="{{ $i }}" selected="">{{ $i }}</option>
								@else
								<option value="{{ $i }}">{{ $i }}</option>
								@endif
								@endfor
							</select>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer">Enregistrer</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal ajout/édition utilisateur -->
<div class="modal fade" id="modal_condition_d_apparition" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Conditions d'apparition</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-sm-6">
						<template v-for="champ_modale in champs_libres">
							<p @click="recuperer_les_options(champ_modale)" v-if="champ_modale.type == 1 ||champ_modale.type == 20">@{{ champ_modale.nom }}</p>
						</template>
					</div>
					<div class="col-sm-6" >
						<h4><small>@{{ nom_sql_du_select }}</small></h4>
						<hr v-if="nom_sql_du_select != ''" style="width: 100%;">
						<template v-for="option in options">
							<p @click="ajouter_condition(option)"> - @{{ option }}</p>
						</template>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" @click="fermer_modale" data-dismiss="modal">Fermer</button>
				<button type="button" class="btn btn-primary" @click="enregistrer_conditions">Enregistrer</button>
			</div>
		</div>
	</div>
</div>
</div>


@endsection

@section('scripts')
<script>
	$( "#sortable" ).sortable({

		items: '.js_champ',
		update: function() {

			vue_instance.enregistrer();
		},
	});
	$( "#sortable" ).disableSelection();


</script>
@endsection

@section('donnees_pour_vuejs_data')
champs_libres: {!! $les_champs !!},
formulaire: {!! $formulaire !!},
champ: {},
options: {},
nom_sql_du_select: "",
nom_sql_du_champ_libre: "",
les_conditions: [
@foreach(table_libre($type_element)->champs_libres()->where('afficher_sur_formulaire', 1)->orderBy('ordre')->get() as $le_champ)
@if($le_champ['type'] == 20 || $le_champ['type'] == 1)
{{$le_champ['nom_sql']}} = [],
@endif
@endforeach
],
@endsection

<script>
	@section('donnees_pour_vuejs_methods')

	ajouter_condition: function(option) {

		this.les_conditions[this.nom_sql_du_select].push(option);
	},

	enregistrer_conditions: function(event){

		$.post({

			url: "{{ URL::to("/eden/parametrage/formulaire/ajouter_conditions") }}",
			dataType: "json",
			data: {

				nom_formulaire: champ.nom_formulaire,
				nom_sql_champ_libre: vue_instance.nom_sql_du_champ_libre,
				les_conditions: vue_instance.les_conditions,
			}
		});
	},

	recuperer_les_options: function(champ){

		vue_instance.nom_sql_du_select = champ.nom_sql;

		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire/obtenir_options") }}",
			dataType: "json",
			data: {

				type_element: champ.type_element,
				nom_sql: champ.nom_sql,
			}
		}, function(data) {
			vue_instance.options = data;
		});

	},


	afficher_modale: function(nom_sql_champ_libre) {

		$('#modal_condition_d_apparition').modal('show');
		vue_instance.nom_sql_du_champ_libre = nom_sql_champ_libre;
	},

	fermer_modale: function(event) {

		$('#modal_condition_d_apparition').modal('hide');
	},

	modifier_champ: function(champ) {

		this.champ = champ;
		$('#modal_modification_champ').modal('show');
	},

	modifier_formulaire: function() {

		$('#modal_modification_formulaire').modal('show');
	},

	enregistre_un_champ: function(champ) {

		if (champ.taille_avant == null || champ.taille_avant == '') {
			champ.taille_avant = 0;
		}

		if (champ.taille_apres == null || champ.taille_apres == '') {
			champ.taille_apres = 0;
		}

		// console.log(champ.taille_avant);

		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire/enregistre_un_champ") }}",
			dataType: "json",
			data: {

				nom_formulaire: champ.nom_formulaire,
				nom_sql: champ.nom_sql,
				taille_avant: champ.taille_avant,
				taille_apres: champ.taille_apres,
				taille_libelle: champ.taille_libelle,
				taille_champ: champ.taille_champ,
			}
		});
	},

	enregistrer_formulaire: function() {

		$('#modal_modification_formulaire').modal('hide');
		var formulaire = vue_instance.formulaire;
		// on enregistre
		$.post({

			url: "{{ route("parametrage.formulaire.modifier") }}",
			dataType: "json",
			data: {
				id_du_formulaire: formulaire['id'],
				nom_formulaire: formulaire['nom_formulaire'],
				vuejs_data: formulaire['vuejs_data'],
				vuejs_methods: formulaire['vuejs_methods'],
				surcharger_la_vue: formulaire['surcharger_la_vue'],
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else{

				vue_instance.champs_libres.push(donnees.nouveau_champ);
			}

			// loading(false);

			// vue_instance.champs_libres = donnees.champs_libres;
			// vue_instance.$forceUpdate();
		});
	},

	enregistrer: function() {

		// loading(true);



		nom_sql = $("#nom_sql").val();
		taille_libelle = $("#taille_libelle").val();
		taille_champ = $("#taille_champ").val();
		taille_apres = $("#taille_apres").val();
		taille_avant = $("#taille_avant").val();

		$('#modal_modification_champ').modal('hide');
		// on enregistre
		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire") }}",
			dataType: "json",
			data: {
				type_element: '{{$type_element}}',
				taille_avant: taille_avant,
				taille_libelle: taille_libelle,
				taille_champ: taille_champ,
				taille_apres: taille_apres,
				nom_sql: nom_sql,
				nom_formulaire: '{{ $nom_formulaire }}',
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else{

				vue_instance.champs_libres.push(donnees.nouveau_champ);
			}

			// loading(false);

			// vue_instance.champs_libres = donnees.champs_libres;
			// vue_instance.$forceUpdate();
		});
	},

	supprimer_le_champ: function(champ) {

		id_champ = champ.id;

		$.post({

			url: "{{ URL::to("eden/parametrage/formulaire/supprimer") }}",
			dataType: "json",
			data: {
				id_champ: id_champ,
			}
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}
			else {

				for(index = 0;index < vue_instance.champs_libres.length;index++){

					if (vue_instance.champs_libres[index].id == id_champ) {

						vue_instance.champs_libres.splice(index, 1);
					}
				}
				// console.log(donnees.les_champs);
				vue_instance.champs_libres = donnees.les_champs;
			}

		});
	},
	@endsection
</script>

@push('scripts')
<script>

	$(document).ready(function(){

		$("a").on('click', function(event) {

			if (this.hash !== "") {

				event.preventDefault();
				var hash = this.hash;
				$('html, body').animate({ scrollTop: $(hash).offset().top - 70}, 800, function(){

					window.location.hash = hash;
				});
			}
			$(hash).addClass('js_scroll');
			setTimeout(function(){
				$(hash).removeClass('js_scroll');
			}, 3000);
		});
	});

	for(le_champ in vue_instance.champs_libres) {

		if (vue_instance.champs_libres[le_champ]['taille_libelle'] == 0) {

			$.post({

				url: "{{ URL::to("eden/parametrage/formulaire/modifier_taille_libelle") }}",
				dataType: "json",
				data: {

					nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
					nom_sql: vue_instance.champs_libres[le_champ]['nom_sql'],
					taille_libelle: '6',
				}
			});
		}
		if (vue_instance.champs_libres[le_champ]['taille_champ'] == 0) {

			$.post({

				url: "{{ URL::to("eden/parametrage/formulaire/modifier_taille_champ") }}",
				dataType: "json",
				data: {

					nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
					nom_sql: vue_instance.champs_libres[le_champ]['nom_sql'],
					taille_champ: '6',
				}
			});

		}
	}

	$('.js_parametrage_formulaire').sortable({
		update : function (event, ui) {

			var position = $(ui.originalPosition['top']);
			var position_origine = $(ui.position['top']);
			var item = ui.item[0];
			var type_element = item.dataset.typeelement;
			var nom_sql_pour_drop=item.dataset.nomsql;
			var ordre_origine = item['id'];
			var difference = position[0] - position_origine[0];

			if (difference > 0) {

				var ordre_nouveau = ui.item[0].nextSibling.attributes[0].nodeValue;
				item.setAttribute("id",ordre_nouveau);
				var nb_element_a_modifier = ordre_origine - ordre_nouveau;
				var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) + 1;
				var element_a_modifier = ui.item[0].nextSibling;
				var nom_sql_pour_bouclage = element_a_modifier.dataset.nomsql;

				$.post({

					url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre_champ") }}",
					dataType: "json",
					data: {
						nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
						nom_sql: nom_sql_pour_drop,
						ordre: ordre_nouveau,

					}
				});

				for (var i = 1; i <= nb_element_a_modifier; i++) {

					element_a_modifier.setAttribute("id", nouveau_ordre_element_suivant);
					$.post({

						url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre_champ") }}",
						dataType: "json",
						data: {
							nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
							nom_sql: nom_sql_pour_bouclage,
							ordre: nouveau_ordre_element_suivant,

						}
					});

					element_a_modifier = element_a_modifier.nextElementSibling;
					nouveau_ordre_element_suivant++;
					nom_sql_pour_bouclage = element_a_modifier.dataset.nomsql;
				}
			}

			else{

				var ordre_nouveau = ui.item[0].previousSibling.attributes[0].nodeValue;
				item.setAttribute("id",ordre_nouveau);
				var nb_element_a_modifier = ordre_nouveau - ordre_origine;
				var nouveau_ordre_element_suivant = parseInt(ordre_nouveau) - 1;
				var element_a_modifier = ui.item[0].previousSibling;
				var nom_sql_pour_bouclage = element_a_modifier.dataset.nomsql;

				$.post({

					url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre_champ") }}",
					dataType: "json",
					data: {
						nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
						nom_sql: nom_sql_pour_drop,
						ordre: ordre_nouveau,

					}
				});

				for (var i = 0; i < nb_element_a_modifier; i++) {

					element_a_modifier.setAttribute("id", nouveau_ordre_element_suivant);
					$.post({

						url: "{{ URL::to("eden/parametrage/formulaire/modifier_ordre_champ") }}",
						dataType: "json",
						data: {
							nom_formulaire: vue_instance.champs_libres[le_champ]['nom_formulaire'],
							nom_sql: nom_sql_pour_bouclage,
							ordre: nouveau_ordre_element_suivant,

						}
					});

					element_a_modifier = element_a_modifier.previousElementSibling;
					nouveau_ordre_element_suivant--;
					nom_sql_pour_bouclage = element_a_modifier.dataset.nomsql;
				}

			}



		}
	});

</script>
@endpush
