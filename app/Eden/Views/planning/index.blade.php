@extends('eden::templates.template')

@section('title') {{ traduction('interface.planning.planning') }} @endsection

@section('content')

	<div class="content-wrapper" >
		<div  id="base-content" class="container-fluid">
			<planning ref="planning"></planning>
		</div>
	</div>

@endsection

@section('styles')
	<link rel="stylesheet" href="{{ asset('eden/css/planning.css') }}?<?=time()?>" />
@endsection
