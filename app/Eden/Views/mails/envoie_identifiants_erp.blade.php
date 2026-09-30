@extends('eden::mails.template_v2')

@section('titre')
    <h2 style="text-align: center;">Votre compte Eden</h2>
@endsection

@section('explication')
    <small>Ne divulgez jamais vos identifiants, ils sont personnels.</small><br>
    <span style="text-align: justify;">Un compte Eden a été créé avec votre adresse mail !</span><br>
    Pour choisir votre mot de passe, rendez-vous sur le lien suivant :<br>
    <a href="{!! $url !!}">{!! $url !!}</a>
@endsection

@section('titre_anglais')
    <h2 style="text-align: center;">Your Eden account.</h2>
@endsection

@section('explication_anglaise')
    <small>Never give out your login details, they are personal.</small><br>
    <span style="text-align: justify;">An Eden account has been created with your email address !</span><br>
    To choose your password, please visit the following link :<br>
    <a href="{!! $url !!}">{!! $url !!}</a>
@endsection