@extends('eden::mails.template_v2')

@section('titre')
    Voici votre code d'accès à usage unique pour EDEN
@endsection

@section('explication')
    Le code expirera dans 10 minutes
    <h2>{{$code_connexion}}</h2>
@endsection

@section('titre_anglais')
    Here your unique code for connexion to EDEN
@endsection

@section('explication_anglaise')
    This code will expired in 10 minutes
    <h2>{{$code_connexion}}</h2>
@endsection
