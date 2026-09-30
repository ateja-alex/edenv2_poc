<!-- toggle -->
<td class="css_td_valeur_parametrage"
    v-if="fonctionnalite.type == 'toggle'">
    <div class="css_toggle_parametrage slide">
        <input @change="changement_valeur(fonctionnalite)" :id="fonctionnalite.fonctionnalite" type="checkbox"
               class="css_checkbox_toggle_slide_parametrage"
               v-model="{{$modele}}"
        />
        <label :for="fonctionnalite.fonctionnalite"
               class="css_label_toggle_slide_parametrage">
            <div class="toggle_rectangle slide"></div>
        </label>
    </div>
</td>
<!-- select -->
<td class="css_td_valeur_parametrage"
    v-if="fonctionnalite.type == 'select'">
    <select @change="changement_valeur(fonctionnalite)" v-model="{{$modele}}" class="css_form">
        <option value="" v-if="fonctionnalite.valeur_vide === true" v-html="traduction('interface.valeurs_select.aucun')"></option>
        <option :value="cle" v-for="(valeur, cle) in fonctionnalite.valeurs_select">@{{valeur}}</option>
    </select>
</td>
<!-- input -->
<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'input'">
    <input @change="changement_valeur(fonctionnalite)" type="text" :placeholder="fonctionnalite.placeholder"
           v-model="{{$modele}}" v-if="categorie != 'Paramètres SMTP'">
    <input @change="changement_valeur(fonctionnalite)" type="text" :placeholder="fonctionnalite.placeholder"
           v-model="{{$modele}}" v-if="categorie == 'Paramètres SMTP'" autocomplete="new-password">
</td>
<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'password'">
    <input @change="changement_valeur(fonctionnalite)" type="password" :placeholder="fonctionnalite.placeholder"
           v-model="{{$modele}}" v-if="categorie != 'Paramètres SMTP'">
    <input @change="changement_valeur(fonctionnalite)" type="password" :placeholder="fonctionnalite.placeholder"
           v-model="{{$modele}}" v-if="categorie == 'Paramètres SMTP'" autocomplete="new-password">
</td>
<!-- textarea -->
<td class="css_td_valeur_parametrage"
    v-if="fonctionnalite.type == 'textarea'">
                                                    <textarea @change="changement_valeur(fonctionnalite)" :placeholder="fonctionnalite.placeholder"
                                                              v-model="{{$modele}}"></textarea>
</td>
<!-- Date -->
<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'date'">
    <input type="date" @change="changement_valeur(fonctionnalite)" :placeholder="fonctionnalite.placeholder" v-model="{{$modele}}">
</td>
<!-- badge multisélection -->
<td class="css_td_valeur_parametrage"
    v-if="fonctionnalite.type == 'badge_multiselection'">
                                                    <span v-for="(valeur, cle) in fonctionnalite.valeurs_badges"
                                                          class="badge" :class="{'badge-success': {{$modele}}[cle] , 'badge-default': !{{$modele}}[cle]}"
                                                          @click="{{$modele}}[cle] = ({{$modele}}[cle] == true ? false : true);$forceUpdate();"
                                                          style="margin:5px;">
                                                        @{{ valeur }}
                                                    </span>
</td>
<!--connexion -->
<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'connexion'">
    <button type="button" class="btn btn-primary css_connexion_parametrage" @click="$data[`modale_${fonctionnalite.fonctionnalite}`] = !$data[`modale_${fonctionnalite.fonctionnalite}`]" v-if="$data.parametres_fonctionnalites[`${fonctionnalite.fonctionnalite}`] === false"><i class="fas fa-lock" style="margin-right: 10px;"></i> @traduction('interface.fonctionnalites.connexion')</button>
    <div v-else style="display: flex;justify-content: center;align-items: center;">
        <div class="btn btn-success css_connecte_parametrage" @click="" style="border: none !important;margin: 0;display: flex;align-items: center;justify-content: center;">
            <i class="fas fa-lock-open" style="margin-right: 10px;"></i>
            <span>@traduction('interface.fonctionnalites.connecte')</span>
        </div>
        <span class="css_deconnexion_parametrage" @click="deconnexion_api(fonctionnalite.fonctionnalite)">x</span>
    </div>
</td>
<!-- publipostage_texte -->
<td class="css_td_valeur_parametrage" v-if="fonctionnalite.type == 'publipostage_texte'">
     <champ-publipostage :type_element="fonctionnalite.type_element_publipostage"
        :modele="parametres_fonctionnalites" :exclusion_balises_parametrage="true" :nom_sql="fonctionnalite.fonctionnalite"></champ-publipostage>
</td>
