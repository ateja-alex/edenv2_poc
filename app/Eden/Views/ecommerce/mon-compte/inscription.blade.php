@extends('layouts.template')
@section('content')
    <section class="css_section_margin">
        <div class="container">
            <div class="row flex flex-start">
                <div class="col-xs-12 col-md-6">
                    @include('addons.login')
                </div>
                <div class="col-xs-12 col-md-6">
                    {{-- Création de compte --}}
                    <h2 class="css_titre_inscription">
                        Inscrivez <span>vous</span>
                    </h2>
                    <div class="css_conteneur_menu_formulaire_inscription">
                        <div class="css_menu_formulaire_inscription js_menu_formulaire_inscription active" data-inscription="normal">
                            <span class="css_txte_menu_formulaire_inscription"><img class="css_img_picto_menu_formulaire_inscription" src="{{ asset('ecommerce-amc/images/pictos/user-blanc.png') }}" alt=""> Compte Particulier 1</span>
                        </div>
                        <div class="css_menu_formulaire_inscription_pro js_menu_formulaire_inscription" data-inscription="pro">
                            <span class="css_txte_menu_formulaire_inscription"><img class="css_img_picto_menu_formulaire_inscription" src="{{ asset('ecommerce-amc/images/pictos/user-pro-blanc.png') }}" alt=""> Compte Pro</span>
                        </div>
                    </div>
                    <div class="css_block_formulaire_inscription_normal js_block_formulaire_inscription_normal">
                        <form action="" class="form-horizontal css_form_amc">
                            {{-- Civilité --}}
                            <div class="form-group">
                              <label for="civilite" class="col-sm-4 control-label">Civilité*</label>
                              <label class="radio-inline col-sm-2">
                                <input type="radio" name="civilite" checked>M.
                              </label>
                              <label class="radio-inline col-sm-2">
                                <input type="radio" name="civilite">Mme.
                              </label>
                            </div>
                            {{-- Nom --}}
                            <div class="form-group">
                                <label for="nom" class="col-sm-4 control-label">Nom*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nom" class="form-control">
                                </div>
                            </div>
                            {{-- Prénom --}}
                            <div class="form-group">
                                <label for="prenom" class="col-sm-4 control-label">Prénom*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="prenom" class="form-control">
                                </div>
                            </div>
                            {{-- Adresse 1 --}}
                            <div class="form-group">
                                <label for="adresse_1" class="col-sm-4 control-label">Adresse 1*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="adresse_1" class="form-control">
                                </div>
                            </div>
                            {{-- Adresse 2 --}}
                            <div class="form-group">
                                <label for="adresse_2" class="col-sm-4 control-label">Adresse 2</label>
                                <div class="col-sm-8">
                                    <input type="text" name="adresse_2" class="form-control">
                                </div>
                            </div>
                            {{-- Code postal --}}
                            <div class="form-group">
                                <label for="code_postal" class="col-sm-4 control-label">Code postal*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="code_postal" class="form-control">
                                </div>
                            </div>
                            {{-- Ville --}}
                            <div class="form-group">
                                <label for="ville" class="col-sm-4 control-label">Ville*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="ville" class="form-control">
                                </div>
                            </div>
                            {{-- Tél --}}
                            <div class="form-group">
                                <label for="tel" class="col-sm-4 control-label">Tél*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="tel" class="form-control">
                                </div>
                            </div>
                            {{-- E-mail --}}
                            <div class="form-group">
                                <label for="mail" class="col-sm-4 control-label">E-mail*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="mail" class="form-control">
                                </div>
                            </div>
                            {{-- Mot de passe --}}
                            <div class="form-group">
                                <label for="password" class="col-sm-4 control-label">Mot de passe*</label>
                                <div class="col-sm-8">
                                    <input type="password" name="mail" class="form-control">
                                </div>
                            </div>
                            <div class="form-group text-center">

                            <button type="submit" class="btn btn-rouge-amc css_btn_inscription_submit">S'inscrire</button>
                        </div>
                        </form>
                    </div>
                    <div class="css_block_formulaire_inscription_pro js_block_formulaire_inscription_pro hidden-block">
                        <form action="" class="form-horizontal css_form_amc">
                            {{-- Civilité --}}
                            <div class="form-group">
                              <label for="civilite" class="col-sm-4 control-label">Civilité*</label>
                              <label class="radio-inline col-sm-2">
                                <input type="radio" name="civilite" checked>M.
                              </label>
                              <label class="radio-inline col-sm-2">
                                <input type="radio" name="civilite">Mme.
                              </label>
                            </div>
                            {{-- Nom --}}
                            <div class="form-group">
                                <label for="nom" class="col-sm-4 control-label">Nom*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="nom" class="form-control">
                                </div>
                            </div>
                            {{-- Prénom --}}
                            <div class="form-group">
                                <label for="prenom" class="col-sm-4 control-label">Prénom*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="prenom" class="form-control">
                                </div>
                            </div>
                            {{-- Société --}}
                            <div class="form-group">
                                <label for="societe" class="col-sm-4 control-label">Société*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="societe" class="form-control">
                                </div>
                            </div>
                            {{-- Siren --}}
                            <div class="form-group">
                                <label for="siren" class="col-sm-4 control-label">Siren*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="siren" class="form-control">
                                </div>
                            </div>
                            {{-- Adresse 1 --}}
                            <div class="form-group">
                                <label for="adresse_1" class="col-sm-4 control-label">Adresse 1*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="adresse_1" class="form-control">
                                </div>
                            </div>
                            {{-- Adresse 2 --}}
                            <div class="form-group">
                                <label for="adresse_2" class="col-sm-4 control-label">Adresse 2</label>
                                <div class="col-sm-8">
                                    <input type="text" name="adresse_2" class="form-control">
                                </div>
                            </div>
                            {{-- Code postal --}}
                            <div class="form-group">
                                <label for="code_postal" class="col-sm-4 control-label">Code postal*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="code_postal" class="form-control">
                                </div>
                            </div>
                            {{-- Ville --}}
                            <div class="form-group">
                                <label for="ville" class="col-sm-4 control-label">Ville*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="ville" class="form-control">
                                </div>
                            </div>
                            {{-- Tél --}}
                            <div class="form-group">
                                <label for="tel" class="col-sm-4 control-label">Tél*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="tel" class="form-control">
                                </div>
                            </div>
                            {{-- E-mail --}}
                            <div class="form-group">
                                <label for="mail" class="col-sm-4 control-label">E-mail*</label>
                                <div class="col-sm-8">
                                    <input type="text" name="mail" class="form-control">
                                </div>
                            </div>
                            {{-- Mot de passe --}}
                            <div class="form-group">
                                <label for="password" class="col-sm-4 control-label">Mot de passe*</label>
                                <div class="col-sm-8">
                                    <input type="password" name="password" class="form-control">
                                </div>
                            </div>
                            {{-- Confirmer mot de passe --}}
                            <div class="form-group">
                                <label for="password_confirm" class="col-sm-4 control-label">Confirmer mot de passe*</label>
                                <div class="col-sm-8">
                                    <input type="password" name="password_confirm" class="form-control">
                                </div>
                            </div>
                            {{-- Message --}}
                            <div class="form-group">
                                <label for="message" class="col-sm-4 control-label">Message*</label>
                                <div class="col-sm-8">
                                    <textarea class='form-control' placeholder='' row='2'></textarea>
                                </div>
                            </div>
                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-rouge-amc css_btn_inscription_submit">
                                    Demander une ouverture de compte PRO
                                </button>
                            </div>
                        </form>
                    </div>


                </div>
            </div>
        </div>
    </section>
@endsection
