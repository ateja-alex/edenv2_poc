@extends('eden::rapports.rapport_html')

@section('contenu_rapport')

	@foreach($notifications as $notification)
		
		@php
			$avatar = modele('utilisateur', $notification->cree_par)->avatar;
		@endphp

		<div style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #eee; min-height: 60px;">
			<img alt="image" class="rounded-circle" style="max-width: 50px; max-height: 50px; margin-left: 15px; float: left; margin-right: 10px;" src="{{ empty($avatar) ? 'eden/images/no_avatar.jpg' : 'storage/'.$avatar }}" />
			{!! $notification->contenu_html !!}
		</div>
	@endforeach
	<div style="text-align: center;"><a href="{{ route('base_eden.liste.index', ['notification']) }}" >@traduction('rapport.mes_notifications.afficher_historique')</a></div>

@endsection
