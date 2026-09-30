@extends('eden::templates.template')
@section('title') Calendrier @endsection

@section('content')

<div class="content-wrapper" style="padding: 0px !important;">
	<div id="base-content" class="container-fluid" style="padding: 0px;">
		<affichage-calendrier ref="affichage_calendrier" @if(!empty($mon_calendrier)) :filtres_pour_fiche="{utilisateur : {{moi()->id}} }" @endif :valeurs_par_defaut_tache="valeurs_par_defaut_tache"></affichage-calendrier>
	</div>
</div>


@endsection

@push('styles')
	body:has(#div_calendrier):not(:has(#calendrier_formulaire)) {

		overflow: hidden!important;
	}
@endpush
@push("donnees_pour_vuejs_data")
	valeurs_par_defaut_tache: {},
@endpush
@push("donnees_pour_vuejs_created")

	var vue_contexte = this;
	const queryString = window.location.search;
	const urlParams = new URLSearchParams(queryString);
	const entries = urlParams.entries();

	for(const entry of entries) {

		vue_contexte.valeurs_par_defaut_tache[entry[0]] = entry[1];

	}

@endpush
