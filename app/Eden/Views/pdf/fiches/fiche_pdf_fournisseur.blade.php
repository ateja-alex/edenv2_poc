@php
	$langue = maquette('langue_par_defaut_code');
@endphp
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,600;0,700;0,800;1,300;1,400;1,600;1,700;1,800&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="path/to/font-awesome/css/font-awesome.min.css">
	<style>
		body {
			margin: 10px;
			font-family: 'Open Sans', sans-serif;
		}
		h3 {
			border-bottom: 1px solid #dedede;
			display: inline-block;
			text-transform: uppercase;
			font-weight: 300;
		}
		table {
			border-spacing: 0;
			border-collapse: 0;
		}
		table.css_tableau_donnees_fiche_pdf {
			width: 100%;
			border: 1px solid #dedede;
		}
		table.css_tableau_donnees_fiche_pdf tr td {
			border: 1px solid #dedede;
			padding: 1px;
			line-height: 10px;
		}
		.m-auto {
			margin: auto;
		}
		.css_tableau_info_client {
			/*background: rgba(122, 184, 116, 0.6);*/
			color: #000;
			border: none !important;
		}
		.css_infos_adresse_client {
			/*background: rgba(66, 95, 158, 0.6);*/
			color: #000;
			border: none !important;
		}
		.css_tableau_info_client tr td, .css_infos_adresse_client tr td {
			border: none !important;
		}
		.css_tableau_ca_indicatifs {
			width: 100%;
			border-spacing: 10px;
		}
		.css_tableau_ca_indicatifs tr td {
			background: #f1f1ef;
			font-size: 20px;
			font-weight: 600;
			padding: 6px 10px;
    		text-align: center;
		}
		.css_tableau_ca_indicatifs tr td span {
			font-size: 14px;
			font-weight: 400;
		}
		.css_separateur_indicateur {
			width: 25%;
			margin: 6px auto;
			margin-bottom: 0px;
			background: black;
			height: 1px;
		}
	</style>
</head>
<body>


	<table class="m-auto" width="100%;">
		<tbody>
			<tr>
				<td><img src="{{ asset('storage/'.maquette('logo_application_connexion')) }}" alt=""></td>
				<td>
					<h2 style="text-align: right;">{!! traduction('pdf.fiche_fournisseur.titre', $langue) !!} <br>{{ $fiche->nom }}</h2>
				</td>
			</tr>
		</tbody>
	</table>

	@foreach($blocs_a_afficher as $bloc)
		@include($bloc['vue'],array('donnees' => $bloc['donnees']))
	@endforeach
	</html>
