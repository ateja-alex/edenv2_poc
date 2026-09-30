<?php temps_execution('debut listes.blade.php'); ?>

@extends('eden::templates.template')

@section('title')
	@if(isset($type_element) && $type_element != null)
		{{traduction('interface.liste')}} {{ table_libre($type_element)->element_pluriel }}
	@else
		{{traduction('interface.liste')}}
	@endif
@endsection

@section('content')

	<?php temps_execution('debut listes.blade.php::content'); ?>

	<div class="content-wrapper" >
		<div id="base-content" class="container-fluid">

			{{-- Fil d'arianne --}}
			@include('eden::includes.fil_ariane', ['fil_ariane' => array(
				array('nom' => table_libre($type_element)->element_pluriel)
			)])

			@include('eden::listes.includes.liste', [

				'type_element' => $type_element,
				'id_liste' => $id_liste,
				'options_liste' => $options_liste,
				'kanban' => $kanban ?? false,
			])
		</div>
	</div>
@endsection

@push('scripts')
	<script>

	$('body').on('dblclick', '.js_liste_ligne_lien_vers_fiche', function() {

		var id = $(this).closest('tr').attr('element_id');

		@if(table_libre($type_element)->fiche == 1)

			document.location = "{{ URL::to('eden/fiche/'.$type_element) }}/"+id;

		@endif

	})

	$('.js_focus_input_recherche, .js_input_recherche_liste').focus();
	</script>
@endpush