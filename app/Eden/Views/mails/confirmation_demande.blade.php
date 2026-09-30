@extends('eden::mails.template_v2')

@section('explication')

    @php

        $reponse = traduction('mails.confirmation_demande.refusee', $employe->langue);
        if($demande_conge->statut == 1) 
            $reponse = traduction('mails.confirmation_demande.acceptee', $employe->langue);

    @endphp

    {!! traduction('mails.expressions.bonjour', $employe->langue) !!} {{ $employe->nom }} {{ $employe->prenom }},<br/><br/>

    <p>{!! traduction('mails.confirmation_demande.demande', $employe->langue, array(formate_date('d/m/Y', $demande_conge->date_de_debut), formate_date('d/m/Y', $demande_conge->date_de_fin)))!!} <b>{{$reponse}}</b>.</p>
    <p>{!! traduction('mails.expressions.cordialement', $employe->langue) !!}</p>

@endsection
