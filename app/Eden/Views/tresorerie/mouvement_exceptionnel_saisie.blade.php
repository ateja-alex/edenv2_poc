
@extends('eden::templates.template')

@section('title')Mouvements exceptionnels @stop

@section('styles')

<style>

td{
    font-size:12px;
}

.css_option_active{

    color:#20B50F;
    font-weight:bold;
}

.gras{
    font-weight:bold;
    cursor : pointer;
}

.ligne_cochee_invisible.ligne_cochee  {

	display: none;
}

.ligne_cochee{

    text-decoration: line-through;
	color: #c7c7c7;
}
</style>
@endsection

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
									<h4>@traduction('interface.tresorerie.mouvement_exceptionnel_saisie.titre')</h4>
									<span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
										<div class="dropdown-header">@traduction('interface.tresorerie.mouvement_exceptionnel_saisie.liste_des_entites')</div>
										<a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
									</div>
									<span class="css_ajouter_element css__lien" @click= 'ajouter_nouvelle_ligne'><i class="fa fa-fw fa-plus-square" ></i>@traduction('interface.tresorerie.mouvement_exceptionnel_saisie.cliquez_ici_ajouter_ligne')</span>
								</div>
							</div>
							<div class="card-body">
								<div class="table-responsive">
									<table class="table table-bordered table-hover" id="tableau_recapitulatif_mouvement" width="100%" cellspacing="0" >
										<thead>
											<tr class="css_table_titre"  >
												<td style="text-transform: uppercase">@traduction('interface.tresorerie.mouvement_exceptionnel_saisie.tableau_recap.colonne.nom')</td>
												@foreach($mois as $unmois)
													<td>{{$unmois}}</td>
												@endforeach
											</tr>
										</thead>
										<tbody >
											<tr v-for='mouvement_exceptionnel in mouvements_exceptionnels' :key="mouvement_exceptionnel.id" :id_mouvement_exceptionnel='mouvement_exceptionnel.id' class="css_table_contenu js_table_contenu" >
												<td style="display: flex"><i class="fas fa-trash suppression_outil " style="padding-top:7px;padding-right:5px;" ></i><input type="text"  name="nom" :placeholder="traduction('interface.tresorerie.mouvement_exceptionnel_saisie.tableau_recap.placeholder.nom')" :value='mouvement_exceptionnel.nom'/></td>
												<td v-for="(montant_mensuel, date) in mouvement_exceptionnel.montants_mensuels"   type="mois_selectionnes" :id_mois='date' ><input style="width:100%" type="number" :name='date' :value='montant_mensuel' @wheel.prevent @keydown.up.prevent @keydown.down.prevent></td>
											</tr>
											<tr id_mouvement_exceptionnel="0" class="css_table_contenu js_table_contenu" id="nouvelle_ligne" style="display: none">
												<td style="display: flex"><i class="fas fa-trash suppression_outil" style="padding-top:7px;padding-right:5px;" @click="supprime_cette_ligne"></i><input type="text"  name="nom" :placeholder="traduction('interface.tresorerie.mouvement_exceptionnel_saisie.tableau_recap.placeholder.nom')" /></td>
												@foreach($dates as $unedate)                       
													<td type="mois_selectionnes" id_mois={{$unedate}} ><input style="width:100%" type="number" name={{$unedate}} value="" @wheel.prevent @keydown.up.prevent @keydown.down.prevent /></td>
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
@endsection

@section('donnees_pour_vuejs_data')

mouvements_exceptionnels : {!! json_encode($mouvements_exceptionnels) !!},
type : 'mouvement_exceptionnel',
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

		// le nom
		options.nom = $(ligne).find('input[name=nom]').val();


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

		options.montants_mensuels = montants_mensuels;

		options.entite_id = vue_instance.entite_choisi.id;

		// l'id du mouvement
		options.id = $(ligne).attr('id_mouvement_exceptionnel');

		// on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/mouvement_exceptionnel/enregistrer')}}",
			dataType: "json",
			data: options,
		})
		.done(async function(donnees){

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$(ligne).attr('id_mouvement_exceptionnel', donnees.id_mouvement_exceptionnel);


		});


	},

	supprime_cette_ligne(event){

		var event =$(event.target);
		var ligne = event.closest('tr');

		var id_mouvement_exceptionnel = ligne.attr('id_mouvement_exceptionnel');

		$.post({
			url : "{{ URL::to('/eden/tresorerie/mouvement_exceptionnel/supprimer')}}",
			dataType: "json",
			data: {id: id_mouvement_exceptionnel},
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

			$('#tableau_recapitulatif_mouvement tbody tr').each(function() {

				if($(this).attr('id_mouvement_exceptionnel') == undefined || $(this).attr('id_mouvement_exceptionnel') == 0)
				return;

				ordre.push($(this).attr('id_mouvement_exceptionnel'));
			});

			// on lance un appel ajax
			$.post({
				url : "{{ URL::to('/eden/tresorerie/mouvement_exceptionnel/maj_ordre')}}",
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
			url : "{{ URL::to('/eden/tresorerie/mouvement_exceptionnel/changement_entite')}}",
			dataType: "json",
			data: {

				entite_id : entite_id,
			},
		})
		.done(function(donnees){

			vue_instance.mouvements_exceptionnels = donnees.mouvements_exceptionnels;
			vue_instance.entites = donnees.entites;
			vue_instance.entite_choisi = donnees.entite_choisi;

			$('.nouvelle_ligne').remove();

			if(vue_instance.mouvements_exceptionnels.length == 0) {

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

	$('#tableau_recapitulatif_mouvement tbody').sortable({

		update: function() {

			vue_instance.mise_a_jour_ordre();

		}
	});


		$(document).ready(function() {


		$('body').on('change', '#tableau_recapitulatif_mouvement input', function() {

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


