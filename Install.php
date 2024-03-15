<?php
namespace GDO\IP2City;

use GDO\Address\GDO_City;
use GDO\Address\GDT_ZIP;
use GDO\Core\GDO_DBException;
use GDO\Core\GDT_CreatedAt;
use GDO\Core\GDT_String;
use GDO\Core\GDT_UInt;
use GDO\Country\GDT_Country;
use GDO\DB\Database;
use GDO\IP2City\Method\Import;
use GDO\Util\CSV;

final class Install
{

    /**
     * @throws GDO_DBException
     */
    public static function ip2city(): void
    {
        if (self::isEmpty())
        {
            Import::make()->run();
        }
    }

    /**
     * @throws GDO_DBException
     */
    public static function installIPv4(): void
    {
        $mod = Module_IP2City::instance();
        $path = $mod->filePath('ip-location-db/dbip-city/dbip-city-ipv4.csv.gz');
        self::installIPvX($path);
    }


    /**
     * @throws GDO_DBException
     */
    public static function installIPv6(): void
    {
        $mod = Module_IP2City::instance();
        $path = $mod->filePath('ip-location-db/dbip-city/dbip-city-ipv6.csv.gz');
        self::installIPvX($path);
    }


    /**
     * #ip_range_start, ip_range_end, country_code, state1, state2, city, postcode, latitude, longitude, timezone
     * @throws GDO_DBException
     */
    public static function installIPvX(string $path): void
    {
        try
        {
            $columns = GDO_IP2City::table()->gdoColumnsOnly('cip_min', 'cip_max', 'cip_city', 'cip_country');
            $bulk = [];
            Database::instance()->disableForeignKeyCheck();
            if ($fp = gzopen($path, 'r'))
            {
                while ($row = CSV::parseGZLine($fp))
                {
                    $ipmin = $row[0];
                    $ipmax = $row[1];
                    $country = $row[2];
                    $cityname = $row[5];
                    $zip = $row[6];
                    $lat = $row[7];
                    $lng = $row[8];
//                    if (!$city = GDO_City::getByVars(['city_name' => $cityname, 'city_country' => $country]))
//                    {
//                        $city = GDO_City::blank([
//                            'city_name' => $cityname,
//                            'city_country' => $country,
//                            'city_zip' => $zip,
//                            'city_pos_lat' => $lat,
//                            'city_pos_lng' => $lng,
//                        ])->insert();
//                    }
                    $bulk[] = [
                        inet_pton($ipmin),
                        inet_pton($ipmax),
                        $cityname,
                        $country,
                    ];
                    if (count($bulk) === 200)
                    {
                        GDO_IP2City::bulkReplace($columns, $bulk);
                        $bulk = [];
                    }
                }
            }
            GDO_IP2City::bulkReplace($columns, $bulk);
        }
        catch (\Throwable $ex)
        {
            var_dump($row);
        }
        finally
        {
            Database::instance()->enableForeignKeyCheck();
            if (isset($fp))
            {
                gzclose($fp);
            }
        }
    }

    /**
     * @throws GDO_DBException
     */
    private static function isEmpty(): bool
    {
        return GDO_IP2City::table()->countWhere() === 0;
    }

}
