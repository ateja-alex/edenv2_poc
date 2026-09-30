@extends('eden::templates.template')

@section('title') {{ traduction('interface.parametrage.logs.titre') }} @stop

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => traduction('interface.parametrage.ariane')),
				array('nom' => traduction('interface.parametrage.logs.ariane'))
			)])

			@if(!empty($erreur_parametrage))
			<div id="onglets_logs mb-3">
				<ul class="nav nav-tabs liste_onglets" id="onglets_principaux" style="margin-bottom:10px; margin-left: 1px; border-bottom: 0px solid #dedede;">
					<li>
						<a data-toggle="tab" href="#onglet_logs_laravel" class="css_background_couleur_primaire_active show active">
							Logs Laravel
						</a>
					</li>
					<li>
						<a data-toggle="tab" href="#onglet_logs_bdd" class="css_background_couleur_primaire_active">
							Logs BDD
						</a>
					</li>
				</ul>
			</div>
			@endif

			<div class="tab-content">

				<div id="onglet_logs_laravel" class="tab-pane fade show active">

					<div class="row">
						<div class="col-md-12">
							<div class="card mb-3">
								
								<div class="card-header">

									<div class="css_flex_header_liste">
										<h4>
											@traduction('interface.parametrage.logs.titre')
										</h4>
										<div class="ml-auto"></div>

										<span
											:class="'badge '+ (!types_desactives.includes(type) ? 'badge-success': '')"
											v-for="type in types"
											@click="changer_type(type)"
											v-text="type"></span>

										<input v-model="recherche_logs" placeholder="{{traduction('interface.parametrage.logs.recherche')}}" class="css_input_recherche_liste js_input_recherche_liste" style="float:right;">
									</div>
								</div>
								<div class="card-body">
									<div class="table-responsive">

										<span class="badge badge-secondary"
												v-for="(log, index) in liste"
												style="margin-right:5px;"
												:class="{'badge-success' : log_actuel == log.basename}"
												@click="charger_log(log.basename)">@{{ log.nom }}</span>

										<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
											<thead>
												<tr>
													<th scope="col">{{traduction('interface.parametrage.logs.date')}}</th>
													<th scope="col">{{traduction('interface.parametrage.logs.type')}}</th>
													<th scope="col">{{traduction('interface.parametrage.logs.erreur')}}</th>
													<th scope="col">{{traduction('interface.parametrage.logs.action')}}</th>
												</tr>
											</thead>
											<tbody>

												<tr v-for="(log, index_ligne) in lignes_log"
													@dblclick="charger_ligne(log.numero);"
													v-show="log.erreur.toLowerCase().indexOf(recherche_logs.toLowerCase()) != -1 && !types_desactives.includes(log.type)">

													<td>@{{log.date}}</td>
													<td>
														<span class="badge" :class="'badge-'+log.type_couleur">@{{log.type}}</span>
													</td>
													<td>@{{log.erreur}}</td>
													<td>
														<button class="btn btn-secondary" style="cursor:pointer" @click="charger_ligne(log.numero);">
															<span class="fa fa-eye"></span>
														</button>
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
				@if(!empty($erreur_parametrage))
				<div id="onglet_logs_bdd" class="tab-pane fade">
						<div class="alert alert-danger">
							{!! implode('<br/>', $erreur_parametrage) !!}
						</div>
				</div>
				@endif
			</div>
		</div>
	</div>

	<!-- Modal envoi par email -->
	<div class="modal fade" id="modal_affichage_details_logs" role="dialog" aria-hidden="true">
		<div class="modal-dialog " style="max-width: 90%;height: 90%" role="document">
			<div class="modal-content" style="height:100%">
				<div class="modal-header ">
						<div class="modal-title">
							<h5>
								<div style="float: right;position: fixed;right: 15px;" >
									<span type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true"><i style ="width: 30px;height: 30px;line-height: 30px;" class="fas fa-times"></i></span>
									</span>
								</div>
								{{traduction('interface.parametrage.logs.titre_modal')}}
							</h5>
						</div>
				</div>
				<div class="modal-body" style="max-height: calc(100vh - 175px);overflow-y: auto;">

					<table class="table table-bordered table-hover css_form" width="100%" cellspacing="0">
						<thead>
							<tr>
								<th colspan="2" style="font-size: 15px;font-weight:bold;text-transform:none;">@{{ details_log.erreur }}</th>
							</tr>
						</thead>
						<tbody>

							<tr v-for="(log, index_ligne) in details_log.stacktrace">
								<td><span class="badge" :class="'badge-'+log.type_couleur">@{{log.type}}</span></td>
								<td>@{{log.ligne}}</td>
							</tr>

						</tbody>
					</table>

				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_data')

	liste: {},
	log_actuel:false,
	lignes_log: false,
	details_log: false,
	types: [],
	recherche_logs: '',
	recherche_details_logs: '',
    types_desactives: [],

@endpush

@push('donnees_pour_vuejs_methods')

	charger_liste_logs: function() {
		
		loading(true);
		
		$.post({

			url: '{{ route('parametrage.logs.action', 'liste') }}',
			data: {},
			success: function(retour) {

				vue_instance.liste = retour.liste;

				// Si on n'a aucun fichier chargé, on charge le premier log
				if(vue_instance.lignes_log === false) {
					vue_instance.charger_log(retour.liste[0].basename);
				}

			}
		});
	},

	charger_log: function(log) {
		
		loading(true);

		vue_instance.log_actuel = log;
		
		$.post({

			url: '{{ route('parametrage.logs.action', 'log') }}',
			data: {log:log},
			success: function(retour) {

				vue_instance.lignes_log = retour.lignes_log;
				vue_instance.types = retour.types;
		
				loading(false);

			}
		});
	},

	charger_ligne: function(ligne) {
		
		loading(true);
		
		$.post({

			url: '{{ route('parametrage.logs.action', 'ligne') }}',
			data: {log:vue_instance.log_actuel, ligne:ligne},
			success: function(retour) {

				vue_instance.details_log = retour.details;

				$('#modal_affichage_details_logs').modal('show');
		
				loading(false);

			}
		});
	},

    changer_type: function(type) {

        const index = this.types_desactives.indexOf(type);
    
        if (index === -1)
			this.types_desactives.push(type);
        else
			this.types_desactives.splice(index, 1);
    },

@endpush

@push('scripts')
	<script>

		vue_instance.charger_liste_logs();

	</script>
@endpush('scripts')
