@extends('eden::mails.template_v2')

@section('explication')

    <p>{!! traduction('mails.calendrier.bonjour', $utilisateur->langue, [$utilisateur->prenom, $utilisateur->nom]) !!}</p>

    <p>{!! traduction('mails.calendrier.contenu', $utilisateur->langue) !!}</p>
@endsection