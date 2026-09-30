@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $langue_destinataire) !!},<br/><br/>

    {!! traduction('mails.export_donnees.export_termine', $langue_destinataire) !!} <a href="{{ $lien_export }}" >{!! traduction('mails.export_donnees.ici', $langue_destinataire) !!}</a>.

@endsection