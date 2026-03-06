<?php helper('form');
$validation = session('validation');
?>

<div id="sousContenu">
    <?php echo form_open(site_url('ChangerMdp')); ?>
    <div class="corpsForm">
        <p>
            <?= form_label('Mot de passe actuel :', 'txtMDPactu') ?>
            <?= form_input([
                'name'      => 'txtMDPactu',
                'id'        => 'txtMDPactu',
                'type'      => 'password',
                'maxlength' => 45,
                'size'      => 15
            ]) ?>
            <?php if (isset($validation) && $validation->hasError('txtMDPactu')): ?>
                <span class="erreurSaisie"><?= esc($validation->getError('txtMDPactu')) ?></span>
            <?php endif; ?>
        </p>

        <p>
            <?= form_label('Nouveau mot de passe :', 'NVMdp') ?>
            <?= form_input([
                'name'      => 'NVMdp',
                'id'        => 'NVMdp',
                'type'      => 'password',
                'maxlength' => 45,
                'size'      => 15
            ]) ?>
            <?php if (isset($validation) && $validation->hasError('NVMdp')): ?>
                <span class="erreurSaisie"><?= esc($validation->getError('NVMdp')) ?></span>
            <?php endif; ?>
        </p>

        <p>
            <?= form_label('Confirmer le mot de passe :', 'ConfirmMdp') ?>
            <?= form_input([
                'name'      => 'ConfirmMdp',
                'id'        => 'ConfirmMdp',
                'type'      => 'password',
                'maxlength' => 45,
                'size'      => 15
            ]) ?>
            <?php if (isset($validation) && $validation->hasError('ConfirmMdp')): ?>
                <span class="erreurSaisie"><?= esc($validation->getError('ConfirmMdp')) ?></span>
            <?php endif; ?>
        </p>

    </div>

    <div class="piedForm">
        <p>
            <?= form_submit('btnValider', 'Valider', ['class' => 'bouton']) ?>
            <?= form_reset('btnEffacer', 'Effacer', ['class' => 'bouton']) ?>
        </p>
    </div>

    <?= form_close(); ?>
</div>