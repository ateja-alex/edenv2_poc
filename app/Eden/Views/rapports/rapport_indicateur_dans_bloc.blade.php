<rapport id="{{$id_rapport}}">
	
		<div class="col-md-12" @if($theme == 1) style="background: var(--background_navbar);" @else style="border: 1px solid var(--background_navbar);" @endif>
			<!-- les options du rapport -->
			<div class="pull-right" style="display: flex; float: right">
				<!-- les options -->
				@foreach($options as $option => $informations_pour_option)
					<div class="css_rapport_filtre_et_options">
						@include('eden::rapports.filtres_et_options.'.$option, $informations_pour_option)
					</div>
				@endforeach


			</div>

			<div class="css_actualisation_rapport js_actualisation_rapport">@traduction('rapport.divers.cliquez_ici_pour_actualiser_le_rapport')</div>
			<div class="css_actualisation_rapport_en_cours js_actualisation_rapport_en_cours" style="display:none">
				@traduction('rapport.divers.actualisation_du_rapport_en_cours')<br/>
				<img src="{{ asset('eden/images/ajax_loader.gif') }}" />

			</div>
			<?php
					/*
					<div class="js_rapport"  style="{{ ($activer_scroll) ? 'overflow-x:scroll' : '' }}">
					*/
					// dump($rapport->parametres_filtres);
					?>

					<a href="{{ route('base_eden.rapport.liste_avec_indicateur', [$rapport->rapport_libre->id_rapport]) }}" target="_blank" style="overflow: hidden;text-align: center;padding-bottom: 10px;padding-top: 10px; display: block;"></a></a>
					
						@if($objectif_atteint !== false && $objectif_atteint !== null) 
							
							<h4 @if($theme == 1) style="font-size: 35px;color: white;" @else style="font-size: 35px" @endif>{!! $valeur_indicateur !!}</h4>
							@if($objectif_atteint == 1)
								<i class="fa fa-sun" style="color: #ffcf3b;font-size: 35px;"></i>
							@else
								<i class="fa fa-cloud-showers-heavy" style="color: #bfbfbf;font-size: 35px;"></i>
							@endif
						@else
						
							<h4 @if($theme == 1) style="font-size: 35px;color: white;margin-top: 12px" @else style="font-size: 35px;margin-top: 12px" @endif>{!! $valeur_indicateur !!}</h4>
						@endif
						<br>
						<span @if($theme == 1) style="font-size: 18px; color: white;" @else style="font-size: 18px" @endif>
							{!! traduction($rapport->rapport_libre->index_traduction.'.titre') !!}
							@yield('option_abonnement')
						</span>
					</a>

			
			</div>
		</rapport>




