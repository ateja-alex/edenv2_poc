<html>
<head>
    <style>
        @page {
            margin: 0.5cm;
        }

    </style>
</head>

<body>
<table>
    <tr>
        <td width='60%'>
            <h1>test</h1>
            {{ traduction('rapport.divers.document_genere_le') }} {{ date('d/m/Y à H:i:s') }} <br>
        </td>
        <td width='40%' style='text-align: right;'><img src="{{ str_replace('https://', 'http://', asset('storage/'.config('maquette.logo_application_connexion'))) }}" style="max-width: 200px; max-height: 200px;" /></td>
    </tr>
</table>

<br/>
<br/>
<br/>
<h1>Variable 1 : {{ $variable1 }}</h1>
<h2>Variable 2 : {{ $variable2 }}</h2>

</body>
</html>