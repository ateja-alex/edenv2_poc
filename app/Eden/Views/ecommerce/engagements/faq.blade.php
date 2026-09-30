@extends('eden::ecommerce.template.template')
@section('content')
    <section class="css_section_margin css_section_container">
        <div class="container-fluid">
            <div class="row">
                {{-- Block Menu nos engagements --}}
                @include('eden::ecommerce.addons.menu-engagements')
                <div class="col-sm-12 col-md-9">
                    <h1 class='css_titre_caracterisques_sur_mesure'>
                        Foire aux <span>questions</span>
                    </h1>
                    <p>
                        Cliquez sur les sous-titres pour accéder aux réponses à vos questions.
                    </p>
                    <div class="css_conteneur_faq js_conteneur_faq">
                        <h2>
                            Questions relatives à vos données personnelles
                        </h2>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span><span>Dois-je créer un compte</span> pour passer commande ?</span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Sur le site www.amc-production.fr, vous devez créer un compte pour pouvoir passer commande. Ce compte vous permettra de suivre l’état d’avancement de votre commande et de télécharger votre facture.
                                    <br>
                                    <br>
                                    La création d’un compte est très simple. Une fois votre panier validé, vous inscrivez votre adresse mail dans la page Connexion.
                                    <br>
                                    <br>
                                    Vous entrez ensuite vos informations personnelles (nom, prénom) et définissez vous même votre mot de passe. Votre compte est alors crée, vous identifiant est votre adresse mail, votre mot de passe est le mot de passe que vous avez défini.
                                    <br>
                                    <br>
                                    Vous pouvez désormais vous connecter sur votre espace privé pour suivre votre commande.
                                </p>
                            </div>
                        </div>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span><span>Mes données personnelles</span> sont-elles protégées ?</span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Bien sûr, vos données servent uniquement à pouvoir communiquer avec vous dans le cadre de nos relations commerciales, vous avertir de l’expédition de votre commande par mail, et SMS, vous livrer votre commande…
                                    <br>
                                    <br>
                                    Ces informations ne sont jamais partagées avec des tiers ou revendues sauf accord de votre part. Et, conformément à la Loi Informatique et Libertés du 6 janvier 1978, vous pouvez exercer vos droits d’accès, d’opposition et de rectification sur vos données figurant sur www.amc-production.fr.
                                </p>
                            </div>
                        </div>
                        <h2>
                            Questions relatives à votre commande
                        </h2>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>suivre ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    En haut à gauche du site internet, vous verrez un lien "Suivre votre commande" qui vous permet après authentification d’accéder à l’ensemble des informations de votre commande.
                                    <br>
                                    <br>
                                    Connectez vous avec votre identifant (votre adresse mail) et votre mot de passe (que vous avez défini). Si vous avez oublié votre mot de passe, cliquez simplement sur “Mot de passe oublié”, saisissez votre adresse mail, cliquez sur le lien du mail afin qu’un nouveau mot de passe vous soit généré et renvoyé par mail. Vous pouvez alors vous connecter sur votre espace client privé. Vous pourrez redéfinir votre mot de passe dans “Vos informations personnelles”.
                                    <br>
                                    <br>
                                    Une fois connecté sur votre espace privé, vous verrez les quatre grandes fonctionnalités liées à votre comp    te.
                                </p>
                            </div>
                        </div>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>suivre ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    En haut à gauche du site internet, vous verrez un lien “Suivre votre commande” qui vous permet après authentification d’accéder à l’ensemble des informations de votre commande.
                                    <br>
                                    <br>
                                    Connectez vous avec votre identifant (votre adresse mail) et votre mot de passe (que vous avez défini). Si vous avez oublié votre mot de passe, cliquez simplement sur “Mot de passe oublié”, saisissez votre adresse mail, cliquez sur le lien du mail afin qu’un nouveau mot de passe vous soit généré et renvoyé par mail. Vous pouvez alors vous connecter sur votre espace client privé. Vous pourrez redéfinir votre mot de passe dans “Vos informations personnelles”.
                                    <br>
                                    <br>
                                    Une fois connecté sur votre espace privé, vous verrez les quatre grandes fonctionnalités liées à votre compte.
                                    <br>
                                    <br>
                                    <span class="text-bold">Historique et détail de mes commandes :</span>
                                    <br>
                                    Vous visualisez ici vos commandes passées depuis la création de votre commande. C’est dans cet espace que vous pouvez télécharger votre facture, une fois votre commande expédiée.
                                    <br>
                                    <br>
                                    <span class="text-bold">Mes adresses :</span>
                                    <br>
                                    Choisissez vos adresses de facturation et de livraison. Ces dernières seront présélectionnées lors de vos commandes. Vous pouvez également ajouter d'autres adresses, ce qui est particulièrement intéressant pour envoyer des cadeaux ou recevoir votre commande au bureau. Attention, vous nous pouvez pas changer d’adresses de facturation ou de livraison pour une commande en cours, pour cela contactez le service client via le formulaire de contact.
                                    <br>
                                    <br>
                                    <span class="text-bold">Mes informations personnelles :</span>
                                    <br>
                                    Modifiez simplement vos informations personnelles. Conformément aux dispositions de la loi du n°78-17 du 6 janvier 1978, vous disposez d'un droit d'accès, de rectification et d'opposition sur les données nominatives vous concernant. Attention, vous nous pouvez pas changer vos informations personnelles pour une commande en cours, pour cela contactez le service client via le formulaire de contact.
                                    <br>
                                    <br>
                                    <span class="text-bold">Mes bons de réduction :</span>
                                    <br>
                                    Suivez vos bons de réductions.
                                    <br>
                                    <br>
                                    Le site <a href="www.amc-production.fr">www.amc-production.fr</a> a été crée pour apporter un maximum de services et répondre à l’ensembre des questions de nos clients.
                                </p>
                            </div>
                        </div>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>récupérer ma facture ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    En vous connectant à votre compte, et en cliquant sur “Historique et détails de mes commandes”, vous pouvez téléchager vos factures.
                                    <br>
                                    Comme dans l’exemple ci-dessous:
                                    <img src="{{ asset('ecommerce/amc-production/images/placeholder/commentrecupererfacture.jpg') }}" alt="">
                                    <br>
                                    Conformément à la législation, vos factures ne sont disponibles qu’une fois votre commande finalisée, c’est-à-dire expédiée.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Où en est <span>ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    En vous connectant à votre compte, et en cliquant sur "Historique et détails de mes commandes", vous pouvez visusalisez l’état de votre commande.
                                    <br>
                                    Voici en détail des explications relatives à l’état de votre commande.
                                </p>
                                <div class="flex">
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/attentecheque.jpg') }}" alt="">
                                        <p>
                                            Si vous avez décidé de régler par chèque, vous pouvez à tout moment télécharger votre bon de commande édité, dans votre suivi de commande.
                                            Une fois votre commande reçue, vous recevez un mail de confirmation et la commande passe à "Fabrication en cours"
                                        </p>
                                    </div>
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/fabrication.jpg') }}" alt="">
                                        <p>
                                            Votre commande est integrée dans nos plannings de fabrication, vous recevez un mail lors de l’expédition de votre commande.
                                        </p>
                                    </div>
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/expedie.jpg') }}" alt="">
                                        <p>
                                            Votre commande est expédiée, le transporteur prend contact avec vous dans les 24/48h suivant la date d’expédition pour définir avec vous le moment idéal pour vous livrer.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>ajouter un commentaire pour la fabrication de mon produit sur mesure ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Il arrive fréquemment que vous ayez besoin de nous communiquer quelques précisions sur votre commande. Pour ce faire, nous avons crée un espace de commentaire en dessous de votre récapitulatif de commande (votre panier).
                                    <img src="{{ asset('ecommerce/amc-production/images/placeholder/commentaires.jpg') }}" alt="">
                                    <br>
                                    Ces informations seront traitées par notre atelier pour répondre à vos besoins. En cas d’impossibilité ou d’incompréhension, nous vous appelerons.
                                    <br>
                                    <br>
                                    Pour les devis papiers, écrivez simplement, sur le devis ou sur papier libre vos commentaires, ceux-ci seront traités de la même manière.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Puis-je <span>modifier ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Une fois la commande validée et le paiement effectué, vous ne pouvez plus modifier votre commande sur le site internet.
                                    <br>
                                    <br>
                                    Notre service commercial est à votre disposition de 8h à 12h et de 13h30 à 18h30 du lundi au vendredi au 03-66-72-91-88 pour trouver une solution.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Puis-je <span>annuler ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Conformément aux dispositions de l'article L 121-20-2 du Code de la Consommation Française, l'acheteur n'a pas la possibilité de se rétracter après sa commande, dans la mesure où les produits proposés sur le site et commandés sont confectionnés selon les spécifications de l'Acheteur et sont nettement personnalisés.
                                </p>
                            </div>
                        </div>

                        <h2>
                            Questions relatives au paiement
                        </h2>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment puis-je <span>régler mes achats sur votre site internet ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <div class="flex">
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/comptant.jpg') }}" alt="">
                                        <p>
                                            Vous pouvez payer votre commande au comptant en utilisant les types de paiements suivants:
                                            <br>
                                            <br>
                                            - Par carte bancaire avec le CM-CIC Paiement, solution de paiement sécurié du CIC
                                            <br>
                                            <br>
                                            - Par carte ou compte Paypal, avec le service sécurisé de Paypal
                                            <br>
                                            <br>
                                            - Par chèque, en imprimant le bon de commande et en nous l’envoyant par courrier à l'adresse suivante : AMC Production - Service Courrier, CDV 5314 -350 Chemin Pré Neuf, 38 350 LA MURE.
                                            (notre société possède son service de traitement de courrier à La Mure, et non au siège de la société à Lille)
                                        </p>
                                    </div>
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/deuxfois.jpg') }}" alt="">
                                        <p>
                                            Modalités proposées grâce au partenariat avec notre transporteur, vous pouvez régler 50 % de votre commande, soit par Paypal, soit par carte bancaire, soit par chèque.
                                            <br>
                                            <br>
                                            Vous réglez alors les 50% restants, plus les 29 € de frais de dossier liées à la modalités 50/50 directement par chèque à remettre au transporteur le jour de la livraison.
                                        </p>
                                    </div>
                                    <div class="css_block_etat_commande_faq">
                                        <img src="{{ asset('ecommerce/amc-production/images/placeholder/troisfois.jpg') }}" alt="">
                                        <p>
                                            Vous pouvez également régler en 3 fois, et ceux uniquement par chèque.
                                            <br>
                                            <br>
                                            Des frais de dossier d’un montant de 1,5% du total TTC de votre commande sont alors ajoutés à votre commande.
                                            <br>
                                            <br>
                                            Les 3 chèques sont a expédiés à la commande et à envoyer à l'adresse:
                                            <br>
                                            <br>
                                            AMC Production - Service Courrier, CDV 5314 -350 Chemin Pré Neuf, 38 350 LA MURE.
                                            (notre société possède son service de traitement de courrier à La Mure, et non au siège de la société à Lille)
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Les modes de paiements sont-ils <span>Sécurisés ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    AMC PRODUCTION travaille avec les acteurs reconnus du paiement en ligne, et a suivi un contrôle rigoureux pour pouvoir vous offrir les meilleurs garanties de sécurisations de vos réglements par internet.
                                    <br>
                                    <br>
                                    Le CM-CIC: Un des précurseurs du e-commerce, le CIC a développé sa solution de paiement en ligne: le CM-CIC. Sécurisé et fiable, ce service est utilisé par plus de 12 000 sites e-commerces en France.
                                    <br>
                                    <br>
                                    Paypal: Paypal est la solution internationale de paiement en ligne la plus répandue dans le monde. Créée dès 1998, Paypal est aujourd’hui une solution incoutournable.
                                    <br>
                                    <br>
                                    Chèque: Nous travaillons depuis plus de 2 ans avec un service innovant de traitement des courriers et des commandes par chèque. Ce service basé à La Mure dans l'Isère est d'une fiabilité exemplaire. C'est pour cette raison que nous vous invitons à envoyer vos commande par chèque à l'adresse: AMC Production - Service Courrier, CDV 5314 -350 Chemin Pré Neuf, 38 350 LA MURE.
                                </p>
                            </div>
                        </div>

                        <h2>
                            Questions relatives à la livraison
                        </h2>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>ajouter un commentaire pour la livraison de ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Dans le processus de commande, à l’étape 3. “Adressse”, vous avez la possibilité de laisser un commentaire pour la livraison. Ce commentaire est automatiquement communiqué au transporteur qui pourra ainsi vous livrer avec plus de facilités.
                                    <br>
                                    <img src="{{ asset('ecommerce/amc-production/images/placeholder/commentaires2.jpg') }}" alt="">
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Quelles sont les <span>modalités de livraison de mes achats ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    La seule modalité de livraison est de vous livrer à votre adresse de livraison. Pour les livraisons hors France Métropolitaine, Belgique et Luxembourg, nous vous prions de prendre contact avec le service commercial.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Quels sont les <span>délais de livraison ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Notre usine fabrique vos produits selon vos dimensions, nos délais d’expédition des produits sur mesure sont de 15 jours, auquel il faut ajouter 24/48 heures pour que le transporteur livre votre commande.
                                    <br>
                                    Les accessoires standards sont expédiés sous 48 heures et donc livrés sous 3 ou 4 jours. A savoir, que le transporteur vous contact par mail et SMS avant livraison afin de définir avec vous la demi-journée de livraison. Vous n’attendez donc pas votre colis, c’est vous qui choisissez la date de livraison.
                                </p>
                            </div>
                        </div>


                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Puis-je <span>faire expédier mes colis vers une adresse différente de l’adresse de facturation ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Vous avez bien sûr la possibilité d’indiquer une adresse de livraison différente de l’adresse de facturation.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment sont <span>conditionnés mes produits ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Les produits AMC PRODUCTION sont livrés et préservés dans les meilleures conditions, par un transporteur professionnel de renom. AMC Production emballe soigneusement vosproduits dans un emballage spécialement conçu pour que votre produit ne subisse pas de dommage lors du transport.
                                </p>
                            </div>
                        </div>


                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Comment <span>connaître la date de livraison de mes produits ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Lors de votre commande, nous vous indiquons une date de livraison. Vous êtes ensuite prévenu lors de l’expédition de votre commande, le transporteur prend ensuite contact avec vous pour définir la date et la demi-journée correspond à vos disponibilités de réception des colis.
                                </p>
                            </div>
                        </div>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Que faire <span>si ma commande n’est pas arrivée à destination ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Si votre colis a été expédié mais n’est pas arrivé à destination, contactez notre Service Relation Clients par téléphone muni de votre numéro de commande.
                                </p>
                            </div>
                        </div>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span><span>J’étais absent lors du passage du transporteur,</span> comment puis-je récupérer mon colis ?</span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    En cas d’absence lors de la livraison, notre transporteur vous laissera un avis de passage avec un numéro d’appel qui vous permettra de convenir d’un rendez-vous pour une nouvelle livraison.
                                </p>
                            </div>
                        </div>

                        <h2>
                            Questions relatives aux conditions de retour
                        </h2>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span><span>Mon produit est arrivé abîmé,</span> comment faire ?</span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Chez AMC Production, la satisfaction du client passe avant tout ! Vous pouvez à tout moment contacter le service après vente d'AMC Production via le formulaire de contact ou encore au 03.66.72.91.88. du lundi au vendredi de 8h à 12h et de 13h30 à 18h30. Un technicien expérimenté analysera votre situation, et vous proposera la meilleure solution possible pour répondre à votre attente.
                                </p>
                            </div>
                        </div>
                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span>Quelles sont les <span>conditions de remboursement en cas d’annulation de ma commande ?</span></span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Conformément aux dispositions de l'article L 121-20-2 du Code de la Consommation Française, l'acheteur n'a pas la possibilité de se rétracter après sa commande, dans la mesure où les produits proposés sur le site et commandés sont confectionnés selon les spécifications de l'Acheteur et sont nettement personnalisés.
                                </p>
                            </div>
                        </div>

                        <h2>
                            Questions relatives aux garanties
                        </h2>

                        <div class="css_block_faq js_block_faq">
                            <h3 class='css_question_faq js_question_faq'><span><span>Combien de temps sont garantis</span> les produits ?</span></h3>
                            <div class="css_reponse_faq js_reponse_faq">
                                <p>
                                    Afin de vous prouver la qualité de nos produits, tous les produits AMC Production sont garantis 5 ans ! En cas de panne, vous nous contactez afin de faire un diagnostic à distance. S'il s'agit véritablement d'un problème matériel, nous étudions avec vous la meilleure façon d'y remédier en vous faisant parvenir la ou les pièces à remplacer, ainsi que toute l'assistance technique nécessaire (Voir nos conditions générales de vente pour la mise en œuvre de la garantie). Toutes nos motorisations et automatismes SOMFY® sont également garantis 5 ans par SOMFY®.
                                </p>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
