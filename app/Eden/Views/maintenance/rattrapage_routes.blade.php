@extends('eden::templates.template')


@section('title')
    Rattrapage des routes
@stop

@section('content')
    <div class="content-wrapper">
        <div id="base-content" class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div style="display: flex;justify-content: space-between;align-items: center;background: white;padding: 20px;margin-bottom: 12px;border: 1px solid lightgrey;font-size: 15px;">
                        <h4>Validation des modifications :</h4>
                        <div @click="valider_rattrapage" style="background: red;color: white;padding: 10px;border-radius: 5px;cursor: pointer;">
                            Valider
                        </div>
                    </div>
                    <div class="card mb-3" v-for="(etat,fichier) in etats_des_lieux">
                        <div class="card-header">
                            <h4>
                                <span>Changement dans le fichier : @{{ fichier }}</span>
                            </h4>
                        </div>

                        <div  class="card-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <td>Valeur avant</td>
                                    <td>Valeur après</td>
                                </tr>
                                </thead>
                                <tbody>
                                <template v-for="(valeurs,type) in etat">
                                    <tr>
                                        <td colspan="2" style="background: lightgrey">
                                            Type : @{{ type }}
                                        </td>
                                    </tr>
                                    <tr v-for="(valeur_apres,valeur_vant) in valeurs">
                                        <td>@{{ valeur_vant }}</td>
                                        <td>@{{ valeur_apres }}</td>
                                    </tr>
                                </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('donnees_pour_vuejs_data')
    etats_des_lieux : @json($etats_des_lieux),
@endpush

@push('donnees_pour_vuejs_methods')

    valider_rattrapage : async function(){

        if(!await confirm_eden('Êtes vous certain ?'))
            return;

        $.post({
            url: '{{route('maintenance.rattrapage_routes.index_post')}}',
            data : {
                etats_des_lieux : this.etats_des_lieux,
            }
        }).done(() => {
            location.reload();
        });
    },

@endpush