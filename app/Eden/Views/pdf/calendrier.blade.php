@php

switch($format_calendrier){

    case 'jour':
        $operande_width = 0.2;
        break;
    case 'semaine_5j':
        $operande_width = 1;
        break;
    case 'semaine_6j':
        $operande_width = 1.3;
        break;
    case 'semaine_7j':
        $operande_width = 1.5;
        break;
}
@endphp

<html>
<head>

    <style>

        table{
            border-collapse: collapse;
            font-size: 14px;
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

        .bloc_tache_calendrier{

            position:absolute;
            border: solid black 1px;
            z-index: 10;
            overflow:hidden;
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
        footer{
            position:absolute;
            text-align: center;
            bottom:-10px;left:50%;
            transform:translate(-50%,0%);
        }
        footer span{
            font-weight:bold;
            font-size:16px;
        }
        #colonne_heure{
            text-align:center;
            width:5%;
        }
        tbody tr{
            text-align:center;
        }
        .titre_tache {

            font-size: 10px;
            text-wrap:nowrap;
            line-height:10px;
            padding-top:1px
        }
    </style>
</head>
<body>

    @include('eden::pdf.include.calendrier.header')

    <div id="div_calendrier">
        <table id="semaine_5j" class="table table-bordered table-hover" width="100%" cellspacing="0">
            <thead>
                @if(View::hasSection('header_tableau'))
                    @yield('header_tableau')
                @else
                <tr>
                    <th id="colonne_heure"></th>
                    @foreach($dates as $date)
                        <th width="{{ 100 / count($dates) - 5 / 100 }}%">
                            <div>
                                <span>{{ traduction($date['index_traduction'],null,true) }} {{ $date['date'] }}</span>

                                @if(isset($date['indisponibilites']))
                                    @foreach($date['indisponibilites'] as $indisponibilite)
                                        <span class="badge badge-default" style="'width: fit-content;background:{{ $indisponibilite['couleur'] }};color:{{ $indisponibilite['couleur_police'] }}">
                                            {!! $indisponibilite['chaine_affichage'] !!}
                                        </span>
                                    @endforeach
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
                <tr>
                    <th style="width:5%"></th>
                    @foreach($dates as $date)
                        <th width="{{ 100 / count($dates) - 5 / 100 }}%">
                            <div>
                                @if(isset($agenda[$date['format_us']]['taches']['journee_entiere']))
                                    @foreach($agenda[$date['format_us']]['taches']['journee_entiere'] as $cle => $tache)
                                        <div style="border: 1px solid black">
                                            <div class="titre_tache" style="text-wrap:nowrap;line-height:10px;">
                                                <b style="font-size: 10px;">{!! $tache->titre !!}</b><br>
                                                <span style="font-size: 9px;">{!! management('tache', $tache->id, $tache)->champ('commentaire')->affiche() !!}</span><br/>
                                                <span style="font-size: 9px;">{!! management('tache', $tache->id, $tache)->champ('vehicule')->affiche() !!}</span><br/>
                                                <span style="font-size: 9px;">{!! management('tache', $tache->id, $tache)->champ('materiel')->affiche() !!}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </th>
                    @endforeach
                </tr>
                @endif
            </thead>
            <tbody id="tbody_semaine_5j">
                @foreach($heures as $heure)
                    <tr>
                        <td> {{ $heure }} </td>
                        @foreach($dates as $date)
                            <td class="colonne_tableau droppable date_{{ $date['format_us'] }}">
                                <div class="calendrier_taches">
                                    @if(isset($agenda[$date['format_us']]['taches'][$heure]))
                                        @foreach($agenda[$date['format_us']]['taches'][$heure] as $cle => $tache)
                                            @include('eden::pdf.include.calendrier.tache')
                                        @endforeach
                                    @endif
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('eden::pdf.include.calendrier.footer')
</body>
</html>