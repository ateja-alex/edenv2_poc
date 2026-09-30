@extends('eden::mails.template_v2')

@section('explication')

    Bonjour,<br/><br/>

    <p>Une erreur est survenue lors de l'execution d'une requête SQL par cron</p>
    <p><b>Requête :</b> {{ $requete }}</p>
@endsection