@extends('eden::mails.template_v2')

@section('titre')
    Notification
@endsection

@section('explication')
    Bonjour,<br/><br/>

    {!! $contenu_html !!}
@endsection

@section('bouton')
    <a href="{{ route('base_eden.accueil.index') }}" style="background:{{config('maquette.background_navbar')}};color:#ffffff;font-family:Open Sans, Helvetica, Arial, sans-serif;font-size:13px;font-weight:normal;line-height:120%;Margin:0;text-decoration:none;text-transform:none;" target="_blank">
        <b style="font-weight:700"><b style="font-weight:700">Rendez-vous sur votre tableau de bord</b></b>
    </a>
@endsection