<?php

/*

<div class="row">
	@foreach(table_libre($type_element)->champs_libres()->orderBy('ordre')->get() as $champ)
		
		@if($champ->type == '-1')
			</div>
			<div class="row">
				<div class="col-sm-12 css_form_ligne_titre">{{ $champ->nom }}</div>
			</div>
			<div class="row">
		@else
			<div class="col-sm-{{ $champ->taille_libelle }}">{{ $champ->nom }}</div>
			<div class="col-sm-{{ $champ->taille_champ }}">{!! champ(champ_libre($type_element, $champ->nom_sql)->modele)->attr('v-model', $type_element.'.'.$champ->nom_sql)->cree() !!}</div>
		@endif
	@endforeach
</div>

*/

?>

<?php

if(table_libre($type_element) === null)
	exception("La table libre '$type_element' n'a pas été trouvée dans la vue du formulaire générique");

if(!isset($uniquement_champs_editables))
	$uniquement_champs_editables = false;

$informations_type_element = service('traduction')->informations_type_element($type_element);

?>

@if(!empty($informations_type_element))
	<template v-if="{{$type_element}}.index_traduction != undefined" >
		<traduction-table :key="{{$type_element}}.index_traduction"  categorie="{{$informations_type_element['categorie']}}" :filtrage_index="{{$type_element}}.index_traduction+'.'"></traduction-table>
	</template>
@endif

@foreach(table_libre($type_element)->champs_libres()->where('afficher_sur_formulaire', 1)->orderBy('ordre')->get() as $champ_libre)

	@if($champ_libre['type'] == 42 && $type_element_formulaire_parent == $champ_libre->type_element_ajax)
		@continue
	@endif

	@if(in_array($champ_libre->nom_sql, array('modifie_par', 'modifie_le', 'cree_par', 'cree_le')))
		@continue
	@endif

	@if(!isset($champs_a_ne_pas_creer) || !is_array($champs_a_ne_pas_creer) || !in_array($champ_libre->nom_sql, $champs_a_ne_pas_creer))

		@if(isset($informations_type_element['champs']) && in_array($champ_libre->nom_sql,$informations_type_element['champs']))
			<template v-if="{{$type_element}}.index_traduction === undefined">
		@endif

		@if($champ_libre->type == -1)
			<div class="row">
				<div class="col-sm-12 css_form_ligne_titre">
					{!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->nom_vue() !!}
				</div>
			</div>

		@elseif($champ_libre->type == -2)
			<div class="row">
				<div class="col-sm-12 css_form_ligne_titre">
					{{ $champ_libre->contenu }}
				</div>
			</div>
		@else
			@if(isset($forcer_valeur) && isset($forcer_valeur[$champ_libre->nom_sql]))
				<div class="row">
					<div class="col-sm-6">{!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->nom_vue() !!}</div>
					<div class="col-sm-6">
						{!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->affiche($forcer_valeur[$champ_libre->nom_sql]) !!}
						<input type="hidden" name="{{$champ_libre->nom_sql}}" v-model="{{$champ_libre->type_element.'.'.$champ_libre->nom_sql}}" value="$forcer_valeur[$champ_libre->nom_sql]">
					</div>
				</div>
			@else
				<div class="row">
					<div class="col-sm-6">{!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->nom_vue() !!}</div>
					@if($champ_libre->lecture_seule == 1 || ($uniquement_champs_editables == true && empty(champ_libre_modele($champ_libre->type_element, $champ_libre['nom_sql'])->modification_post_validation)) )
						<div class="col-sm-6" @if($champ_libre['conditions_v_show_manuelle'] != null) v-show="{!! $champ_libre['conditions_v_show_manuelle'] !!}" @endif @if($champ_libre['conditions_v_if_manuelle'] != null) v-if="{!! $champ_libre['conditions_v_if_manuelle'] !!}" @endif>@if(in_array($champ_libre->type_element, App\Eden\Variables::$documents_gescom)){{ '{'.'{ document.'.$champ_libre->nom_sql.' }'.'}' }}@else{{ '{'.'{ '.$champ_libre->type_element.'.'.$champ_libre->nom_sql.' }'.'}' }}@endif</div>
					@else
						<div class="col-sm-6">{!! management($champ_libre->type_element)->champ($champ_libre->nom_sql)->cree() !!}</div>
					@endif
				</div>

			@endif

			@if(isset($informations_type_element['champs']) && in_array($champ_libre->nom_sql,$informations_type_element['champs']))
				</template>
			@endif

		@endif
	@endif
@endforeach
