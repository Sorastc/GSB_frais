<?php

namespace App\Controllers;

use App\Libraries\Gsb_lib;
use App\Models\GsbModel;

class ChangementMDP extends BaseController
{
    protected $gsb_lib;
    protected $gsb_model;

    public function __construct()
    {
        helper(['url', 'form']);

        $this->gsb_lib = new Gsb_lib();
        $this->gsb_model = new GsbModel();
    }
    public function ChangementMDP()
    {
        return view('structures/page_entete')
               .view('modifMDP')
               .view('structures/page_pied');
    }
    public function ModifMDP()
    {
        $infoMDP = $this->gsb_model->get_info_mdp($this->mdp);
        $mdpActu = $infoMDP['mdp'];

        $mdp=$this->request->getPost('txtMDPactu');
        $NVmdp=$this->request->getPost('NVMdp');
        $Validationmdp=$this->request->getPost('ConfirmMdp');
        

        if ($mdpActu == $mdp) {
            if ($NVmdp == $Validationmdp) {
                $this->gsb_model->maj_mdp($this->$idVisiteur);
            }
        }
    }
}
