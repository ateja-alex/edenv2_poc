
@extends('eden::templates.template')

@section('title')Charges recurrentes @stop

@section('styles')

<style>

thead td{
    font-size:10px;
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
						<h4>@traduction('interface.tresorerie.charge_recurrente_saisie.titre') </h4>
						<span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<div class="dropdown-header">@traduction('interface.tresorerie.charge_recurrente_saisie.liste_des_entites')</div>
							<a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
						</div>
                        <span class="css_ajouter_element css__lien" @click= 'ajouter_nouvelle_ligne'><i class="fa fa-fw fa-plus-square" ></i> @traduction('interface.tresorerie.charge_recurrente_saisie.cliquez_ici_ajouter_poste')</span>
					</div>
                </div>
                <div class="card-body">
                        <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="tableau_recapitulatif_poste" width="100%" cellspacing="0">
                                    <thead>
                                        <tr class="css_table_titre " >
                                            <td style="font-size:15px" >@traduction('interface.tresorerie.charge_recurrente_saisie.tableau_recap.colonne.poste')</td>
                                            <td width="70px" >@traduction('interface.tresorerie.charge_recurrente_saisie.tableau_recap.colonne.montant_ttc')</td>
                                            <td width="70px">@traduction('interface.tresorerie.charge_recurrente_saisie.tableau_recap.colonne.chaque_mois')</td>
											@for($i = 1; $i < 13; $i++)
												<td style="text-transform: uppercase">{!! App\Eden\Variables::mois_de_lannee_format_complet($i) !!}</td>
											@endfor

                                        </tr>
                                    </thead>
                                        <tbody >
                                            <tr v-for='charge_recurrente in charges_recurrentes' :key="charge_recurrente.id" :id_charge_recurrente='charge_recurrente.id' class="css_table_contenu js_table_contenu" >
												<td style="display: flex"><i class="fas fa-trash suppression_outil " style="padding-top:7px;padding-right:5px;" ></i><input type="text"  name="charge" :placeholder="traduction('interface.tresorerie.charge_recurrente_saisie.placeholder.nouvelle_charge')" :value='charge_recurrente.charge' style="width:100%;"/></td>
												<td><input type="number" name="montant" :value="charge_recurrente.montant" style="width:100%;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/></td>
                                                <td type="charge_mensuelle">
                                                    <span  v-if="charge_recurrente.charge_mensuelle == 1" class="badge badge-success">@traduction('interface.valeurs_select.actif')</span>
                                                    <span  v-else class="badge" >@traduction('interface.valeurs_select.inactif')</span>
                                                </td>
                                                <td v-for="(mois_selectionne, mois) in charge_recurrente.mois_selectionnes "   type="mois_selectionnes" :id_mois='mois' >
                                                    <span v-if="mois_selectionne == 1" class="badge badge-success">@traduction('interface.valeurs_select.actif')</span>
                                                    <span v-else class="badge" >@traduction('interface.valeurs_select.inactif')</span>
                                                </td>
											</tr>
                                            <tr id_charge_recurrente="0" class="css_table_contenu js_table_contenu" id="nouvelle_ligne" style="display: none">
                                                <td style="display: flex"><i class="fas fa-trash suppression_outil" style="padding-top:7px;padding-right:5px;"></i><input type="text" name="charge" :placeholder="traduction('interface.tresorerie.charge_recurrente_saisie.placeholder.nouvelle_charge')" style="width:100%;"/></td>
                                                <td><input type="number" name="montant" value="" style="width:100%;" @wheel.prevent @keydown.up.prevent @keydown.down.prevent/></td>
                                                <td type="charge_mensuelle"><span class="badge badge-success">@traduction('interface.valeurs_select.actif')</span></td>
                                                    @for ($i = 0; $i < 12; $i++)
                                                        <td type="mois_selectionnes" id_mois={{$i}}><span class="badge badge-success">@traduction('interface.valeurs_select.actif')</span></td>

                                                    @endfor
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

charges_recurrentes : {!! json_encode($charges_recurrentes) !!},
type : 'charge_recurrente',
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

		// la chage
		options.charge = $(ligne).find('input[name=charge]').val();

        // le montant
		options.montant = $(ligne).find('input[name=montant]').val();

		// est ce une charge mensuelle ?
		options.charge_mensuelle = $(ligne).find('td[type=charge_mensuelle] .badge-success').length;

		// le détail par mois
		var mois_selectionnes = {
			1: $(ligne).find('td[type=mois_selectionnes]').eq(0).find('.badge-success').length,
			2: $(ligne).find('td[type=mois_selectionnes]').eq(1).find('.badge-success').length,
			3: $(ligne).find('td[type=mois_selectionnes]').eq(2).find('.badge-success').length,
			4: $(ligne).find('td[type=mois_selectionnes]').eq(3).find('.badge-success').length,
			5: $(ligne).find('td[type=mois_selectionnes]').eq(4).find('.badge-success').length,
			6: $(ligne).find('td[type=mois_selectionnes]').eq(5).find('.badge-success').length,
			7: $(ligne).find('td[type=mois_selectionnes]').eq(6).find('.badge-success').length,
			8: $(ligne).find('td[type=mois_selectionnes]').eq(7).find('.badge-success').length,
			9: $(ligne).find('td[type=mois_selectionnes]').eq(8).find('.badge-success').length,
			10: $(ligne).find('td[type=mois_selectionnes]').eq(9).find('.badge-success').length,
			11: $(ligne).find('td[type=mois_selectionnes]').eq(10).find('.badge-success').length,
			12: $(ligne).find('td[type=mois_selectionnes]').eq(11).find('.badge-success').length,
		}

		options.mois_selectionnes = mois_selectionnes;

		options.entite_id = vue_instance.entite_choisi.id;

		// l'id de la charge
		options.id = $(ligne).attr('id_charge_recurrente');

		// on fait une requete ajax pour enregistrer
		$.post({
			url : "{{ URL::to('/eden/tresorerie/charge_recurrente/enregistrer')}}",
			dataType: "json",
			data: options,
		})
		.done(async function(donnees){

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			$(ligne).attr('id_charge_recurrente', donnees.id_charge_recurrente);

		});


	},

	supprime_cette_ligne(event){

		var event =$(event.target);
		var ligne = event.closest('tr');

		var id_charge_recurrente =ligne.attr('id_charge_recurrente');

		$.post({
			url : "{{ URL::to('/eden/tresorerie/charge_recurrente/supprimer')}}",
			dataType: "json",
			data: {id: id_charge_recurrente},
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

			$('#tableau_recapitulatif_poste tbody tr').each(function() {

				if($(this).attr('id_charge_recurrente') == undefined || $(this).attr('id_charge_recurrente') == 0)
				return;

				ordre.push($(this).attr('id_charge_recurrente'));
			});

			// on lance un appel ajax
			$.post({
				url : "{{ URL::to('/eden/tresorerie/charge_recurrente/maj_ordre')}}",
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
				url : "{{ URL::to('/eden/tresorerie/charge_recurrente/changement_entite')}}",
				dataType: "json",
				data: {

					entite_id : entite_id,
				},
			})
			.done(function(donnees){

				//console.log(donnees);
				vue_instance.charges_recurrentes = donnees.charges_recurrentes;
				vue_instance.entites = donnees.entites;
				vue_instance.entite_choisi = donnees.entite_choisi;

				$('.nouvelle_ligne').remove();

				if(vue_instance.charges_recurrentes.length == 0) {

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


    $('body').on('click', '.js_table_contenu .badge', function() {

			if($(this).hasClass('badge-success')) {

				$(this).removeClass('badge-success').text('Inactif');
			}
			else {

				$(this).addClass('badge-success').text('Actif');
			}

			// c'est le bouton charge_mensuelle
			if($(this).closest('td').attr('type') == 'charge_mensuelle') {

				if($(this).hasClass('badge-success')) {

					$(this).closest('tr').find('.badge').addClass('badge-success').text('actif');
				}
				else {

					$(this).closest('tr').find('.badge').removeClass('badge-success').text('inactif');
				}
			}
			else {

				if(!$(this).hasClass('badge-success')) {

					$(this).closest('tr').find('td[type=charge_mensuelle] .badge').removeClass('badge-success').text('inactif');
				}
			}

			vue_instance.mise_a_jour_ligne($(this).closest('tr'));
		});

    $('#tableau_recapitulatif_poste tbody').sortable({

		update: function() {

			vue_instance.mise_a_jour_ordre();

		}
	});


		$(document).ready(function() {


		$('body').on('change', '#tableau_recapitulatif_poste input', function() {

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
