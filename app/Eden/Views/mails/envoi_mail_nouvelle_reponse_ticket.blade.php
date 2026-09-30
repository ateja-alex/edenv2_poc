@extends('eden::mails.template_v2')

@section('explication')

    {!! traduction('mails.expression.bonjour', $mail_destinataire['langue']) !!},<br/><br/>

    {!! traduction('mails.nouvelle_reponse_ticket.nouveau_message', $mail_destinataire['langue']) !!}

    @if ($mail_destinataire['easy_dev'] == 1)
        <a href="{{env('EDEN_CONSOLE_API_URL')}}eden/fiche/ticket/{{$id_element}}">{!! traduction('mails.nouvelle_reponse_ticket.lien', $mail_destinataire['langue']) !!}</a><br/>
    @else
        <a href="{{ route('base_eden.fiche.index', ['type_element' => $type_element , 'id' => $id_element, 'methode' => 'afficher' ])}}">{!! traduction('mails.nouvelle_reponse_ticket.lien', $mail_destinataire['langue']) !!}</a><br/>
    @endif

@endsection