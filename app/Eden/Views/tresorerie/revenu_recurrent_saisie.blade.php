
@extends('eden::templates.template')

@section('title')Revenus recurrents @stop

@section('content')

<div class="content-wrapper" >
@include("eden::includes.treso_menu")
<div id="base-content" class="container-fluid">
    <div class="row">
	<div class="col-sm-12">
    <div class="row">
		<div class="col-md-12">
			<div class="card mb-3">
				<div class="card-header">
					<div class="css_flex_header_liste">
						<h4>@traduction('interface.tresorerie.revenue_reccurent_saisie.titre')</h4>
						<span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<div class="dropdown-header">@traduction('interface.tresorerie.revenue_reccurent_saisie.liste_des_entites')</div>
							<a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
						</div>
                        <span class="css_ajouter_element css__lien" @click= 'ajouter_nouvelle_ligne'><i class="fa fa-fw fa-plus-square" ></i>@traduction('interface.tresorerie.revenue_reccurent_saisie.cliquez_ici_ajouter_une_ligne') </span>
					</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableau_recapitulatif_revenu" width="100%" cellspacing="0" >
                                    <thead>
                                        <tr class="css_table_titre"  >
                                            <td>@traduction('interface.tresorerie.revenue_reccurent_saisie.tableau_recap.colonne.source_de_revenus')</td>
                                            @foreach($mois as $unmois)
                                                <td>{{$unmois}}</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                        <tbody >
                                            <tr v-for='revenu_recurrent in revenus_recurrents' :key="revenu_recurrent.id" :id_revenu_recurrent='revenu_recurrent.id' class="css_table_contenu js_table_contenu" >
												<td style="display: flex"><i class="fas fa-trash suppression_outil " style="padding-top:7px;padding-right:5px;" ></i><input type="text"  name="source_revenu" :placeholder="traduction('interface.tresorerie.revenue_reccurent_saisie.tableau_recap.champs.source_de_revenu')" :value='revenu_recurrent.source_revenu'/></td>
												<td v-for="(montant_mensuel, date) in revenu_recurrent.montants_mensuels"   type="mois_selectionnes" :id_mois='date' ><input style="width:100%" type="number" :name='date' :value='montant_mensuel' @wheel.prevent @keydown.up.prevent @keydown.down.prevent/></td>
											</tr>
											<tr id_revenu_recurrent="0" class="css_table_contenu js_table_contenu" id="nouvelle_ligne" style="display: none">
												<td style="display: flex"><i class="fas fa-trash suppression_outil" style="padding-top:7px;padding-right:5px;" @click="supprime_cette_ligne"></i><input type="text"  name="source_revenu" :placeholder="traduction('interface.tresorerie.revenue_reccurent_saisie.tableau_recap.champs.source_de_revenu')" /></td>
												@foreach($dates as $unedate)                       
													<td type="mois_selectionnes" id_mois={{$unedate}} ><input style="width:100%" type="number" name={{$unedate}} value="" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/></td>
												@endforeach
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
</div>
</div>
</div>
@endsection

@section('donnees_pour_vuejs_data')

revenus_recurrents : {!! json_encode($revenus_recurrents) !!},
type : 'revenu_recurrent',
entite_choisi : {!! json_encode($entite_choisi) !!},
entites : {!! json_encode($entites) !!},

@endsection

@section('donnees_pour_vuejs_methods')


	ajouter_nouvelle_ligne() {

			var nouvelle_ligne = $('#nouvelle_ligne').clone();

			nouvelle_ligne.attr('id', '').attr('class', 'css_table_contenu js_table_contenu nouvelle_ligne').show();

			$('#nouvelle_ligne').before(nouvelle_ligne);

			var vue_instance = this;

			$('.suppression_outil').on('click', function(event){

				vue_instance.supprime_cette_ligne(event);
			});

	},


	mise_a_jour_ligne(ligne) {

		var options = {};

		var vue_instance = this;

		// la source de revenu
		options.source_revenu = $(ligne).find('input[name=source_revenu]').val();

		options.entite_id = vue_instance.entite_choisi.id;

		// le détail par mois
		var montants_mensuels = {
			0: $(ligne).find('td[type=mois_selectionnes]').eq(0).find('input').val(),
			1: $(ligne).find('td[type=mois_selectionnes]').eq(1).find('input').val(),
			2: $(ligne).find('td[type=mois_selectionnes]').eq(2).find('input').val(),
			3: $(ligne).find('td[type=mois_selectionnes]').eq(3).find('input').val(),
			4: $(ligne).find('td[type=mois_selectionnes]').eq(4).find('input').val(),
			5: $(ligne).find('td[type=mois_selectionnes]').eq(5).find('input').val(),
			6: $(ligne).find('td[type=mois_selectionnes]').eq(6).find('input').val(),
			7: $(ligne).find('td[type=mois_selectionnes]').eq(7).find('input').val(),
			8: $(ligne).find('td[type=mois_selectionnes]').eq(8).find('input').val(),
			9: $(ligne).find('td[type=mois_selectionnes]').eq(9).find('input').val(),
			10: $(ligne).find('td[type=mois_selectionnes]').eq(10).find('input').val(),
			11: $(ligne).find('td[type=mois_selectionnes]').eq(11).find('input').val(),

		};

		options.montants_mensuels = montants_mensuels;

		// l'id du revenu
		options.id = $(ligne).attr('id_revenu_recurrent');

		// on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/revenu_recurrent/enregistrer')}}",
			dataType: "json",
			data: options,
		})
		.done(async function(donnees){

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$(ligne).attr('id_revenu_recurrent', donnees.id_revenu_recurrent);


		});


	},

	supprime_cette_ligne(event){

		var event =$(event.target);
		var ligne = event.closest('tr');

		var id_revenu_recurrent = ligne.attr('id_revenu_recurrent');

		$.post({
			url : "{{ URL::to('/eden/tresorerie/revenu_recurrent/supprimer')}}",
			dataType: "json",
			data: {id: id_revenu_recurrent},
		}).done(async function(donnees){

			if(donnees !== true) {

				await erreur(donnees);
				return;
			}


		});

		ligne.fadeOut(500);
	},

	mise_a_jour_ordre(){

		// on doit enregistrer l'ordre
			var ordre = new Array;

			$('#tableau_recapitulatif_revenu tbody tr').each(function() {

				if($(this).attr('id_revenu_recurrent') == undefined || $(this).attr('id_revenu_recurrent') == 0)
				return;

				ordre.push($(this).attr('id_revenu_recurrent'));
			});

			// on lance un appel ajax
			$.post({
				url : "{{ URL::to('/eden/tresorerie/revenu_recurrent/maj_ordre')}}",
				dataType: "json",
				data: {

					ordre: ordre
				},
			})
			.done(async function(donnees){

				if(donnees !== true) {

				await erreur(donnees);
				return;
			}
			});

	},

	changement_entite(event){

		var vue_instance = this;
		var	event = $(event.target);
		entite_id = event.attr('id');

		// on lance un appel ajax
			$.post({
				url : "{{ URL::to('/eden/tresorerie/revenu_recurrent/changement_entite')}}",
				dataType: "json",
				data: {

					entite_id : entite_id,
				},
			})
			.done(function(donnees){

				vue_instance.revenus_recurrents = donnees.revenus_recurrents;
				vue_instance.entites = donnees.entites;
				vue_instance.entite_choisi = donnees.entite_choisi;

				$('.nouvelle_ligne').remove();

				if(vue_instance.revenus_recurrents.length == 0) {

					vue_instance.ajouter_nouvelle_ligne();
				}

				setTimeout(function(){
				$('.suppression_outil').on('click', function(event){

					vue_instance.supprime_cette_ligne(event);
				});
				},500);
			});
		},

@endsection

@section('scripts')

	<script type="text/javascript">

	$('#tableau_recapitulatif_revenu tbody').sortable({

		update: function() {

			vue_instance.mise_a_jour_ordre();

		}
	});


		$(document).ready(function() {


		$('body').on('change', '#tableau_recapitulatif_revenu input', function() {

			vue_instance.mise_a_jour_ligne($(this).closest('tr'));

			setTimeout(function(){

				vue_instance.mise_a_jour_ordre();

			},500);
		});

		// on ajoute une première ligne
		if($('.js_table_contenu:visible').length == 0) {

			vue_instance.ajouter_nouvelle_ligne();
		}

		$('.suppression_outil').on('click', function(event){

			vue_instance.supprime_cette_ligne(event);
		});

	});
	</script>

@endsection
