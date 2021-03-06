<div id="menu">
 <div class="leftFloat">
<?php
    $menu = new Horde_Menu(Horde_Menu::MASK_NONE);
    try {
        foreach ($registry->callByPackage('horde', 'admin_list') as $method => $val) {
            if ($registry->isAdmin() || 
                $injector->getInstance('Horde_Perms')->hasPermission('horde:administration:' . $method, $registry->getAuth(), Horde_Perms::SHOW)) {
                $menu->add(Horde::url($registry->applicationWebPath($val['link'])), $val['name'], $val['icon'], '');
            }
        }
    } catch (Horde_Exception $e) {}

    echo $menu->render();
?>
 </div>
</div>
<?php require HORDE_BASE . '/services/sidebar.php'; ?>
<?php $GLOBALS['notification']->notify(array('listeners' => 'status'));
