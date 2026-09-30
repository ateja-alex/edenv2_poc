@extends('eden::mails.template_v2')

@section('titre')
    Réinitialisez votre mot de passe
@endsection

@section('explication')
    Cliquez sur le bouton pour réinitialisez le mot de passe.<br>
    Si le bouton ne s'affiche pas correctement, vous pouvez copier coller le lien directement dans votre navigateur :<br/>
    {{ $lien_reinitialisation_mot_de_passe }}
@endsection

@section('bouton')
    <a href="{{ $lien_reinitialisation_mot_de_passe }}" style="background:{{maquette('background_navbar')}};color:#ffffff;font-family:Open Sans, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
        <b style="font-weight:700">Réinitialisez votre mot de passe</b>
    </a>
@endsection

@section('titre_anglais')
    Reset your password
@endsection

@section('explication_anglaise')
    Click on the button to reset the password.<br>
    If the button does not display correctly, you can copy and paste the link directly into your browser :<br/>
    {{ $lien_reinitialisation_mot_de_passe }}
@endsection

@section('bouton_anglais')
    <a href="{{ $lien_reinitialisation_mot_de_passe }}" style="background:{{maquette('background_navbar')}};color:#ffffff;font-family:Open Sans, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
        <b style="font-weight:700">Reset your password</b>
    </a>
@endsection
