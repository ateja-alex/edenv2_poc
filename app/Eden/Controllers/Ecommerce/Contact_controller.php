<?php

namespace App\Eden\Controllers\Ecommerce;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Mail;
USE Response;
USE Validator;


class Contact_controller extends Controller {

    /**
     * 
     * Affiche la page contact
     * 
     * @return Response
     */
    public function index() {
		
		return view('eden::ecommerce.contact');
    }

    /**
    * On gère le formulaire de contact
    *
    * @return Response
    */
    public function contact_post(Request $request) {

        // On vérifie les champs
        $validator = Validator::make($request->all(), array(

            'nom' => 'required|max:100',
            'prenom' => 'required|max:100',
            'adresse_mail' => 'required|email',
            'numero_telephone' => 'required|max:1000'
        ));


        if($validator->fails()) {

            // On redirige avec un message d'erreur
            return redirect()->back()->withErrors($validator->messages()->first());
        }

        
        // On envoi un e-mail
        $result = Mail::send('eden::emails.contact', ['data' => $request->all()], function($email) use ($request) {

            $email->from($request->input('adresse_mail'));
            $email->subject($request->input('objet'));
            $email->to('service.client@impressthem.fr');
            // $email->to('frederic.bry@easy-developpement.fr');
        });



        return redirect()->back()->with('succes', traduction('messages.php.ecommerce.contact.message_envoye'));

    }

    
}
