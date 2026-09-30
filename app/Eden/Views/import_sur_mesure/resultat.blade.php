<div class="row">
    <div class="col-md-12">
        <div class="row" style="margin-bottom: 15px">
            <div class="col-md-12" v-if="import_en_cours.statut == 3 && nombre_importes_correctement != import_en_cours.nombres_a_importer" style="text-align: center;">
                <h3 style="color:red">
                    <i class="fas fa-ban"></i>
                    <span>@traduction('interface.import_sur_mesure.import_termines_mais_erreurs')</span>
                </h3>
            </div>
            <div class="col-md-12" v-else-if="import_en_cours.statut == 3" style="text-align: center;">
                <h3 style="color:green">
                    <i class="fas fa-check"></i>
                    <span>@traduction('interface.import_sur_mesure.import_termine')</span>
                </h3>
            </div>
            <div class="col-md-12" v-else style="text-align: center;">
                <h3 style="color:red">
                    <i class="fas fa-ban"></i>
                    <span>@traduction('interface.import_sur_mesure.import_annule')</span>
                </h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="display: inline-flex">
                <h6 style="margin-right:5px;"> @traduction('interface.import_sur_mesure.bilan') : </h6>
                <span style="position: relative;top:-3px;" v-html="traduction('interface.import_sur_mesure.nombre_importes',null,[nombre_importes_correctement,import_en_cours.nombres_a_importer])"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="display: inline-flex;" v-if="Object.keys(erreurs).length > 0">
                <h6 style="color: red;margin-right: 11px;"> @traduction('interface.import_sur_mesure.erreurs') !</h6>
                <a style="position: relative;top: -4px;" class="css_action_icon primaire fa fa-fw fa-file-csv"
                    :href="'{{ asset('storage/import_sur_mesure/compte_rendu_import_') }}'+import_en_cours.id+'.csv'"
                    :title="traduction('interface.import_sur_mesure.tooltip.lignes_non_importees')"
                    data-toggle="tooltip"></a>
            </div>
        </div>
        <div class="row">
            <template v-for="(lignes_erreurs,erreur) in erreurs" >
                <div class="col-md-12">
                    <b style="color:red">@{{ erreur }}</b>
                </div>
                <div class="col-md-12" >
                    <span>@traduction('interface.import_sur_mesure.lignes') : @{{ lignes_erreurs }}</span>
                </div>
            </template>
        </div>
    </div>
</div>

@push('donnees_pour_vuejs_computed')

    erreurs : function(){

        if(this.import_en_cours != null){
            if(typeof this.import_en_cours.erreurs == 'string')
                return JSON.parse(this.import_en_cours.erreurs);
            else
                return this.import_en_cours.erreurs;
        }

        return [];
    },

    nombre_importes_correctement : function(){

        var nombre_importes_correctement = 0;

        if(this.import_en_cours != null){

            nombre_importes_correctement = this.import_en_cours.nombres_importes;

            $.each(this.erreurs,function(index,erreur){
                nombre_importes_correctement = nombre_importes_correctement - erreur.length;
            });

        }

        return nombre_importes_correctement;

    },

@endpush