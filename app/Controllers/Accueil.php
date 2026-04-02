<?php

namespace App\Controllers;

/**
 * Contrôleur de la page d'accueil de l'intranet GSB.
 * 
 * Gère l'affichage de la page principale ainsi que
 * la récupération du flux RSS de santé.
 */
class Accueil extends BaseController
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
     * Affiche la page d'accueil de l'intranet.
     * 
     * Vérifie que l'utilisateur est connecté, récupère les articles
     * du flux RSS et retourne la vue complète de la page d'accueil.
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse La vue assemblée ou une redirection vers la page de connexion
     */
    public function index(){
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }
        $data['titre'] = "Bienvenue sur l'intranet GSB";
        $articles = $this->getFluxRSS();
        $data['articles'] = $articles;

        return view('structures/page_entete')
            . view('structures/messages')
            . view('sommaire')
            . view('structures/contenu_entete', $data)
            . view('actualites', $data)
            . view('structures/page_pied');
    }
    
    /** Méthode par défaut */

    /**
     * Récupère les articles depuis le flux RSS de Santé Magazine.
     * 
     * Effectue une requête cURL vers le flux RSS, parse le XML retourné
     * et construit un tableau d'articles avec leurs informations principales.
     * 
     * @return array Tableau d'articles contenant les clés 'titre', 'description',
     *               'lien', 'date' et 'image', ou un tableau avec la clé 'error'
     *               en cas d'échec du chargement du flux.
     */
    public function getFluxRSS()
    {
        // Vérifie si l'utilisateur est connecté
        

        $url = 'https://www.santemagazine.fr/feeds/rss/sante';
        $articles = [];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // temporaire si pas openssl
        $fluxXml = curl_exec($ch);
        curl_close($ch);
        $rss = simplexml_load_string($fluxXml);

        if ($rss) {
            foreach ($rss->channel->item as $item) {
                $articles[] = [
                    "titre" => $item->title,
                    "description" => $item->description,
                    "lien" => $item->link,
                    "date" => $item->pubDate,
                    "image" => $item->enclosure['url'] // attribut de la balise enclausure
                ];
            }
        } 
        else {
            return array("error" => "Impossible de charger le flux RSS.");
        }
        return $articles;
    }
}