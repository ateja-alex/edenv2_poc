chart = Highcharts.chart('graphique_highcharts_{{ $id_rapport }}', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie',
    },
    title: {
        text: "{{ traduction('rapport.divers.repartition') }}"
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                },
                connectorColor: 'silver'
            }
        }
    },
    series: [{
        name: 'CA HT',
        data: [

                @if(is_array($rapport->ca_pour_sous_familles))
                @foreach($rapport->ca_pour_sous_familles as $nom => $ca)

            { name: "{{$nom}}", y: {{$ca}} },
            @endforeach
            @endif

        ]
    }]
});