<div class="row" v-if="tableau_de_bord_contenu.rapport">
    <div class="col-sm-12">
        @traduction('interface.tableau_de_bord.correspondances_filtres')
    </div>
    <div class="col-sm-12">
        <table class="table table-bordered table-hover">
            <tbody>
            <tr v-for="correspondance_filtre in correspondances_filtres">
                <td v-html="$root.traduction(correspondance_filtre.filtre.index_traduction)"></td>
                <td>
                    <select-champs-libres style="width: 100%;"
                                          :type_element="tableau_de_bord_contenu.rapport.type_element"
                                          :nom_sql="correspondance_filtre.nom_sql_compatible"
                                          :champs_libres="[{
														type_element : tableau_de_bord_contenu.rapport.type_element,
														index_traduction : 'tables_libres.'+tableau_de_bord_contenu.rapport.type_element+'.nom_table',
														champs_libres : champs_libres_correspondances.filter(
															champ_libre => verification_compatibilite(correspondance_filtre.filtre.compatibilites,champ_libre)
														)
													}]"
                                            @changement_select_champs_libres="changement_select_champs_libres($event,correspondance_filtre.filtre.id)">
                    </select-champs-libres>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>