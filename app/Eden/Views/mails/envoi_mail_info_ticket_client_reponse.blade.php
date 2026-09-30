@extends('eden::mails.template_v2')
@section('debut_body')
	<div class="separation_mail"></div>
@endsection
@section('explication')

	{!! $contenu_reponse !!}

	<div style="display: none;opacity: 0;color:white;">
		ticket_client_id_releve_ticket_eden_{{ $management_entete->modele->id }}
	</div>

@endsection