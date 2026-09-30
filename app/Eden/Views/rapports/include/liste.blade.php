@php $index_ligne_save = null @endphp
@foreach($lignes as $index_ligne => $info_ligne)

	@php $index_ligne_save = $index_ligne @endphp

	@if($index_ligne == 0)
		<thead>
	@endif
	@if($index_ligne == 1)
		<tbody>
	@endif

	<tr class="{{ $info_ligne['classes_css'] }}" style="{{ $info_ligne['style'] }}">

		@if(isset($info_ligne['sous_titre']) && $info_ligne['sous_titre'] === true)
			@if(is_array($info_ligne['donnees']))
				@foreach($info_ligne['donnees'] as $info)
					<td colspan="1">{!! $info !!}</td>
				@endforeach
			@else
				
				<td colspan="100">{!! $info_ligne['donnees'] !!}</td>
			@endif
		@else
			
			@foreach($info_ligne['donnees'] as $une_donnee)
				
				@if(is_array($une_donnee))
					<td class="{!! $une_donnee[1] !!}" @if(isset($une_donnee[2])) colspan="{{$une_donnee[2]}}" @endif>{!! $une_donnee[0] !!}</td>
				@else
					<td>{!! $une_donnee !!}</td>
				@endif
			@endforeach
		@endif
	</tr>
	
	@if($index_ligne == 0)
		</thead>
	@endif

@endforeach

@if($index_ligne_save >= 1)
	</tbody>
@endif

@push('scripts')
	
	
	
	<script>
		@if($rapport->datatable === true)
			$('#{{$id_rapport}} table').dataTable({
				
				scrollY: 		400,
				searching: 		false, 
				ordering:  		false, 
				paging: 		false, 
				scrollX:        true,
				scrollCollapse: true,
				/*
				fixedColumns:   {
					leftColumns: 1,
				}
				*/
			});
		@endif
		
	</script>
								
@endpush