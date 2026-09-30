<?php
return [
    'type_formulaire' => 'fiche',
    'vue_js' => [
        'vuejs_data' => "",
        'vuejs_methods' => "",
        'surcharger_la_vue' => "",
    ],
    'champs_libres' => [
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'nom',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'nom_sql',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "1",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_lecture_seule' => '1',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'creation_liste',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 1',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'table_par_defaut',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 1',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'tables',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 0',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'alias_tables',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "3",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 0',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'joins',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "4",
            'taille_apres' => "0",
            'ordre' => "2",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 0',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => 'autres_conditions',
            'taille_avant' => "0",
            'taille_libelle' => "2",
            'taille_champ' => "10",
            'taille_apres' => "0",
            'ordre' => "4",
            'type_champ' => "",
            'valeur_html' => "",
            'id_editeur' => "",
            'condition_affichage_v_if' => 'vue_sql.type_de_vue == 0',
        ],
        [
            'nom_formulaire' => "vue_sql",
            'type_element' => 'vue_sql',
            'nom_sql' => "HTML lors d'un select",
            'type_champ' => "1",
            'taille_avant' => "0",
            'taille_libelle' => "0",
            'taille_champ' => "12",
            'taille_apres' => "0",
            'ordre' => "5",
            'valeur_html' => "
            <div class=\"row\" v-if=\"vue_sql.type_de_vue == 1\">
                <div class=\"col-sm-2\">Requete</div>
                <div class=\"col-sm-10\" style=\"display:flex\">
                    <textarea name=\"requete\" v-model=\"vue_sql.requete\" ></textarea>
                    <i class=\"fas fa-question-circle css_pointer\" title=\"Aide requête\" onclick=\"$('#tooltip_requete').toggle();\"  style=\"padding: 10px; background:red;color: white;height:30px;text-align: center;\"></i>    
                </div>    
                <div class=\"alert alert-danger\" id=\"tooltip_requete\" style=\"display:none\">
                Exemple de requete : <br/><i style=\"font-size: 13px\">CREATE OR REPLACE VIEW union_ca AS SELECT montant_document_ht, 'facture' AS type_document, #colonnes_eden# FROM facture_vente UNION SELECT montant_document_ht * -1, 'avoir', #colonnes_eden# FROM avoir_vente</i>
                <br/><i style=\"font-size: 11px\">Le tag <b>#colonnes_eden#</b> permet d'automatiquement rajouter les champs \"id\" et \"chaine_tags_recherche\"
                <br/><br/>Dans le cas où l'une des tables utilisés comportent la gestion des droits et uniquement dans ce cas, veuillez ajouter <b>#colonnes_eden_avec_profil#</b> au niveau du select pour les tables avec une gestion des droits et <b>#colonnes_eden_sans_profil#</b> pour les tables qui n'ont pas de gestion des droits.</i>
                <br/>Exemple : <br/><i style=\"font-size: 13px\">CREATE OR REPLACE VIEW union_ca AS select montant_document_ht, 'facture', AS type_document,#colonnes_eden#,<b>#colonnes_eden_avec_profil#</b> FROM facture_vente UNION SELECT montant_document_ht * -1, 'avoir', #colonnes_eden#,<b>#colonnes_eden_sans_profil#</b> FROM avoir_vente</i>
                <br/><br/>
                <i style=\"font-size: 11px\">Pour chaque champ compris dans l'UNION (sauf <b>#colonnes_eden#</b>,<b>#colonnes_eden_avec_profil#</b>,<b>#colonnes_eden_sans_profil#</b>), veuillez ajouter une ligne dans le tableau GESTION DES CHAMPS LIBRES avec : 
                <ul>
                    <li>Le champ référent de cette colonne</li> 
                    <li>Le nom sql du champ étant le nom de la colonne ou la valeur du AS </li>
                    <li>Le nom du champ</li>
                </ul>
                </i>
                </div>
            </div>",
            'id_editeur' => "1",
            'nom_vue' => "",
            'type_vue' => "",
        ],
    ],
];