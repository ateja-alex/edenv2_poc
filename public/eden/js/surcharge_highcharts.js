/**
 * View the data in a table below the chart
 */
Highcharts.Chart.prototype.viewData = function () {
    if (!this.insertedTable) {
        var div = document.createElement('div');
        div.className = 'highcharts-data-table';
        // Insert after the chart container
        this.renderTo.parentNode.insertBefore(div, this.renderTo.nextSibling);
        div.innerHTML = this.getTable();
        this.insertedTable = true;
        var date_str = new Date().getTime().toString();
        var rand_str = Math.floor(Math.random() * (1000000)).toString();
        this.insertedTableID = 'div_' + date_str + rand_str
        div.id = this.insertedTableID;
    }
    else {
        $('#' + this.insertedTableID).toggle();
    }
};

Highcharts.setOptions({
    lang: {
        'downloadXLS' : vue_instance.traduction('interface.highcharts.telecharger_xls'),
        'downloadPNG' : vue_instance.traduction('interface.highcharts.telecharger_png'),
        'downloadJPEG' : vue_instance.traduction('interface.highcharts.telecharger_jpg'),
        'downloadCSV' : vue_instance.traduction('interface.highcharts.telecharger_csv'),
        'hideData' : vue_instance.traduction('interface.highcharts.cacher_data'),
        'viewData' : vue_instance.traduction('interface.highcharts.afficher_data'),
        'printChart' : vue_instance.traduction('interface.highcharts.imprimer_graphique'),
        'exportData' : {
            'categoryHeader':vue_instance.traduction('interface.highcharts.categorie'),
        }
    }
});

Highcharts.setOptions({
    exporting: {
        buttons: {
            contextButton: {
                menuItems: []
            }
        }
    }
});

Highcharts.setOptions({
    exporting: {
        buttons: {
            contextButton: {
                menuItems: {
                    0:"printChart",
                    1:"separator",
                    2:"downloadPNG",
                    3:"downloadJPEG",
                    4:"separator",
                    5:"downloadCSV",
                    6:"downloadXLS",
                    7:"viewData"
                }
            }
        }
    }
})

