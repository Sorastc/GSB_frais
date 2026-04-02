<?php

namespace App\Controllers;

use App\Models\GsbModel;
use App\Libraries\Gsb_lib;

/**
 * Contrôleur de gestion des fiches de frais d'un visiteur.
 * 
 * Permet à un visiteur connecté de saisir, modifier et supprimer
 * ses frais forfaitaires et hors forfait pour le mois en cours.
 */
class Gererfrais extends BaseController
{
    /**
     * Année de la fiche de frais courante.
     * 
     * @var string
     */
    private $id_annee;

    /**
     * Mois de la fiche de frais courante.
     * 
     * @var string
     */
    private $id_mois;

    /**
     * Identifiant de la fiche de frais courante.
     * 
     * @var int|null
     */
    private $id_fiche;

    /**
     * Identifiant du visiteur connecté.
     * 
     * @var int|null
     */
    private $id_visiteur;

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
     * Charge les helpers, instancie la bibliothèque et le modèle GSB,
     * initialise l'année et le mois courants, et récupère la fiche
     * de frais existante si elle existe.
     */
    public function __construct()
    {
        helper(['url', 'form', 'html']);

        $this->gsb_lib = new Gsb_lib();
        $this->gsb_model = new GsbModel();

        $anneemois = $this->gsb_lib->get_annee_mois();
        $this->id_annee = $this->gsb_lib->get_annee_from_anneemois($anneemois);
        $this->id_mois = $this->gsb_lib->get_mois_from_anneemois($anneemois);
        $this->id_visiteur = session()->get('idUtilisateur');

        $fiche = $this->gsb_model->get_id_ficheFrais($this->id_visiteur, $this->id_annee, $this->id_mois);
        if ($fiche != null && !empty($fiche['idFiche'])) {
            $this->id_fiche = $fiche['idFiche'];
        }
    }

    /**
     * Méthode par défaut
     * 
     * Vérifie la connexion de l'utilisateur et crée une nouvelle fiche
     * de frais si c'est le premier accès du mois, puis délègue l'affichage
     * à la méthode commun().
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse La vue assemblée ou une redirection vers la page de connexion
     */
    public function index()
    {
        // Vérifie si l'utilisateur est connecté
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/');
        }

        $data['infos'] = [];
        if ($this->gsb_model->est_premier_frais_mois($this->id_visiteur, $this->id_annee, $this->id_mois)) {
            $resultatOk = $this->gsb_model->cree_nouvelles_lignes_frais($this->id_visiteur, $this->id_annee, $this->id_mois);
            if ($resultatOk) {
                $fiche = $this->gsb_model->get_id_ficheFrais($this->id_visiteur, $this->id_annee, $this->id_mois);
                if ($fiche != null && !empty($fiche['idFiche'])) {
                    $this->id_fiche = $fiche['idFiche'];
                }
                $data['infos'][] = "Une nouvelle fiche de frais vient d'être créée pour le mois en cours";
            } else {
                $data['erreurs'][] = "Impossible de créer une nouvelle fiche de frais";
            }
        }
        return $this->commun($data);
    }

    /**
     * Validation des frais forfaitaires
     * 
     * Récupère les frais forfaitaires soumis via le formulaire POST
     * et effectue leur mise à jour en base de données.
     * 
     * @return string La vue assemblée avec un message de succès ou d'erreur
     */
    public function valider_maj_fraisforfait()
    {
        $lesFrais = $this->request->getPost('lesFrais');
        $resultatOk = $this->gsb_model->maj_frais_forfait($this->id_fiche, $lesFrais);
        if ($resultatOk) {
            $data['infos'][] = "Les modifications des frais forfaitaires ont bien été effectuées";
        } else {
            $data['erreurs'][] = "Prolème de modifications des frais forfaitaires";
        }
        return $this->commun($data);
    }

    /**
     * Création d'un nouveau frais hors forfait
     * 
     * Valide les règles de saisie du formulaire, puis crée un nouveau
     * frais hors forfait en base de données avec la date, le libellé
     * et le montant saisis.
     * 
     * @return string|\CodeIgniter\HTTP\RedirectResponse La vue assemblée ou retour avec erreurs de validation
     */
    public function valider_creation_fraishorsforfait()
    {
        $reglesSaisie = [
            'txtDateHF' => [
                'rules' => 'required',
                'label' => 'Date'
            ],
            'txtLibelleHF' => [
                'rules' => 'required|min_length[5]',
                'label' => 'Libellé'
            ],
            'txtMontantHF' => [
                'rules' => 'required|numeric',
                'label' => 'Montant'
            ]
        ];

        if (!$this->validate($reglesSaisie)) {
            // Redirection avec input et validation
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $dateFrais = $this->request->getPost("txtDateHF");
        $libelle = $this->request->getPost("txtLibelleHF");
        $montant = $this->request->getPost("txtMontantHF");

        $resultatOk = $this->gsb_model->creer_nouveau_frais_hors_forfait($this->id_fiche, $libelle, $dateFrais, $montant);
        if ($resultatOk) {
            $data['infos'][] = "La création d'un frais hors-forfait a bien été effectuée";
        } else {
            $data['erreurs'][] = "Problème lors de la création d'un frais hors-forfait";
        }

        return $this->commun($data);
    }

    /**
     * Suppression d'un frais hors forfait
     * 
     * Supprime le frais hors forfait correspondant à l'identifiant
     * passé en paramètre et affiche un message de confirmation ou d'erreur.
     * 
     * @param  int    $id_fraishorsforfait  Identifiant du frais hors forfait à supprimer
     * @return string La vue assemblée avec un message de succès ou d'erreur
     */
    public function supprimer_fraishorsforfait($id_fraishorsforfait)
    {
        $suppOk = $this->gsb_model->supprimer_frais_hors_forfait($id_fraishorsforfait);
        if ($suppOk) {
            $data['infos'][] = "La suppression du frais hors-forfait a bien été effectuée";
        } else {
            $data['erreurs'][] = "Problème lors de la suppression du frais hors-forfait";
        }
        return $this->commun($data);
    }

    /**
     * Traitement commun pour l'affichage de la page de gestion des frais
     * 
     * Assemble toutes les vues nécessaires : entête, frais forfaitaires
     * avec leur formulaire d'édition, frais hors forfait avec leur tableau
     * et formulaire d'ajout, et pied de page.
     * 
     * @param  array  $data  Tableau de données contenant les messages d'info/erreur
     * @return void
     */
    private function commun($data)
    {
        echo view('structures/page_entete');
        echo view('structures/messages', $data);
        echo view('sommaire');

        $date_titre = $this->gsb_lib->get_nom_mois($this->id_mois) . " " . $this->id_annee;

        $data['titre'] = "Renseigner ma fiche de frais du mois de " . $date_titre;

        echo view('structures/contenu_entete', $data);

        // Frais forfaitisés
        $data['sc_titre'] = 'Eléments forfaitisés';
        echo view('structures/souscontenu_entete', $data);

        $data['fraisforfait'] = $this->gsb_model->get_les_frais_forfait($this->id_fiche);
        echo view('fraisforfait_edit', $data);
        echo view('structures/souscontenu_pied');

        // Frais hors forfait
        $data['sc_titre'] = 'Eléments hors forfait';
        echo view('structures/souscontenu_entete', $data);
        $listeFraisHorsForfait = $this->gsb_model->get_les_frais_hors_forfait($this->id_fiche);
        foreach ($listeFraisHorsForfait as &$fraisHF) {
            $fraisHF['dateFraisFR'] = $this->gsb_lib->date_vers_francais($fraisHF['dateFrais']);
            $fraisHF['montantFormate'] = $this->gsb_lib->format_montant($fraisHF['montant']);
        }
        unset($fraisHF);
        $data['fraishorsforfait'] = $listeFraisHorsForfait;

        echo view('fraishorsforfait_table_sup', $data);
        echo view('fraishorsforfait_edit');
        echo view('structures/souscontenu_pied');

        echo view('structures/page_pied');
    }
}