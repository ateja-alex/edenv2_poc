<label for="" class="cellule_document_colonne_article w-100" v-if="article_sur_document.coefficient != undefined && article_sur_document.coefficient.length != 0">
    <span>@traduction('document.colonnes.designation.coefficient.titre')</span><br/>
        @if($articles_modifiables === true && empty($recapitulatif))
            <template v-for="coefficient in article_sur_document.coefficient">

                <div class="designation_coefficient_article">
                    <span
                        class="css_ajouter_ligne_nomenclature"
                        @click="masquer_coefficient_sur_document(article_sur_document,coefficient.id);modification_coeff_article(article_sur_document);"
                    >
                        <i
                            class="far fa-trash-alt"
                            data-toggle="tooltip"
                            data-position="top"
                            data-container=".cellule_designation_article"
                            data-boundary="window"
                            :data-original-title="traduction('document.colonnes.designation.coefficient.supprimer')"
                        ></i>
                    </span>

                    <div class="d-flex designation_coefficient_article_input">
                        <label for="" class="designation_coefficient_article_label">@traduction('document.colonnes.designation.coefficient.denomination') :</label>
                        <input type="text" name="" id="" :placeholder="traduction('document.colonnes.designation.coefficient.denomination')" v-model="coefficient.nom">
                    </div>
                    <div class="d-flex designation_coefficient_article_input">
                        <label for="" class="designation_coefficient_article_label">@traduction('document.colonnes.designation.coefficient.type') :</label>
                        <select v-model="coefficient.type">
                            <option value="0">{{traduction('document.colonnes.designation.coefficient.pourcentage')}}</option>
        {{--                    <option value="1">Fais fixes</option>--}}
                        </select>
                    </div>
                    <div class="d-flex designation_coefficient_article_input">
                        <label for="" class="designation_coefficient_article_label">@traduction('document.colonnes.designation.coefficient.quantite') :</label>
                        <input type="number" name="" id="" :placeholder="traduction('document.colonnes.designation.coefficient.quantite')" @change="modification_coeff_article(article_sur_document);"  v-model="coefficient.quantite" @wheel.prevent @keydown.up.prevent @keydown.down.prevent>
                    </div>


                    <span class="font-weight-bold designation_coefficient_article_input" style="display:block;" v-if="coefficient.type == 0">
                        @traduction('document.colonnes.designation.coefficient.total') : @{{ (article_sur_document.tarif * article_sur_document.quantite * (coefficient.quantite/100)).toFixed(2) }}
                    </span>
                    <span class="font-weight-bold designation_coefficient_article_input" v-else>
                        @traduction('document.colonnes.designation.coefficient.total') : @{{ coefficient.quantite }}
                    </span>
                </div>
            </template>
        @else
            <template v-for="coefficient in article_sur_document.coefficient">
                <br/>@{{article_sur_document.coefficient.nom}}
            </template>
        @endif
</label>