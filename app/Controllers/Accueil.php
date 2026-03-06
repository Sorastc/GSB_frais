<?php

namespace App\Controllers;

class Accueil extends BaseController
{
    public function __construct()
    {
        // On charge le helper URL et HTML
        helper(['url', 'html']);
    }

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

    public function getFluxRSS()
    {
        // Vérifie si l’utilisateur est connecté
        

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
