<?php
use Mattsadev\Tones\Main;

/**
 * Grab the Main object and return it.
 * Wrapper for Main::instance().
 *
 * @since 1.0.0
 * @return Main Singleton instance of plugin class.
 */
function tones(): Main {
    return Main::instance();
}