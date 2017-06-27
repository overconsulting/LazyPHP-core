<?php
/**
 * File Core\Session.php
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */

namespace Core;

use Helper\Bootstrap;

/**
 * Class to manage session
 *
 * @category Core
 * @package  Netoverconsulting
 * @author   Loïc Dandoy <ldandoy@overconsulting.net>
 * @license  GNU
 * @link     http://overconsulting.net
 */
class Session
{
    /**
     * Init
     *
     * @return void
     */
    public static function init()
    {
        session_start();
    }

    /**
     * Add a flash message
     *
     * @param string $message Message to display
     * @param string $type Message type (danger|success|warning|info)
     * @param bool $canClose
     *
     * @return void
     */
    public static function addFlash($message, $type, $canClose = true)
    {
        $flash = self::get('flash');
        if ($flash === null) {
            $flash = array();
        }

        $flash[] = array(
            'message' => $message,
            'type' => $type,
            'canClose' => $canClose
        );

        self::set('flash', $flash);
    }

    /**
     * Get the html for flash messages
     *
     * @return string
     */
    public static function flash()
    {
        $html = '';

        $flash = self::get('flash');
        if ($flash !== null) {
            $html .= '<div class="container"><div class="row"><div class="col-md-12">';
            foreach ($flash as $f) {
                $html .= Bootstrap::alert($f['message'], $f['type'], $f['canClose']);
            }
            $html .= '</div></div></div>';
            self::remove('flash');
        }
        return $html;
    }

    /**
     * Add/Set a session variable
     *
     * @return void
     */
    public static function set($name, $value = null)
    {
        $_SESSION[$name] = $value;
    }

    /**
     * Get a session variable
     *
     * @return mixed
     */
    public static function get($name)
    {
        return isset($_SESSION[$name]) ? $_SESSION[$name] : null;
    }

    /**
     * Get a session variable
     *
     * @return mixed
     */
    public static function getAll()
    {
        return isset($_SESSION) ? $_SESSION : null;
    }

    /**
     * Remove a session variable
     *
     * @return void
     */
    public static function remove($name)
    {
        unset($_SESSION[$name]);
    }

    /**
     * Get and remove a session variable
     *
     * @return mixed
     */
    public static function getAndRemove($name)
    {
        $value = self::get($name);
        self::remove($name);
        return $value;
    }

    /**
     * Check if use is connected
     * @return bool
     */
    public static function isConnected($search)
    {
        if (Session::get($search)) {
            return true;
        }
        return false;
    }

    /**
     * Get the session id
     * @return string
     */
    public static function getSessionId()
    {
        return session_id();
    }
}