@extends('eden::authentification.template')

@section('title')
ERP
@endsection

@section('link')
    <link href="{{ asset('eden/css/gestion-paiement.css') }}" rel="stylesheet">    
@endsection

@section('formulaire')

	@if(isset($erreur))
		<div class="alert alert-danger text-center">{{ $erreur }}<br>{{ $message }}</div>
	@endif
	@if(isset($warning))
		<div class="alert alert-warning text-center">{{ $warning }}</div>
	@endif
	@if(isset($ok))
		<div class="alert alert-success text-center">{{ $ok }}</div>
	@endif

@endsection
						