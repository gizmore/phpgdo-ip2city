<?php
namespace GDO\IP2City;

use GDO\Address\GDO_City;
use GDO\Address\GDT_City;
use GDO\Core\GDO_DBException;
use GDO\Core\GDO_Module;
use GDO\Core\GDT_Checkbox;
use GDO\Core\GDT_Enum;
use GDO\Net\GDT_IP;
use GDO\Register\GDO_UserActivation;
use GDO\User\GDO_User;

final class Module_IP2City extends GDO_Module
{

    public int $priority = 75;

    /**
     * @throws GDO_DBException
     */
    public function onInstall(): void
    {
        Install::ip2city();
    }

    public function onLoadLanguage(): void
    {
        $this->loadLanguage('lang/ip2city');
    }

    public function getDependencies(): array
    {
        return [
            'Address',
            'Country',
            'Cronjob',
        ];
    }

    public function getClasses(): array
    {
        return [
            GDO_IP2City::class,
        ];
    }

    public function getConfig(): array
    {
        return [
            GDT_Checkbox::make('detect_city_on_signup')->notNull()->initial('1'),
        ];
    }

    public function getUserConfig(): array
    {
        return [
            GDT_City::make('detected_city')
        ];
    }

    public function cfgDetectOnSignup(): bool
    {
        return $this->getConfigValue('detect_city_on_signup');
    }

    /**
     * @throws GDO_DBException
     */
    public function hookUserActivated(GDO_User $user, GDO_UserActivation $activation = null): void
    {
        if ($this->cfgDetectOnSignup())
        {
            $ip = GDT_IP::current();
            $city = $this->detectIP($ip);
            $this->saveUserSetting($user, 'detected_city', $city);
        }
    }

    /**
     * @throws GDO_DBException
     */
    public function detectIP(string $ip): ?GDO_IP2City
    {
        return GDO_IP2City::for($ip);
    }


}
