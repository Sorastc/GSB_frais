<?php

namespace App\Controllers;

use App\Libraries\Gsb_lib;
use App\Models\GsbModel;

/**
 * Contrôleur de gestion du changement de mot de passe.
 * 
 * Permet à un utilisateur connecté de modifier son mot de passe
 * en vérifiant l'ancien mot de passe et la confirmation du nouveau.
 */
class ChangementMDP extends BaseController
{
    /**
     * Instance de la bibliothèque GSB.
     * 
     * @var Gsb_lib
     */
    protected $gsb_lib;

    /**
     * Instance du modèle GSB.
     * 
     * @var GsbModel
     */
    protected $gsb_model;

    /**
     * Constructeur du contrôleur.
     * 
     * Charge les helpers nécessaires et instancie la bibliothèque
     * ainsi que le modèle GSB.
     */
    public function __construct()
    {
        helper(['url', 'form']);

        $this->gsb_lib = new Gsb_lib();
        $this->gsb_model = new GsbModel();
    }

    /**
     * Affiche la vue du formulaire de changement de mot de passe.
     * 
     * @return string La vue assemblée avec l'entête, le formulaire et le pied de page
     */
    public function ChangementMDP()
    {
        return view('structures/page_entete')
               .view('modifMDP')
               .view('structures/page_pied');
    }

    /**
     * Traite la modification du mot de passe de l'utilisateur.
     * 
     * Récupère le mot de passe actuel depuis la base de données, le compare
     * avec celui saisi par l'utilisateur, puis met à jour le mot de passe
     * si l'ancien est correct et que le nouveau correspond à sa confirmation.
     * 
     * @return void
     */
    public function ModifMDP()
    {
        $regles = [
            'txtMDPactu' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Mot de passe actuel'
            ],
            'NVMdp' => [
                'rules' => 'required|min_length[3]',
                'label' => 'Nouveau mot de passe'
            ],
            'ConfirmMdp' => [
                'rules' => 'required|matches[NVMdp]',
                'label' => 'Confirmation du mot de passe'
            ]
        ];

        if (!$this->validate($regles)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $idUtilisateur = session()->get('idUtilisateur');

        if (!$idUtilisateur) {
            return redirect()->to('connexion')->with('erreurs', 'Session expirée, veuillez vous reconnecter.');
        }

        $mdp     = $this->request->getPost('txtMDPactu');
        $NVmdp   = $this->request->getPost('NVMdp');
        $infoMDP = $this->gsb_model->get_info_mdp($idUtilisateur);

        if (!$infoMDP) {
            return redirect()->back()->withInput()->with('erreurs', 'Utilisateur introuvable.');
        }

        $mdpActu = (string) $infoMDP['mdp'];

        if ($mdpActu !== $mdp) {
            return redirect()->back()->withInput()->with('erreurs', 'Mot de passe actuel incorrect.');
        }

        $this->gsb_model->maj_mdp($idUtilisateur, $NVmdp);

        return redirect()->to('/accueil');
    }
}