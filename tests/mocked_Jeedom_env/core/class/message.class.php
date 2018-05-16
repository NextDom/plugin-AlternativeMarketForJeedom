<?php

/**
 * Mock de la classe message
 */
class message
{
    /**
     * Mock de la méthode pour afficher un message
     */
    public static function add($plugin, $msg, $a, $b)
    {
        MockedActions::add('message_add', array('message' => $msg, 'plugin' => $plugin));
    }
}
