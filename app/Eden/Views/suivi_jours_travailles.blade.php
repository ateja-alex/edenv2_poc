@extends('eden::templates.template')

@section('title')
	{{traduction('composant.suivi_jours_travailles.titre')}}
@endsection

@section('content')
	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">
			<suivi-jours-travailles></suivi-jours-travailles>
		</div>
	</div>

@endsection