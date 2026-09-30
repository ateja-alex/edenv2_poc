
@extends('eden::templates.template')

@section('title')Creances clients @stop

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
						<h4>@traduction('interface.tresorerie.creance_client_saisie.titre')</h4>
						<span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<div class="dropdown-header">@traduction('interface.tresorerie.creance_client_saisie.liste_des_entites')</div>
							<a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
						</div>
                        <span class="css_ajouter_element css__lien" @click= 'ajouter_nouvelle_ligne'><i class="fa fa-fw fa-plus-square" ></i> @traduction('interface.tresorerie.creance_client_saisie.cliquez_ici_ajouter_une_ligne')</span>
					</div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableau_recapitulatif_creance" width="100%" cellspacing="0" >
                                    <thead>
                                        <tr class="css_table_titre"  >
                                            <td style="text-transform: uppercase">@traduction('interface.tresorerie.creance_client_saisie.tableau_recap.colonne.client')</td>
                                            @foreach($mois as $unmois)
                                                <td>{{$unmois}}</td>
                                            @endforeach
                                        </tr>
                                    </thead>
                                        <tbody >
                                            <tr v-for='creance_client in creances_clients' :key='creance_client.id' :id_creance_client='creance_client.id' class="css_table_contenu js_table_contenu" >
												<td style="display: flex"><i class="fas fa-trash suppression_outil " style="padding-top:7px;padding-right:5px;" ></i><input type="text"  name="client" :placeholder="traduction('interface.tresorerie.creance_client_saisie.tableau_recap.placeholder.client_acme')" :value='creance_client.client'/></td>
												<td v-for="(montant_mensuel, date) in creance_client.montants_mensuels"   type="mois_selectionnes" :id_mois='date' ><input style="width:100%" type="number" :name='date' :value='montant_mensuel' @wheel.prevent @keydown.up.prevent @keydown.down.prevent/></td>
											</tr>
											<tr id_creance_client="0" class="css_table_contenu js_table_contenu" id="nouvelle_ligne" style="display: none">
												<td style="display: flex"><i class="fas fa-trash suppression_outil" style="padding-top:7px;padding-right:5px;" ></i><input type="text"  name="client" :placeholder="traduction('interface.tresorerie.creance_client_saisie.tableau_recap.placeholder.client_acme')" /></td>
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

creances_clients : {!! json_encode($creances_clients) !!},
type : 'creance_client',
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

		// le client
		options.client = $(ligne).find('input[name=client]').val();


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
			12: $(ligne).find('td[type=mois_selectionnes]').eq(12).find('input').val(),

		};
		options.entite_id = vue_instance.entite_choisi.id;

		options.montants_mensuels = montants_mensuels;

		// l'id de la creance
		options.id = $(ligne).attr('id_creance_client');

		// on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/creance_client/enregistrer')}}",
			dataType: "json",
			data: options,
		})
		.done(async function(donnees){

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$(ligne).attr('id_creance_client', donnees.id_creance_client);


		});


	},

	supprime_cette_ligne(event){

		var event =$(event.target);
		var ligne = event.closest('tr');

		var id_creance_client =ligne.attr('id_creance_client');

		$.post({
			url : "{{ URL::to('/eden/tresorerie/creance_client/supprimer')}}",
			dataType: "json",
			data: {id: id_creance_client},
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

			$('#tableau_recapitulatif_creance tbody tr').each(function() {

				if($(this).attr('id_creance_client') == undefined || $(this).attr('id_creance_client') == 0)
				return;

				ordre.push($(this).attr('id_creance_client'));
			});

			// on lance un appel ajax
			$.post({
				url : "{{ URL::to('/eden/tresorerie/creance_client/maj_ordre')}}",
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
			url : "{{ URL::to('/eden/tresorerie/creance_client/changement_entite')}}",
			dataType: "json",
			data: {

				entite_id : entite_id,
			},
		})
		.done(function(donnees){

			vue_instance.creances_clients = donnees.creances_clients;
			vue_instance.entites = donnees.entites;
			vue_instance.entite_choisi = donnees.entite_choisi;

			$('.nouvelle_ligne').remove();

			if(vue_instance.creances_clients.length == 0) {

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

	$('#tableau_recapitulatif_creance tbody').sortable({

		update: function() {

			vue_instance.mise_a_jour_ordre();

		}
	});


		$(document).ready(function() {


		$('body').on('change', '#tableau_recapitulatif_creance input', function() {

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

</script>>
@endsection


