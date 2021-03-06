<?php
/**
 * Prepare the test setup.
 */
require_once dirname(__FILE__) . '/Base.php';

/**
 * Copyright 2010-2012 Horde LLC (http://www.horde.org/)
 *
 * @author     Jan Schneider <jan@horde.org>
 * @category   Horde
 * @package    Share
 * @subpackage UnitTests
 * @license    http://www.horde.org/licenses/lgpl21 LGPL 2.1
 */
class Horde_Share_Sql_MysqlTest extends Horde_Share_Test_Sql_Base
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('mysql')) {
            return;
        }
        $config = self::getConfig('SHARE_SQL_MYSQL_TEST_CONFIG',
                                  dirname(__FILE__) . '/..');
        if ($config && !empty($config['share']['sql']['mysql'])) {
            self::$db = new Horde_Db_Adapter_Mysql($config['share']['sql']['mysql']);
            parent::setUpBeforeClass();
        }
    }
}
