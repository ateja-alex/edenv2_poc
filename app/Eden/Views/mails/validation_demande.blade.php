@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expressions.bonjour', $demande_conge['destinataire']['langue']) !!},<br/><br/>

    <p>{{ $demande_conge['employe']->prenom }} {{ $demande_conge['employe']->nom }} {!! traduction('mails.validation_demande.demande', $demande_conge['destinataire']['langue'], array(formate_date('d/m/Y', $demande_conge['modele']->date_de_debut).' '.$demande_conge['modele']->texte_periode_de_debut)) !!} {{ formate_date('d/m/Y', $demande_conge['modele']->date_de_fin).' '.$demande_conge['modele']->texte_periode_de_fin }}. <a href="{{ route('base_eden.fiche.index', ['employe_demande_conge', $demande_conge['modele']->id, 'afficher']) }}">{!! traduction('mails.validation_demande.lien_vers_fiche') !!}</a></p>
    <p>{!! traduction('mails.validation_demande.raison', $demande_conge['destinataire']['langue']) !!} : {!! management('employe_demande_conge', $demande_conge['modele']->id)->champ('raison')->affiche($demande_conge['modele']->raison) !!} </p>
    <p>{!! traduction('mails.validation_demande.commentaire', $demande_conge['destinataire']['langue']) !!} : {!! $demande_conge['modele']->commentaire !!} </p>

    {!! traduction('mails.validation_demande.valider', $demande_conge['destinataire']['langue']) !!}<br/>
    <a href="{{ route('employe_demande_conge.validation_conge', ['id_demande' => $demande_conge['modele']->id, 'reponse' => 1]) }}">{!! traduction('mails.validation_demande.oui', $demande_conge['destinataire']['langue']) !!}</a><br/>
    <a href="{{ route('employe_demande_conge.validation_conge', ['id_demande' => $demande_conge['modele']->id, 'reponse' => 2]) }}">{!! traduction('mails.validation_demande.non', $demande_conge['destinataire']['langue']) !!}</a><br/>

@endsection
