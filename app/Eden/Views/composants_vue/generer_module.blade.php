@extends('eden::templates.template')

@section('title') Génération des composants @stop

@php

$dossier = app_path('Eden/Views/composants_vue/js');

function scan_dossier ($dossier){
	$sous_fichiers_dossiers = scandir($dossier);
	$liste_fichier = [];

	foreach($sous_fichiers_dossiers as $sous_fichiers_dossier){
		if(in_array($sous_fichiers_dossier, array('.', '..', 'html', 'par_utilisateur', 'include','liste_libre.blade.php')) || strpos($sous_fichiers_dossier,'.old') === true)
			continue;

		if(is_dir($dossier.'/'.$sous_fichiers_dossier)){
			$liste_fichier[][$sous_fichiers_dossier] = scan_dossier($dossier.'/'.$sous_fichiers_dossier);
		}
		else{
			$liste_fichier[] = str_replace('.blade.php', '', $sous_fichiers_dossier);
		}
	}
	return $liste_fichier;
};



$options = scan_dossier($dossier);
sort($options);

@endphp

@section('content')

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
					array('route' => 'parametrage.index', 'nom' => 'Paramétrage'),
					array('nom' => 'Génération des composants')
				)])
			<div class="row">
				<div class="col-md-12">
					<div class="card mb-3">
						<div class="card-header">
							<h4>
								Génération des modules
							</h4>
						</div>
						<div class="card-body">
								@php
									$options_select = "";
									function affiche_group($option_liste, &$options_select, $option_parent = ""){
										foreach ($option_liste as $option) {
											if(is_array($option)){
												affiche_group(array_values($option)[0], $options_select, $option_parent.array_key_first($option). ".");
											}
											else{
												$options_select .= '<option value="'.$option_parent.$option.'">'.str_replace(".", " > ", $option_parent.$option).'</option>';
											}
										}
									}
									affiche_group($options, $options_select);
								@endphp

							<select v-model="module_a_generer">
								{!! $options_select !!}
							</select>
						</div>
						<div class="card-footer" style="text-align: end;">
							<button type="button" class="btn btn-primary" @click="generer_module()">Générer</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('donnees_pour_vuejs_methods')

	generer_module : function(){

		var $this = this;

		loading(true);

		$.get({
			url: '/eden/maintenance/generation_fichier/composants/module/' + $this.module_a_generer,
		}).done(function(){
			loading(false);
			toastr.success('Génération terminée');
		});

	},

@endpush


