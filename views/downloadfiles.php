<?php
// This file is part of VPL for Moodle - http://vpl.dis.ulpgc.es/
//
// VPL for Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// VPL for Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with VPL for Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Download required/initial files
 *
 * @package mod_vpl
 * @copyright 2012 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

require_once(dirname(__FILE__).'/../../../config.php');
require_once(dirname(__FILE__).'/../locallib.php');
require_once(dirname(__FILE__).'/../vpl.class.php');

try {
    require_login();
    $id = required_param( 'id', PARAM_INT );
    $type = required_param( 'type', PARAM_ALPHANUMEXT );
    $vpl = new mod_vpl( $id );
    $vpl->password_check();
    $vpl->network_check();
    if ($type=='execution'){
        $vpl->require_capability( VPL_MANAGE_CAPABILITY );
    }elseif (! $vpl->is_visible()) {
        notice( get_string( 'notavailable' ) );
    }
    $filegroup = $vpl->get_fgm($type);
    $filegroup->download_files( $vpl->get_printable_name() );
    die();
} catch ( Exception $e ) {
    notice( $e->getMessage() );
}
