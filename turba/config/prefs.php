<?php
/**
 * See horde/config/prefs.php for documentation on the structure of this file.
 *
 * IMPORTANT: Local overrides should be placed in pref.local.php, or
 * prefs-servername.php if the 'vhosts' setting has been enabled in Horde's
 * configuration.
 */

$prefGroups['addressbooks'] = array(
    'column' => _("Display Preferences"),
    'label' => _("Address Books"),
    'desc' => _("Choose which address books to use."),
    'members' => array('default_dir', 'addressbookselect', 'sync_books'),
);

$prefGroups['columns'] = array(
    'column' => _("Display Preferences"),
    'label' => _("Column Preferences"),
    'desc' => _("Select which fields to display in the address lists."),
    'members' => array('columnselect'),
);

$prefGroups['display'] = array(
    'column' => _("Display Preferences"),
    'label' => _("Display"),
    'desc' => _("Select view to display by default and paging preferences."),
    'members' => array('initial_page', 'maxpage', 'perpage'),
);

$prefGroups['format'] = array(
    'column' => _("Display Preferences"),
    'label' => _("Name Format"),
    'desc' => _("Select which format to display names."),
    'members' => array('name_format', 'name_sort'),
);

// Address Book selection widget
$_prefs['addressbookselect'] = array(
    'type' => 'special',
);

// Address books to be displayed in the address book selection widget
// and in the Browse menu item.  The address book name is stored using
// the source key from backends.php (e.g. "localsql").
// You can provide default values this way:
//   'value' => json_encode(array('source_one', 'source_two'))
// If 'value' is empty (''), all address books that the user has permissions
// to will be listed.
$_prefs['addressbooks'] = array(
    'value' => ''
);

// Address books use for synchronization
$_prefs['sync_books'] = array(
    'value' => 'a:0:{}',
    'type' => 'multienum',
    'desc' => _("Select the address books that should be used for synchronization with external devices:"),
);

// Columns selection widget
$_prefs['columnselect'] = array(
    'type' => 'special'
);

// Columns to be displayed in Browse and Search results, with entries
// for the columns displayed for each address book.  Separate address
// book stanzas with \n and columns with \t. The "name" column is
// currently always displayed first and so cannot be modified here.
// Double quotes MUST be used as in the example.
$_prefs['columns'] = array(
    'value' => "localsql\temail"
);

// user preferred sorting column
// serialized array of hashes containing 'field' and 'ascending' keys
$_prefs['sortorder'] = array(
    'value' => 'a:1:{i:0;a:2:{s:5:"field";s:8:"lastname";s:9:"ascending";b:1;}}'
);

// number of maximum pages and items per page
$_prefs['maxpage'] = array(
    'value' => 10,
    'type' => 'number',
    'desc' => _("Maximum number of pages"),
);

$_prefs['perpage'] = array(
    'value' => 20,
    'type' => 'number',
    'desc' => _("Number of items per page"),
);

// the page to display.  Either 'browse.php' or 'search.php'
$_prefs['initial_page'] = array(
    'value' => 'search.php',
    'type' => 'enum',
    'desc' => _("View to display by default:"),
    'enum' => array(
        'browse.php' => _("Address Book Listing"),
        'search.php' => _("Search")
    )
);

// the format to display names.  Either 'last_first' or 'first_last'
$_prefs['name_format'] = array(
    'value' => 'none',
    'type' => 'enum',
    'desc' => _("Select the format used to <em>display</em> names:"),
    'enum' => array(
        'last_first' => _("\"Lastname, Firstname\" (ie. Doe, John)"),
        'first_last' => _("\"Firstname Lastname\"  (ie. John Doe)"),
        'none' => _("no formatting")
    )
);

// the format to sort names.  Either 'last_first' or 'first_last'
$_prefs['name_sort'] = array(
    'value' => 'none',
    'type' => 'enum',
    'desc' => _("Select the format used to <em>sort</em> names:"),
    'enum' => array(
        'last_first' => _("\"Lastname, Firstname\" (ie. Doe, John)"),
        'first_last' => _("\"Firstname Lastname\"  (ie. John Doe)"),
        'none' => _("no formatting")
    )
);

// Default directory
// Addressbook list array is dynamically built when prefs screen is displayed
$_prefs['default_dir'] = array(
    'value' => '',
    // 'value' => 'localsql',
    'type' => 'enum',
    'desc' => _("This will be the default address book when adding or importing contacts."),
);

// preference for holding any preferences-based addressbooks.
$_prefs['prefbooks'] = array(
    'value' => ''
);

// Personal contact.
$_prefs['own_contact'] = array(
    // The format is 'source_name;contact_id'.
    'value' => ''
);

/* Local overrides. */
if (file_exists(dirname(__FILE__) . '/prefs.local.php')) {
    include dirname(__FILE__) . '/prefs.local.php';
}
