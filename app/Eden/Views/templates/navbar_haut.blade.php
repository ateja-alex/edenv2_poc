<!-- 	<a href="javascript:;" class="btn btn-dark d-block d-md-none" onclick="$('#navbarResponsive').toggle('fast');">|||</a> -->
<!-- <a href="javascript:;" class="btn btn-dark d-block d-md-none"@click="modifier_type_menu">|||</a> -->
<div class="menu_app">

	<span class="navbar-brand minimalize-styl-2 d-inline-block" onClick="$('#menu_app').slideToggle()" style="padding: 0px; font-size: 19px; vertical-align: top; line-height: 45px; color: {{ maquette('couleur_police_nom_application') }};" >

		<span class="fa fa-bars"></span> {{ maquette('nom_application') }}

	</span>
</div>

<div class="titre_navbar_haut_gauche">

@if(empty(moi_extranet()))

	@if(maquette('utilisation_logo_haut_gauche'))
		<a class="titre_navbar_haut" href="{{ route('base_eden.accueil.index') }}" style="padding: 0px; font-size: 19px; vertical-align: top; line-height: 10px; color: {{ maquette('couleur_police_nom_application') }};" >
			<img src="{{ asset('storage/'.maquette('logo_application')) }}" style="max-height: 45px; max-width: 227px;" />
		</a>
	@else
		<a class="titre_navbar_haut" href="{{ route('base_eden.accueil.index') }}" style="padding: 0px; font-size: 19px; vertical-align: top; line-height: 45px; color: {{ maquette('couleur_police_nom_application') }};" >
			{{ maquette('nom_application') }}
		</a>
	@endif

	<?php temps_execution('navbar haut logo'); ?>

	<div class="bloc_recherche_globale">
		<form id="recherche-form" class="css_form css_recherche_form_navbar" >
			<span class="fa fa-search" style="color: white; margin-left: 5px;"></span>
			<input type="text" onkeyup="envoyer_requete_recherche_erp()" name="recherche_globale_sur_appli" id="recherche_globale_sur_appli" :placeholder="traduction('interface.placeholder.recherche_globale_sur_erp')" style="color: {{ maquette('couleur_police_nom_application') }}; border-bottom: 0px;" autocomplete="off" />
		</form>
	</div>

@else

	@if(maquette('utilisation_logo_haut_gauche') === 1)
		<a class="titre_navbar_haut" href="{!! url(maquette('page_accueil')) !!}" style="padding: 0px; font-size: 19px; vertical-align: top; line-height: 10px; color: {{ maquette('couleur_police_nom_application') }};" >
			<img src="{{ asset('storage/'.maquette('logo_application')) }}" style="max-height: 45px; max-width: 227px;" />
		</a>
	@else
		<a class="titre_navbar_haut" href="{!! url(maquette('page_accueil')) !!}" style="padding: 0px; font-size: 19px; vertical-align: top; line-height: 45px; color: {{ maquette('couleur_police_nom_application') }};" >
			{{ maquette('nom_application') }}
		</a>
	@endif

@endif

</div>

@if(empty(moi_extranet()))

	{{-- Boutons d'actions --}}
	<div class="css_groupe_bouton_action_navbar_haut">
		@include('eden::templates.boutons_navbar_haut')
	</div>

@endif

@push('scripts')
<script>
	$(document).on('click', function(e) {
		var target = $(e.target);
		if(!target.is($('#bouton_toggle_parametrage_eden')) && !target.is($('#toggle_parametrage_eden').find('*').addBack())) {
			$('#toggle_parametrage_eden').hide('fast');
		}
		if(!target.is($('#bouton_toggle_version_eden')) && !target.is($('#toggle_version_eden').find('*').addBack())) {
			$('#toggle_version_eden').hide('fast');
		}
	});
</script>
@endpush
