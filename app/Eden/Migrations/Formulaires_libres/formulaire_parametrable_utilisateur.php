<?php
    return [
        'nom_formulaire' => "formulaire_parametrable_utilisateur",
        'titre_formulaire' => "Formulaire utilisateur paramétrable",
        'type_element' => 'utilisateur',
        'vue_js' => [
            'vuejs_data' => "",
            'vuejs_methods' => "",
            'surcharger_la_vue' => "1",
        ],
        'champs_libres' => [
            [
                'nom_formulaire' => "formulaire_parametrable_utilisateur",
                'type_element' => "utilisateur",
                'nom_sql' => "Intégration bloc html 1",
                'taille_avant' => "0",
                'taille_libelle' => "0",
                'taille_champ' => "12",
                'taille_apres' => "0",
                'ordre' => "1",
                'type_champ' => "1",
                'valeur_html' => "<div class=\"row\">
	<div class=\"col-sm-12 css_form_ligne_titre\">" . traduction_blade('formulaire.formulaire_parametrable_utilisateur.titre_categorie.informations_complementaires') . "</div>
</div>",
                'id_editeur' => "1",
                'nom_vue' => "",
                'type_vue' => "",
                'condition_affichage_v_if' => "1",

            ],
        ],
    ];
