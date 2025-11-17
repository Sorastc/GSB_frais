<?php
namespace App\Controllers;

use App\Models\GsbModel;

class Connexion extends BaseController
{
    protected $gsb_model;

    public function __construct()
    {
        helper(['url', 'form']); // helpers URL et form

        $this->gsb_model = new GsbModel();
    }

    /**
     * Affiche l’écran de connexion
     */
    public function login()
    {
        return view('structures/page_entete')
            . view('structures/messages')
            . view('connexion')
            . view('structures/page_pied');
    }

    /**
     * Valide la saisie du formulaire de connexion
     */
    public function valider()
    {
        $reglesSaisie = [
            'txtLogin' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Login'
            ],
            'pwdMdp' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Mot de passe'
            ]
        ];

        if (!$this->validate($reglesSaisie)) {
            // Redirection avec input et validation
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $login = $this->request->getPost('txtLogin');
        $mdp = $this->request->getPost('pwdMdp');

        $visiteur = $this->gsb_model->get_infos_visiteur($login, $mdp);

        if ($visiteur) {
            session()->set([
                'idUtilisateur' => $visiteur['idUtilisateur'],
                'nom' => $visiteur['nom'],
                'prenom' => $visiteur['prenom'],
                'libelleRole' => $visiteur['libelle'],
                'idRole' => $visiteur['idRole'],
                'isLoggedIn' => true
            ]);
            return redirect()->to('/accueil');
        }

        return redirect()->back()->withInput()->with('erreurs', 'Login ou mot de passe incorrect');
    }

    /**
     * Déconnecte l’utilisateur
     */
    public function deconnexion()
    {
        session()->remove(['idUtilisateur', 'nom', 'prenom', 'libelleRole', 'idRole', 'isLoggedIn']);
        return redirect()->to('/')->with('infos', 'Vous avez bien été déconnecté.');
    }

}
