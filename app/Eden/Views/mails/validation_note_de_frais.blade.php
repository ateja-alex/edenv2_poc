@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $note_de_frais['destinataire']['langue']) !!},<br/><br/>

    <p>{{ $note_de_frais['employe']['nom'] }} {{ $note_de_frais['employe']['prenom'] }} {!! traduction('mails.validation_note_de_frais.saisie', $note_de_frais['destinataire']['langue']) !!} {{ formate_date('d/m/Y', $note_de_frais['modele']['date']) }}.</p>
    <p>{!! traduction('mails.validation_note_de_frais.justificatif_note_de_frais', $note_de_frais['destinataire']['langue']) !!} : <a href="{{ $note_de_frais['route_justificatif'] }}">{!! traduction('mails.validation_note_de_frais.justificatif', $note_de_frais['destinataire']['langue']) !!}</a> </p>
    <p>{!! traduction('mails.validation_note_de_frais.montant_total', $note_de_frais['destinataire']['langue']) !!} :  {{ montant($note_de_frais['modele']['montant_ttc'], 2) }}</p>

    {!! traduction('mails.validation_note_de_frais.valider', $note_de_frais['destinataire']['langue']) !!}<br/>
    <a href="{{ $note_de_frais['route_validation'] }}">{!! traduction('mails.validation_note_de_frais.oui', $note_de_frais['destinataire']['langue']) !!}</a><br/>
    <a href="{{ $note_de_frais['route_refus'] }}">{!! traduction('mails.validation_note_de_frais.non', $note_de_frais['destinataire']['langue']) !!}</a><br/>

@endsection
