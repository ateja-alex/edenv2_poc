@extends('eden::fiches.fiche_generique')

@push('donnees_pour_vuejs_data')
	contexte_campagne_de_prospection : {!! !empty($campagne_de_prospection_en_cours) ? 'true': 'false' !!},
@endpush