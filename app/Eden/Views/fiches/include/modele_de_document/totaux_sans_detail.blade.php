<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_stot_ht_av_remise">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_stot_ht_av_remise" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ht_avant_remise') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_av_remise_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ht')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_stot_ht_av_remise">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_stot_ht_av_remise" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ht_avant_remise') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_av_remise_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ht')"></a>
        @endif
    </td>

    <td style="text-align: right;">33,6 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_av_remise_ht .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ht')"></a></td>
</tr>

<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_stot_ttc_av_remise">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_stot_ttc_av_remise" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ttc_avant_remise') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_av_remise_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ttc')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_stot_ttc_av_remise">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_stot_ttc_av_remise" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ttc_avant_remise') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_av_remise_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ttc')"></a>
        @endif
    </td>

    <td style="text-align: right;">40,32 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_av_remise_ttc .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_av_remise_ttc')"></a></td>
</tr>

<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_remise_ht">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_remise_ht" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.remise_ht') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_remise_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_remise_ht')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_remise_ht">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_remise_ht" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.remise_ht') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_remise_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_remise_ht')"></a>
        @endif
    </td>

    <td style="text-align: right;">20 %/{!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_remise_ht .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_remise_ht')"></a></td>
</tr>

<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_stot_ht">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_stot_ht" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ht') }}"></span> (20%)
            <a class="fab fa-css3-alt" title=".totaux_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ht')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_stot_ht">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_stot_ht" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ht') }}"></span> (20%)
            <a class="fab fa-css3-alt" title=".totaux_ht .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ht')"></a>
        @endif
    </td>

    <td style="text-align: right;">26,88 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_ht .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ht')"></a></td>
</tr>

<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_stot_eco_contribution">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_stot_eco_contribution" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_eco_contribution') }}"></span> (20%)
            <a class="fab fa-css3-alt" title=".totaux_eco_contribution .titre_totaux_eco_contribution" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_eco_contribution')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_stot_eco_contribution">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_stot_eco_contribution" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_eco_contribution') }}"></span> (20%)
            <a class="fab fa-css3-alt" title=".totaux_eco_contribution .titre_totaux_eco_contribution" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_eco_contribution')"></a>
        @endif
    </td>

    <td style="text-align: right;">0,5 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_eco_contribution .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_eco_contribution')"></a></td>
</tr>

<tr>
    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_stot_tva">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_stot_tva" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_tva') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_tva .titre_totaux_tva" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_tva')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_stot_tva">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_stot_tva" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_tva') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_tva .titre_totaux_tva" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_tva')"></a>
        @endif
    </td>

    <td style="text-align: right;">6.72 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_tva .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_tva')"></a></td>
</tr>

<tr>

    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_total_ttc">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_tot_ttc" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ttc') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_total_ttc">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_tot_ttc" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.total_ttc') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a>
        @endif
    </td>

    <td style="text-align: right;">33,60 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_ttc .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a></td>
</tr>

<tr>

    <td v-if="{{$lignes}}[index].colonnes" :colspan="{{$lignes}}[index].colonnes.length-2"></td>

    <td style="background:#e9e9e9">
        @if(isset($cellule))
            <input type="checkbox" v-model="{{$lignes}}[index][index_cell].afficher_solde_ttc">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index][index_cell].label_solde_ttc" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.net_a_payer') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a>
        @else
            <input type="checkbox" v-model="{{$lignes}}[index].afficher_solde_ttc">
            <span class="text-bold"><input type="text" style="width:100px;" v-model="{{$lignes}}[index].label_solde_ttc" placeholder="{{ traduction('module_sur_fiche.fiche.modele_de_document.totaux.net_a_payer') }}"></span>
            <a class="fab fa-css3-alt" title=".totaux_ttc .titre_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a>
        @endif
    </td>

    <td style="text-align: right;">33,60 {!! maquette('devise_application_symbole') !!} <a class="fab fa-css3-alt" title=".totaux_ttc .montant_totaux" data-toggle="tooltip" @click="inserer_classe_au_css('totaux_ttc')"></a></td>
</tr>