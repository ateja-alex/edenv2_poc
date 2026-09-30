@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $langue_destinataire) !!},<br/><br/>

    {!! traduction('mails.rapport_par_mail.demande', $langue_destinataire) !!} : {{$titre}}.<br/>
    {!! traduction('mails.rapport_par_mail.piece_jointe', $langue_destinataire) !!}

@endsection