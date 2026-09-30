<span data-toggle="modal" data-target="#impression_fiche_pdf">
    <i class="css_action_icon primaire fa fa-fw fa-print" title="{{ traduction('module_sur_fiche.fiche.client.imprimer_pdf') }}" data-toggle="tooltip"></i>
</span>

@push('modales')
<div class="modal fade" id="impression_fiche_pdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">@traduction('module_sur_fiche.modale_impression.impression_fiche_pdf')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('base_eden.fiche.index_post', ['type_element' => $management_element->_type_element, 'id' => $management_element->modele->id, 'methode' => 'generer_fiche_pdf']) }}" method="post" id="modale_impression_formulaire_{{ $management_element->modele->id }}">
                <div class="alert alert-envoi-sms" style="display: none"></div>

                <div class="modal-body">
                    <div class="row" style="margin-bottom: 1%">
                        <div class="col-md-12">
                            <span class="badge badge-success" @click="selectionner_tous_les_blocs(true)">@traduction('module_sur_fiche.modale_impression.tout_cocher')</span> <span class="badge badge-secondary" @click="selectionner_tous_les_blocs(false)">@traduction('module_sur_fiche.modale_impression.tout_decocher')</span>
                        </div>
                    </div>

                    @foreach($blocs_impression_fiche as $blocs)
                        <div class="css_form_ligne_titre mb-3">{{$blocs['nom']}}</div>
                        @foreach($blocs['blocs'] as $cle => $nom)
                            <div class="form-check pl-2">
                                <input type="checkbox" class="form-check-input ml-0 checkbox_modale_impression" name="{{$cle}}" id="{{$cle}}">
                                <label class="form-check-label" for="{{$cle}}">{{$nom}}</label>
                                @php
                                    $array_blocs_sans_listes = array('bloc_client','bloc_ticket_client');
                                @endphp
                                @if(!in_array($cle,$array_blocs_sans_listes))
                                    <select name="nombre_elements_{{ $cle }}" style="float: right">
                                        <option value="5" v-html="traduction('module_sur_fiche.modale_impression.5_derniers')"></option>
                                        <option selected value="10" v-html="traduction('module_sur_fiche.modale_impression.10_derniers')"></option>
                                        <option value="12_mois" v-html="traduction('module_sur_fiche.modale_impression.12_derniers_mois')"></option>
                                        <option value="24_mois" v-html="traduction('module_sur_fiche.modale_impression.24_derniers_mois')"></option>
                                        <option value="tout" v-html="traduction('module_sur_fiche.modale_impression.tous_elements')"></option>
                                    </select>
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">@traduction('module_sur_fiche.modale_impression.fermer')</button>
                    <button type="submit" class="btn btn-sm btn-success">@traduction('module_sur_fiche.modale_impression.imprimer')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('donnees_pour_vuejs_methods')

    selectionner_tous_les_blocs: function(tout_selectionner){

    $('.checkbox_modale_impression').prop('checked', tout_selectionner);
    },
@endpush