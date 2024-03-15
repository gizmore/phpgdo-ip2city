<?php
namespace GDO\IP2City\Method;

use GDO\Core\GDO_DBException;
use GDO\Cronjob\MethodCronjob;
use GDO\IP2City\Install;

final class Import extends MethodCronjob
{

    public function runAt(): string
    {
        return $this->runDailyAt(4);
    }

    /**
     * @throws GDO_DBException
     */
    public function run(): void
    {
        Install::installIPv4();
        Install::installIPv6();
    }

}
