@extends('eden::listes')

@section('title') {{ traduction('rapports.'.$rapport->id_rapport.'.titre') }} @stop

@section('titre_page') 
	
	{{-- Fil d'arianne --}}
	@include('eden::rapports.include.fil_arianne_rapport')
@stop

@push('donnees_pour_vuejs_data')
	recherche_rapport: '',
@endpush

