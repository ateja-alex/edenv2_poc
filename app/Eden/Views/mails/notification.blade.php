@extends('eden::mails.template_v2')

@section('explication')

	{!! traduction('mails.expressions.bonjour', $langue_destinataire) !!}<br/><br/>

	@foreach($notifications as $notification)
		{!! $notification['contenu_html'] !!}<br>
		<a href="{{ route('base_eden.fiche.index', ['type_element' => $notification['type_element'] , 'id' => $notification['element_id'], 'methode' => 'afficher' ])}}">{!! traduction('mails.notification.afficher', $langue_destinataire) !!}</a>
		<br/><br/>
	@endforeach

	<br>

@endsection


