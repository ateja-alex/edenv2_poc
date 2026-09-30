<?php

namespace App\Eden\Controllers;

use App\Eden\Variables;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Str;


class Upload_controller extends Controller {

    /**
	 *
	 * Enregistre un fichier uploadé
	 *
	 */
    public function upload(Request $formulaire) {

        if($formulaire->file('image')->getError() > 0)
            return response()->json(array(
                'erreur' =>  true,
                'message' => traduction('messages.php.upload.fichier_trop_volumineux')
            ));

        if(!in_array($formulaire->file('image')->getMimeType(),Variables::extension_fichier_accepte()))
            return response()->json(array(
                'erreur' =>  true,
                'message' => traduction('messages.php.upload.type_non_valide')
            ));

        if (fonctionnalite('pieces_jointes_garder_nom_originel') === true) {

            $nom_fichier = retraite_caracteres_speciaux(pathinfo($formulaire->file('image')->getClientOriginalName(), PATHINFO_FILENAME), '_');

            $extension = pathinfo($formulaire->file('image')->getClientOriginalName(), PATHINFO_EXTENSION);

            $nom_original = $nom_fichier . '.' . $extension;

            $path = $formulaire->file('image')->storeAs('public', $nom_original);
        } else {
            $hash = Str::random(40);

            $path = $formulaire->file('image')->storeAs('public', $hash . '.' . $formulaire->file('image')->getClientOriginalExtension());
        }

        return response()->json(array(
            'lien_fichier' => str_replace('public/', '', $path)
        ));
    }


}
