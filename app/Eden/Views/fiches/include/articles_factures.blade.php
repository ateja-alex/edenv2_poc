<div class="card mb-3">
	<div class="card-header">
		<h4 class="d-flex justify-content-between">
			<span> @traduction('module_sur_fiche.articles_factures.titre') </span>
			<span class="fa fa-calendar" data-toggle="modal" data-target="#dates_mensuelles_articles_factures"></span>
		</h4>
	</div>
	<div class="card-body">
		<template v-if="articles_factures.length > 0">
			<div class="row">
				<div class="col-md-6">
					<div class="table-responsive">
						<table class="table table-bordered table-hover" width="100%" cellspacing="0">
							<thead>
								<tr>
									<th>@traduction('module_sur_fiche.articles_factures.article')</th>
									<th>@traduction('module_sur_fiche.articles_factures.quantite')</th>
									<th>@traduction('module_sur_fiche.articles_factures.montant_total_ht')</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(article, cle) in articles_factures" v-show="cle <= articles_factures_page * 10 - 1 &&  cle >= (articles_factures_page - 1) * 10">
									<td v-html="article.article_id"></td>
									<td>@{{ article.quantite }}</td>
									<td>@{{ article.montant_ht | montant }} {{ maquette('devise_application_symbole') }}</td>
								</tr>
							</tbody>
						</table>
						<nav class='css_nav_pagination_listes'>
							<ul class="pagination css_pagination_perso">
								<li class="page-item" v-show="articles_factures_page > 1">
									<a class="page-link" @click="articles_factures_page = 1">@traduction('module_sur_fiche.articles_factures.premiere_page')</a>
								</li>
								
								<li :class="{'page-item':true, 'active':(page === articles_factures_page)}" v-for="page in articles_factures_pages" v-show="page >= articles_factures_page-5 && page <= articles_factures_page+5">
									<a class="page-link" @click="articles_factures_page = page">@{{ page }}</a>
								</li>
								<li class="page-item" v-show="articles_factures_page <= articles_factures_pages.length">
									<a class="page-link" @click="articles_factures_page = articles_factures_pages.length">@traduction('module_sur_fiche.articles_factures.derniere_page')</a>
								</li>
							</ul>
						</nav>
					</div>
				</div>
				<div class="col-md-6">
					<div id="articles_factures_graphique"></div>
				</div>
			</div>
		</template>
		<template v-else>
			<div class="row">
				<div class="col-md-12">
					<b>@traduction('module_sur_fiche.articles_factures.vide')</b>
				</div>
			</div>
		</template>
	</div>
</div>

<div class="modal fade" id="dates_mensuelles_articles_factures" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">@traduction('module_sur_fiche.articles_factures.titre_modal')</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body css_form">
				<div class="row">
					<div class="col-sm-2">@traduction('module_sur_fiche.articles_factures.du')</div>
					<div class="col-sm-2">
						<select v-model="date_periode_articles_factures.mois_debut" name="date_mensuelle_mois_debut_articles_factures" id="date_mensuelle_mois_debut_articles_factures">
							<option value="">-</option>
							@for($i=1; $i<=12; $i++)
								<option value="{{ substr('0'.$i, -2) }}">{{ substr('0'.$i, -2) }}</option>
							@endfor
						</select>
					</div>
					<div class="col-sm-2"> 
						<select v-model="date_periode_articles_factures.annee_debut" name="date_mensuelle_annee_debut_articles_factures" id="date_mensuelle_annee_debut_articles_factures">
							<option value="">-</option>
							@for($i=date('Y')-10; $i<=date('Y')+10; $i++)
								<option value="{{ $i }}" >{{ $i }}</option>
							@endfor
						</select>
					</div>
					<div class="col-sm-2">@traduction('module_sur_fiche.articles_factures.au')</div>
					<div class="col-sm-2">
						<select v-model="date_periode_articles_factures.mois_fin" name="date_mensuelle_mois_fin_articles_factures" id="date_mensuelle_mois_fin_articles_factures">
							<option value="">-</option>
							@for($i=1; $i<=12; $i++)
								<option value="{{ substr('0'.$i, -2) }}">{{ substr('0'.$i, -2) }}</option>
							@endfor
						</select>
					</div>
					<div class="col-sm-2">
						<select v-model="date_periode_articles_factures.annee_fin" name="date_mensuelle_annee_fin_articles_factures" id="date_mensuelle_annee_fin_articles_factures" >
							<option value="">-</option>
							@for($i=date('Y')-10; $i<=date('Y')+10; $i++)
								<option value="{{ $i }}">{{ $i }}</option>
							@endfor
						</select>
					</div>
				</div>
				<div class="row">
					<div class="col-md-12">
						<input type="hidden" name="date_mensuelle_variable_articles_factures" id="date_mensuelle_variable_articles_factures" value="" />
						<b>@traduction('module_sur_fiche.articles_factures.variables')</b> <br/>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('annee_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.cette_annee')</span>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('annee_n_moins_un_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.annee_derniere')</span>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('annee_n_plus_un_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.annee_prochaine')</span>

						<br/>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('mois_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.ce_mois_ci')</span>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('mois_moins_un_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.le_mois_dernier')</span>
						<span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('mois_plus_un_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.le_mois_prochain')</span>

						<br/>
						@traduction('module_sur_fiche.articles_factures.divers') : <span class="css_bouton" data-dismiss="modal" @click="formater_date_periode('aujourdui_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.jusqu_a_aujourdhui')</span>
					</div>
				</div>
			</div>	
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">{{traduction('interface.modales.fermer')}}</button>
				<button type="button" class="btn btn-primary" data-dismiss="modal" @click="formater_date_periode('date_periode_articles_factures')">@traduction('module_sur_fiche.articles_factures.appliquer')</button>
			</div>
		</div>
	</div>
</div>

{{-- <script> --}}
@push('donnees_pour_vuejs_data')

	articles_factures_page: 1,
	articles_factures: {!! $articles_factures !!},
	articles_factures_pages: [],
	nombre_pages: 0,
	date_periode_articles_factures : {
		mois_debut : '',
		annee_debut : '',
		mois_fin : '',
		annee_fin : '' 
	},
	annee_periode_articles_factures : {
		annee_debut : "{!! date('Y-01-01') !!}",
		annee_fin : "{!! date('Y-12-31') !!}",
	},
	annee_n_moins_un_periode_articles_factures : {
		date_debut : "{!! date('Y-01-01', strtotime('-1 year')) !!}",
		date_fin : "{!! date('Y-12-31', strtotime('-1 year')) !!}",
	},
	annee_n_plus_un_periode_articles_factures : {
		date_debut : "{!! date('Y-01-01', strtotime('+1 year')) !!}",
		date_fin : "{!! date('Y-12-31', strtotime('+1 year')) !!}",

	},
	mois_periode_articles_factures : {
		date_debut : "{!! date('Y-m-d',strtotime('first day of this month')) !!}",
		date_fin : "{!! date('Y-m-d',strtotime('last day of this month')) !!}",
	},
	mois_moins_un_periode_articles_factures  : {
		date_debut : "{!! date('Y-m-d',strtotime('first day of last month')) !!}",
		date_fin : "{!! date('Y-m-d',strtotime('last day of last month')) !!}",
	},
	mois_plus_un_periode_articles_factures : {
		date_debut : "{!! date('Y-m-d',strtotime('first day of next month')) !!}",
		date_fin : "{!! date('Y-m-d',strtotime('last day of next month')) !!}",
	},
	aujourdui_periode_articles_factures : {
		date_debut : "1000-01-01",
		date_fin : "{!! date('Y-m-d',strtotime('now')) !!}",
	},
	
@endpush

@push('donnees_pour_vuejs_methods')
	
	articles_factures_pagination: function() {
			
		this.articles_factures_nombre_pages = Math.ceil(this.articles_factures.length / 10);
		
		for (let index = 1; index <= this.articles_factures_nombre_pages; index++) {
			
			this.articles_factures_pages.push(index);
		}
	},


	/**
	 *
	 *
	 * On formatte les dates afin d'avoir une date de type Y-m-d
	 *
	 */
	formater_date_periode : function (type) {

		var date = this[type];

		if(type == 'date_periode_articles_factures') {

			var date_debut = date.annee_debut + '-' + date.mois_debut + '-' + '01';
			var date_fin = date.annee_fin + '-' + date.mois_fin + '-' + '01';
		}
		else {

			var date_debut = date.date_debut;
			var date_fin = date.date_fin;
		}

		var periode = {date_debut, date_fin};
		this.appliquer_filtre_date(periode);
	},

	appliquer_filtre_date: function(periode) {

		var context = this;

		$.post({

			url: "{!! route('base_eden.fiche.index_post', [$management_element->_type_element, $management_element->modele->id, 'filtre_date_articles_factures' ], false) !!}",
			dataType: "json",
			method: 'POST',
			data: periode,
			cache: false,
		}).done(async function(donnees) {

			if(donnees.retour !== true) {

				await erreur(donnees.retour);
				return;
			}

			context.articles_factures_pages  = [];
			context.articles_factures = donnees.articles_factures;
			context.articles_factures_pagination();
			Highcharts.chart('articles_factures_graphique', {
				chart: {
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false,
					type: 'pie'
				},
				title: {
					text: '{{ traduction('module_sur_fiche.articles_factures.analyse_par_famille') }}'
				},
				tooltip: {
					pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
							format: '<b>{point.name}</b>: {point.percentage:.1f} %',
							style: {
								color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
							}
						}
					}
				},
				series: [{
					name: '{{ traduction('module_sur_fiche.articles_factures.proportion') }}',
					colorByPoint: true,
					data: donnees.articles_factures_par_famille_graphique,
				}]
			});
		});
	},
	
@endpush

@push('donnees_pour_vuejs_created')
	
	this.articles_factures_pagination();
	
@endpush

@push('scripts')
	<script>
	
		Highcharts.chart('articles_factures_graphique', {
			chart: {
				plotBackgroundColor: null,
				plotBorderWidth: null,
				plotShadow: false,
				type: 'pie'
			},
			title: {
				text: '{{ traduction('module_sur_fiche.articles_factures.analyse_par_famille') }}'
			},
			tooltip: {
				pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
			},
			plotOptions: {
				pie: {
					allowPointSelect: true,
					cursor: 'pointer',
					dataLabels: {
						enabled: true,
						format: '<b>{point.name}</b>: {point.percentage:.1f} %',
						style: {
							color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
						}
					}
				}
			},
			series: [{
				name: '{{ traduction('module_sur_fiche.articles_factures.proportion') }}',
				colorByPoint: true,
				data: [
					@foreach($articles_factures_par_famille as $famille_id => $montant)
						{
							@if(!empty($famille_id))
								name: "{{modele('famille', $famille_id)->nom}}",
							@else
								name: '{{ traduction('module_sur_fiche.articles_factures.non_precise') }}',
							@endif
							y: {{ $montant }},
						},
					@endforeach
				]
			}]
		});
	</script>
@endpush
