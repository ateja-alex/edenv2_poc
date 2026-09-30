@extends('eden::rapports.rapport_base')

@section('contenu_rapport')
	
	
	<div class="row">
		<div class="col-md-6">
			<table class="table table-bordered table-hover" width="100%" cellspacing="0">
				@include('eden::rapports.include.liste')
			</table>
		</div>
		<div class="col-md-6">
			<div id="graphique_highcharts_{{ $id_rapport }}" style="min-width: 310px; height: 400px; margin: 0 auto"></div>
		</div>
		
		<!-- le champ pour l'id famille cliqué -->
		<input type="hidden" name="famille_id" id="famille_id_{{$id_rapport}}" class="formulaire_{{$id_rapport}}" value="" />
	</div>
@endsection

@push('scripts')
<script>
	// Build the chart
	var chart ;
	@include('eden::rapports.js.ca_par_famille')
</script>
@endpush

	