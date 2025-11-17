<?php $session = session(); ?>

<div id="menuGauche">
    <div id="infosUtil">
        <div id="user">
            <img src="<?= base_url('assets/images/UserIcon.png') ?>" alt="Utilisateur" />
        </div>
        <div id="infos">
            <h2><?= esc($session->get('prenom') . ' ' . $session->get('nom')) ?></h2>
            <h3><?= esc($session->get('libelleRole')) ?></h3>  
        </div>
        <ul class="menuList">
            <li>
                <?= anchor('connexion/deconnexion', 'Déconnexion', ['title' => 'Se déconnecter']) ?>
            </li>
        </ul> 
    </div>  

    <ul id="menuPrincipal" class="menuList">
        <li>
            <?= anchor('accueil', 'Accueil', ['title' => 'Accueil']) ?>
        </li>

        <?php if ($session->get('idRole') == "V"){?>
        <li>
            <?= anchor('gererfrais', 'Saisie fiche de frais', ['title' => 'Saisie fiche de frais']) ?>
        </li>
        <li>
            <?= anchor('etatfrais', 'Mes fiches de frais', ['title' => 'Consultation de mes fiches de frais']) ?>
        </li>
        <?php }
        elseif($session->get('idRole') == "C")
        { ?>
        <li>
            <?= anchor('validerFicheFrais', 'Valider fiche de frais', ['title' => 'Valider fiche de frais']) ?>
        </li>
        <li>
            <?= anchor('suiviePaiementFicheFrais', 'Suivi paiement fiche de frais', ['title' => 'Suivre le paiement fiche de frais']) ?>
        </li>
        <?php } ?>
        
        
    </ul>
</div>
