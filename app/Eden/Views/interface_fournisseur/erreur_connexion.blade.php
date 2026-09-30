@extends('eden::authentification.template')

@section('title')
Interface Fournisseur
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')
<div>

    <h3 class="text-center">Connexion fournisseur</h3> <br>
    
    <div class="alert alert-danger">Votre URL d'accès est incorrecte ou à été révoquée</div>
</div>
@endsection