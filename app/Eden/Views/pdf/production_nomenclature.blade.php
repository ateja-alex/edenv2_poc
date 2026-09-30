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
    <thead>
    <tr>
        <th>{!! traduction('champs_libres.production_nomenclature.date.nom') !!}</th>
        <th>{!! traduction('champs_libres.production_nomenclature.article.nom') !!}</th>
        <th>{!! traduction('champs_libres.production_nomenclature.entrepot.nom') !!}</th>
        <th>{!! traduction('champs_libres.production_nomenclature.quantite.nom') !!}</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td>{!! $date !!}</td>
        <td>{!! $article !!}</td>
        <td>{!! $entrepot !!}</td>
        <td>{!! $quantite !!}</td>
    </tr>
    </tbody>
</table>
</body>
</html>