@extends('eden::templates.template')

@section('title') Configuration fonctionnalités @stop

@push('styles')

@endpush

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
				array('nom' => 'Fonctionnalités')
			)])

			@if(!empty($fonctionnalites_manquantes))
				<div class="alert alert-danger">
					<h4>Fonctionnalités manquantes dans app/Eden/Config/fonctionnalites.php</h4><br><br>
					@foreach($fonctionnalites_manquantes as $categorie => $fonctionnalites)
						<h5>{!! $categorie !!} :</h5>
						@foreach($fonctionnalites as $fonctionnalite)
							- {!! $fonctionnalite !!} <br>
						@endforeach
						<br>
					@endforeach
				</div>
			@endif

			<div class="row">
				<div class="col-md-12">
					{{-- New visuel --}}
					<div class="row">
						<div class="col-md-12">
							<div class="card">
								<div class="card-header d-flex align-items-center">
									<h4>Par module</h4>
									<div style="margin-left: auto!important;">
										<form method="post" class="css_block_btn_recherche_liste js_recherche_fonctionnalite">
											<input type="text" placeholder="Recherche" id="js_recherche_liste" class="css_input_recherche_liste" v-model="recherche_fonctionnalite" style="padding-left: 5px">
											<div class="css_btn_recherche_liste" @click="recherche_fonctionnalite_methode()">
												<i aria-hidden="true" class="fa fa-search"></i>
											</div>
										</form>
									</div>
                                    <a href="{{ URL::to("eden/parametrage/fonctionnalites/export/excel") }}">
                                        <span data-toggle="tooltip" data-placement="left" data-original-title="Exporter" class="css_ajouter_element ml-2">
                                            <i aria-hidden="true" class="css_action_icon fas fa-external-link-alt css_font_16"></i>
                                        </span>
                                    </a>
								</div>
								<div class="card-body">
									<div class="row" v-if="!resultats_recherche">
										@foreach($modules as $module)
											<div class="col-md-3 mb-3">
												<a href="{{ route('parametrage.fonctionnalites.module', $module['nom_module_lien']) }}">
													<div class="css_block_acces_module_parametrage">
														<img src="{{ asset($module['lien_icone']) }}" alt="">
														<span>
															@if(in_array($module['type_module'],['Fonctionnalités générales','Gestion commerciale']))
																{!! str_replace(' ', '<br />', $module['type_module']) !!}
															@else
																{!! $module['type_module'] !!}
															@endif
														</span>
													</div>
												</a>
											</div>
										@endforeach
									</div>
									<div class="row" v-else>
										<div class="col-md-5"></div>
										<div class="col-md-2">
											<span class="btn btn-xs btn-primary" @click="resultats_recherche = false;recherche_fonctionnalite = ''">Revenir aux modules</span>
										</div>
										<div class="col-md-5"></div>

										<div class="col-md-12">
											<br>
											<table class="table table-bordered table-hover css_form css_table_parametrage">
												<tbody>
													<template v-for="(resultat_recherche,nom_categorie) in resultats_recherche">
														<tr>
															<td colspan="2" class="css_form_ligne_titre" v-if="resultat_recherche.resultats.length">
																@{{ resultat_recherche.nom }}
															</td>
														</tr>
														<template v-for="resultat in resultat_recherche.resultats">
															<tr class="ligne_fonctionnalite">
																<td>
																	<div class="d-flex align-items-center">
																		<span><a :href="'{{URL::to('/eden/parametrage/fonctionnalites/')}}'+'/'+nom_categorie+'#ancre_'+resultat.fonctionnalite">@{{ resultat.nom }} <i style="font-style: italic;color: #c7c6c6;font-size: 12px;">@{{resultat.fonctionnalite}}</i></a></span>
																	</div>
																</td>
														</template>
													</template>
												</tbody>
											</table>
											<div class="row" v-if="resultats_recherche.length == 0">
												<div class="col-md-12">
													Aucun résultat !
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
	</div>

@endsection

@section('scripts')
	<script>

		// rien ne se passe si on appuie son entrer dans la recherche
		// à la limite on pourrait recharger la liste
		$('#js_recherche_liste').bind('keypress', function(e) {

			if(e.keyCode == 13) {

				vue_instance.recherche_fonctionnalite_methode();
				e.preventDefault();
			}
		});
	</script>
@endsection

@push('donnees_pour_vuejs_data')

	recherche_fonctionnalite: '',
	resultats_recherche: false,
	aucun_resultat: false,
@endpush

@push('donnees_pour_vuejs_methods')

	recherche_fonctionnalite_methode: function(){

		var vue_instance = this;

		// on va chercher les potentielles occurences
		$.post({

		url: "{{ URL::to('/eden/parametrage/fonctionnalites/recherche') }}",
		dataType: "json",
		method: "post",
		data:{
			recherche_texte: vue_instance.recherche_fonctionnalite,
		},
		}).done(function(retour) {

			vue_instance.resultats_recherche = retour;

		});
	},
@endpush
