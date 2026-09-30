@extends('eden::mails.template_v2')

@section('explication')

    <p>{!! traduction('mails.expressions.bonjour', $utilisateur['langue']) !!} {!! $utilisateur['prenom'] !!},</p>
    <p>{!! traduction('mails.validation_compte_email.pour_activer_compte', $utilisateur['langue']) !!}</p>
    <p>{!! traduction('mails.validation_compte_email.validation_compte', $utilisateur['langue']) !!}</p>
    <div style="text-align: center">
        <a href="{{ $lien_validation_compte_email }}" style="background:{{maquette('background_navbar')}};color:#ffffff;text-decoration:none;padding: 10px 25px;border-radius: 2px;" target="_blank">
            <span style="font-weight:700">{!! traduction('mails.validation_compte_email.texte_bouton', $utilisateur['langue']) !!}</span>
        </a>
    </div>
    <p>{!! traduction('mails.validation_compte_email.bouton_non_fonctionnel', $utilisateur['langue']) !!}</p>
    <p>{!! $lien_validation_compte_email !!}</p>
    <h2 style="font-weight:700">{!! traduction('mails.validation_compte_email.pourquoi_etape_importante', $utilisateur['langue']) !!}</h2>
    <p>{!! traduction('mails.validation_compte_email.explication_importance', $utilisateur['langue']) !!}</p>
    <p>{!! traduction('mails.validation_compte_email.questions_ou_difficultes', $utilisateur['langue']) !!}</p>
    <p>{!! traduction('mails.validation_compte_email.merci', $utilisateur['langue']) !!}</p>
    <p>{!! traduction('mails.validation_compte_email.signature', $utilisateur['langue']) !!}</p>

@endsection




