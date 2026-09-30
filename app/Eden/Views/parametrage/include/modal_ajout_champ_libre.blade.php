<template v-if="modal_ajout_element">
    <transition name="modal" >
        <div class="modal-mask">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Gestion des champs libres</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" @click="modal_ajout_element = false">&times;</span>
                        </button>
                    </div>
                    <form action="#" method="post" class="css_form" id="formulaire_champ_libre">
                        <div class="modal-body">
                            {{ csrf_field() }}
                            <input type="hidden" name="id_cl" v-model="champ_libre.id_cl" />
                            <input type="hidden" name="nom_sql" v-model="champ_libre.nom_sql" />
                            <div class="row">
                                <div class="col-sm-12 css_form_ligne_titre">Informations générales</div>
                            </div>
                            <div class="row" v-show="champ_libre.id_cl == undefined">
                                <div class="col-sm-2">Nom</div>
                                <div class="col-sm-4"><input type="text" @change="calcul_nom_sql('champ_libre.nom_sql', 'champ_libre.nom')" name="nom" v-model="champ_libre.nom" /></div>
                                <div class="col-sm-2" v-show="champ_libre.id_cl == undefined">Nom sql</div>
                                <div class="col-sm-4" v-show="champ_libre.id_cl == undefined"><input type="text" @change="calcul_nom_sql('champ_libre.nom_sql')" name="nom_sql" v-model="champ_libre.nom_sql" /></div>
                            </div>
                            <div class="row" v-if="champ_libre.id_cl !== undefined && champ_libre.index_traduction != null">
                                <div class="col-sm-12">
                                    <traduction-table :key="cle_composant_traduction" ref="traduction_table"  categorie="1" :filtrage_index="champ_libre.index_traduction+'.'"></traduction-table>
                                </div>
                            </div>
                            <div class="row"  v-show="champ_libre.id_cl == undefined">
                                <div class="col-sm-2">Type</div>
                                <div class="col-sm-4">
                                    <select name="type" v-model="champ_libre.type" @change="changement_type_champ()">
                                        <optgroup label="Mise en page">
                                            <option value="-5">Sous formulaire</option>
                                            <option value="-3">Onglet</option>
                                            <option value="-4">Fin onglet</option>
                                            <option value="-2">HTML</option>
                                            <option value="-1">Titre</option>
                                            <option value="16">Tableau</option>
                                        </optgroup>
                                        <optgroup label="Textes">
                                            <option value="0">Texte</option>
                                            <option value="6">Zone de texte</option>
                                        </optgroup>
                                        <optgroup label="Listes">
                                            <option value="1">Liste libre</option>
                                            <option value="20">Liste formatée</option>
                                        </optgroup>
                                        <optgroup label="Nombres">
                                            <option value="2">Nombre entier</option>
                                            <option value="3">Nombre décimal</option>
                                            <option value="17">Pourcentage</option>
                                        </optgroup>
                                        <optgroup label="Dates">
                                            <option value="4">Date</option>
                                            <option value="5">Date et heure</option>
                                            <option value="8">Heure</option>
                                            <option value="18">Timestamp</option>
                                        </optgroup>
                                        <optgroup label="Sélections multiples">
                                            <option value="10">Sélection multiple</option>
                                        </optgroup>
                                        <optgroup label="Imports">
                                            <option value="7">Import de fichier</option>
                                            <option value="15">Dropzone (import multiple)</option>
                                        </optgroup>
                                        <optgroup label="Selection type élément dynamique">
                                            <option value="21">Type Element Dynamique</option>
                                            <option value="22">ID Element Dynamique</option>
                                        </optgroup>
                                        <optgroup label="Divers">
                                            <option value="42">Sélection élément AJAX / Select</option>
                                            <option value="9">Sélection couleur</option>
                                            <option value="13">Note</option>
                                            <option value="14">Numérotation automatique</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <template v-if="champ_libre.id_cl == undefined && (champ_libre.type == 1 || champ_libre.type == 20 || champ_libre.type_reference == 1 || champ_libre.type_reference == 20) ">
                                    <div class="col-sm-2">Liste</div>
                                    <div class="col-sm-4">
                                        <select name="liste_choix" v-model="champ_libre.liste_choix" @click="charger_valeur_liste_pour_valeur_par_defaut">
                                            <option value="" v-show="champ_libre.type == 1 || champ_libre.type_reference == 1">Liste libre éditable</option>
                                            @foreach($liste_formatees as $index => $nom)
                                                <option value="{{ $index }}" v-show="champ_libre.type == 20 || champ_libre.type_reference == 20">{{ $nom }}</option>
                                            @endforeach
                                            @foreach($liste_libres as $index => $nom)
                                                <option value="{{ $index }}" v-show="champ_libre.type == 1 || champ_libre.type_reference == 1">{{ $nom }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </template>
                            </div>
                            <div class="row"  v-show="champ_libre.id_cl != undefined">
                                <div class="col-sm-2">Type</div>
                                <div class="col-sm-4">
                                    <select name="type" v-model="champ_libre.type"  disabled="">
                                        @foreach($types_champs_libres as $index => $nom)
                                            <option value="{{ $index }}">{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2" v-show="(champ_libre.type == 1 || champ_libre.type == 20 || champ_libre.type_reference == 1 || champ_libre.type_reference == 20) ">Liste</div>
                                <div class="col-sm-4" v-show="(champ_libre.type == 1 || champ_libre.type == 20 || champ_libre.type_reference == 1 || champ_libre.type_reference == 20)">
                                    <select name="liste_choix" v-model="champ_libre.liste_choix" disabled="">
                                        <option value="" v-show="champ_libre.type == 1 || champ_libre.type_reference == 1">Liste libre éditable</option>
                                        @foreach($liste_formatees as $index => $nom)
                                            <option value="{{ $index }}" v-show="champ_libre.type == 20 || champ_libre.type_reference == 20">{{ $nom }}</option>
                                        @endforeach
                                        @foreach($liste_libres as $index => $nom)
                                            <option value="{{ $index }}" v-show="champ_libre.type == 1 || champ_libre.type_reference == 1">{{ $nom }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row" v-if="champ_libre.type == 10" @change="changement_type_reference()">
                                <div class="col-sm-2">Type de référence</div>
                                <div class="col-sm-4">
                                    <select :disabled="champ_libre.id_cl > 0" name="type_reference" v-model="champ_libre.type_reference">
                                        <optgroup label="Textes">
                                            <option value="0">Texte</option>
                                            <option value="6">Zone de texte</option>
                                        </optgroup>
                                        <optgroup label="Listes">
                                            <option value="1">Liste libre</option>
                                            <option value="20">Liste formatée</option>
                                        </optgroup>
                                        <optgroup label="Nombres">
                                            <option value="2">Nombre entier</option>
                                            <option value="3">Nombre décimal</option>
                                            <option value="17">Pourcentage</option>
                                        </optgroup>
                                        <optgroup label="Dates">
                                            <option value="4">Date</option>
                                            <option value="5">Date et heure</option>
                                            <option value="8">Heure</option>
                                            <option value="18">Timestamp</option>
                                        </optgroup>
                                        <optgroup label="Divers">
                                            <option value="42">Sélection élément AJAX / Select</option>
                                            <option value="9">Sélection couleur</option>
                                            <option value="13">Note</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                            <div class="row"  v-if="champ_libre.id_cl == undefined && champ_libre.type == 10">
                                <div class="col-sm-2">Créer la table libre liée à la table pivot</div>
                                <div class="col-sm-4">
                                    <select name="creation_table_libre_pivot" v-model="champ_libre.creation_table_libre_pivot" >
                                        <option value="0">Non</option>
                                        <option value="1">Oui</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row" v-if="verification_HTML_titre(champ_libre.type)">
                                <template v-if="[0, 14, 1, 20, 5, 4, 6, 3, 2, 7, 8].includes(parseInt(champ_libre.type)) || [0, 14, 1, 20, 5, 4, 6, 3, 2, 7, 8].includes(parseInt(champ_libre.type_reference))">
                                    <div class="col-sm-2">
                                        Format
                                    </div>
                                    <div class="col-sm-4">
                                        <select name="format_champ" @change="changement_format" v-model="champ_libre.format_champ">
                                            <option value="" v-if="champ_libre.type==6 || champ_libre.type_reference == 6">Textarea</option>
                                            <option value="wysiwyg" v-if="champ_libre.type==6 || champ_libre.type_reference == 6">WYSIWYG</option>

                                            <template v-if="champ_libre.type==0 || champ_libre.type_reference == 0">
                                                <option value="">Texte simple</option>
                                                <option value="email">Email</option>
                                                <option value="numero_telephone">Numéro de téléphone</option>
                                                <option value="url">Url</option>
                                                <option value="password">Mot de passe</option>
                                                <option value="dossier">Dossier poste utilisateur</option>
                                                <option value="premiere_lettre_majuscule">Première lettre en majuscule</option>
                                                <option value="majuscule">En majuscules</option>
                                                <option value="siren">SIREN</option>
                                                <option value="nic">NIC</option>
                                                <option value="siret">SIRET</option>
                                                <option value="bic">BIC</option>
                                                <option value="iban">IBAN</option>
                                                <option value="tva_intra">TVA intracommunautaire</option>
                                                <option value="secu_sociale">Numéro de sécurité sociale</option>
                                                <option value="code_postal">Code postal</option>
                                                <option value="adresse">Adresse</option>
                                                <option value="mdp_systeme">Mot de passe système</option>
                                                <option value="icone">Icône</option>
                                            </template>


                                            <option value="" v-if="champ_libre.type==4 || champ_libre.type_reference==4">Jour/Mois/Année</option>
                                            <option value="m/Y" v-if="champ_libre.type==4 || champ_libre.type_reference==4">Mois/Année</option>

                                            <option value="" v-if="champ_libre.type==5 || champ_libre.type_reference==5">Jour/Mois/Année Heure:Minute:Seconde</option>
                                            <option value="d/m/Y" v-if="champ_libre.type==5 || champ_libre.type_reference==5">Jour/Mois/Année</option>
                                            <option value="m/Y" v-if="champ_libre.type==5 || champ_libre.type_reference==5">Mois/Année</option>

                                            <option value="H:i" v-if="champ_libre.type==8 || champ_libre.type_reference==8">Heure:Minute</option>
                                            <option value="H:i:s" v-if="champ_libre.type==8 || champ_libre.type_reference==8">Heure:Minute:Seconde</option>

                                            <option value="" v-if="champ_libre.type==42 || champ_libre.type_reference==42">Recherche AJAX</option>
                                            <option value="select" v-if="champ_libre.type==42 || champ_libre.type_reference==42">Select</option>

                                            <option value="" v-if="champ_libre.type_reference==20 || champ_libre.type_reference==1"></option>
                                            <option value="select" v-if="champ_libre.type_reference==20 || champ_libre.type_reference==1">Select Multiple</option>
                                            <option value="typeahead" v-if="champ_libre.type_reference==20 || champ_libre.type_reference==1">Typeahead</option>

                                            <option value="" v-if="champ_libre.type==3 || champ_libre.type_reference == 3">Décimal / sans séparateur de millier</option>
                                            <option value="monetaire" v-if="champ_libre.type==3 || champ_libre.type_reference == 3">Monétaire / sans séparateur de millier</option>

                                            <option value="" v-if="champ_libre.type==2 || champ_libre.type_reference == 2">Entier</option>
                                            <option value="sans_separateur" v-if="champ_libre.type==2 || champ_libre.type_reference == 2">Sans séparateur de millier</option>
                                            <option value="decimal_separateur_milliers" v-if="champ_libre.type==3 || champ_libre.type_reference == 3">Décimal / avec séparateur de millier</option>
                                            <option value="monetaire_separateur_milliers" v-if="champ_libre.type==3 || champ_libre.type_reference == 3">Monétaire / avec séparateur de millier</option>


                                            <template v-if="champ_libre.type==7">
                                                <option value="">Classique</option>
                                                <option value="logo">Logo</option>
                                                <option value="signature">Signature</option>
                                            </template>
                                        </select>
                                    </div>
                                </template>
                                <div class="col-sm-4" v-if="champ_libre.type==14" style="display: flex">
                                    <input type="text" name="format_champ" placeholder="{nomsql-Ymd}{break}{numero}" v-model="champ_libre.format_champ">
                                    <i title="Aide requête" v-if="champ_libre.type==14" @click="tooltip_numerotation_automatique = !tooltip_numerotation_automatique" class="fas fa-question-circle css_pointer" aria-hidden="true" style="padding: 10px; background: #ff0000; color: white; height: 30px; text-align: center;"></i>
                                </div>
                                <template v-if="[3,'3'].includes(champ_libre.type) && (champ_libre.format_champ == 'monetaire' || champ_libre.format_champ == 'monetaire_separateur_milliers')">
                                    <div class="col-sm-2">
                                        Nombre de décimales
                                    </div>
                                    <div class="col-sm-4" style="display: flex">
                                        <input type="number" name="nombre_decimale" placeholder="1" v-model="champ_libre.nombre_decimale" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                    </div>
                                </template>

                                <template v-if="(champ_libre.type==0 || champ_libre.type==6) && (champ_libre.format_champ == null || !champ_libre.format_champ.includes('siren','siret','nic'))">
                                    <div class="col-sm-2">
                                        Longueur max
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="number" placeholder="0" name="nombre_max_caracteres" v-model="champ_libre.nombre_max_caracteres" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                    </div>
                                </template>

                                <template v-if="[5, '5', 8, '8'].includes(champ_libre.type) ||[5, '5', 8, '8'].includes(champ_libre.type_reference) ">
                                    <div class="col-sm-2">
                                        Intervalle de saisie du temps
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="number" placeholder="1" name="contenu" v-model="champ_libre.contenu" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                    </div>
                                </template>
                            </div>
                            <div class="row" v-if="champ_libre.format_champ == 'decimal_separateur_milliers' || champ_libre.format_champ == 'monetaire_separateur_milliers'">
                                <div class="col-sm-2">Séparateur milliers</div>
                                <div class="col-sm-1">
                                    <input name="decimal_separateur_milliers" type="text" v-model="champ_libre.decimal_separateur_milliers" name="decimal_separateur_milliers">
                                </div>
                            </div>
                            <div class="row" v-if="champ_libre.type==14 && tooltip_numerotation_automatique">
                                <div class="col-sm-2"></div>
                                <div class="col-sm-4">
                                    <p style="font-size:11px;background-color: #bababa;padding: 1px;"><span style="font-weight:bold">{break}  : </span> Défini le préfixe qui sera utilisé pour le compteur, et n'est remplacé par rien</p>
                                    <p style="font-size:11px;background-color: #bababa;padding: 1px;"><span style="font-weight:bold">{numero} : </span> Sera remplacé par le numéro sous la forme 00001</p>
                                    <p style="font-size:11px;background-color: #bababa;padding: 1px;"><span style="font-weight:bold">{nomsql-Ymd} : </span> Renvoie la date de création de l'élément au format demandé ex :</p>
                                    <p style="font-size:11px;background-color: #bababa;padding: 1px;">
                                        <strong>{nomsql-Y}</strong> = yyyy<br/>
                                        <strong>{nomsql-y}</strong> = yy<br/>
                                        <strong>{nomsql-Ym}</strong> = yyyy-mm<br/>
                                        <strong>{nomsql-mY}</strong> = mm-yyyy<br/>
                                        <strong>{nomsql-Ymd}</strong> = yyyy-mm-dd<br/>
                                        <strong>{nomsql-dmY}</strong> = dd-mm-yyyy<br/>
                                    </p>
                                </div>
                            </div>
                            <div class="row" v-if="(champ_libre.type==0 && champ_libre.format_champ=='url') || champ_libre.type==13 || champ_libre.type_reference == 13">
                                <input type="hidden" name="contenu[0]" v-model="champ_libre.contenu[0]" />
                                <div class="col-sm-2" >Icône </div>
                                <div class="col-sm-2" >
                                    <button :style="champ_libre.contenu[2] && champ_libre.type != 13 && champ_libre.type_reference != 13? 'background'+champ_libre.contenu[2]+'!important' : ''" type="button" class="btn btn-primary iconpicker-component">
                                        <i :class="champ_libre.contenu[0]" :style="{color : champ_libre.contenu[1]}" ></i>
                                    </button>
                                    <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle icone_url"
                                            data-selected="fa-paperclip" data-toggle="dropdown">
                                        <span class="caret"></span>
                                        <span class="sr-only">Icone</span>
                                    </button>
                                    <div class="dropdown-menu"></div>
                                </div>
                                <div class="col-sm-2" >
                                    <span v-if="champ_libre.type==13 || champ_libre.type_reference == 13"> Couleur coché </span>
                                    <span v-else>Couleur de l'icône </span>
                               </div>
                                <div class="col-sm-2" >
                                    <input type="color" name="contenu[1]" v-model="champ_libre.contenu[1]">
                                </div>
                                <div class="col-sm-2" >
                                    <span v-if="champ_libre.type==13 || champ_libre.type_reference == 13"> Couleur non coché </span>
                                    <span v-else>Couleur de fond </span>
                                </div>
                                <div class="col-sm-2" >
                                    <input type="color" name="contenu[2]" v-model="champ_libre.contenu[2]">
                                </div>
                            </div>
                            <div class="row"  v-show="champ_libre.type == 42 || (champ_libre.type == 10 && champ_libre.type_reference == 42) || champ_libre.type == -5 || champ_libre.type_reference == -5">
                                <div class="col-sm-2">Elément</div>
                                <div class="col-sm-4">
                                    <select name="type_element_ajax" v-model="champ_libre.type_element_ajax">
                                        @foreach($liste_tables as $index => $nom)
                                            <option value="{{ $index}}">{{ $nom }} ({{ $index}})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-2" v-show="champ_libre.type == -5 || champ_libre.type_reference == -5">Colonne source</div>
                                <div class="col-sm-4" v-show="champ_libre.type == -5 || champ_libre.type_reference == -5"><input type="text" name="colonne_source" v-model="champ_libre.colonne_source" /></div>
                            </div>

                            <div class="row" v-if="champ_libre.type == 20 && ( champ_libre.liste_choix == 14 || champ_libre.liste_choix == 3)">
                                <div class="col-sm-12 css_form_ligne_titre">Valeurs</div>
                            </div>
                            <template v-if="champ_libre.type == 20 && ( champ_libre.liste_choix == 14 || champ_libre.liste_choix == 3)">
                                <div class="row">
                                    <div class="col-sm-2">
                                        <span >Format d'affichage</span>
                                    </div>
                                    <div class="col-sm-2">
                                        <select @change="charger_picker();" v-model="format_affichage_champ">
                                            <option value=0>Classique</option>
                                            <option value=1>Texte personnalisé</option>
                                            <option value=2>Icône</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" v-if="format_affichage_champ == 1">
                                    <template v-if="traduction('valeurs_listes_formatees.'+champ_libre.liste_choix+'.'+champ_libre.type_element+'.'+champ_libre.nom_sql+'.valeur_0') == 'valeurs_listes_formatees.'+champ_libre.liste_choix+'.'+champ_libre.type_element+'.'+champ_libre.nom_sql+'.valeur_0'">
                                        <div class="col-sm-2">
                                            <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_0"></traduction-element>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" name="contenu[0]" v-if="champ_libre.contenu[0] != null" v-model="champ_libre.contenu[0]" />
                                            <input type="text" name="contenu[0]" v-else :value="traduction('valeurs_listes_formatees.'+champ_libre.liste_choix,'valeur_0')" />
                                        </div>
                                        <div class="col-sm-2">
                                            <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_1"></traduction-element>
                                        </div>
                                        <div class="col-sm-2">
                                            <input type="text" v-if="champ_libre.contenu[1] != null" name="contenu[1]" v-model="champ_libre.contenu[1]" />
                                            <input type="text" name="contenu[1]" v-else :value="traduction('valeurs_listes_formatees.'+champ_libre.liste_choix,'valeur_1')" />
                                        </div>
                                        <div class="col-sm-2" v-if="champ_libre.liste_choix == 3">
                                            <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_2"></traduction-element>
                                        </div>
                                        <div class="col-sm-2" v-if="champ_libre.liste_choix == 3">
                                            <input type="text" name="contenu[2]" v-if="champ_libre.contenu[0] != null" v-model="champ_libre.contenu[2]" />
                                            <input type="text" name="contenu[2]" v-else :value="traduction('valeurs_listes_formatees.'+champ_libre.liste_choix,'valeur_2')" />
                                        </div>
                                    </template>
                                    <template v-else>
                                        <input type="hidden" name="contenu[]" value="0" />
                                        <input type="hidden" name="contenu[]" value="1" />
                                        <input type="hidden" name="contenu[]" v-if="champ_libre.liste_choix == 3" value="2" />

                                        <div class="col-sm-12">
                                            <traduction-table  categorie="9" :filtrage_index="'valeurs_listes_formatees.'+champ_libre.liste_choix+'.'+champ_libre.type_element+'.'+champ_libre.nom_sql+'.'"></traduction-table>
                                        </div>
                                    </template>
                                </div>
                                <div class="row" v-else-if="format_affichage_champ == 2">
                                    <input type="hidden" name="contenu[0]" value="icone" />
                                    <input type="hidden" name="contenu[1]" v-model="champ_libre.contenu[1]" />
                                    <input type="hidden" name="contenu[3]" v-model="champ_libre.contenu[3]" />
                                    <input type="hidden"  v-if="champ_libre.liste_choix == 3" name="contenu[5]" v-model="champ_libre.contenu[5]" />
                                    <div class="col-sm-1">
                                        <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_0"></traduction-element>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-primary iconpicker-component"  >
                                            <i :class="champ_libre.contenu[1]" :style="{color : champ_libre.contenu[2]}"></i>
                                        </button>
                                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle format_affichage_champ"
                                                data-selected="fa-car" data-toggle="dropdown" id="format_affichage_champ_1" >
                                            <span class="caret"></span>
                                            <span class="sr-only">Icone</span>
                                        </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="color" name="contenu[2]" v-model="champ_libre.contenu[2]" >
                                    </div>
                                    <div class="col-sm-1">
                                        <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_1"></traduction-element>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-primary iconpicker-component"  >
                                            <i :class="champ_libre.contenu[3]" :style="{color : champ_libre.contenu[4]}"></i>
                                        </button>
                                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle format_affichage_champ"
                                                data-selected="fa-car" data-toggle="dropdown" id="format_affichage_champ_3" >
                                            <span class="caret"></span>
                                            <span class="sr-only">Icone</span>
                                        </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <div class="col-sm-1">
                                        <input type="color" name="contenu[4]" v-model="champ_libre.contenu[4]">
                                    </div>
                                    <div class="col-sm-1" v-if="champ_libre.liste_choix == 3">
                                        <traduction-element :index_traduction="'valeurs_listes_formatees.'+champ_libre.liste_choix" champ="valeur_2"></traduction-element>
                                    </div>
                                    <div class="col-sm-2" v-if="champ_libre.liste_choix == 3">
                                        <button type="button" class="btn btn-primary iconpicker-component"  >
                                            <i :class="champ_libre.contenu[5]" :style="{color : champ_libre.contenu[6]}"></i>
                                        </button>
                                        <button type="button" class="icp icp-dd btn btn-primary dropdown-toggle format_affichage_champ"
                                                data-selected="fa-car" data-toggle="dropdown" id="format_affichage_champ_5" >
                                            <span class="caret"></span>
                                            <span class="sr-only">Icone</span>
                                        </button>
                                        <div class="dropdown-menu"></div>
                                    </div>
                                    <div class="col-sm-1" v-if="champ_libre.liste_choix == 3">
                                        <input type="color" name="contenu[6]" v-model="champ_libre.contenu[6]">
                                    </div>
                                </div>
                            </template>

							<template v-if="['adresse','code_postal'].includes(champ_libre.format_champ)">
								<div class="row">
									<div class="col-sm-12" >Mappage</div>
								</div>
								<div class="row" v-for="champ in (champ_libre.format_champ == 'adresse' ? ['adresse','numero_rue','nom_rue','ville','region','departement','pays','code_postal'] : ['region','ville'])">
									<div class="col-sm-2" v-html="traduction('interface.mappage_champs.adresse.'+champ)"></div>
									<div class="col-sm-4" style="display: flex;align-items: center;gap: 5px;cursor:pointer;">
										<select v-model="champ_libre.contenu[champ]">
											<option :value="champ_libre_texte.nom_sql" v-html="traduction(champ_libre_texte.index_traduction+'.nom') + ' ('+champ_libre_texte.nom_sql+')'"
													v-for="champ_libre_texte in champs_libres.filter((champ_libre_filtre) => {return champ_libre_filtre.type == 0 || champ_libre_filtre.type == null})"></option>
										</select>
                                        <i class="fas fa-times" v-if="champ_libre.contenu[champ] != null" @click="champ_libre.contenu[champ] = null"></i>
									</div>
								</div>
								<input type="hidden" :value="JSON.stringify(champ_libre.contenu)" name="contenu">
							</template>

                            <div class="row" v-if="champ_libre.type == 20 && champ_libre.liste_choix == 14 ">
                                <div class="col-sm-2" >Format champ</div>
                                <div class="col-sm-4">
                                    <select name="format_champ" v-model="champ_libre.format_champ">
                                        <option value="">Liste déroulante</option>
                                        <option value="toggle">Bouton On/Off</option>
                                        <option value="badge_cliquable">Badge cliquable</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" v-if="champ_libre.type == 7|| champ_libre.type_reference == 7">
                                <div class="col-sm-2">Fichiers</div>
                                <div class="col-sm-4">
                                    <select v-model="champ_libre.type_fichier">
                                        <option value="*.*">Tous</option>
                                        <option value="image/*">Images</option>
                                        <option value=".jpg, .png">JPG, PNG</option>
                                        <option value="application/pdf">PDF</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <input type="text" name="type_fichier" v-model="champ_libre.type_fichier">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">Visible</div>
                                <div class="col-sm-4">
                                    <select name="visibilite" v-model="champ_libre.visibilite">
                                        <option value="">Tout le temps</option>
                                        <option value="creation">Seulement en création</option>
                                        <option value="modification">Seulement en modification</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">Valeur par défaut</div>
                                <template v-if="champ_libre.type == 1 || champ_libre.type == 20 || champ_libre.type_reference == 20 || champ_libre.type_reference == 1">
                                    <div class="col-sm-4">
                                        <select :name="'valeur_defaut'+(champ_libre.type_reference == 20 || champ_libre.type_reference == 1 ? '[]' : '')" v-model="champ_libre.valeur_defaut" :multiple="champ_libre.type_reference == 20 || champ_libre.type_reference == 1" :style="champ_libre.type_reference == 20 || champ_libre.type_reference == 1 ? 'height: 80px;' : ''">
                                            <option v-if="champ_libre.liste_choix == 1" value="#utilisateur_connecte#">Utilisateur connecté</option>
                                            <option v-for="(valeur,index) in liste_pour_valeur_par_defaut" :value="index">@{{ valeur }}</option>
                                        </select>
                                    </div>
                                </template>
                                <template v-else-if="[4,'4',5,'5', 8, '8'].includes(champ_libre.type)">
                                    <div class="col-sm-4">
                                        <select name="valeur_defaut_oui_non" v-model="champ_libre.valeur_defaut_oui_non">
                                            <option value="">Pas de valeur par défaut</option>
                                            <option value="#aujourdhui#" v-if="[4,'4',5,'5'].includes(champ_libre.type)">Aujourd'hui</option>
                                            <option value="#maintenant#" v-if="[8, '8'].includes(champ_libre.type)">Maintenant</option>
                                        </select>
                                    </div>
                                    <template v-if="['#aujourdhui#', '#maintenant#'].includes(champ_libre.valeur_defaut_oui_non)">
                                        <template v-if="[5,'5', 8, '8'].includes(champ_libre.type)">
                                            <div class="col-sm-1">
                                                <input placeholder="00:00:00" v-if="champ_libre.valeur_defaut_ajout_unite_1 == 'personnalise'" name="valeur_defaut_ajout_quantite_1" type="text" v-model="champ_libre.valeur_defaut_ajout_quantite_1">
                                                <input placeholder="+ 10" v-else name="valeur_defaut_ajout_quantite_1" type="number" @change="verifie_valeur_ajoutee_possible()" min="0" v-model="champ_libre.valeur_defaut_ajout_quantite_1" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                            </div>
                                            <div class="col-sm-2">
                                                <select name="valeur_defaut_ajout_unite_1" @change="verifie_valeur_ajoutee_possible()" v-model="champ_libre.valeur_defaut_ajout_unite_1">
                                                    <option value="">Pas de valeur ajoutée</option>
                                                    <option value="hours">Heures</option>
                                                    <option value="minutes">Minutes</option>
                                                    <option value="seconds">Secondes</option>
                                                    <option value="personnalise">Temps personnalisé</option>
                                                </select>
                                            </div>
                                        </template>
                                        <template v-if="[4, '4', 5,'5'].includes(champ_libre.type)">
                                            <div class="col-sm-1">
                                                <input placeholder="+ 2" name="valeur_defaut_ajout_quantite_2" type="number" @change="verifie_valeur_ajoutee_possible()" min="0" v-model="champ_libre.valeur_defaut_ajout_quantite_2" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                                            </div>
                                            <div class="col-sm-2">
                                                <select name="valeur_defaut_ajout_unite_2" v-model="champ_libre.valeur_defaut_ajout_unite_2">
                                                    <option value="">Pas de valeur par défaut</option>
                                                    <option value="days">Jours</option>
                                                    <option value="week">Semaines</option>
                                                    <option value="month">Mois</option>
                                                    <option value="year">Années</option>
                                                </select>
                                            </div>
                                        </template>
                                    </template>
                                </template>
                                <template v-else-if="champ_libre.type == 6 && champ_libre.format_champ == 'wysiwyg'">
                                    <div class="col-sm-4"><textarea-wysiwyg-vue :modele="champ_libre" nom_sql="valeur_defaut" name="valeur_defaut"></textarea-wysiwyg-vue></div>
                                </template>
                                <template v-else>
                                    <div class="col-sm-4"><input type="text" name="valeur_defaut" v-model="champ_libre.valeur_defaut" /></div>
                                </template>
                                <div class="col-sm-2" v-if="champ_libre.type == 1">Cacher sans valeur</div>
                                <div class="col-sm-4" v-if="champ_libre.type == 1">
                                    <select name="cacher_sans_valeur" v-model="champ_libre.cacher_sans_valeur" >
                                        <option value="0">Non</option>
                                        <option value="1">Oui</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" v-if="champ_libre.type == 7">
                                <div class="col-sm-2">Nom de la pièce jointe</div>
                                <div class="col-sm-10"><input type="text" name="nom_pj" v-model="champ_libre.nom_pj" /></div>
                            </div>
                            <div class="row" v-if="champ_libre.type == 42 || champ_libre.type == 10">
                                <div class="col-sm-2">Désactiver la création à la volée</div>
                                <div class="col-sm-4">
                                    <select name="desactiver_creation_a_la_volee" v-model="champ_libre.desactiver_creation_a_la_volee">
                                        <option value="1">Oui</option>
                                        <option value="0">Non</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row" v-if="champ_libre.type == 21">
                                <input type="hidden" name="contenu" :value="JSON.stringify(champ_libre.contenu)">
                                <div class="col-sm-2">Types éléments disponibles</div>
                                <div class="col-sm-10" style="padding: 10px;display: flex;flex-wrap: wrap;gap: 10px;align-items: center;">
										<span class="css_lien_selection_element" :style="'line-height: 20px;padding:5px 10px;cursor:unset;'+(type_element.valeur !== false ? 'background-color:#28a745;color:white!important;' : 'color: #999;')" v-for="type_element in champ_libre.contenu">
											@if(table_libre($type_element)->vue_sql !=1)
                                                <span @click="supprimer_type_element_dynamique_possible(type_element.type_element);" style="padding-right: 10px;border-right: 1px solid;cursor: pointer;">
													<i class="fas fa-times"></i>
												</span>
                                            @endif
											<span style="cursor:pointer;margin-left: 5px;" @click="type_element.valeur == false ? type_element.valeur = true : type_element.valeur = false" v-html="type_element.type_element">
											</span>
										</span>
                                </div>
                                <div class="col-sm-2"></div>
                                <div class="col-sm-4">
                                    <select v-model="type_element_dynamique_a_ajouter">
                                        <option value=""></option>
                                        <option v-for="type_element_a_proposer in champ_libre.types_elements_a_proposer" :value="type_element_a_proposer.type_element">@{{ type_element_a_proposer.nom_table }}</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <i class="fas fa-plus" @click="ajouter_type_element_dynamique_possible()"></i>
                                </div>
                            </div>

                            <div class="row" v-if="champ_libre.type == 22">
                                <input type="hidden" name="contenu" :value="champ_libre.contenu">
                                <div class="col-sm-2">Type élément dynamique ciblé</div>
                                <div class="col-sm-4">
                                    <select v-model="champ_libre.contenu">
                                        <option :value=null></option>
                                        <option v-for="type_element_dynamique in this.champs_libres.filter(c => c.type == 21 && !(this.champs_libres.filter(champ => champ.type == 22 && champ.nom_sql != champ_libre.nom_sql).map(champ => champ.contenu)).includes(c.nom_sql)).map(c => c.nom_sql)"
                                                :value="type_element_dynamique">@{{ type_element_dynamique }}</option>
                                    </select>
                                </div>
                            </div>

                            <template v-if="verification_HTML_titre(champ_libre.type)">
                                <div class="row">
                                    <div class="col-sm-12 css_form_ligne_titre">Attributs</div>
                                </div>
                                @if(editeur())
                                    <div class="row">
                                        <div class="col-sm-2">Champ système</div>
                                        <div class="col-sm-4">
                                            <select name="champ_systeme" v-model="champ_libre.champ_systeme">
                                                <option value="0">Non</option>
                                                <option value="1">Oui</option>
                                            </select>
                                        </div>
                                    </div>
                                @endif
                                <div class="row">
                                    <div class="col-sm-2">Obligatoire</div>
                                    <div class="col-sm-4">
                                        <select name="obligatoire" v-model="champ_libre.obligatoire">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">Unique Entite</div>
                                    <div class="col-sm-4">
                                        <select name="unique_entite" v-model="champ_libre.unique_entite">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Unique</div >
                                    <div class="col-sm-4">
                                        <select name="unique" v-model="champ_libre.unique">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">Lecture seule</div>
                                    <div class="col-sm-4">
                                        <select name="lecture_seule" v-model="champ_libre.lecture_seule">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Recherche</div>
                                    <div class="col-sm-4">
                                        <select name="recherche" v-model="champ_libre.recherche">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">Ne pas loguer</div>
                                    <div class="col-sm-4">
                                        <select name="ne_pas_loguer" v-model="champ_libre.ne_pas_loguer">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Doit être inférieur au champ</div>
                                    <div class="col-sm-4">
                                        <input type="text" name="doit_etre_plus_petit_que" v-model="champ_libre.doit_etre_plus_petit_que" placeholder="nom sql du champ supérieur"/>
                                    </div>


                                </div>

                                <div class="row" v-if="champ_libre.type == 42">
                                    <div class="col-sm-2">Filtre badge</div>
                                    <div class="col-sm-4">
                                        <select name="badge_filtre" v-model="champ_libre.badge_filtre">
                                            <option value="0">Non</option>
                                            <option value="1">Oui</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row" v-if="champ_libre.type == 0">
                                    <div class="col-sm-3">Valeur par défaut</div>
                                    <div class="col-sm-9"><input type="text" name="valeur_defaut" v-model="champ_libre.valeur_defaut" /></div>
                                </div>
                                <div class="row" v-if="champ_libre.type == 6">
                                    <div class="col-sm-3">Valeur par défaut</div>
                                    <div class="col-sm-9"><textarea name="valeur_defaut" v-model="champ_libre.valeur_defaut"></textarea></div>
                                </div>
                                <div class="row" v-if="champ_libre.type >= 0 && document_gescom.includes(champ_libre.type_element)">
                                    <div class="col-sm-2">Correspondance fiche tiers</div>
                                    <div class="col-sm-4"><input type="text" name="correspondance_fiche_tiers" v-model="champ_libre.correspondance_fiche_tiers" /></div>
                                </div>
                                <div class="row" v-if="champ_libre.type == 42 || champ_libre.type_reference == 42 || (champ_libre.type == 22 && champ_libre.contenu != null)">
                                    <div class="col-sm-2">Filtres</div>
                                    <div class="col-sm-9">
                                        <div class="row" v-for="type_element_filtre in (champ_libre.type == 22 ? types_elements_dynamiques : [champ_libre.type_element_ajax])">
                                            <div v-if="champ_libre.type == 22" class="col-sm-12">
                                                <b>@{{ traduction('tables_libres.'+type_element_filtre+'.nom_table')}}</b>
                                                <span v-html="' ('+type_element_filtre+')'"></span>
                                            </div>
                                            <div class="col-sm-12">
                                                <recherche-avancee ref="recherche_avancee"
                                                    :chargement_externe="true" 
                                                    :enregistrement_desactive="true"
                                                    :bloc_unitaire="true"
                                                    :parametres_recherche_avancee="{
                                                        type_element : type_element_filtre, type : 'champs_libres.'+champ_libre.type_element+'.'+champ_libre.nom_sql, id_cible : types_elements_dynamiques,
                                                    }"
                                                    :type_element_source="champ_libre.type_element"
                                                    :informations_complementaires="{type_element_filtre : type_element_filtre}"></recherche-avancee>
                                            </div>
                                            <input type="hidden" :name="'filtres['+type_element_filtre+']'" v-if="champ_libre.filtres" :value="JSON.stringify(champ_libre.filtres[type_element_filtre] ?? [])">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <template v-if="champ_libre.type == 16">
                                <div class="row">
                                    <div class="col-sm-12 css_form_ligne_titre">Parametrage du tableau</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-2">Colonnes</div>
                                    <div class="col-sm-10">
                                        {{-- Affichage des colonnes existantes --}}
                                        <template v-for="(colonne, index) in champ_libre.colonnes_tableau">
                                            <input type="text" :name="'colonnes_tableau['+index+']'" v-model="champ_libre.colonnes_tableau[index]" style="width: 8%;">
                                            <span @click="supprimer_colonne(index)" style="margin-right: 2%;"><i class="fa fa-times" aria-hidden="true"></i></span>

                                        </template>
                                        {{-- Ajout d'une colonne --}}
                                        <br>
                                        <input type="text" v-model="champ_libre.nom_colonne" style="margin-top: 1%;width: 20%;">
                                        <span @click="ajout_colonne" style="width: 15%;"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                    </div>
                                </div>
                            </template>

                            <template v-if="champ_libre.id_cl > 0">
                                <div class="row">
                                    <div class="col-sm-12 css_form_ligne_titre">Profils</div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <profil-droits-divers type="eden_champslibres" :index="champ_libre.id_cl"></profil-droits-divers>
                                    </div>
                                </div>
                            </template>
                            <div class="row">
                                <div class="col-sm-12 css_form_ligne_titre">Mise en page</div>
                            </div>

                            <div class="row" v-if="verification_HTML_titre(champ_libre.type)">
                                <div class="col-sm-2" v-if="verification_HTML_titre(champ_libre.type)">Bulle d'aide</div>
                                <div class="col-sm-10">
                                    <textarea name="aide" v-model="champ_libre.aide" v-if="verification_HTML_titre(champ_libre.type)"></textarea>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">Classes css</div>
                                <div class="col-sm-10">
                                    <input type="text" name="classes_css" v-model="champ_libre.classes_css">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">Classes js</div>
                                <div class="col-sm-10">
                                    <input type="text" name="classes_js" v-model="champ_libre.classes_js">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">v-show</div>
                                <div class="col-sm-10">
                                    <input type="text" name="conditions_v_show_manuelle" v-model="champ_libre.conditions_v_show_manuelle">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-2">v-if</div>
                                <div class="col-sm-10">
                                    <input type="text" name="conditions_v_if_manuelle" v-model="champ_libre.conditions_v_if_manuelle">
                                </div>
                            </div>

                            <div class="row" v-if="champ_libre.type==-2">
                                <div class="col-sm-2">Contenu</div>
                                <div class="col-sm-10">
                                    <textarea name="contenu" v-model="champ_libre.contenu"></textarea>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-2">Ordre</div>
                                <div class="col-sm-4">
                                    <input type="text" name="ordre" v-model="champ_libre.ordre">
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" @click="modal_ajout_element = false">Fermer</button>
                            @if(table_libre($type_element)->vue_sql !=1)
                                <button type="button" class="btn btn-danger" @click="supprimer" v-show="champ_libre.id_cl != undefined">Supprimer</button>
                                <button type="button" class="btn btn-primary" @click="enregistrer">Enregistrer</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </transition>
</template>

@push('donnees_pour_vuejs_mounted')

    this.$on('changement_recherche_avancee', (parametres) => {
        if(parametres.actualisation)
            this.$set(this.champ_libre.filtres, parametres.informations_complementaires.type_element_filtre, parametres.recherche_avancee.structure);
    });

@endpush

@push('donnees_pour_vuejs_methods')

    chargement_filtres : async function(){

        if(this.champ_libre.type != 22 && this.champ_libre.type != 42 && this.champ_libre.type_reference != 42)
            return;

        await this.$nextTick();

        var recherches_avancees = await $.ajax({
            url : 'eden/recherche_avancee/champs_libres.'+this.champ_libre.type_element+'.'+this.champ_libre.nom_sql+'/recherche_type',
            dataType : 'json',
        }); 

        this.$set(this.champ_libre, 'filtres', {});

        var types_elements = this.champ_libre.type == 22 ? this.types_elements_dynamiques : [this.champ_libre.type_element_ajax];

        for(index_type_element_filtre in types_elements){
            
            var type_element_filtre = types_elements[index_type_element_filtre];
        
            this.$set(this.champ_libre.filtres, type_element_filtre, recherches_avancees.find(r => r.id_cible == type_element_filtre)?.structure ?? []);
        
            var ref_recherche_avancee = this.$refs.recherche_avancee[index_type_element_filtre];

            await ref_recherche_avancee.charger_champs_libres();

            ref_recherche_avancee.recherche_avancee = {
                nom : '',
                structure : this.champ_libre.filtres[type_element_filtre] ?? [],
            };
        }
    },

@endpush

@push('donnees_pour_vuejs_computed')

    types_elements_dynamiques : function(){

        if(this.champ_libre.type != 22 || this.champ_libre.contenu == null)
            return [];

        var champ_type_element = this.champs_libres.find(c => c.nom_sql == this.champ_libre.contenu);

        if(champ_type_element == null || champ_type_element.contenu == null)
            return [];

        return JSON.parse(champ_type_element.contenu).filter(c => c.valeur == true).map(c => c.type_element);
    },

@endpush