<?php
/**
 * Add IMAP alert notifications to the stack.
 *
 * Copyright 2010-2011 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file COPYING for license information (GPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/gpl.html.
 *
 * @author   Michael Slusarz <slusarz@horde.org>
 * @category Horde
 * @license  http://www.fsf.org/copyleft/gpl.html GPL
 * @package  IMP
 */
class IMP_Notification_Handler_Decorator_ImapAlerts
extends Horde_Notification_Handler_Decorator_Base
{
    /**
     * Listeners are handling their messages.
     *
     * @param Horde_Notification_Handler  $handler   The base handler object.
     * @param Horde_Notification_Listener $listener  The Listener object that
     *                                               is handling its messages.
     */
    public function notify(Horde_Notification_Handler $handler,
                           Horde_Notification_Listener $listener)
    {
        if (($listener instanceof Horde_Notification_Listener_Status) &&
            ($ob = $GLOBALS['injector']->getInstance('IMP_Factory_Imap')->create()) &&
            $ob->ob) {
            /* Display IMAP alerts. */
            foreach ($ob->alerts() as $alert) {
                $handler->push($alert, 'horde.warning');
            }
        }
    }

}
