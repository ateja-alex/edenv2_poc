<?php

namespace App\Eden\Managements\Elements;

use App\Eden\Exceptions\Eden_exception;

trait Facturation_electronique_document_trait {

    private function donnees_facturx() {

        $tableau_tva_par_taux = $this->calcule_total_document()['par_tva'];
        $articles = $this->articles();

        $categories_tva = $this->categories_tva_facturx($articles);

        foreach($articles as $article) {
            $article->categorie_tva_facturx = $categories_tva[$article->id] ?? ($article->tva == 0 ? 'Z' : 'S');
            $article->exemption_facturx = $this->exemption_facturx($article->categorie_tva_facturx);
        }

        // une tranche de taux peut regrouper des lignes de régimes différents (ex: exonéré et
        // autoliquidation, tous deux à 0%) : on éclate alors la tranche par (taux, catégorie), en
        // répartissant la TVA de la tranche proportionnellement au HT de chaque catégorie - le
        // reliquat d'arrondi est affecté à la dernière catégorie pour que la somme par tranche reste
        // rigoureusement égale au montant affiché sur le PDF (calcule_total_document).
        $tableau_tva = [];

        foreach($tableau_tva_par_taux as $taux => $informations) {

            $articles_du_taux = $articles->filter(fn($article) => $article->tva == $taux);
            $categories_du_taux = $articles_du_taux->pluck('categorie_tva_facturx')->unique()->values();

            $tva_deja_repartie = 0;

            foreach($categories_du_taux as $index => $categorie) {

                $ht_categorie = $articles_du_taux
                    ->filter(fn($article) => $article->categorie_tva_facturx == $categorie)
                    ->sum('total');

                $derniere_categorie = $index == $categories_du_taux->count() - 1;

                $tva_categorie = $derniere_categorie
                    ? round($informations['tva'] - $tva_deja_repartie, 2)
                    : round($informations['tva'] * ($informations['ht'] > 0 ? $ht_categorie / $informations['ht'] : 0), 2);

                $tva_deja_repartie += $tva_categorie;

                $tableau_tva[] = [
                    'taux' => $taux,
                    'categorie' => $categorie,
                    'exemption' => $this->exemption_facturx($categorie),
                    'ht' => $ht_categorie,
                    'tva' => $tva_categorie,
                ];
            }
        }

        return array_merge([
            'document' => $this->modele,
            'client_management' => management('client', $this->modele->client_id),
            'tableau_tva' => $tableau_tva,
            'total_ht' => array_sum(array_column($tableau_tva_par_taux, 'ht')),
            'total_tva' => array_sum(array_column($tableau_tva_par_taux, 'tva')),
            'total_ttc' => array_sum(array_column($tableau_tva_par_taux, 'ttc')),
            'entite_management' => management('entite', $this->modele->entite_id),
            'adresse_facturation' => modele('adresse', $this->modele->adresse_de_facturation),
            'annuaire_facturation' => !empty($this->modele->annuaire_facturation_id) ? modele('annuaire_facturation', $this->modele->annuaire_facturation_id) : null,
            'articles' => $articles,
            'facture_anterieure' => $this->facture_anterieure_facturx(),
        ], $this->parametrage_facturx());
    }

    /**
     *
     * Code de catégorie de TVA (UNTDID 5305) de chaque ligne, déterminé à partir du même paramétrage
     * que la comptabilisation (categorie_comptable_article -> code_tva), pas du seul taux qui ne
     * permet pas de distinguer exonération, autoliquidation et export/intracommunautaire (le taux
     * est forcé à 0 pour ces trois cas par la vue categorie_comptable_article). Uniquement le sens
     * vente (code_tva_id) : ce trait n'est utilisé que par facture_vente/avoir_vente.
     *
     * Reprend la résolution de comptabilise_compte_article() : le paramétrage vient de la ligne
     * (categorie_comptable_article_id) si renseigné, sinon de la catégorie comptable du document/client
     * (compta_situation_geographique()). Tout en requêtes groupées pour éviter le N+1 sur les lignes.
     *
     */
    protected function categories_tva_facturx($articles) {

        $situation_geographique = $this->modele->categorie_comptable_id ?? $this->compta_situation_geographique();

        $ids_directs = $articles->pluck('categorie_comptable_article_id')->filter()->unique()->values();
        $ids_articles_a_resoudre = $articles
            ->filter(fn($article) => empty($article->categorie_comptable_article_id))
            ->pluck('article_id')->filter()->unique()->values();

        $parametrages = modele('categorie_comptable_article')
            ->where(function($requete) use ($ids_directs, $ids_articles_a_resoudre, $situation_geographique) {

                $requete->whereIn('id', $ids_directs);

                if(!empty($situation_geographique) && $ids_articles_a_resoudre->isNotEmpty())
                    $requete->orWhere(function($sous_requete) use ($ids_articles_a_resoudre, $situation_geographique) {
                        $sous_requete->whereIn('article_id', $ids_articles_a_resoudre)->where('categorie_comptable_id', $situation_geographique);
                    });
            })
            ->get();

        $parametrages_par_id = $parametrages->keyBy('id');
        $parametrages_par_article = $parametrages->where('categorie_comptable_id', $situation_geographique)->keyBy('article_id');

        $code_tva_ids = $parametrages->pluck('code_tva_id')->filter(fn($id) => !empty($id) && $id != -1)->unique()->values();
        $codes_tva = modele('code_tva')->whereIn('id', $code_tva_ids)->get()->keyBy('id');

        $categorie_comptable_ids = $parametrages->pluck('categorie_comptable_id')->filter()->unique()->values();
        $categories_comptables = modele('categorie_comptable')->whereIn('id', $categorie_comptable_ids)->get()->keyBy('id');

        $resultat = [];

        foreach($articles as $article) {

            $parametrage = !empty($article->categorie_comptable_article_id)
                ? $parametrages_par_id->get($article->categorie_comptable_article_id)
                : $parametrages_par_article->get($article->article_id);

            $resultat[$article->id] = $this->categorie_tva_facturx($article, $parametrage, $codes_tva, $categories_comptables);
        }

        return $resultat;
    }

    /**
     *
     * Détermine le code de catégorie de TVA d'une ligne à partir de son paramétrage déjà résolu
     * (categorie_comptable_article) et des référentiels code_tva/categorie_comptable déjà chargés
     * en mémoire par categories_tva_facturx() - aucune requête ici.
     *
     */
    protected function categorie_tva_facturx($article, $parametrage, $codes_tva, $categories_comptables) {

        if(empty($parametrage))
            return $article->tva == 0 ? 'Z' : 'S';

        $code_tva_id = $parametrage->code_tva_id;

        // exonération : la zone fiscale de la catégorie comptable distingue France (E),
        // Union européenne (K, livraison intracommunautaire) et export/DOM-TOM (G)
        if($code_tva_id == -1) {

            $zone_fiscale = (int) ($categories_comptables->get($parametrage->categorie_comptable_id)->zone_fiscale ?? 0);

            return match($zone_fiscale) {
                2 => 'K',
                3, 4 => 'G',
                default => 'E',
            };
        }

        $code_tva = $codes_tva->get($code_tva_id);

        if(!empty($code_tva) && in_array($code_tva->sens, [2, 3]))
            return 'AE';

        return $article->tva == 0 ? 'Z' : 'S';
    }

    /**
     *
     * Motif d'exonération (BT-120/BT-121) associé à un code de catégorie de TVA, à ajouter en
     * complément du CategoryCode pour les catégories qui ne sont pas au taux normal.
     *
     */
    protected function exemption_facturx($categorie) {

        return match($categorie) {
            'AE' => ['code' => 'VATEX-EU-AE', 'texte' => 'Autoliquidation'],
            'K' => ['code' => 'VATEX-EU-IC', 'texte' => "Livraison intracommunautaire exonérée (article 262 ter I du Code général des impôts)"],
            'G' => ['code' => 'VATEX-EU-G', 'texte' => "Exportation hors Union européenne exonérée (article 262 I du Code général des impôts)"],
            'E' => ['code' => null, 'texte' => "Exonération de TVA"],
            default => null,
        };
    }

    /**
     *
     * Référence à la facture antérieure (BG-3), obligatoire pour les avoirs (BR-FR-CO-05).
     * Seul avoir_vente porte un facture_id_source : renvoie null pour les autres types de document.
     *
     */
    protected function facture_anterieure_facturx() {

        if($this->_type_element != 'avoir_vente' || empty($this->modele->facture_id_source))
            return null;

        return modele('facture_vente', $this->modele->facture_id_source);
    }

    /**
     *
     * Paramétrage Factur-X issu du modèle de document utilisé pour la génération (mis en cache par
     * creation_document_pdf() sur $this->modele_de_document). Aucune valeur de repli : si le modèle
     * n'a pas été paramétré, les champs restent vides et verification_facturx() bloque l'envoi.
     *
     */
    protected function parametrage_facturx() {

        $parametrage = json_decode($this->modele_de_document?->parametrage_facturation_electronique ?? '', true) ?: [];

        $parametrage = $parametrage[$this->_type_element] ?? [];

        return [
            'type_code_facturx' => $parametrage['type_code'] ?? null,
            'cadre_facturation_facturx' => $parametrage['cadre_facturation'] ?? null,
            'mention_indemnite_forfaitaire_facturx' => $parametrage['mention_indemnite_forfaitaire'] ?? null,
            'mention_penalites_retard_facturx' => $parametrage['mention_penalites_retard'] ?? null,
            'mention_escompte_facturx' => $parametrage['mention_escompte'] ?? null,
        ];
    }

    /**
     *
     * Retourne le tag (pastille signal) à afficher dans les listes pour le statut de facturation électronique.
     * Renvoie une chaîne vide si le document n'a pas encore de statut.
     *
     */
    public function tag_facturation_electronique($modele) {

        $icones_statut_facturation_electronique = [
            1 => 'fa-paper-plane',
            2 => 'fa-hourglass-half',
            3 => 'fa-exclamation-triangle',
            200 => 'fa-inbox',
            201 => 'fa-cloud-upload',
            202 => 'fa-cloud-download',
            203 => 'fa-folder-open',
            204 => 'fa-flag',
            205 => 'fa-thumbs-up',
            206 => 'fa-adjust',
            207 => 'fa-gavel',
            208 => 'fa-pause-circle',
            209 => 'fa-flag-checkered',
            210 => 'fa-ban',
            211 => 'fa-exchange',
            212 => 'fa-money',
            213 => 'fa-times-circle',
        ];

        if(empty($icones_statut_facturation_electronique[$modele->statut_facturation_electronique]))
            return '';

        return '<span class="badge badge-default css_tag_facturation_electronique">'
            .'FE'
            .'<span class="css_tag_facturation_electronique_separateur"></span>'
            .'<span class="css_tag_facturation_electronique_pastille">'
                .'<i class="fa '.$icones_statut_facturation_electronique[$modele->statut_facturation_electronique].'"></i>'
            .'</span>'
            .$this->champ('statut_facturation_electronique')->affiche($modele->statut_facturation_electronique)
        .'</span>';
    }

    /**
     *
     * Génère le XML CII (Factur-X) du document.
     *
     */
    private function xml_facturx() {
        return view('eden::pdf.include.facture_x.xml_facture', $this->donnees_facturx())->render();
    }

    /**
     *
     * XML CII réellement embarqué dans le PDF passé en paramètre (celui effectivement envoyé),
     * plutôt qu'une régénération à la volée qui pourrait diverger du document tel qu'il a été validé.
     * Renvoie null si absent (PDF non fourni, ou aucun XML embarqué) : c'est une erreur en soi,
     * pas un cas à masquer derrière une génération live de substitution.
     *
     */
    private function xml_facturx_reel($chemin_pdf) {

        if(empty($chemin_pdf))
            return null;

        try {
            return (new \Atgp\FacturX\Reader())->extractXML(\Storage::get($chemin_pdf), false);
        }
        catch(\Atgp\FacturX\Exceptions\ExceptionInterface $e) {
            return null;
        }
    }

    /**
     *
     * Transforme le PDF final (fusionné avec CGV/CGA/pièces jointes le cas échéant) en Factur-X
     * (PDF/A-3 + XML CII embarqué), via la librairie atgp/factur-x qui réimporte les pages du PDF
     * existant : le résultat est donc indépendant de la façon dont ce PDF a été construit/fusionné.
     *
     */
    public function applique_facturx($chemin_pdf) {

        if($this->modele->valide != 1)
            return;

        if(empty($this->modele_de_document) || $this->modele_de_document->facturation_electronique_active != 1)
            return;

        $writer = new \Atgp\FacturX\Writer();

        try {
            $pdf_facturx = $writer->generate(
                \Storage::get($chemin_pdf),
                $this->xml_facturx(),
                \Atgp\FacturX\Utils\ProfileHandler::PROFILE_FACTURX_EN16931
            );
        }
        catch(\Atgp\FacturX\Exceptions\Writer\WriterExceptionInterface|\Atgp\FacturX\Exceptions\XsdValidator\XsdValidatorExceptionInterface $e) {
            throw new Eden_exception('Erreur génération Factur-X sur '.$this->_type_element.' #'.$this->modele->id.' : '.$e->getMessage());
        }

        \Storage::put($chemin_pdf, $pdf_facturx);
    }

    public function apercu_facturx($chemin_pdf) {
        return $this->xml_facturx_reel($chemin_pdf);
    }

    /**
     *
     * Extrait du XML réellement embarqué dans le PDF fourni les valeurs nécessaires à l'affichage
     * lisible (aucune lecture des modèles PHP vivants : ce qui est affiché est ce qui a été validé).
     * Renvoie null si le XML est absent.
     *
     */
    private function donnees_facturx_lisible_reel($chemin_pdf) {

        $xml = $this->xml_facturx_reel($chemin_pdf);

        if(empty($xml))
            return null;

        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        $valeur = function($requete, $contexte = null) use ($xpath) {
            $noeuds = $contexte ? $xpath->query($requete, $contexte) : $xpath->query($requete);
            return $noeuds->item(0)?->textContent;
        };

        $formate_date = function($chaine) {
            $date = !empty($chaine) ? \DateTime::createFromFormat('Ymd', $chaine) : false;
            return $date ? $date->format('d/m/Y') : null;
        };

        $formate_montant = function($chaine) {
            return is_numeric($chaine) ? number_format((float) $chaine, 2, ',', ' ') : null;
        };

        $articles = [];

        foreach($xpath->query("//*[local-name()='IncludedSupplyChainTradeLineItem']") as $ligne) {

            $articles[] = [
                'ligne' => $valeur(".//*[local-name()='LineID']", $ligne),
                'designation' => $valeur(".//*[local-name()='SpecifiedTradeProduct']/*[local-name()='Name']", $ligne),
                'quantite' => $formate_montant($valeur(".//*[local-name()='BilledQuantity']", $ligne)),
                'tarif' => $formate_montant($valeur(".//*[local-name()='ChargeAmount']", $ligne)),
                'tva' => $formate_montant($valeur(".//*[local-name()='SpecifiedLineTradeSettlement']/*[local-name()='ApplicableTradeTax']/*[local-name()='RateApplicablePercent']", $ligne)),
                'total' => $formate_montant($valeur(".//*[local-name()='LineTotalAmount']", $ligne)),
            ];
        }

        $tableau_tva = [];

        // plusieurs blocs peuvent partager le même taux (ex: exonéré et autoliquidation, tous deux à
        // 0%) : on garde un tableau indexé plutôt que par taux, pour ne pas en écraser un par l'autre.
        foreach($xpath->query("//*[local-name()='ApplicableHeaderTradeSettlement']/*[local-name()='ApplicableTradeTax']") as $taxe) {

            $tableau_tva[] = [
                'taux' => $valeur(".//*[local-name()='RateApplicablePercent']", $taxe),
                'categorie' => $valeur(".//*[local-name()='CategoryCode']", $taxe),
                'ht' => $formate_montant($valeur(".//*[local-name()='BasisAmount']", $taxe)),
                'tva' => $formate_montant($valeur(".//*[local-name()='CalculatedAmount']", $taxe)),
            ];
        }

        return [
            'reference_document' => $valeur("//*[local-name()='ExchangedDocument']/*[local-name()='ID']"),
            'date' => $formate_date($valeur("//*[local-name()='ExchangedDocument']/*[local-name()='IssueDateTime']//*[local-name()='DateTimeString']")),
            'date_echeance' => $formate_date($valeur("//*[local-name()='SpecifiedTradePaymentTerms']//*[local-name()='DateTimeString']")),
            'reference_commande_client' => $valeur("//*[local-name()='BuyerOrderReferencedDocument']/*[local-name()='IssuerAssignedID']"),
            'type_code_facturx' => $valeur("//*[local-name()='ExchangedDocument']/*[local-name()='TypeCode']"),
            'cadre_facturation_facturx' => $valeur("//*[local-name()='BusinessProcessSpecifiedDocumentContextParameter']/*[local-name()='ID']"),
            'mention_indemnite_forfaitaire_facturx' => $valeur("//*[local-name()='IncludedNote'][*[local-name()='SubjectCode']='PMT']/*[local-name()='Content']"),
            'mention_penalites_retard_facturx' => $valeur("//*[local-name()='IncludedNote'][*[local-name()='SubjectCode']='PMD']/*[local-name()='Content']"),
            'mention_escompte_facturx' => $valeur("//*[local-name()='IncludedNote'][*[local-name()='SubjectCode']='AAB']/*[local-name()='Content']"),
            'vendeur_nom' => $valeur("//*[local-name()='SellerTradeParty']/*[local-name()='Name']"),
            'vendeur_adresse' => $valeur("//*[local-name()='SellerTradeParty']//*[local-name()='LineOne']"),
            'vendeur_code_postal' => $valeur("//*[local-name()='SellerTradeParty']//*[local-name()='PostcodeCode']"),
            'vendeur_ville' => $valeur("//*[local-name()='SellerTradeParty']//*[local-name()='CityName']"),
            'vendeur_siren' => $valeur("//*[local-name()='SellerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']"),
            'vendeur_numero_tva' => $valeur("//*[local-name()='SellerTradeParty']//*[local-name()='SpecifiedTaxRegistration']/*[local-name()='ID']"),
            'acheteur_nom' => $valeur("//*[local-name()='BuyerTradeParty']/*[local-name()='Name']"),
            'acheteur_adresse' => $valeur("//*[local-name()='BuyerTradeParty']//*[local-name()='LineOne']"),
            'acheteur_code_postal' => $valeur("//*[local-name()='BuyerTradeParty']//*[local-name()='PostcodeCode']"),
            'acheteur_ville' => $valeur("//*[local-name()='BuyerTradeParty']//*[local-name()='CityName']"),
            'acheteur_siren' => $valeur("//*[local-name()='BuyerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']"),
            'acheteur_annuaire' => $valeur("//*[local-name()='BuyerTradeParty']//*[local-name()='URIUniversalCommunication']/*[local-name()='URIID']"),
            'articles' => $articles,
            'tableau_tva' => $tableau_tva,
            'total_ht' => $formate_montant($valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='LineTotalAmount']")),
            'total_tva' => $formate_montant($valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='TaxTotalAmount']")),
            'total_ttc' => $formate_montant($valeur("//*[local-name()='SpecifiedTradeSettlementHeaderMonetarySummation']/*[local-name()='GrandTotalAmount']")),
        ];
    }

    public function apercu_facturx_lisible($chemin_pdf) {

        $donnees = $this->donnees_facturx_lisible_reel($chemin_pdf);

        if(empty($donnees))
            return '<p class="css_verification_facturx_manquant">'.traduction('messages.php.facture_vente.verification_facturx.xml_facturx_absent').'</p>';

        return view('eden::pdf.include.facture_x.xml_facture_lisible', $donnees)->render();
    }

    /**
     *
     * Vérifie que le XML Factur-X réellement embarqué dans le PDF fourni contient les informations
     * critiques confirmées obligatoires par Esalink (identifiants légaux vendeur/acheteur, au moins
     * une ligne). Renvoie la liste des erreurs bloquantes (vide si le XML est conforme).
     *
     */
    protected $types_code_facturx_acompte = ['386', '500', '503'];

    protected $cadres_facturation_facturx_deja_payee = ['B2', 'S2', 'M2'];

    public function verification_facturx($chemin_pdf) {

        $erreurs = [];

        $xml = $this->xml_facturx_reel($chemin_pdf);

        if(empty($xml)) {
            $erreurs[] = ['cle' => 'xml_facturx_absent', 'message' => traduction('messages.php.facture_vente.verification_facturx.xml_facturx_absent')];
            return $erreurs;
        }

        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);

        $identifiant_vendeur = $xpath->query("//*[local-name()='SellerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']")->item(0);

        if(empty($identifiant_vendeur) || empty($identifiant_vendeur->textContent))
            $erreurs[] = ['cle' => 'identifiant_vendeur', 'message' => traduction('messages.php.facture_vente.verification_facturx.identifiant_vendeur_manquant')];

        $identifiant_acheteur = $xpath->query("//*[local-name()='BuyerTradeParty']/*[local-name()='SpecifiedLegalOrganization']/*[local-name()='ID']")->item(0);

        if(empty($identifiant_acheteur) || empty($identifiant_acheteur->textContent))
            $erreurs[] = ['cle' => 'identifiant_acheteur', 'message' => traduction('messages.php.facture_vente.verification_facturx.identifiant_acheteur_manquant')];

        if($xpath->query("//*[local-name()='IncludedSupplyChainTradeLineItem']")->length == 0)
            $erreurs[] = ['cle' => 'aucune_ligne', 'message' => traduction('messages.php.facture_vente.verification_facturx.aucune_ligne')];

        $nom_acheteur = $xpath->query("//*[local-name()='BuyerTradeParty']/*[local-name()='Name']")->item(0);

        if(empty($nom_acheteur) || empty($nom_acheteur->textContent))
            $erreurs[] = ['cle' => 'nom_acheteur', 'message' => traduction('messages.php.facture_vente.verification_facturx.nom_acheteur_manquante')];

        if($xpath->query("//*[local-name()='BuyerTradeParty']/*[local-name()='PostalTradeAddress']")->length == 0)
            $erreurs[] = ['cle' => 'adresse_acheteur', 'message' => traduction('messages.php.facture_vente.verification_facturx.adresse_acheteur_manquante')];

        $adresse_vendeur = $xpath->query("//*[local-name()='SellerTradeParty']/*[local-name()='PostalTradeAddress']")->item(0);

        $adresse_vendeur_complete = !empty($adresse_vendeur)
            && !empty($xpath->query("*[local-name()='PostcodeCode']", $adresse_vendeur)->item(0)?->textContent)
            && !empty($xpath->query("*[local-name()='LineOne']", $adresse_vendeur)->item(0)?->textContent)
            && !empty($xpath->query("*[local-name()='CityName']", $adresse_vendeur)->item(0)?->textContent);

        if(!$adresse_vendeur_complete)
            $erreurs[] = ['cle' => 'adresse_vendeur', 'message' => traduction('messages.php.facture_vente.verification_facturx.adresse_vendeur_incomplete')];

        $uri_id_acheteur = $xpath->query("//*[local-name()='BuyerTradeParty']/*[local-name()='URIUniversalCommunication']/*[local-name()='URIID']")->item(0);

        if(empty($uri_id_acheteur) || empty($uri_id_acheteur->textContent))
            $erreurs[] = ['cle' => 'annuaire_facturation', 'message' => traduction('messages.php.facture_vente.verification_facturx.annuaire_facturation_manquant')];
        else if(!modele('annuaire_facturation')
                ->where('adressage_id', $uri_id_acheteur->textContent)
                ->where('client_id', $this->modele->client_id)
                ->where('active', 1)->exists())
            $erreurs[] = ['cle' => 'annuaire_facturation', 'message' => traduction('messages.php.facture_vente.verification_facturx.annuaire_facturation_non_actif')];

        if(empty($this->modele->date))
            $erreurs[] = ['cle' => 'date_facture', 'message' => traduction('messages.php.facture_vente.verification_facturx.date_manquante')];

        $type_code = $xpath->query("//*[local-name()='ExchangedDocument']/*[local-name()='TypeCode']")->item(0);

        if(empty($type_code) || empty($type_code->textContent))
            $erreurs[] = ['cle' => 'type_code_facturx', 'message' => traduction('messages.php.facture_vente.verification_facturx.type_code_facturx_manquant')];

        $cadre_facturation = $xpath->query("//*[local-name()='BusinessProcessSpecifiedDocumentContextParameter']/*[local-name()='ID']")->item(0);

        if(empty($cadre_facturation) || empty($cadre_facturation->textContent))
            $erreurs[] = ['cle' => 'cadre_facturation_facturx', 'message' => traduction('messages.php.facture_vente.verification_facturx.cadre_facturation_facturx_manquant')];

        $date_facture_facturx = $xpath->query("//*[local-name()='ExchangedDocument']/*[local-name()='IssueDateTime']/*[local-name()='DateTimeString']")->item(0)?->textContent;

        $date_echeance_facturx = $xpath->query("//*[local-name()='SpecifiedTradePaymentTerms']/*[local-name()='DueDateDateTime']/*[local-name()='DateTimeString']")->item(0)?->textContent;

        if(!empty($date_facture_facturx) && !empty($date_echeance_facturx)
            && $date_echeance_facturx < $date_facture_facturx
            && !in_array($type_code?->textContent, $this->types_code_facturx_acompte)
            && !in_array($cadre_facturation?->textContent, $this->cadres_facturation_facturx_deja_payee))
            $erreurs[] = ['cle' => 'date_echeance', 'message' => traduction('messages.php.facture_vente.verification_facturx.date_echeance_anterieure')];

        $mentions_facturx = [
            'PMT' => 'mention_indemnite_forfaitaire_facturx',
            'PMD' => 'mention_penalites_retard_facturx',
            'AAB' => 'mention_escompte_facturx',
        ];

        foreach($mentions_facturx as $subject_code => $cle) {

            $mention = $xpath->query("//*[local-name()='IncludedNote'][*[local-name()='SubjectCode']='$subject_code']/*[local-name()='Content']")->item(0);

            if(empty($mention) || empty($mention->textContent))
                $erreurs[] = ['cle' => $cle, 'message' => traduction('messages.php.facture_vente.verification_facturx.'.$cle.'_manquant')];
        }

        return $erreurs;
    }

}
