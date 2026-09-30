
@extends('eden::templates.template')

@section('title')Visualisation trésorerie @stop
@section('styles')

<style>

td{
    font-size:12px;
}

.css__selectionne{
    font-size: 13px;
	margin-left: 40px;
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
						<h4>@traduction('interface.tresorerie.visualistation_tresorerie.titre')</h4>
                        <span >@traduction('interface.tresorerie.visualistation_tresorerie.solde_actuel') : <span v-for="solde in soldes_par_compte" v-show="solde[2] == entite_choisi['id']">@{{ solde[1] }}</span> {!! maquette('devise_application_nom') !!} (<span v-for="solde in soldes_par_compte" v-show="solde[2] == entite_choisi['id']">@{{ solde[0] }} = @{{ solde[1] }}</span>)</span>
                        <span class="css_ajouter_element css__lien dropdown-toggle" id="entite_choisi" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">@{{entite_choisi.nom}}</span>
						<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
							<div class="dropdown-header">@traduction('interface.tresorerie.visualistation_tresorerie.liste_des_entites')</div>
							<a v-for="entite in entites" class="dropdown-item css__lien" @click="changement_entite" :id='entite.id' >@{{entite.nom}} </a>
						</div>
                        <a href="#" class="css_ajouter_element" onClick="$('#filtres').slideToggle(); return false;"><i class="fa fa-fw fa-filter"></i> @traduction('interface.tresorerie.visualistation_tresorerie.filtres')</a>
                       
					</div>
                </div> 
                <div class="card-header" id="filtres" style="display: none;">
                            <h5 >@traduction('interface.tresorerie.visualistation_tresorerie.filtres')<h5>

                            <span class="css__selectionne" v-show="nombre_de_mois_enregistrer==3" ><i class="fas fa-check"></i>@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_trois_mois')</span>
                            <span class="css__lien" @click="changement_nombre_de_mois_enregistrer(3)"  v-show="nombre_de_mois_enregistrer!=3" >@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_trois_mois')</span>

                            <span class="css__selectionne" v-show="nombre_de_mois_enregistrer==6" ><i class="fas fa-check"></i>@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_six_mois')</span>
                            <span class="css__lien"@click="changement_nombre_de_mois_enregistrer(6)"  v-show="nombre_de_mois_enregistrer!=6" >@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_six_mois')</span>

                            <span class="css__selectionne" v-show="nombre_de_mois_enregistrer==12" ><i class="fas fa-check"></i>@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_douze_mois')</span>
                            <span class="css__lien"@click="changement_nombre_de_mois_enregistrer(12)"  v-show="nombre_de_mois_enregistrer!=12" >@traduction('interface.tresorerie.visualistation_tresorerie.filtres.prevision_douze_mois')</span>
				</div>
                <div class="card-body">
                    <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="tableau_recapitulatif_mouvement" width="100%" cellspacing="0" >
                                    <thead>
                                        <tr class="css_table_titre"  >
                                            <td style="text-transform: uppercase">@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.colonne.poste')</td>
                                            <td v-for="i in nombre_de_mois_enregistrer">@{{mois[i-1]}}</td>
                                        </tr>
                                    </thead>
                                        <tbody >


                                            <tr class="css_table_contenu js_table_contenu" style="cursor:pointer" @click="affiche_lignes('charges_recurrentes')">
                                                <td><b>@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.charges_recurrentes')</b></td>
                                                <td style="font-weight:bold;text-align: right;" v-for="i in nombre_de_mois_enregistrer" v-if="tableau_de_donnees.total_charges_recurrentes[dates[i-1]] != 0"> 
                                                    @{{ tableau_de_donnees.total_charges_recurrentes[dates[i-1]] }} 
                                                </td>
                                                <td  v-else ></td>
                                            </tr>
                                            <tr  v-for="charge_recurrente in tableau_de_donnees.charges_recurrentes"  class="css_table_contenu charges_recurrentes" style="display: none;">
							                    <td >@{{charge_recurrente.charge}}</td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer " v-if="charge_recurrente.mois_selectionnes[dates[i-1]] == 1 && charge_recurrente.montant >0" >
                                                    -@{{ charge_recurrente.montant}}
                                                </td>
                                                <td v-else ></td>
                                            </tr>
                                            <tr  v-for="charge_recurrente_decaissee in tableau_de_donnees.charges_recurrentes_decaissees"  class="css_table_contenu charges_recurrentes" style="display: none;">
                                                <td >@{{charge_recurrente_decaissee.charge}}</td>
                                                <td style="text-align: right;color: #c7c7c7;" v-if="charge_recurrente_decaissee.mois_selectionnes[dates[0]] == 1 && charge_recurrente_decaissee.montant >0">
                                                    <s>-@{{ charge_recurrente_decaissee.montant}}</s>
                                                </td>
                                                <td  v-else ></td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer-1 " v-if="charge_recurrente_decaissee.mois_selectionnes[dates[i]] == 1 && charge_recurrente_decaissee.montant >0" >
                                                    -@{{ charge_recurrente_decaissee.montant}}
                                                </td>
                                                <td  v-else ></td>
                                            </tr>   


                                            <tr  class="css_table_contenu js_table_contenu" style="cursor:pointer" @click="affiche_lignes('creances_clients');">
												<td><b>@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.creances_clients')</b></td>
                                                <td style="font-weight:bold;text-align: right;" v-for="i in nombre_de_mois_enregistrer" v-if="tableau_de_donnees.total_creances_clients[dates[i-1]] !=0"> 
                                                    @{{ tableau_de_donnees.total_creances_clients[dates[i-1]] }} 
                                                </td>
                                                <td  v-else ></td>
                                            </tr>
                                            <tr  v-for="creance_client in tableau_de_donnees.creances_clients"  class="css_table_contenu creances_clients" style="display: none;">
							                    <td >@{{creance_client.client}}</td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer  " v-if=" creance_client.montants_mensuels[dates[i-1]] >0" >
                                                    @{{ creance_client.montants_mensuels[dates[i-1]]}}
                                                </td>
                                                <td  v-else ></td>
                                            </tr> 


                                            <tr  class="css_table_contenu js_table_contenu" style="cursor:pointer" @click="affiche_lignes('revenus_recurrents');">
												<td><b>@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.revenus_recurrents')</b></td>
                                                <td style="font-weight:bold;text-align: right;" v-for="i in nombre_de_mois_enregistrer" v-if="tableau_de_donnees.total_revenus_recurrents[dates[i-1]] !=0"> 
                                                    @{{tableau_de_donnees.total_revenus_recurrents[dates[i-1]]}} 
                                                </td>
                                                <td  v-else ></td>
                                            </tr>
                                            <tr  v-for="revenu_recurrent in tableau_de_donnees.revenus_recurrents"  class="css_table_contenu revenus_recurrents" style="display: none;">
							                    <td >@{{revenu_recurrent.source_revenu}}</td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer " v-if=" revenu_recurrent.montants_mensuels[dates[i-1]] >0" >
                                                    @{{ revenu_recurrent.montants_mensuels[dates[i-1]]}}
                                                </td>
                                                <td  v-else ></td>
                                            </tr> 


                                            <tr  class="css_table_contenu js_table_contenu" style="cursor:pointer" @click="affiche_lignes('mouvements_exceptionnels');">
                                                <td><b>@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.mouvements_exceptionnels')</b></td>
                                                <td style="font-weight:bold;text-align: right;" v-for="i in nombre_de_mois_enregistrer"  v-if="tableau_de_donnees.total_mouvements_exceptionnels[dates[i-1]] !=0"> 
                                                    @{{tableau_de_donnees.total_mouvements_exceptionnels[dates[i-1]]}} 
                                                </td>
                                                <td  v-else ></td>
                                            </tr>
                                            <tr  v-for="mouvement_exceptionnel in tableau_de_donnees.mouvements_exceptionnels"  class="css_table_contenu mouvements_exceptionnels" style="display: none;">
							                    <td >@{{mouvement_exceptionnel.nom}}</td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer " v-if=" mouvement_exceptionnel.montants_mensuels[dates[i-1]] >0" >
                                                    @{{ mouvement_exceptionnel.montants_mensuels[dates[i-1]]}}
                                                </td>
                                                <td  v-else ></td>
                                            </tr> 


                                            <tr  class="css_table_contenu js_table_contenu" >
                                                <td>@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.total_mensuel')</td>
                                                <td style="text-align: right;" v-for="i in nombre_de_mois_enregistrer"> 
                                                    @{{tableau_de_donnees.total_mensuel[dates[i-1]]}} 
                                                </td>
                                            </tr>
                                            <tr  class="css_table_contenu js_table_contenu"  >
                                                <td style="font-size:15px;text-transform: uppercase">@traduction('interface.tresorerie.visualistation_tresorerie.tableau_recap.ligne.solde_global')</td>
                                                <td style="text-align: right;font-size:15px;" v-for="i in nombre_de_mois_enregistrer"> 
                                                    @{{tableau_de_donnees.solde_global_pour_tableau[dates[i-1]]}} 
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
        <div class="row">
            <div class="col-md-12">
			    <div class="card mb-3">
                    <div class="card-header">
                        <div class="css_flex_header_liste">
                            <h4>@traduction('interface.tresorerie.visualistation_tresorerie.titre_module.visualtion_graghique')</h4>
                        </div>
                    </div> 
                    <div class="card-body">
                        <div id="graphique_highcharts_visualisation_tresorerie" ></div>
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
	
    nombre_de_mois_enregistrer : {!! $nombre_de_mois_enregistrer !!},
    mois : {!! json_encode($mois) !!},
    tableau_de_donnees : {!! json_encode($tableau_de_donnees) !!},
    dates : {!! $dates !!},
    type : 'visualisation_tresorerie',
    entite_choisi : {!! json_encode($entite_choisi) !!},
    entites : {!! json_encode($entites) !!},
	soldes_par_compte : {!! collect($soldes_par_compte) !!},

@endsection

@section('donnees_pour_vuejs_mounted')

	const vue_instance = this;

	// vue_instance.visualisation_graphique();
	
	var tableau_mois = [];
	var tableau_montant = [];

	for(var i = 0; i < vue_instance.nombre_de_mois_enregistrer; i++){

		tableau_mois.push(vue_instance.mois[i]);
		tableau_montant.push(vue_instance.tableau_de_donnees.solde_global[vue_instance.dates[i]]);

	}

	vue_instance.categories = tableau_mois;
	vue_instance.donnees_graphique = tableau_montant;

	vue_instance.visualisation_graphique();

@endsection


@section('donnees_pour_vuejs_methods')

	changement_nombre_de_mois_enregistrer(nombre_de_mois_enregistrer){

		const vue_instance = this;

		// on fait une requete ajax pour enregistrer
			$.post({
				url : "{{ URL::to('/eden/tresorerie/enregistrer_nombre_de_mois')}}",
				dataType: "json",
				data: {
					
					nombre_de_mois_enregistrer : nombre_de_mois_enregistrer,
				},

			})
			.done(function(donnees){
				
				vue_instance.nombre_de_mois_enregistrer = donnees;

				vue_instance.visualisation_graphique(vue_instance.nombre_de_mois_enregistrer);

				var tableau_mois = [];
				var tableau_montant = [];

				for(var i = 0; i < vue_instance.nombre_de_mois_enregistrer; i++){

					tableau_mois.push(vue_instance.mois[i]);
					tableau_montant.push(vue_instance.tableau_de_donnees.solde_global[vue_instance.dates[i]]);

				}
				vue_instance.categories = tableau_mois;
				vue_instance.donnees_graphique = tableau_montant;

				vue_instance.visualisation_graphique();
			});

			
	},

	/**
	* 
	* Permet d'afficher ou cacher certaines lignes
	* 
	*/

	affiche_lignes(identifiant) {
		
		if($('.'+identifiant+':visible').length == 0) {
			
			$('.'+identifiant).show();
		}
		else {
			
			$('.'+identifiant).hide();
		}
		
	},

	/**
	* 
	* Permet d'afficher le graphique
	* 
	*/

	visualisation_graphique() {

		if (this.chart) {
			this.chart.destroy();
		}
		
		var donnees_graphique = this.donnees_graphique;
		
		this.chart = Highcharts.chart('graphique_highcharts_visualisation_tresorerie', {
			chart: {
				type: 'areaspline'
			},
			title: {
				text: ''
			},
			legend: {
				layout: 'vertical',
				align: 'left',
				verticalAlign: 'top',
				x: 150,
				y: 100,
				floating: true,
				borderWidth: 1,
				backgroundColor:
					Highcharts.defaultOptions.legend.backgroundColor || '#FFFFFF'
			},
			
	        exporting: {
	            buttons: {
	              contextButton: {
	                menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
	              }
	            },
	        },
			xAxis: {
				categories: this.categories,
				crosshair: true
			},
			yAxis: {
				title :{
					text: ''
				}, 
				labels: {
						format: '{value} {!! maquette('devise_application_symbole') !!}',

				},
			},
			tooltip: {
				headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
				pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
					'<td style="padding:0"><b>{point.y:.1f} {!! maquette('devise_application_symbole') !!}</b></td></tr>',
				footerFormat: '</table>',
				shared: true,
				useHTML: true
			},
			plotOptions: {
				column: {
					pointPadding: 0.2,
					borderWidth: 0
				}
			},
			series: [
					{
						showInLegend: false,     
						name: 'Solde global',
						data: donnees_graphique,
					},
			]


		});

	},

	changement_entite(event){

		var vue_instance = this;
		var	event = $(event.target);
		entite_id = event.attr('id');

		// on lance un appel ajax
		$.post({
			url : "{{ URL::to('/eden/tresorerie/visualisation_tresorerie/changement_entite')}}",
			dataType: "json",
			data: {
					
				entite_id : entite_id,
			},
		})
		.done(function(donnees){
				
			vue_instance.tableau_de_donnees = donnees.tableau_de_donnees;
			vue_instance.entites = donnees.entites;
			vue_instance.entite_choisi = donnees.entite_choisi;
			
			var tableau_mois = [];
			var tableau_montant = [];

			for(var i = 0; i < vue_instance.nombre_de_mois_enregistrer; i++){

				tableau_mois.push(vue_instance.mois[i]);
				tableau_montant.push(vue_instance.tableau_de_donnees.solde_global[vue_instance.dates[i]]);

			}

			vue_instance.categories = tableau_mois;
			vue_instance.donnees_graphique = tableau_montant;
			
			// console.log("appel visualisation_graphique");
			vue_instance.visualisation_graphique();
			// console.log("après appel visualisation_graphique");
		});
	},

@endsection




