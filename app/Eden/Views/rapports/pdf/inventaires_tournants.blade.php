@php
	// Permet d'aller chercher l'id de l'article via l'url de sa fiche
	function recuperer_id_article($donnee){

		// On récupère la position dans la string du début de l'id
		$debut_id = strpos($donnee, "article/")+8;

		// On va chercher la position du premier guillemet de l'url
		$offset_guillemet = strpos($donnee, "\"");

		// Grâce à la position du premier guillemet, on s'en sert en tant qu'offset pour aller chercher la position du 2e guillemet de l'url
		$position_dernier_guillemet = strpos($donnee, "\"", $offset_guillemet+1);

		// On récupère la string en faisant en sorte que l'id soit la dernière chose ( exemple : ......./3434"> TOTO -> ...../3434)
		$donnee_temporaire = substr($donnee,0,$position_dernier_guillemet);

		// On peut aller chercher la bonne sub string car on a la chaine - ce qu'il y a après l'id article + on a sa position de début
		$id_article = substr($donnee_temporaire, $debut_id);

		return $id_article;
	}
@endphp
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
		<small style="font-weight: 400; font-size:16px;">{{ traduction('rapport.divers.document_genere_le') }} {{ date('d/m/Y H:i:s') }} </small>
	</h1>
	<table cellspacing="0" cellpadding="4">
		<tbody>
			@foreach($lignes as $ligne)
			@php $article_id = false; @endphp
			<tr>
				@if(is_array($ligne['donnees']))

					@foreach($ligne['donnees'] as $donnee)
						@if(is_array($donnee))
							<td>{{strip_tags($donnee[0])}}</td>
						@else
							<td>{{strip_tags($donnee)}}</td>
						@endif
						@php
							// On ne passe ici que si c'est un article et que c'est la colonne où on peut récupérer l'id
							if($ligne["classes_css"] != "css_tableau_titre" && \Illuminate\Support\Str::contains($donnee, "fiche/article/")){

								$article_id = recuperer_id_article($donnee);

								$stocks_article = service('stocks')->detail_gestion_des_stocks($article_id,$options["entrepot"]["valeur"]);
							}
						@endphp
					@endforeach
				@elseif(!is_array($ligne['donnees']) && isset($ligne['sous_titre']) && $ligne['sous_titre'] === true)
					<td colspan="3" style="background-color: #d1d1d1;">{{ $ligne['donnees'] }}</td>
				@endif
			</tr>
			@if($article_id != false)

				@foreach($stocks_article['mouvement_stock'] as $stock)
					<tr>
						@if(!isset($stock["conditionnement"]))
							<td style="padding-left: 40px;">{{ traduction('rapport.divers.sans_conditionnement') }}</td>
						@else
							<td style="padding-left: 40px;">{{ $stock["conditionnement"]["nom"] }}</td>
						@endif

						@if(!isset($stock["conditionnement"]))
							<td>{{ $stock['quantite'] }}</td>
						@else
							<td>{{ $stock['quantite_conditionnement'] }}</td>
						@endif
						<td></td>
					</tr>
				@endforeach
			@endif
			@endforeach
		</tbody>
	</table>
</body>
</html>
