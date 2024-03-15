<?php
namespace GDO\IP2City\Method;

use GDO\Core\GDO_ArgError;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT;
use GDO\Form\GDT_AntiCSRF;
use GDO\Form\GDT_Form;
use GDO\Form\GDT_Submit;
use GDO\Form\MethodForm;
use GDO\IP2City\Module_IP2City;
use GDO\Net\GDT_IP;

final class Detect extends MethodForm
{

    protected function createForm(GDT_Form $form): void
    {
        $form->addFields(
            GDT_IP::make('ip')->useCurrent(),
            GDT_AntiCSRF::make(),
        );
        $form->actions()->addField(GDT_Submit::make());
    }

    /**
     * @throws GDO_ArgError
     */
    public function getIP(): string
    {
        return $this->gdoParameterVar('ip');
    }

    /**
     * @throws GDO_ArgError
     * @throws GDO_DBException
     */
    public function formValidated(GDT_Form $form): GDT
    {
        if ($cip = Module_IP2City::instance()->detectIP($this->getIP()))
        {
            $city = $cip->getCityName();
            $ctry = $cip->getCountryCode();
            return $this->message('msg_ip2city_detected', [$city, $ctry]);
        }
        return $this->error('err_ip2city_unknown');
    }

}
