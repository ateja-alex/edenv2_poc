@extends('eden::mails.template_v2')

@section('explication')

{!! traduction('mails.expressions.bonjour', $langue) !!},<br/><br/>

{!! traduction('mails.envoi_email_relance.relance', $langue) !!}<br/><br/>

{!! traduction('mails.envoi_email_relance.consulter', $langue) !!} <a href="{{ route('document.afficher', ['id' => $management->modele->id, 'type_element' => $management->_type_element] ) }}">{!! traduction('mails.envoi_email_relance.ici', $langue) !!}</a>

@endsection


