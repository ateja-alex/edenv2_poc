@extends('eden::templates.template')

@section('title') {{traduction('module_sur_fiche.fiche.pas_droits.titre')}} @stop

@section('content')

	<div id="vue_app" >
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">
				@php
					// Cette variable sert a dire a la vue du fil d'arianne quel nom afficher dans la dernière partie du fil d'arianne
					$nom_fil_arianne = table_libre($type_element)->element;
				@endphp

				{{-- Fil d'arianne --}}
				@if($type_element == "devis_achat" || $type_element == "devis_vente" || $type_element == "facture_achat" || $type_element == "facture_vente" || $type_element == "bl_achat" || $type_element == "bl_vente" || $type_element == "commande_achat" || $type_element == "commande_vente")
					@include('eden::includes.fil_ariane', ['fil_ariane' => array(
						array('route' => 'base_eden.liste.index', 'arguments' => [table_libre($type_element)->type_element], 'nom' => table_libre($type_element)->element_pluriel),
						array('nom' => traduction('module_sur_fiche.fiche.pas_droits.document'))
					)])
				@else
					@include('eden::includes.fil_ariane', ['fil_ariane' => array(
						array('route' => 'base_eden.liste.index', 'arguments' => [table_libre($type_element)->type_element], 'nom' => table_libre($type_element)->element_pluriel),
						array('nom' => traduction('module_sur_fiche.fiche.pas_droits.fiche').' '.table_libre($type_element)->type_element.' - '.$nom_fil_arianne)
					)])
				@endif

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<div class="card-header d-flex align-items-center">
								<h4>
									@traduction('module_sur_fiche.fiche.pas_droits.titre_2')
								</h4>
							</div>
							<div class="card-body" style="text-align: center">
								<i class="fa fa-user-times" style="font-size: 40px" aria-hidden="true"></i><br><br>
								<p>@traduction('module_sur_fiche.fiche.pas_droits.texte')</p><br>
								<button type="button" class="btn btn-secondary">
									@php
										$href = "/eden/accueil";

                                        if(!empty(moi_extranet()))
                                            $href.="/extranet";
									@endphp
									<a href="{{$href}}" style="color: white">@traduction('module_sur_fiche.fiche.pas_droits.accueil')</a>
								</button>
							</div>
						</div>
					</div>				
				</div>
			</div>
		</div>
	</div>
@endsection