@extends('eden::templates/template')

@section('content')
<div class="content-wrapper" >
    <div id="base-content" class="container-fluid">
	
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('interface.logs_element.titre_module.droit_acces')
				</h4>
			</div>
			<div class="card-body">
				@foreach($droits_acces as $utilisateur => $droit)
					<div class="row">
						<div class="col-md-6">{{ $utilisateur }}</div>
						<div class="col-md-1">@if($droit === '?') <span class="badge badge-default">@traduction('interface.logs_element.badge.ne_sais_pas')</span> @endif</div>
						<div class="col-md-1">@if($droit === true) <span class="badge badge-success" style="text-transform: uppercase">@traduction('interface.valeurs_select.oui')</span> @endif</div>
						<div class="col-md-1">@if($droit === false) <span class="badge badge-danger" style="text-transform: uppercase">@traduction('interface.valeurs_select.non')</span> @endif</div>
					</div>
				@endforeach
			</div>
		</div>
	
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('interface.logs_element.titre_module.base_de_donnees') @if(!empty(config('eden.lien_bdd'))) <a href="{{ config('eden.lien_bdd') }}&edit={{ $management->_type_element }}&where%5Bid%5D={{ $management->modele->id }}" target="_blank">@traduction('interface.logs_element.lien_afficher_adminer')</a> @endif
				</h4>
			</div>
			<div class="card-body">
				@foreach($management->modele->toArray() as $attribut => $valeur)
					<div class="row">
						<div class="col-md-6">{{ $attribut }}</div>
						<div class="col-md-6">{{ $valeur }}</div>
					</div>
				@endforeach
			</div>
		</div>
	
		<div class="card mb-3">
			<div class="card-header">
				<h4>
					@traduction('interface.logs_element.titre_module.historique')
				</h4>
			</div>
			<div class="card-body">
				@foreach($historique as $un_evenement)
					<div class="row">
						<div class="col-md-12"><h3>{{ $un_evenement->type_action_affichage }}, {{ formate_date('d/m/Y H:i:s', $un_evenement->date) }}, {{ $un_evenement->id_utilisateur }} </h3></div>
					</div>
					@if(in_array($un_evenement->type_action, array(1, 2)) && !empty($un_evenement->details_lignes()->get()))
						@foreach($un_evenement->details_lignes()->get() as $details)
							@php
								try{
									$champ_managament = management($un_evenement->type_element, $un_evenement->id_element)->champ($details->champ);
                                }
                                catch(Exception $e){
                                    $champ_managament = null;
                                }
							@endphp

							<div class="row" style="background: #f8f8f8">
								<div class="col-md-1"></div>
								<div class="col-md-2">{{ $champ_managament !== null ? $champ_managament->modele->nom : $details->champ }}</div>
								<div class="col-md-4">{!! $champ_managament !== null ? $champ_managament->affiche($details->valeur_avant) : $details->valeur_avant !!} ({{ $details->valeur_avant }})</div>
								<div class="col-md-1">=></div>
								<div class="col-md-4">{!! $champ_managament !== null ? $champ_managament->affiche($details->valeur_apres) : $details->valeur_apres !!} ({{ $details->valeur_apres }})</div>
							</div>
						@endforeach
						<br/>
						<br/>
					@endif
				@endforeach
			</div>
		</div>
		
	</div>
</div>

@endsection