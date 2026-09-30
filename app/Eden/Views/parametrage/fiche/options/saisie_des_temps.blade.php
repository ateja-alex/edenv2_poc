<tr>
    <td>Affichage par défaut</td>
    <td>
        <select v-model="fiche.options.affichage_par_defaut">
            <option v-for="valeur in valeurs_listes_formatees[631]" v-if="valeur.desactivee != 1 && valeur.id_valeur != 0" :value="valeur.id_valeur">@{{ valeur.valeur }}</option>
        </select>
    </td>
</tr>
<tr>
    <td>Unité de saisie des temps</td>
    <td>
        <select v-model="fiche.options.unite">
            <option value="heure">À l'heure</option>
            <option value="jour">À la journée</option>
            <option value="utilisateur">Type de contrat utilisateur</option>
        </select>
    </td>
</tr>
<tr>
    <td>Jours disponibles</td>
    <td>
        @foreach(\App\Eden\Variables::tableau_jours() as $cle_jour => $jour)
            <label :class="'badge badge-'+(fiche.options.jours.includes('{{$cle_jour-1}}') ? 'success' : 'default')" for="jour_{{$cle_jour}}">{{$jour}}</label>
            <input style="display: none" id="jour_{{$cle_jour}}" type="checkbox" v-model="fiche.options.jours" value="{{$cle_jour-1}}" />
        @endforeach
    </td>
</tr>
<tr>
    <td>Gestion des commentaires</td>
    <td>
        <select v-model="fiche.options.gestion_commentaires">
            <option value="0">Non</option>
            <option value="1">Oui</option>
        </select>
    </td>
</tr>
<tr>
    <td>Restriction de la visibilité des temps des autres utilisateurs</td>
    <td>
        <select v-model="fiche.options.restriction_utilisateurs">
            <option value="0">Non</option>
            <option value="1">Oui</option>
        </select>
    </td>
</tr>
<tr v-if="fiche.options.unite == 'heure'">
    <td>Temps par jour par défaut</td>
    <td>
        <input type="number" v-model="fiche.options.nombre_temps_par_defaut" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
    </td>
</tr>
<tr>
    <td>Lors du passage d'une semaine à l'autre, les éléments saisis ne sont pas conservés</td>
    <td>
        <select v-model="fiche.options.desactiver_enregistrer_elements_selectionnes">
            <option value="0">Non</option>
            <option value="1">Oui</option>
        </select>
    </td>
</tr>
<tr>
    <td>Désactiver les jours d'indisponibilités</td>
    <td>
        <select v-model="fiche.options.desactiver_jours_indisponibilites">
            <option value="0">Non</option>
            <option value="1">Oui</option>
        </select>
    </td>
</tr>
