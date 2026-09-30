<?php

namespace App\Eden\Middlewares;
use Closure;


class Eden_fournisseur_connecte
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		
		$parametres = $request->route()->parameters();

		$fournisseur = management('fournisseur', $parametres['id_fournisseur']);

		if ( !$fournisseur->existe() || $parametres['clef_fournisseur_hachee'] != $fournisseur->genere_clef_interface_fournisseur($fournisseur->modele) ) {

            return redirect()->route('interface_fournisseur.erreur_acces');
		}

        return $next($request);
    }
}
