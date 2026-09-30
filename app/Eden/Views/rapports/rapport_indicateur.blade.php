@extends('eden::rapports.rapport_base')

@push('classes_specifiques_conteneur_rapport')
css_rapport_indicateur css_rapport_{{$id_rapport}}
@endpush

@section('contenu_rapport')

	<div class="css_icon_donnees_indicateur">
		@if(!empty($icone_dans_rapport))
			<span class="fa {{ $icone_dans_rapport }} " style="font-size: 50px;opacity: 0.5;"></span>
		@endif
		
			@php

				$route = '#';

				if(!empty($rapport->rapport_libre)){
                        $route = route('base_eden.rapport.liste_avec_indicateur', [$rapport->rapport_libre->id_rapport]);
            
                        if(isset($tableau_de_bord))
                            $route.= "?tableau_de_bord=".$tableau_de_bord;
                }
	
			@endphp
			
			<a href="{{ $route }}" target="_blank" style="color: white;display: flex;align-items: center;justify-content: center">
{{--				On gérera les indicateur avec comparaison période -1 plus tard --}}
{{--				@if(isset($valeur_indicateur_moins_1))--}}
{{--					<span style="font-size: 30px;">--}}
{{--						{!! $valeur_indicateur_moins_1 !!}--}}
{{--					</span>--}}
{{--					<span style="font-size: 30px;margin: 0 30px;">|</span>--}}
{{--				@endif--}}
				<span style="font-size: 30px;">
					{!! $valeur_indicateur !!}
				</span>
			</a>
	
		
		<br/>
		@if($objectif_atteint !== false && $objectif !== false) 
			@if($objectif_atteint == 1)
				<span class="badge badge-success">@traduction('rapport.divers.obj') {{ $objectif }}</span>
			@else
				<span class="badge badge-danger">@traduction('rapport.divers.obj') {{ $objectif }}</span>
			@endif
		@endif
    </div>
								
@endsection

@push('spectifique_conteneur_rapport')

	@if(!empty($rapport->rapport_libre->description))
		data-toggle="tooltip" title="{{$rapport->rapport_libre->description}}"
	@endif
@endpush




	