<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Gsb_lib;

/**
 * Modèle principal de l'application GSB.
 * 
 * Gère toutes les interactions avec la base de données :
 * visiteurs, fiches de frais, frais forfaitaires et hors forfait.
 */
class GsbModel extends Model
{
    /**
     * Retourne les informations d'un visiteur
     * 
     * Recherche un utilisateur en base de données selon son login
     * et son mot de passe.
     * 
     * @param  string $login Login de l'utilisateur
     * @param  string $mdp   Mot de passe de l'utilisateur
     * @return array|null    Tableau contenant les infos du visiteur ou null si non trouvé
     */
    public function get_infos_visiteur($login, $mdp)
    {
        return $this->db->table('recup_info_util')
            ->select('idUtilisateur, nom, prenom, login, mdp, idRole, libelle')
            ->where('login', $login)
            ->where('mdp', $mdp)
            ->get()
            ->getRowArray();
    }

    /**
     * Retourne les détails d'un visiteur
     * 
     * @param  int        $id Identifiant de l'utilisateur
     * @return array|null     Tableau contenant les détails du visiteur ou null si non trouvé
     */
    public function get_detail_visiteur($id)
    {
        return $this->db->table('utilisateur')
            ->where('idUtilisateur', $id)
            ->get()
            ->getRowArray();
    }

    /**
     * Mois disponibles pour un visiteur
     * 
     * Retourne la liste des couples année/mois pour lesquels
     * le visiteur possède une fiche de frais, triés du plus récent au plus ancien.
     * 
     * @param  int   $idVisiteur Identifiant du visiteur
     * @return array             Tableau des couples année/mois disponibles
     */
    public function get_les_mois_disponibles($idVisiteur)
    {
        return $this->db->table('fichefrais')
            ->select('CONCAT(annee,mois) AS "anneemois", annee, mois')
            ->where('idVisiteur', $idVisiteur)
            ->orderBy('annee', 'DESC')
            ->orderBy('mois', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Id fiche de frais pour une année et un mois
     * 
     * Retourne l'identifiant et l'état de la fiche de frais
     * d'un visiteur pour un mois donné.
     * 
     * @param  int        $idVisiteur Identifiant du visiteur
     * @param  string     $annee      Année de la fiche
     * @param  string     $mois       Mois de la fiche
     * @return array|null             Tableau contenant l'idFiche et l'idEtat ou null si non trouvée
     */
    public function get_id_ficheFrais($idVisiteur, $annee, $mois)
    {
        return $this->db->table('fichefrais')
            ->select('fichefrais.idFiche, fichefrais.idEtat')
            ->where('fichefrais.idVisiteur', $idVisiteur)
            ->where('fichefrais.annee', $annee)
            ->where('fichefrais.mois', $mois)
            ->get()
            ->getRowArray();
    }

    /**
     * Infos fiche de frais pour un mois
     * 
     * Retourne les informations complètes d'une fiche de frais
     * (état, date de modification, justificatifs, montant validé et libellé d'état).
     * 
     * @param  int        $idFiche Identifiant de la fiche de frais
     * @return array|null          Tableau contenant les infos de la fiche ou null si non trouvée
     */
    public function get_les_infos_ficheFrais($idFiche)
    {
        return $this->db->table('fichefrais')
            ->select('fichefrais.idFiche, fichefrais.idEtat, fichefrais.dateModif, fichefrais.nbJustificatifs, fichefrais.montantValide, etatfrais.libelle')
            ->join('etatfrais', 'fichefrais.idEtat = etatfrais.idetat')
            ->where('fichefrais.idFiche', $idFiche)
            ->get()
            ->getRowArray();
    }

    /**
     * Frais forfait pour un mois
     * 
     * Retourne la liste des frais forfaitaires associés à une fiche de frais,
     * avec le libellé et la quantité de chaque élément forfaitaire.
     * 
     * @param  int   $idFiche Identifiant de la fiche de frais
     * @return array          Tableau des frais forfaitaires avec libellé et quantité
     */
    public function get_les_frais_forfait($idFiche)
    {
        return $this->db->table('lignefraisforfait')
            ->select('fraisforfait.idfraisforfait, fraisforfait.libelle, lignefraisforfait.quantite')
            ->join('fraisforfait', 'fraisforfait.idfraisforfait = lignefraisforfait.idFraisForfait')
            ->where('lignefraisforfait.idFiche', $idFiche)
            ->orderBy('lignefraisforfait.idFraisForfait', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Frais hors forfait pour un mois
     * 
     * Retourne la liste des frais hors forfait associés à une fiche de frais.
     * 
     * @param  int   $idFiche Identifiant de la fiche de frais
     * @return array          Tableau des frais hors forfait
     */
    public function get_les_frais_hors_forfait($idFiche)
    {
        return $this->db->table('lignefraishorsforfait')
            ->where('idFiche', $idFiche)
            ->get()
            ->getResultArray();
    }

    /**
     * Vérifie si premier frais du mois
     * 
     * Retourne true si le visiteur n'a encore aucune fiche de frais
     * pour le mois et l'année donnés.
     * 
     * @param  int    $idVisiteur Identifiant du visiteur
     * @param  string $annee      Année à vérifier
     * @param  string $mois       Mois à vérifier
     * @return bool               True si aucune fiche n'existe pour ce mois, false sinon
     */
    public function est_premier_frais_mois($idVisiteur, $annee, $mois)
    {
        $row = $this->db->table('fichefrais')
            ->select('count(*) AS nblignesfrais')
            ->where('idVisiteur', $idVisiteur)
            ->where('annee', $annee)
            ->where('mois', $mois)
            ->get()
            ->getRowArray();
        return $row['nblignesfrais'] === "0";
    }

    /**
     * Dernier mois saisi
     * 
     * Retourne le couple année/mois du dernier mois pour lequel
     * le visiteur a saisi une fiche de frais.
     * 
     * @param  int    $idVisiteur Identifiant du visiteur
     * @return string             Le dernier couple anneemois (ex: "202403")
     */
    public function dernier_mois_saisi($idVisiteur)
    {
        $row = $this->db->table('fichefrais')
            ->select('max(CONCAT(annee,mois)) AS dernierAnneeMois')
            ->where('idVisiteur', $idVisiteur)
            ->get()
            ->getRowArray();
        return $row['dernierAnneeMois'];
    }

    /**
     * Tous les id de frais forfait
     * 
     * Retourne la liste de tous les identifiants de frais forfaitaires
     * disponibles dans l'application, triés par ordre croissant.
     * 
     * @return array Tableau des identifiants de frais forfaitaires
     */
    public function get_les_id_frais_forfait()
    {
        return $this->db->table('fraisforfait')
            ->select('idfraisforfait')
            ->orderBy('idfraisforfait')
            ->get()
            ->getResultArray();
    }

    /**
     * Retourne la date de modification du mot de passe d'un utilisateur
     * 
     * @param  int         $idVisiteur Identifiant du visiteur
     * @return string|null             Date de dernière modification du mot de passe
     */
    public function get_info_mdp($idVisiteur)
    {
        $result = $this->db->table('utilisateur')
            ->select('DateModif, mdp')
            ->where('idUtilisateur', $idVisiteur)
            ->get()
            ->getRow();
    
        return $result->DateModif;
    }

    /**
     * Met à jour le mot de passe d'un utilisateur
     * 
     * @param  int         $idVisiteur Identifiant du visiteur
     * @return string|null             Date de modification après mise à jour
     */
    public function maj_mdp($idVisiteur)
    {
        $result = $this->db->table('utilisateur')
            ->select('DateModif, mdp')
            ->where('idUtilisateur', $idVisiteur)
            ->get()
            ->getRow();
    
        return $result->DateModif;
    }

    /**
     * Crée nouvelles lignes de frais
     * 
     * Crée une nouvelle fiche de frais pour le mois en cours,
     * clôture la dernière fiche si elle est encore en état "CR",
     * et initialise les lignes de frais forfaitaires à zéro.
     * 
     * @param  int    $idVisiteur Identifiant du visiteur
     * @param  string $annee      Année de la nouvelle fiche
     * @param  string $mois       Mois de la nouvelle fiche
     * @return bool               True si la création s'est bien déroulée, false sinon
     */
    public function cree_nouvelles_lignes_frais($idVisiteur, $annee, $mois)
    {
        $dernierMois = $this->dernier_mois_saisi($idVisiteur);
        $gsb_lib = new Gsb_lib();
        $num_annee = $gsb_lib->get_annee_from_anneemois($dernierMois);
        $num_mois = $gsb_lib->get_mois_from_anneemois($dernierMois);

        $laDerniereFiche = $this->get_id_ficheFrais($idVisiteur, $num_annee, $num_mois);

        if ($laDerniereFiche != null && $laDerniereFiche['idEtat'] == 'CR') {
            $idDerniereFiche = $laDerniereFiche['idFiche'];
            $this->maj_etat_fiche_frais($idDerniereFiche, 'CL');
        }

        $resultat = $this->db->table('fichefrais')->insert([
            'idVisiteur' => $idVisiteur,
            'annee' => $annee,
            'mois' => $mois,
            'nbJustificatifs' => 0,
            'montantValide' => 0,
            'dateModif' => date('Y-m-d'),
            'idEtat' => 'CR'
        ]);

        $insertionsOK = true;
        if ($resultat) {
            // On récupère l'id de la fiche qui vient d'être créée
            $nouvelleFiche = $this->get_id_ficheFrais($idVisiteur, $annee, $mois);
            $idNouvelleFiche = $nouvelleFiche['idFiche'];
            foreach ($this->get_les_id_frais_forfait() as $unIdFraisForfait) {
                $res = $this->db->table('lignefraisforfait')->insert([
                    'idFiche' => $idNouvelleFiche,
                    'idFraisForfait' => $unIdFraisForfait['idfraisforfait'],
                    'quantite' => 0
                ]);
                if (!$res) {
                    $insertionsOK = false;
                    break;
                }
            }
        } else {
            return false;
        }
        return $insertionsOK;
    }

    /**
     * Met à jour l'état d'une fiche
     * 
     * Exécute la procédure stockée de mise à jour des frais forfaitaires
     * pour la fiche de frais donnée.
     * 
     * @param  int   $idFiche Identifiant de la fiche de frais à mettre à jour
     * @return array          Résultat de l'exécution de la procédure stockée
     */
    public function maj_etat_fiche_frais($idFiche)
    {
        // $this->db->table('fichefrais')->update(
        //     ['idEtat' => $etat, 'dateModif' => date('Y-m-d')],
        //     ['idFiche' => $idFiche]
        // );
        $query = $this->db->query("CALL procedure_mmise_a_jour_frais_forfait(?)", [$idFiche]);
        return $query->getResult();
    }

    /**
     * Met à jour les frais forfait
     * 
     * Met à jour les quantités de chaque élément forfaitaire
     * pour la fiche de frais donnée.
     * 
     * @param  int   $idFiche  Identifiant de la fiche de frais
     * @param  array $lesFrais Tableau associatif [idFrais => quantite]
     * @return bool            True si toutes les mises à jour ont réussi, false sinon
     */
    public function maj_frais_forfait($idFiche, array $lesFrais)
    {
        $majOK = true;
        foreach (array_keys($lesFrais) as $idFrais) {
            $res = $this->db->table('lignefraisforfait')->update(
                ['quantite' => $lesFrais[$idFrais]],
                ['idFiche' => $idFiche, 'idFraisForfait' => $idFrais]
            );
            if (!$res) {
                $majOK = false;
                break;
            }
        }
        return $majOK;
    }

    /**
     * Supprime un frais hors forfait
     * 
     * @param  int  $idFrais Identifiant de la ligne de frais hors forfait à supprimer
     * @return bool          True si la suppression a réussi, false sinon
     */
    public function supprimer_frais_hors_forfait($idFrais)
    {
        return $this->db->table('lignefraishorsforfait')->delete(['idLigneFHF' => $idFrais]);
    }

    /**
     * Crée un nouveau frais hors forfait
     * 
     * Insère une nouvelle ligne de frais hors forfait en base de données
     * pour la fiche de frais donnée.
     * 
     * @param  int    $idFiche  Identifiant de la fiche de frais
     * @param  string $libelle  Libellé du frais hors forfait
     * @param  string $date     Date du frais au format Y-m-d
     * @param  float  $montant  Montant du frais
     * @return bool             True si l'insertion a réussi, false sinon
     */
    public function creer_nouveau_frais_hors_forfait($idFiche, $libelle, $date, $montant)
    {
        $resultat = $this->db->table('lignefraishorsforfait')->insert([
            'idFiche' => $idFiche,
            'libelle' => $libelle,
            'dateFrais' => $date,
            'montant' => $montant
        ]);
        return $resultat;
    }

    /**
     * Supprime un frais hors forfait (méthode alternative)
     * 
     * @param  int  $idFrais Identifiant de la ligne de frais hors forfait à supprimer
     * @return bool          True si la suppression a réussi, false sinon
     */
    public function supprimer_frais_hors_forfait2($idFrais)
    {
        return $this->db->table('lignefraishorsforfait')->delete(['idLigneFHF' => $idFrais]);
    }
}