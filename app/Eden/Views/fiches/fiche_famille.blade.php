@extends('eden::fiches.fiche_generique')

@push('donnees_pour_vuejs_data')
	sous_familles: {!! $sous_familles !!},
@endpush
