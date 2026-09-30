@extends('eden::templates.template')

@section('title') Traduction ERP @stop

@section('content')

	<div id="vue_app" >
		<div class="content-wrapper" >
			<div id="base-content" class="container-fluid">

				<div class="row">
					<div class="col-md-12">
						<div class="card mb-3">
							<!-- Listes libres -->
							<div class="card-header">
								<h3>Traduction des {!! $cas !!}</h3>
							</div>
							<div class="card-body">
								@php $id_liste_libre_ancien = 0 @endphp
								<form action="{{ URL::route('eden.traduction_erp_post') }}" method="POST">
									{{ csrf_field() }}
									<input type="hidden" name="nom_fichier" value="{!! $nom_fichier_enregistrement !!}">
									<table class="table table-bordered table-hover" id="liste_champs_libres" width="100%" cellspacing="0">
										<tbody>
											@if($type_logique_pour_vue == 1)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Nom anglais STD</th>
														<th scope="col">Nom anglais SPE</th>
													</tr>
												</thead>

												@foreach($tableau_original as $ligne)
													
													@php 
														$id_liste_libre_actuel = $ligne->liste_libre_id 
													@endphp

													@if(!isset($listes_libres[$id_liste_libre_actuel]))
														@php continue; @endphp
													@endif
													
													{{-- @if($ligne->index_traduction != null) --}}
														@php $index_traduction = $ligne->index_traduction @endphp
													{{-- @elseif($listes_libres[$id_liste_libre_actuel]['id_rapport'] == null)
														@php $index_traduction = $listes_libres[$id_liste_libre_actuel]['type_element'].'_'.$ligne->nom @endphp
													@else
														@php $index_traduction = $listes_libres[$id_liste_libre_actuel]['id_rapport'].'_'.$ligne->nom @endphp
													@endif --}}

													@if($id_liste_libre_actuel != $id_liste_libre_ancien)
														<tr style="background-color: rgba(33, 33, 33, 0.05) !important">
															<th scope="col"><h4>{!! $listes_libres[$id_liste_libre_actuel]['type_element'] !!}@if($listes_libres[$id_liste_libre_actuel]['id_rapport'] != null) <span style="font-size: 18px!important;">- {!! $listes_libres[$id_liste_libre_actuel]['id_rapport'] !!}</span>@endif</h4></th>
															<th></th>
															<th></th>
														</tr>
													@endif
													<tr>
														<td>{{ $ligne->nom }}</td>
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{$ligne->index_traduction}}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif"></td>
															<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{$ligne->index_traduction}}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
														{{-- @if($listes_libres[$id_liste_libre_actuel]['id_rapport'] == null)
															<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $listes_libres[$id_liste_libre_actuel]['type_element'] }}_{{ $ligne->nom }}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif"></td>
															<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $listes_libres[$id_liste_libre_actuel]['type_element'] }}_{{ $ligne->nom }}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
																
														@else
															<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $listes_libres[$id_liste_libre_actuel]['type_element'] }}_{{ $ligne->nom }}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif"></td>
															<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $listes_libres[$id_liste_libre_actuel]['type_element'] }}_{{ $ligne->nom }}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
														@endif --}}
													</tr>
													@php $id_liste_libre_ancien = $id_liste_libre_actuel @endphp
												@endforeach

											@elseif($type_logique_pour_vue == 2)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Nom anglais STD</th>
														<th scope="col">Nom anglais SPE</th>
													</tr>
												</thead>
												@php $liste_libre_ancien = '' @endphp
												@foreach($tableau_original as $ligne)
													
													@php $liste_libre_actuel = $ligne->type_element @endphp
													@php $index_traduction = $ligne['type_element'].'_'.$ligne->nom_sql @endphp
													@if($liste_libre_actuel != $liste_libre_ancien)
														<tr style="background-color: rgba(33, 33, 33, 0.05) !important">
															<th scope="col"><h4>{!! $ligne['type_element'] !!}</h4></th>
															<th></th>
															<th></th>
														</tr>
													@endif
													
														
													<tr style="@if($seulement_non_saisis == 1 && !empty($traductions_standard[$index_traduction])) display: none; @endif">
														@if($nom_fichier_enregistrement == "champs_libres")
															<td>{{ $ligne->nomfr }}</td>
														@else
															<td>{{ $ligne->nom }}</td>
														@endif
														
														@if((isset($donnees_standard[$ligne->type_element]) && isset($donnees_standard[$ligne->type_element][$ligne->nom_sql])) || in_array($ligne->nom_sql, array('modifie_le', 'cree_le', 'modifie_par', 'cree_par')))
															<td>

																@if(moi()->super_admin == 1)
																	<input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_{{ $ligne->nom_sql }}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif">
																@else
																	<span>@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif</span>
																	<input type="hidden" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_{{ $ligne->nom_sql }}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif">

																@endif
															</td>
															@else
															<td>-</td>
														@endif
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_{{ $ligne->nom_sql }}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
													</tr>
													@php $liste_libre_ancien = $liste_libre_actuel @endphp
												@endforeach

											@elseif($type_logique_pour_vue == 3)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Element anglais STD</th>
														<th scope="col">Element anglais SPE</th>
														<th scope="col">Element pluriel anglais STD</th>
														<th scope="col">Element pluriel anglais SPE</th>
														<th scope="col">Nom table anglais STD</th>
														<th scope="col">Nom table anglais SPE</th>
													</tr>
												</thead>
												@php $liste_libre_ancien = '' @endphp
												@foreach($tableau_original as $ligne)
													
													@php $liste_libre_actuel = $ligne->type_element @endphp
													@if($liste_libre_actuel != $liste_libre_ancien)
														<tr style="background-color: rgba(33, 33, 33, 0.05) !important">
															<th scope="col"><h4>{!! $ligne['type_element'] !!}</h4></th>
															<th></th>
															<th></th>
															<th></th>
														</tr>
													@endif
													<tr>
														<td style="width: 40%;">{{ $ligne->elementfr }}</td>
														
														<!-- Element -->
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_element]" value="@if(isset($traductions_standard[$ligne['type_element'].'_element'])){{ $traductions_standard[$ligne['type_element'].'_element'] }}@endif"></td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_element]" value="@if(isset($traductions_specifique[$ligne['type_element'].'_element'])){{ $traductions_specifique[$ligne['type_element'].'_element'] }}@endif"></td>
														
														<!-- Element pluriel -->
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_element_pluriel]" value="@if(isset($traductions_standard[$ligne['type_element'].'_element_pluriel'])){{ $traductions_standard[$ligne['type_element'].'_element_pluriel'] }}@endif"></td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_element_pluriel]" value="@if(isset($traductions_specifique[$ligne['type_element'].'_element_pluriel'])){{ $traductions_specifique[$ligne['type_element'].'_element_pluriel'] }}@endif"></td>
														
														<!-- Nom table -->
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_nom_table]" value="@if(isset($traductions_standard[$ligne['type_element'].'_nom_table'])){{ $traductions_standard[$ligne['type_element'].'_nom_table'] }}@endif"></td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne['type_element'] }}_nom_table]" value="@if(isset($traductions_specifique[$ligne['type_element'].'_nom_table'])){{ $traductions_specifique[$ligne['type_element'].'_nom_table'] }}@endif"></td>
													</tr>
													@php $liste_libre_ancien = $liste_libre_actuel @endphp
												@endforeach

											@elseif($type_logique_pour_vue == 4)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Nom anglais SPE</th>
													</tr>
												</thead>
												@foreach($tableau_original as $ligne)
													
													@php 
														$index_traduction = $ligne['id_valeur'] 
													@endphp
													<tr>
														<td>{{ $ligne->valeur }}</td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{!! $ligne->id_valeur !!}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
													</tr>
												@endforeach

											@elseif($type_logique_pour_vue == 5)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Nom anglais STD</th>
														<th scope="col">Nom anglais SPE</th>
													</tr>
												</thead>
												@foreach($tableau_original as $ligne)
													
													@php $index_traduction = $ligne['id'] @endphp
													<tr style="background-color: rgba(33, 33, 33, 0.05) !important">
														<td>{{ $ligne['nom'] }}</td>
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{!! $ligne['id'] !!}]" value="@if(isset($traductions_standard[$index_traduction])){{ $traductions_standard[$index_traduction] }}@endif"></td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{!! $ligne['id'] !!}]" value="@if(isset($traductions_specifique[$index_traduction])){{ $traductions_specifique[$index_traduction] }}@endif"></td>
															
													</tr>
													@if(isset($ligne['sous_menus']))
														@foreach($ligne['sous_menus'] as $sous_menu)
															<tr>
																<td>{{ $sous_menu['nom'] }}</td>
																<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{!! $sous_menu['id'] !!}]" value="@if(isset($traductions_standard[$sous_menu['id']])){{ $traductions_standard[$sous_menu['id']] }}@endif"></td>
																<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{!! $sous_menu['id'] !!}]" value="@if(isset($traductions_specifique[$sous_menu['id']])){{ $traductions_specifique[$sous_menu['id']] }}@endif"></td>
															</tr>
														@endforeach
													@endif
												@endforeach
											@elseif($type_logique_pour_vue == 6)
												<thead>
													<tr>
														<th scope="col">Nom français</th>
														<th scope="col">Nom anglais STD</th>
														<th scope="col">Nom anglais SPE</th>
													</tr>
												</thead>
												@foreach($tableau_original as $ligne)
													
													@php 
														$id_liste_libre_actuel = $ligne->liste_libre_id 
													@endphp
													
													@if($id_liste_libre_actuel != $id_liste_libre_ancien)
														<tr style="background-color: rgba(33, 33, 33, 0.05) !important">
															<th scope="col"><h4>{!! $listes_libres[$id_liste_libre_actuel]['type_element'] !!} @if($listes_libres[$id_liste_libre_actuel]['id_rapport'] != null) <span style="font-size: 18px!important;">- {!! $listes_libres[$id_liste_libre_actuel]['id_rapport'] !!}</span>@endif</h4></th>
															<th></th>
															<th></th>
														</tr>
													@endif
													<tr>
														<td>{{ $ligne->nom }}</td>
														<td><input type="text" name="traductions_standard[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne->index_traduction }}]" value="@if(isset($traductions_standard[$ligne->index_traduction])){{ $traductions_standard[$ligne->index_traduction] }}@endif"></td>
														<td><input type="text" name="traductions_specifique[en][{!! $nom_fichier_enregistrement !!}][{{ $ligne->index_traduction }}]" value="@if(isset($traductions_specifique[$ligne->index_traduction])){{ $traductions_specifique[$ligne->index_traduction] }}@endif"></td>
													</tr>
													@php $id_liste_libre_ancien = $id_liste_libre_actuel @endphp
												@endforeach
											@else
												{!! $erreur !!}
											@endif
										</tbody>
									</table>
									<div class="row">
    									<div class="col-md-12">
    										<input type="submit" class="btn btn-primary" value="Valider" />
    									</div>
 	  								</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</div>
</div>

@endsection

