@extends('eden::fiches.include.annuaire_facturation')

@section('liste_annuaire_facturation')
    @include('eden::fiches.include.liste_libre_sur_fiche', ['module' => 'fiche_client_annuaire_facturation'])
@endsection
