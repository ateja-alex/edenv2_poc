@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $langue) !!},<br/><br/>

<h1>{!! traduction('mails.envoi_email_relecture.relecture', $langue) !!}, <a href="{{ $url }}">{!! traduction('mails.envoi_email_relecture.afficher', $langue) !!}</a></h1>


@endsection