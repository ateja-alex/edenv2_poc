@if(view()->exists("eden::rapports.pdf.".$id_rapport))
	@include("eden::rapports.pdf.".$id_rapport,array('lignes' => $lignes, 'titre' => $titre, 'options' => $options))
@else
	<html>
	<head>
		<style>
			@page {
				margin: 0.5cm;
			}
			table {
				width: 100%;
				border: 1px solid black;
			}
			table th {
				border: 1px solid black;
			}
			table td {
				border: 1px solid black;
			}
			.text-center {
				text-align: center;
			}
		</style>
	</head>
	<body>
		<h1 class="text-center">
			{{ $titre }}
			<br>
			<small style="font-weight: 400; font-size:16px;">{!! traduction('pdf.document_gescom.document_genere_le', maquette('langue_par_defaut_code')) !!} : {{ date('d/m/Y H:i:s') }} </small>
		</h1>
		<table cellspacing="0" cellpadding="4">
			<tbody>
				@foreach($lignes as $ligne)
				<tr>
					@if(is_array($ligne['donnees']))
						
						@foreach($ligne['donnees'] as $donnee)
							@if(is_array($donnee))
								<td>{{strip_tags($donnee[0])}}</td>
							@else
								<td>{{strip_tags($donnee)}}</td>
							@endif
						@endforeach
					@elseif(!is_array($ligne['donnees']) && isset($ligne['sous_titre']) && $ligne['sous_titre'] === true)
						<td>{{ $ligne['donnees'] }}</td>
					@endif
				</tr>
				@endforeach
			</tbody>
		</table>
	</body>
	</html>

@endif