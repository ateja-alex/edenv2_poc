<html>
<head>

    <style>

        .table_planning{
            width:100%;
            margin-top:30px;
        }

        table{
            border-collapse: collapse;
            font-size: 14px;
             width:100%;
        }
        td{
            border: solid grey 1px;
            border-bottom-color: rgba(150,150,150,0.3);
            border-top-color: rgba(150,150,150,0.3);
            z-index: 1;
            position:relative;
        }

        th{
            border: solid grey 1px;
        }

        header{
            margin-top:-20px
        }

        header img{
            margin-top:-5px;
            position:absolute;
            width:60px;
            height:60px
        }
        .titre_calendrier{
            text-align: center;
            line-height: 8px
        }
        .titre_calendrier p{
            font-weight:bold;
            font-size:18px
        }
        tbody tr{
            text-align:center;
        }

        .titre_equipe td span{
            width: 100%;
        }

        .bloc_tache{
            border: solid black 1px;
        }
    </style>
</head>
<body>

    @include('eden::pdf.include.planning.header')

    <div class="table_planning">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th class="colonne_utilisateur"></th>
                    @foreach($dates['planning']['semaine'][0]['dates'] as $date)
                        <th class="text-center css_dates_semaine css_cellule_tache">
                            {{$date}}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($taches_par_equipe as $equipe)
                    <tr class="titre_equipe">
                        <td colspan="{{sizeof($dates['planning']['semaine'][0]['dates']) + 1}}">
                            {!!$equipe['nom_html'] !!}
                        </td>
                    </tr>
                    @include('eden::pdf.include.planning.taches',['taches' => $equipe['taches']])
                @endforeach
            </tbody>
        </table>
    </div>
</body>