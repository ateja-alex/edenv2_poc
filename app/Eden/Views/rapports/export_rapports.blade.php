<table>
	@foreach($lignes as $ligne)
		<tr>
			@if(is_array($ligne['donnees']))
				@foreach($ligne['donnees'] as $donnee)
					@if(is_array($donnee))
						<td>@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) <strong> @endif{{strip_tags($donnee[0])}}@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) </strong> @endif</td>
					@else
						<td>@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) <strong> @endif{{strip_tags(str_replace('&nbsp;', ' ', $donnee))}}@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) </strong> @endif</td>
					@endif
				@endforeach
			@else
				<td>@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) <strong> @endif{{strip_tags(str_replace('&nbsp;', ' ', $ligne['donnees']))}}@if(isset($ligne['sous_titre']) && $ligne['sous_titre'] == true) </strong> @endif</td>
			@endif
		</tr>
	@endforeach
</table>