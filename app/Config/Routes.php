<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Définition des routes de l'application GSB.
 * 
 * Ce fichier centralise toutes les routes de l'application et les associe
 * aux contrôleurs et méthodes correspondants.
 * 
 * @var RouteCollection $routes
 */

// -----------------------------------------------------------------------
// Connexion / Déconnexion
// -----------------------------------------------------------------------

/** Affiche la page de connexion (page par défaut) */
$routes->get('/', 'Connexion::login'); 

/** Affiche la page de connexion */
$routes->get('connexion', 'Connexion::login');

/** Traite le formulaire de connexion */
$routes->post('/connexion/valider', 'Connexion::valider');  

/** Déconnecte l'utilisateur et redirige vers la page de connexion */
$routes->get('/connexion/deconnexion', 'Connexion::deconnexion');  

// -----------------------------------------------------------------------
// Accueil
// -----------------------------------------------------------------------

/** Affiche la page d'accueil avec le flux RSS */
$routes->get('/accueil', 'Accueil::index');  

// -----------------------------------------------------------------------
// Gestion des frais
// -----------------------------------------------------------------------

/** Affiche la fiche de frais du mois en cours */
$routes->get('gererfrais', 'GererFrais::index');

/** Traite la mise à jour des frais forfaitaires */
$routes->post('gererfrais/maj_fraisforfait', 'GererFrais::valider_maj_fraisforfait');

/** Traite la création d'un nouveau frais hors forfait */
$routes->post('gererfrais/creation_fraishorsforfait', 'GererFrais::valider_creation_fraishorsforfait');

/** Supprime un frais hors forfait par son identifiant numérique */
$routes->get('gererfrais/supp_fraishorsforfait/(:num)', 'GererFrais::supprimer_fraishorsforfait/$1');

// -----------------------------------------------------------------------
// État des frais
// -----------------------------------------------------------------------

/** Affiche l'état des fiches de frais du visiteur */
$routes->get('etatfrais', 'EtatFrais::index');

/** Traite la sélection d'un mois dans la liste déroulante */
$routes->post('etatfrais/mois', 'EtatFrais::selectionner_mois');

// -----------------------------------------------------------------------
// Comptable
// -----------------------------------------------------------------------

/** Affiche la page de validation des fiches de frais (en développement) */
$routes->get('validerFicheFrais', 'ValiderFicheFrais::index');

/** Affiche la page de suivi du paiement des fiches de frais (en développement) */
$routes->get('suiviePaiementFicheFrais', 'SuiviePaiementFicheFrais::index');

// -----------------------------------------------------------------------
// Changement de mot de passe
// -----------------------------------------------------------------------

/** Affiche le formulaire de changement de mot de passe */
$routes->get('modifMDP', 'ChangementMDP::ChangementMDP');
// $routes->post('modifMDP', 'ChangementMDP::ChangementMDP');