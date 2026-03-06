<?php

namespace App\Controllers;

use App\Models\GsbModel;
use App\Libraries\Gsb_lib;

class ValiderFicheFrais extends BaseController
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
        $data['titre'] = "Validation des fiches frais";
        return view('structures/page_entete')
            . view('structures/messages')
            . view('sommaire')
            . view('structures/contenu_entete', $data)
            . view('en_travaux')
            . view('structures/page_pied');
    }
}