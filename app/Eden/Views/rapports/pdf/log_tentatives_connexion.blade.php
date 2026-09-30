<html>
<head>
    <style>
        @page {
            margin: 0.5cm;
        }

        body, td {
            font-family: Arial, Helvetica, sans-serif;
            color: #575757;
        }


        table {
            width: 100%;
            border-spacing: 0px;
            border-collapse: collapse;
        }

        .cadre {

            padding: 10px;
            padding-top: 1px;
            border: 1px solid #575757;
        }
        .evolution {
            font-size: 11px;
        }

        .css_bloc_resultat {

            background: #eee;
            padding: 5px;
            margin-bottom: 5px;

        @if(isset($rapport_libre->parametrage_rapport_libre['style_css_resultats']))
            {!! $rapport_libre->parametrage_rapport_libre['style_css_resultats'] !!}
        @endif
}
    </style>
</head>

<body>
<table>
    <tr>
        <td width='60%'>
            <h1>{{ traduction('rapport.divers.recapitulatif_tentatives_connexions_echouees') }}</h1>
            {{ traduction('rapport.divers.document_genere_le') }} {{ date('d/m/Y à H:i:s') }} <br>
        </td>
        <td width='40%' style='text-align: right;'><img src="{{ str_replace('https://', 'http://', asset('storage/'.config('maquette.logo_application_connexion'))) }}" style="max-width: 200px; max-height: 200px;" /></td>
    </tr>
</table>

<br/><br/>
Total :<br>
@foreach($tableau_classement_ip['total'] as $ligne_tableau)
    <div class="css_bloc_resultat">
        {!! $ligne_tableau !!}
    </div>
@endforeach

@if(!empty($tableau_classement_ip['eden']))
    <br/>
    Eden :<br>
    @foreach($tableau_classement_ip['eden'] as $ligne_tableau)
        <div class="css_bloc_resultat">
            {!! $ligne_tableau !!}
        </div>
    @endforeach
@endif

@if(!empty($tableau_classement_ip['extranet']))
<br/>
    Extranet :<br>
    @foreach($tableau_classement_ip['extranet'] as $ligne_tableau)
        <div class="css_bloc_resultat">
            {!! $ligne_tableau !!}
        </div>
    @endforeach
@endif

</body>
</html>