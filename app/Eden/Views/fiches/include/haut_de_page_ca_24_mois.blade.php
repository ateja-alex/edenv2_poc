<div id="graphique_highcharts" class="highcharts-light" style="min-width: 310px; height: 150px; margin: 0 auto;"></div>

@push('scripts')
<script>

Highcharts.chart('graphique_highcharts', {
    chart: {
        type: 'column',
		backgroundColor: 'transparent',
    },
    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        categories: [
			{!! implode(',', $fiche_client_indicateur_haut_de_page_ca_24_mois['legende']) !!}
        ],
        crosshair: true
    },
    exporting: {
        buttons: {
          contextButton: {
            menuItems: ["viewFullscreen", "printChart", "separator", "downloadPNG", "downloadJPEG", "separator", "downloadCSV", "downloadXLS", "viewData"]
          }
        },
    },
    yAxis: {
        min: 0,
        title: {
            text: ''
        }
    },
    tooltip: {
        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
            '<td style="padding:0"><b>{point.y:,.2f} {{ maquette('devise_application_symbole') }} HT</b></td></tr>',
        footerFormat: '</table>',
        shared: true,
        useHTML: true
    },
    plotOptions: {
        column: {
            pointPadding: 0.2,
            borderWidth: 0
        }
    },
    series: [
			{
				showInLegend: false,     
				name: '{{traduction('module_sur_fiche.graphique_ca_24_mois.ca_n_1')}}',
				data: [	
					{!! implode(',', $fiche_client_indicateur_haut_de_page_ca_24_mois['n_moins_1']) !!}
				]
			},
			{
				showInLegend: false,     
				name: '{{traduction('module_sur_fiche.graphique_ca_24_mois.ca_n')}}',
				data: [	
					{!! implode(',', $fiche_client_indicateur_haut_de_page_ca_24_mois['n']) !!}
				]
			},
	]
});

</script>
@endpush

	