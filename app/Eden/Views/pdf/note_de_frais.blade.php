@php
	$langue = maquette('langue_par_defaut_code');
@endphp

<html>
<head>
	<style type="text/css">

		* {
			font-size:12px;
		}

		.page_break {
			page-break-after: always;
		}

		#tableau_donnees{
			width: 100%;
			font-size: 16px;
			border:1px solid black;
			border-collapse:collapse
		}

		#tableau_donnees th {
			padding: 10px;
			border:1px solid black;
		}

		#tableau_donnees td {
			padding: 3px;
			border: 1px solid black;

		}

		#totaux {

			width: 100%;
			border-collapse: collapse;

		}

		#totaux td {

			border: 1px solid black;
			padding: 5px;
			vertical-align: top;
		}

		#totaux td.total_titre, #totaux td.total_valeur {

			text-align: right;
		}

		#totaux td.mise_en_valeur {

			font-size: 18px;
			font-weight: bold;
		}
	</style>
</head>
<body>

<table width="100%">
	<tr>
		<th style="font-size:18px;text-transform: uppercase;">
			{!! traduction('pdf.note_de_frais.titre', $langue) !!}
		</th>
	</tr>
	@if(isset($filtres['date']) && $filtres['date']['valeur']!= 'Tous')
		<tr>
			<td style="text-align:center;font-size:12px;font-style: italic;">
				{{$filtres['date']['valeur']}}
			</td>
		</tr>
	@endif
	@if(isset($filtres['cree_par']) && $filtres['cree_par']['valeur']!= 'Tous')
		<tr>
			<td style="text-align:center;font-size:12px;font-style: italic;">
				{!! traduction('pdf.note_de_frais.de', $langue) !!} {{$filtres['cree_par']['valeur']}}
			</td>
		</tr>
	@endif
	<tr>
		<td><br></td>
	</tr>
</table>
<table style="width:100%" >
	<tr>
		<td valign="top" style="text-align: center;width: 100%;">
			<table id="tableau_donnees">
				<thead>
					<tr>
						@foreach($colonnes as $colonne)
							<th>{{$colonne['nom']}}</th>
						@endforeach
						<th>{!! traduction('pdf.note_de_frais.annexe', $langue) !!}</th>
					</tr>
				</thead>
				<tbody>
					@foreach($ids_note_de_frais as $id)
						<tr>
							@foreach($colonnes as $colonne)
								@if($colonne['methode'])
									<td>{!! management('note_de_frais',$id)->{$colonne['methode']}(modele('note_de_frais',$id)) !!}
								@else
									<td>{!! management('note_de_frais',$id)->champ($colonne['nom_sql'])->affiche() !!}</td>
								@endif
							@endforeach
							<td>{{$annexes[$id]['page']}}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</td>
	</tr>
</table>

<br>

<table  style="vertical-align: top;width: 100%;">

		<tr>
			<td width="50%" valign="top"></td>
			<td width="50%" valign="top">
				<table id="totaux">
					<tr>
						<td class="total_titre">{!! traduction('pdf.note_de_frais.total_tva', $langue) !!} </td>
						<td class="total_valeur">{{management('note_de_frais',$id)->champ('montant_ht')->affiche($totaux['tva'])}} {!! maquette('devise_application_nom') !!}</td>
					</tr>
					<tr>
						<td class="total_titre">{!! traduction('pdf.note_de_frais.total_ttc', $langue) !!} </td>
						<td class="total_valeur">{{management('note_de_frais',$id)->champ('montant_ttc')->affiche($totaux['ttc'])}} {!! maquette('devise_application_nom') !!}</td>
					</tr>
				</table>
			</td>
		</tr>
</table>

@foreach($annexes as $annexe)

	@if($annexe['image']
		&& is_file(storage_path('app/public/'.$annexe['image']))
		&& in_array($annexe['extension'], array('png', 'bmp', 'jpg', 'gif', 'jpeg')))
		<div class="page_break"></div>
		<img src="{{storage_path('app/public/'.$annexe['image'])}}" style="max-width:99%;max-height:99%;">
	@endif

@endforeach


<script type="text/php">
			if (isset($pdf)) {
				$text = "Page {PAGE_NUM}";
				$size = 10;
				$font = $fontMetrics->getFont("Verdana");
				$width = $fontMetrics->get_text_width($text, $font, $size) / 2;
				$x = ($pdf->get_width() - $width);
				$y = $pdf->get_height() - 35;
				$pdf->page_text($x, $y, $text, $font, $size);
			}
		</script>
</body>
</html>
