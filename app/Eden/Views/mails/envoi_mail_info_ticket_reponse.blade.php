@extends('eden::mails.template_v2')

@section('explication')

    Bonjour,<br/><br/>

    <p> Un ticket vient de recevoir une réponse, vous pouvez le consulter :

        @php
			$projet = modele('projet', $ticket->projet_id);
		@endphp

		@if(!empty($projet->token_synchro_suivi_recette) && !empty($projet->url_site_suivi_recette))
            <a href="{{$projet->url_site_suivi_recette}}/eden/fiche/suivi_recette_easydev/{{$ticket->ticket_id}}">{{$projet->url_site_suivi_recette}}/eden/fiche/suivi_recette_easydev/{{$ticket->ticket_id}}</a>
        @endif.
    </p>
    @if(!empty($dernier_message))
        <p>Email de l'auteur :  {!! $dernier_message->email_utilisateur !!}</p>
        <p>Message : </p>
        <div class="alert-info" style="padding:5px;border-radius:3px; max-width: 70%;">
            <div>{!! $dernier_message->message !!}</div>
        </div>
        @if(!empty($dernier_message->piece_jointe))
             <div >
                 Ce message comporte une pièce jointe visible depuis le ticket sur l'ERP.
             </div>
        @endif
    @endif
@endsection