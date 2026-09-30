@extends('eden::mails.template_v2')
@if(!empty($contenu_mail))

    @section('explication')
        {!! $contenu_mail !!}
    @endsection
@else

    @section('titre')
        Compte extranet
    @endsection

    @section('explication')
        @if($compte_createur !== null)
            <p>Votre compte a été créé par {{ $compte_createur->nom }} {{ $compte_createur->prenom }}</p>
        @elseif($_SERVER['HTTP_HOST'] == fonctionnalite('url_extranet'))
            <p>Votre compte a bien été créé</p>
        @endif
    @endsection

    @section('bouton')
        <a href="{{ $url }}" style="color: white; text-decoration: none; display: block;">Finaliser votre inscription</a>
    @endsection
@endif
