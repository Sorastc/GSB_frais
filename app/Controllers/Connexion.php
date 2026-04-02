<?php
namespace App\Controllers;
use DateTime;
use App\Models\GsbModel;

/**
 * Contrôleur de gestion de la connexion et déconnexion des utilisateurs.
 * 
 * Gère l'authentification des visiteurs, la validation du formulaire
 * de connexion ainsi que l'expiration du mot de passe.
 */
class Connexion extends BaseController
{
    /**
     * Instance du modèle GSB.
     * 
     * @var GsbModel
     */
    protected $gsb_model;

    /**
     * Constructeur du contrôleur.
     * 
     * Charge les helpers URL et form, et instancie le modèle GSB.
     */
    public function __construct()
    {
        helper(['url', 'form']); // helpers URL et form

        $this->gsb_model = new GsbModel();
    }

    /**
     * Affiche l'écran de connexion
     * 
     * @return string La vue assemblée avec l'entête, le formulaire de connexion et le pied de page
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
     * 
     * Vérifie les règles de saisie, authentifie l'utilisateur,
     * contrôle l'expiration du mot de passe (6 mois) et initialise
     * la session si la connexion est réussie.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse Redirection vers l'accueil, la modification
     *                                            du mot de passe ou retour avec message d'erreur
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
        $login=$this->request->getPost('txtLogin');
        $mdp=$this->request->getPost('pwdMdp');

        $visiteur = $this->gsb_model->get_infos_visiteur($login, $mdp);
        $dateModif = $this->gsb_model->get_info_mdp($visiteur['idUtilisateur']);
        $dateActuelle = new DateTime();

        if ($visiteur) {


            $dateModifObj = new DateTime($dateModif);
            $diff = $dateActuelle->diff($dateModifObj);
            $moisEcoules = ($diff->y * 12) + $diff->m;

            if ($moisEcoules >= 6) {
                // Mot de passe expiré → rediriger vers la page de changement
                return redirect()->to('modifMDP')->with('error', 'Votre mot de passe a expiré, veuillez le modifier.');
            }

            // $dateModifObj = new DateTime($dateModif);
            // $diff = $dateActuelle->diff($dateModifObj);
            // $joursEcoules = ($diff->y * 365) + ($diff->m * 30) + $diff->d;

            // if ($joursEcoules >= 1) {
            //     return redirect()->to('modifMDP');
            // }

            session()->set([
                'idUtilisateur' => $visiteur['idUtilisateur'],
                'nom'           => $visiteur['nom'],
                'prenom'        => $visiteur['prenom'],
                'libelleRole'   => $visiteur['libelle'],
                'idRole'        => $visiteur['idRole'],
                'isLoggedIn'    => true
            ]);
            return redirect()->to('/accueil');
        }

        return redirect()->back()->withInput()->with('erreurs', 'Login ou mot de passe incorrect');
    }

    /**
     * Déconnecte l'utilisateur
     * 
     * Supprime toutes les données de session liées à l'utilisateur
     * et redirige vers la page d'accueil avec un message de confirmation.
     * 
     * @return \CodeIgniter\HTTP\RedirectResponse Redirection vers la page de connexion
     */
    public function deconnexion()
    {
        session()->remove(['idUtilisateur', 'nom', 'prenom', 'libelleRole', 'idRole', 'isLoggedIn']);
        return redirect()->to('/')->with('infos', 'Vous avez bien été déconnecté.');
    }

}