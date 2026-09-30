<?php

namespace App\Eden\Managements\Services;

use Illuminate\Support\Facades\Log;
use PDF;
use PDFMerger;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use OpenSpout\Writer\CSV\Writer as CsvWriter;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Options;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

use Illuminate\Support\Facades\Storage;

class Export_service {

    public $taille_chunk = ['pdf' => 1000, 'xlsx' => 5000, 'csv' => 5000];

    /**
     *
     * Exporte la liste au format pdf
     *
     */
    public function exporter_pdf($donnees, $nom_fichier) {

        $donnees_pour_pdf = array(

            'lignes' => $donnees['lignes'],
            'colonnes' => $donnees['colonnes']
        );

        // on génère le PDF
        $pdf = PDF::loadView('eden::pdf.export_liste_pdf', $donnees_pour_pdf);
        $pdf->setPaper('a4', 'landscape');

        if(file_exists(storage_path('app/public/exports/' . $nom_fichier . '.pdf'))){

            $merger = $merger = PDFMerger::init();
            $merger->addPDF(storage_path('app/public/exports/' . $nom_fichier . '.pdf'));
            $merger->addString($pdf->output());

            $merger->merge();
            $merger->save(storage_path('app/public/exports/' . $nom_fichier . '.pdf'));
        }
        else
            Storage::put('public/exports/' . $nom_fichier . '.pdf', $pdf->output());
    }

    private function initialiser_fichier_xlsx_csv($donnees, $nom_fichier){

        Storage::makeDirectory('public/exports');

        $writer = strpos($nom_fichier, 'xlsx') !== false ? new XlsxWriter() : new CsvWriter();

        $writer->openToFile(storage_path('app/public/exports/' . $nom_fichier));

        $colonnes = $donnees['colonnes']->pluck('nom')->toArray();

        $writer->addRow(Row::fromValues($colonnes));

        $writer->close();
    }

    public function exporter_xlsx_csv($donnees, $nom_fichier) {

        if(!file_exists(storage_path('app/public/exports/' . $nom_fichier)))
            $this->initialiser_fichier_xlsx_csv($donnees, $nom_fichier);
            
        $donnees_export = array();

        $xlsx = strpos($nom_fichier, 'xlsx') !== false;

        if($xlsx){
            $readerOptions = new Options();
            $readerOptions->SHOULD_FORMAT_DATES = true;
            $reader = new XlsxReader($readerOptions);
        }
        else 
            $reader = new CsvReader();

        $reader->open(storage_path('app/public/exports/' . $nom_fichier));

        $sheet = $reader->getSheetIterator()->current();

        foreach ($sheet->getRowIterator() as $index_row => $row) {
            $donnees_export[] = $row;
        }

        $reader->close();

        $writer = $xlsx ? new XlsxWriter() : new CsvWriter();

        $writer->openToFile(storage_path('app/public/exports/' . $nom_fichier));

        foreach($donnees['lignes'] as $ligne){

            $donnees_ligne = array();

            foreach($donnees['colonnes'] as $colonne){

                $valeur = $ligne[$colonne->id];

                if(is_numeric($valeur))
                    $donnees_ligne[] = (float) $valeur == $valeur ? floatval($valeur) : intval($valeur);
                else
                    $donnees_ligne[] = strip_tags($valeur);
            }
            
            $donnees_export[] = Row::fromValues($donnees_ligne);
        }

        $writer->addRows($donnees_export); 

        $writer->close();
    }
}