<table>
	<tr>
		@if($type_export != 'basique')
			<td>#</td>
		@endif
	@foreach($colonnes as $colonne)
		<td>{{ $colonne->nom }}</td>
	@endforeach
	</tr>
	@foreach($lignes as $ligne)
		<tr>
			@if($type_export != 'basique')
				@if(isset($ligne['element']['id']))
					<td>{{ $ligne['element']['id'] }}</td>
				@elseif(isset($ligne['element']['id_cl']))
					<td>{{ $ligne['element']['id_cl'] }}</td>
				@endif
			@endif
			@foreach($colonnes as $colonne)
				<td>{{ strip_tags($ligne[$colonne->id]) }}</td>
			@endforeach
		</tr>
	@endforeach
</table>