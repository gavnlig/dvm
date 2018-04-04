<?php

namespace Util;

class System {

    /**
     * @return string Full path of the home directory for the current php context.
     */
    public static function homePath() {
        //This function is taken from the Drush project.
        // Cannot use $_SERVER superglobal since that's empty during UnitUnishTestCase
        // getenv('HOME') isn't set on Windows and generates a Notice.
        $home = @$_SERVER['HOME'] ?: @$_SERVER['USERPROFILE'] ?: getenv('HOME') ?: getenv('USERPROFILE');
        if (empty($home) && !empty(@$_SERVER['HOMEDRIVE']) && !empty(@$_SERVER['HOMEPATH'])) {
            // home on windows
            $home = sprintf('%s%s', $_SERVER['HOMEDRIVE'], $_SERVER['HOMEPATH']);
        }
        $home = rtrim($home, DIRECTORY_SEPARATOR);
        
        return empty($home) ? '.' : $home;
    }
}
