<?php

namespace App\Controllers;

use App\Models\GsbModel;
use App\Libraries\Gsb_lib;

/**
 * Contrôleur de suivi du paiement des fiches de frais.
 * 
 * Affiche la page de suivi du paiement des fiches de frais.
 * Fonctionnalité en cours de développement.
 */
class SuiviePaiementFicheFrais extends BaseController
{
    /**
     * Constructeur du contrôleur.
     * 
     * Charge les helpers URL et HTML nécessaires aux vues.
     */
    public function __construct()
    {
        // On charge le helper URL et HTML
        helper(['url', 'html']);
    }

    /**
     * Affiche la page de suivi du paiement des fiches de frais.
     * 
     * Vérifie que l'utilisateur est connecté puis affiche
     * la vue "en travaux" dans l'attente du développement complet.
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse La vue assemblée ou une redirection vers la page de connexion
     */
    public function index(){
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        $data['titre'] = "Suivi du paiememnt des fiches de frais";
        return view('structures/page_entete')
            . view('structures/messages')
            . view('sommaire')
            . view('structures/contenu_entete', $data)
            . view('en_travaux')
            . view('structures/page_pied');
    }
}