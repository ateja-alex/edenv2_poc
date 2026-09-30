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
            <th>{!! traduction('champs_libres.transfert_inter_entrepot.date.nom') !!}</th>
            <th>{!! traduction('champs_libres.transfert_inter_entrepot.article_id.nom') !!}</th>
            <th>{!! traduction('champs_libres.transfert_inter_entrepot.entrepot_depart_id.nom') !!}</th>
            <th>{!! traduction('champs_libres.transfert_inter_entrepot.entrepot_arrivee_id.nom') !!}</th>
            <th>{!! traduction('champs_libres.transfert_inter_entrepot.quantite.nom') !!}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($articles as $article)

            <tr>
                <td>{!! $date !!}</td>
                <td>{!! $article->affichage !!}</td>
                <td>{!! $entrepot_depart !!}</td>
                <td>{!! $entrepot_arrive !!}</td>
                <td>{!! $article->quantite !!} {!! !empty($article->conditionnement_affichage) ? '('.$article->conditionnement_affichage.')': '' !!}</td>
            </tr>            
            
        @endforeach
    </tbody>
</table>
</body>
</html>