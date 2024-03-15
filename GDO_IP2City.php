<?php
namespace GDO\IP2City;

use GDO\Address\GDT_City;
use GDO\Core\GDO;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_Char;
use GDO\Core\GDT_String;
use GDO\Maps\GDT_Lat;
use GDO\Maps\GDT_Lng;
use GDO\Maps\GDT_Position;
use GDO\Net\GDT_PackedIP;

final class GDO_IP2City extends GDO
{

    /**
     * @throws GDO_DBException
     */
    public static function for(string $ip): ?self
    {
        $ipn = quote(inet_pton($ip));
        return self::table()->getWhere("cip_min<=$ipn AND cip_max>=$ipn");

    }

    public function gdoColumns(): array
    {
        return [
            GDT_PackedIP::make('cip_min')->primary()->notNull(),
            GDT_PackedIP::make('cip_max')->primary()->notNull(),
            GDT_String::make('cip_city')->max(196)->notNull(),
            GDT_Char::make('cip_country')->length(2)->notNull(),
            GDT_Lat::make('cip_pos_lat'),
            GDT_Lng::make('cip_pos_lng'),
        ];
    }

    public function getCityName(): string
    {
        return $this->gdoVar('cip_city');
    }

    public function getCountryCode(): string
    {
        return $this->gdoVar('cip_country');
    }

}
