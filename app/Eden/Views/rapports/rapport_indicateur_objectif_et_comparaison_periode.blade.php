@extends('eden::rapports.rapport_base')

@section('contenu_rapport')
	
	
	<div class="container-fluid">
	
		<div class="row">
			<div class="col-md-12" style="text-align: center; font-size: 33px;">{{ $valeur_indicateur }}</div>
			
{{--			@if($valeur_objectif_2 !== null)--}}
{{--				@if($objectif_1_atteint === true) --}}
{{--					<div class="col-md-6" style="background: rgb(180, 226, 93); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_1 }}</div>--}}
{{--				@else--}}
{{--					<div class="col-md-6" style="background: rgb(255, 103, 103); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_1 }}</div>--}}
{{--				@endif--}}
{{--				--}}
{{--				@if($objectif_2_atteint === true) --}}
{{--					<div class="col-md-6" style="background: rgb(180, 226, 93); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_2 }}</div>--}}
{{--				@else--}}
{{--					<div class="col-md-6" style="background: rgb(255, 103, 103); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_2 }}</div>--}}
{{--				@endif--}}
{{--			@else--}}
{{--				@if($objectif_1_atteint === true) --}}
{{--					<div class="col-md-12" style="background: rgb(180, 226, 93); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_1 }}</div>--}}
{{--				@else--}}
{{--					<div class="col-md-12" style="background: rgb(255, 103, 103); padding: 10px 0px; font-size: 14px; text-align: center; color: white;">{{ $valeur_objectif_1 }}</div>--}}
{{--				@endif--}}
{{--			@endif--}}
		</div>
	</div>
								
@endsection


	