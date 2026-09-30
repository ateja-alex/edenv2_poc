@php
	$langue = maquette('langue_par_defaut_code');
@endphp
<html>
	<head>
		<style>
			@page {
				margin: 0.5cm;
			}
            body{
                font-size: 14px;
                text-align: center;
            }
            h4{
                font-size: 18px;
             }
            #cheques,#cheques thead td ,#cheques tfoot td {
				border: 1px solid black;
				text-align: left;
			}
            table thead td,.td_montant,#banque {
                text-align: center !important;
            }
            #cheques td {
				border-left: 1px solid #000;
				padding: 4px;
			}
			#cheques{
				border-collapse: collapse;
                min-width: 100%;
			}
            #nom_entite h4,#nom_entite p{
                margin-bottom:0;
            }
            #banque{
                margin: auto;
                table-layout: fixed;
                width: 75%;
                font-size: 12px;
            }
            #nom_entite{
                line-height: 1.5em;
            }

		</style>

	</head>

	<body>
        <h4> {!! traduction('pdf.bordereau.titre', $langue) !!}
			<br>
			<small style="text-align:right !important;font-size:12px;font-weight:normal;">{!! traduction('pdf.bordereau.edition', $langue) !!} : {{ date('d/m/Y') }} </small>
		</h4>
		<span id="nom_entite">
            <span style="font-weight: bold;font-size: 18px">{{$entite->nom}}</span><br>
            {{$entite->adresse}}<br>
            {{$entite->adresse_complement}}<br>
            {{$entite->code_postal}} {{$entite->ville}}
        </span>
        <p style="font-weight:bold;text-decoration: underline">{!! traduction('pdf.bordereau.references_bancaires', $langue) !!}</p>
        <table id="banque">
            <thead>
            <tr>
                <td>{!! traduction('pdf.bordereau.etabl', $langue) !!}</td>
                <td>{!! traduction('pdf.bordereau.iban', $langue) !!}</td>
                <td>{!! traduction('pdf.bordereau.bic', $langue) !!}</td>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td width="40%">{{$banque->nom}}</td>
                <td width="40%">{{$banque->iban}}</td>
                <td width="20%">{{$banque->bic}}</td>
            </tr>
            </tbody>
        </table>
        <h4>{!! traduction('pdf.bordereau.bordereau_du', $langue, array($bordereau->numero)) !!} {{$bordereau->date}}</h4>
		<table id="cheques">
			<thead>
				<tr>
					<td width="10%" >{!! traduction('pdf.bordereau.tiers', $langue) !!}</td>
					<td width="25%">{!! traduction('pdf.bordereau.nom_tiers', $langue) !!}</td>
					<td width="15%">{!! traduction('pdf.bordereau.montant', $langue) !!}</td>
					<td width="5%">{!! traduction('pdf.bordereau.monnaie', $langue) !!}.</td>
					<td width="25%">{!! traduction('pdf.bordereau.reference', $langue) !!}</td>
					<td width="20%">{!! traduction('pdf.bordereau.documents', $langue) !!}</td>
				</tr>
			</thead>
			<tbody>
				@foreach($paiements as $paiement)
					<tr>
						<td>{{$paiement->compte_auxiliaire}}</td>
						<td>{{$paiement->nom}}</td>
						<td class="td_montant">{{ management('paiement')->champ('montant')->affiche($paiement->montant)}}</td>
						<td>{!! maquette('devise_application_iso') !!}</td>
						<td>{{$paiement->titre}}</td>
						<td>{{$paiement->document}}</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td></td>
					<td>{!! traduction('pdf.bordereau.total', $langue, array($bordereau->nombre_de_cheques)) !!}</td>
					<td class="td_montant">{{management('bordereau')->champ('montant')->affiche($bordereau->montant)}}</td>
					<td>{!! maquette('devise_application_iso') !!}</td>
					<td></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
		<script type="text/php">
			if (isset($pdf)) {
				$text = "Page {PAGE_NUM} / {PAGE_COUNT}";
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