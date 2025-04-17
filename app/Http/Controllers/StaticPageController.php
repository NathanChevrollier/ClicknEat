<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StaticPageController extends Controller
{
    /**
     * Affiche la page des mentions légales
     */
    public function legalNotice()
    {
        return view('static.legal_notice');
    }

    /**
     * Affiche la page des conditions générales d'utilisation
     */
    public function termsOfService()
    {
        return view('static.terms_of_service');
    }

    /**
     * Affiche la page de politique de confidentialité
     */
    public function privacyPolicy()
    {
        return view('static.privacy_policy');
    }
}
