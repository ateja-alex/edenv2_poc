<?php

namespace App\Eden\Controllers;

use App\Eden\Managements\Cache_management;
use Illuminate\Http\Request;

use App\Eden\Models\Table_libre;

use App\Http\Controllers\Controller;


class Menu_controller extends Controller {
		
	//Modification du type de menu

	public function modifier_type_menu(Request $request){
		
		if($request->type_menu== 1 ){

			$nouveau_type_menu = 2;
			$nouvelle_taille_contenu = "43px";
		}
	
		else{
	
			$nouveau_type_menu = 1;
			$nouvelle_taille_contenu = "225px";
		}

		parametre_utilisateur('type_menu', $nouveau_type_menu);

		return response()->json(array("nouvelle_taille_contenu" => $nouvelle_taille_contenu, "nouveau_type_menu" => $nouveau_type_menu));
	}
}
