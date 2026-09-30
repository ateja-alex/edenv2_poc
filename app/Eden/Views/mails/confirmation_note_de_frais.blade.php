@extends('eden::mails.template_v2')

@section('explication')

    @php

        $reponse = traduction('mails.confirmation_demande.refusee', $employe->langue);
        if($note_de_frais->accepte == 1) 
            $reponse = traduction('mails.confirmation_demande.acceptee', $employe->langue);

    @endphp

    {!! traduction('mails.expressions.bonjour', $employe->langue) !!} {{ $employe->nom }} {{ $employe->prenom }},<br/><br/>

    <p>{!! traduction('mails.confirmation_note_de_frais.note_de_frais', $employe->langue, formate_date('d/m/Y', $note_de_frais->date)) !!} <b>{{$reponse}}</b>.</p>
    <p>{!! traduction('mails.validation_note_de_frais.montant_total', $employe->langue) !!} :  {{ montant($note_de_frais->montant_ttc, 2) }}{{ maquette('devise_application_symbole') }}</p>
    <p>{!! traduction('mails.expressions.cordialement', $employe->langue) !!}</p>

@endsection
