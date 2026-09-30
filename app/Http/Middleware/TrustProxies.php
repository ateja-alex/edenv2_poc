<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies;

    /**
     * Derriere l'ingress Kubernetes (Traefik), le TLS est termine en amont :
     * sans proxy de confiance, X-Forwarded-Proto est ignore et
     * request()->secure() renvoie false.
     * TRUSTED_PROXIES : "*" ou liste de CIDR separes par des virgules
     * (ex. "10.2.0.0/16"). Vide = aucun proxy (comportement historique).
     */
    public function __construct()
    {
        $proxies = trim((string) env('TRUSTED_PROXIES', ''));

        if ($proxies === '*') {
            $this->proxies = '*';
        } elseif ($proxies !== '') {
            $this->proxies = array_map('trim', explode(',', $proxies));
        }
    }

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
