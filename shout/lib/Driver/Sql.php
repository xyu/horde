<?php
/**
 * Provides the SQL backend driver for the Shout application.
 *
 * Copyright 2009-2010 Alkaloid Networks LLC (http://projects.alkaloid.net)
 *
 * See the enclosed file COPYING for license information (BSD). If you
 * did not receive this file, see
 * http://www.opensource.org/licenses/bsd-license.php.
 *
 * @author  Ben Klang <ben@alkaloid.net>
 * @package Shout
 */
class Shout_Driver_Sql extends Shout_Driver
{
    /**
     * Handle for the current database connection.
     * @var object $_db
     */
    protected $_db = null;

    /**
     * Handle for the current writable database connection.
     * @var object $_db
     */
    protected $_write_db = null;

    /**
     * Boolean indicating whether or not we're connected to the LDAP
     * server.
     * @var boolean $_connected
     */
    protected $_connected = false;


    /**
    * Constructs a new Shout LDAP driver object.
    *
    * @param array  $params    A hash containing connection parameters.
    */
    public function __construct($params = array())
    {
        parent::__construct($params);
        $this->_connect();
    }

    public function getAccounts()
    {
        $this->_connect();

        $sql = 'SELECT name, code FROM accounts';
        $vars = array();

        $msg = 'SQL query in Shout_Driver_Sql#getAccounts(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_db->query($sql, $vars);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if ($row instanceof PEAR_Error) {
            throw new Shout_Exception($row);
        }

        $accounts = array();
        while ($row && !($row instanceof PEAR_Error)) {
            $accounts[$row['code']] = $row['name'];
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        }

         $result->free();
         return $accounts;
    }

    public function saveAccount($code, $name)
    {
        $this->_connect();

        if (isset($details['oldname'])) {
            if (!isset($menus[$details['oldname']])) {
                throw new Shout_Exception(_("Old account not found.  Edit aborted."));
            } else {
                throw new Shout_Exception(_("Unsupported operation."));
                $sql = 'UPDATE accounts SET code = ?, name =? WHERE code = ?';
            }
        } else {
            $sql = 'INSERT INTO accounts (code, name) VALUES (?,?)';
        }

        $vars = array($code, $name);

        $msg = 'SQL query in Shout_Driver_Sql#getAccounts(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_db->query($sql, $vars);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

    }

    public function getMenus($account)
    {
        $this->_connect();

        $sql = 'SELECT accounts.code AS account, menus.name AS name, ' .
               'menus.description AS description, menus.soundfile AS soundfile ' .
               'FROM menus INNER JOIN accounts ON menus.account_id = accounts.id ' .
               'WHERE accounts.code = ?';
        $values = array($account);

        $msg = 'SQL query in Shout_Driver_Sql#getMenus(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_db->query($sql, $values);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if ($row instanceof PEAR_Error) {
            throw new Shout_Exception($row);
        }

        $menus = array();
        while ($row && !($row instanceof PEAR_Error)) {
            $menu = $row['name'];
            $menus[$menu] = array(
                'name' => $menu,
                'description' => $row['description'],
                'soundfile' => $row['soundfile']
            );
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        }
        $result->free();
        return $menus;
    }

    public function saveMenu($account, $details)
    {
        $menus = $this->getMenus($account);
        if (isset($details['oldname'])) {
            if (!isset($menus[$details['oldname']])) {
                throw new Shout_Exception(_("Old menu not found.  Edit aborted."));
            } else {
                $sql = 'UPDATE menus SET name = ?, description = ?, ' .
                       'soundfile = ? WHERE account_id = (SELECT id FROM ' .
                       'WHERE code = ?) AND name = ?';
                $values = array($details['name'], $details['description'],
                                $details['soundfile'], $account,
                                $details['oldname']);
            }
        } else {
            $sql = "INSERT INTO menus (account_id, name, description, soundfile) " .
                   "VALUES ((SELECT id FROM accounts WHERE code = ?), ?, ?, ?)";
            $values = array($account, $details['name'],
                            $details['description'], $details['soundfile']);
        }

        $msg = 'SQL query in Shout_Driver_Sql#saveMenu(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_write_db->query($sql, $values);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        return true;
    }

    public function getMenuActions($account, $menu)
    {
        static $menuActions;
        if (!empty($menuActions[$menu])) {
            return $menuActions[$menu];
        }

        $sql = "SELECT accounts.code AS account, menus.name AS description, " .
               "actions.name AS action, menu_entries.digit AS digit, " .
               "menu_entries.args AS args FROM menu_entries " .
               "INNER JOIN menus ON menu_entries.menu_id = menus.id " .
               "INNER JOIN actions ON menu_entries.action_id = actions.id " .
               "INNER JOIN accounts ON menus.account_id = accounts.id " .
               "WHERE accounts.code = ? AND menus.name = ?";
        $values = array($account, $menu);

        $msg = 'SQL query in Shout_Driver_Sql#getMenuActions(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_db->query($sql, $values);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if ($row instanceof PEAR_Error) {
            throw new Shout_Exception($row);
        }

        $menuActions[$menu] = array();
        while ($row && !($row instanceof PEAR_Error)) {
            $menuActions[$menu][$row['digit']] = array(
                'digit' => $row['digit'],
                'action' => $row['action'],
                'args' => Horde_Yaml::load($row['args'])
            );
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        }
        $result->free();

        return $menuActions[$menu];
    }

    public function saveMenuAction($account, $menu, $digit, $action, $args)
    {
        // Remove any existing action
        $sql = 'DELETE FROM menu_entries WHERE menu_id = ' .
               '(SELECT id FROM menus WHERE account_id = ' .
               '(SELECT id FROM accounts WHERE code = ?) AND name = ?) ' .
               'AND digit = ?';
        $values = array($account, $menu, $digit);
        $msg = 'SQL query in Shout_Driver_Sql#saveMenuAction(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_write_db->query($sql, $values);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        $sql = 'INSERT INTO menu_entries (menu_id, digit, action_id, args) ' .
               'VALUES((SELECT id FROM menus WHERE account_id = ' .
               '(SELECT id FROM accounts WHERE code = ?) AND name = ?), ?, ' .
               '(SELECT id FROM actions WHERE name = ?), ?)';
        $yamlargs = Horde_Yaml::dump($args);
        $values = array($account, $menu, $digit, $action, $yamlargs);
        Horde::logMessage("Data: ".print_r($values, true), 'ERR');
        $msg = 'SQL query in Shout_Driver_Sql#saveMenuAction(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $result = $this->_write_db->query($sql, $values);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        return true;
    }

    /**
     * Get a list of devices for a given account
     *
     * @param string $account    Account in which to search for devicess
     *
     * @return array  Array of devices within this account with their information
     *
     * @access private
     */
    public function getDevices($account)
    {
        $sql = 'SELECT id, name, alias, callerid, context, mailbox, host, ' .
               'permit, nat, secret, disallow, allow ' .
               'FROM %s WHERE accountcode = ?';
        $sql = sprintf($sql, $this->_params['table']);
        $args = array($account);
        $msg = 'SQL query in Shout_Driver_Sql#getDevices(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $sth = $this->_db->prepare($sql);
        $result = $this->_db->execute($sth, $args);
        if ($result instanceof PEAR_Error) {
            throw new Shout_Exception($result);
        }

        $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        if ($row instanceof PEAR_Error) {
            throw new Shout_Exception($row);
        }

        $devices = array();
        while ($row && !($row instanceof PEAR_Error)) {
            // Asterisk uses the "name" field to indicate the registration
            // identifier.  We use the field "alias" to put a friendly name on
            // the device.  Thus devid => name and name => alias
            $devid = $row['name'];
            $row['devid'] = $devid;
            $row['name'] = $row['alias'];
            unset($row['alias']);

            // Trim off the context from the mailbox number
            list($row['mailbox']) = explode('@', $row['mailbox']);

            // The UI calls the 'secret' a 'password'
            $row['password'] = $row['secret'];
            unset($row['secret']);

            // Hide the DB internal ID from the front-end
            unset($row['id']);

            $devices[$devid] = $row;

            /* Advance to the new row in the result set. */
            $row = $result->fetchRow(DB_FETCHMODE_ASSOC);
        }

        $result->free();
        return $devices;
    }

    /**
     * Save a device (add or edit) to the backend.
     *
     * @param string $account  The account in which this device is valid
     * @param string $devid    Device ID to save
     * @param array $details      Array of device details
     */
    public function saveDevice($account, $devid, &$details)
    {
        // Check permissions and possibly update the authentication tokens
        parent::saveDevice($account, $devid, $details);

        // See getDevices() for an explanation of these conversions
        $details['alias'] = $details['name'];
        $details['name'] = $details['devid'];
        unset($details['devid']);
        $details['mailbox'] .= '@' . $account;

        // Prepare the SQL query and arguments
        $args = array(
            $details['name'],
            $account,
            $details['callerid'],
            $details['mailbox'],
            $details['password'],
            $account,
            $details['alias'],
        );

        if (!empty($devid)) {
            // This is an edit
            $details['name'] = $details['devid'];
            $sql = 'UPDATE %s SET name = ?, accountcode = ?, callerid = ?, ' .
                   'mailbox = ?, secret = ?, context = ?, alias = ?, ' .
                   'canreinvite = "no", nat = "yes", type = "peer", ' .
                   'host = "dynamic" WHERE name = ?';
            $args[] = $devid;
        } else {
            // This is an add.  Generate a new unique ID and secret
            $sql = 'INSERT INTO %s (name, accountcode, callerid, mailbox, ' .
                   'secret, context, alias, canreinvite, nat, type, host) ' .
                   'VALUES (?, ?, ?, ?, ?, ?, ?, "no", "yes", "peer", ' .
                   '"dynamic")';

        }
        $sql = sprintf($sql, $this->_params['table']);

        $msg = 'SQL query in Shout_Driver_Sql#saveDevice(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $sth = $this->_write_db->prepare($sql);
        $result = $this->_write_db->execute($sth, $args);
        if ($result instanceof PEAR_Error) {
            $msg = $result->getMessage() . ': ' . $result->getDebugInfo();
            Horde::logMessage($msg, 'ERR');
            throw new Shout_Exception(_("Internal database error.  Details have been logged for the administrator."));
        }

        return true;
    }

    public function deleteDevice($account, $devid)
    {
        parent::deleteDevice($account, $devid);

        $sql = 'DELETE FROM %s WHERE devid = ?';
        $sql = sprintf($sql, $this->_params['table']);
        $values = array($devid);

        $msg = 'SQL query in Shout_Driver_Sql#deleteDevice(): ' . $sql;
        Horde::logMessage($msg, 'DEBUG');
        $res = $this->_write_db->query($sql);

        if ($res instanceof PEAR_Error) {
            throw new Shout_Exception($res->getMessage(), $res->getCode());
        }

        return true;
    }

    /**
     * Get a list of users valid for the accounts
     *
     * @param string $account Account on which to search
     *
     * @return array User information indexed by voice mailbox number
     */
    public function getExtensions($account)
    {
        throw new Shout_Exception("Not implemented yet.");
    }

    /**
     * Save a user to the LDAP tree
     *
     * @param string $account Account to which the user should be added
     *
     * @param string $extension Extension to be saved
     *
     * @param array $userdetails Phone numbers, PIN, options, etc to be saved
     *
     * @return TRUE on success, PEAR::Error object on error
     */
    public function saveExtension($account, $extension, $userdetails)
    {
        parent::saveExtension($account, $extension, $details);
        throw new Shout_Exception("Not implemented.");
    }

    /**
     * Deletes a user from the LDAP tree
     *
     * @param string $account Account to delete the user from
     * @param string $extension Extension of the user to be deleted
     *
     * @return boolean True on success, PEAR::Error object on error
     */
    public function deleteExtension($account, $extension)
    {
        throw new Shout_Exception("Not implemented.");
    }

    /**
     * Attempts to open a persistent connection to the SQL server.
     *
     * @throws Shout_Exception
     */
    protected function _connect()
    {
        if ($this->_connected) {
            return;
        }

        Horde::assertDriverConfig($this->_params, $this->_params['class'],
                                  array('phptype', 'charset'));

        if (!isset($this->_params['database'])) {
            $this->_params['database'] = '';
        }
        if (!isset($this->_params['username'])) {
            $this->_params['username'] = '';
        }
        if (!isset($this->_params['hostspec'])) {
            $this->_params['hostspec'] = '';
        }

        /* Connect to the SQL server using the supplied parameters. */
        $this->_write_db = DB::connect($this->_params,
                                       array('persistent' => !empty($this->_params['persistent'])));
        if ($this->_write_db instanceof PEAR_Error) {
            throw new Shout_Exception($this->_write_db);
        }

        // Set DB portability options.
        switch ($this->_write_db->phptype) {
        case 'mssql':
            $this->_write_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS | DB_PORTABILITY_RTRIM);
            break;

        default:
            $this->_write_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS);
        }

        /* Check if we need to set up the read DB connection seperately. */
        if (!empty($this->_params['splitread'])) {
            $params = array_merge($this->_params, $this->_params['read']);
            $this->_db = DB::connect($params,
                                     array('persistent' => !empty($params['persistent'])));
            if ($this->_db instanceof PEAR_Error) {
                throw Shout_Exception($this->_db);
            }

            // Set DB portability options.
            switch ($this->_db->phptype) {
            case 'mssql':
                $this->_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS | DB_PORTABILITY_RTRIM);
                break;

            default:
                $this->_db->setOption('portability', DB_PORTABILITY_LOWERCASE | DB_PORTABILITY_ERRORS);
            }

        } else {
            /* Default to the same DB handle for the writer too. */
            $this->_db = $this->_write_db;
        }

        $this->_connected = true;
    }

    /**
     * Disconnects from the SQL server and cleans up the connection.
     */
    protected function _disconnect()
    {
        if ($this->_connected) {
            $this->_connected = false;
            $this->_db->disconnect();
            $this->_write_db->disconnect();
        }
    }

}
