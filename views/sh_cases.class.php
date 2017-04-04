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
 * Syntaxhighlighter for sh scripts
 *
 * @package mod_vpl
 * @copyright 2009 Juan Carlos Rodríguez-del-Pino
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author Juan Carlos Rodriguez-del-Pino
 **/

require_once dirname(__FILE__).'/sh_text.class.php';

class vpl_sh_cases extends vpl_sh_text{
    protected $predefined_vars;
    protected function is_identifier_char($c){
        return ($c >= 'a' && $c <= 'z') || ($c >= 'A' && $c <= 'Z')
        || ($c >= '0' && $c <= '9') || ($c == '$') || ($c == '_');
    }
    protected function show_pending(&$rest){
        if(array_key_exists($rest  , $this->reserved)){
            $this->initTag(self::C_RESERVED);
            parent::show_pending($rest);
            echo self::ENDTAG;
        }else if(array_key_exists($rest  , $this->predefined_vars)){
            $this->initTag(self::C_VARIABLE);
            parent::show_pending($rest);
            echo self::ENDTAG;
        }else{
            parent::show_pending($rest);
        }
        $rest ='';
    }
    function __construct(){
        $list =array('Classe', 'Class', 'ValsInCons',
                    'Method', 'ValsIn', 'ValsOut',
		    'Grade', 'JunitFiles', 'ShowInput', 'MessageErr', 'ExceptionOut',
	    	    'MethodGet', 'TimeLimitInSec');
        foreach ($list as $word) {
            $this->reserved[$word]=1;
        }
        $list=array();
        foreach ($list as $word) {
            $this->predefined_vars[$word]=1;
        }
        parent::__construct();
    }
    const NORMAL=0;
    const IN_STRING=1;
    const IN_DSTRING=2;
    const IN_CSTRING=3;
    const IN_COMMENT=4;
    function print_file($filename, $filedata, $showln=true){
        $this->begin($filename,$showln);
        $state=self::NORMAL;
        $pending='';
        $l = strlen($filedata);
        if($l){
            $this->show_line_number();
        }
        $current='';
        $previous='';
        for($i=0;$i<$l;$i++){
            $previous=$current;
            $current=$filedata[$i];
            if($i < ($l-1)) {
                $next = $filedata[$i+1];
            }else{
                $next ='';
            }
            if($current == self::CR){
                if($next == self::LF) {
                    continue;
                }else{
                    $current = self::LF;
                }
            }

            switch($state){
                case self::NORMAL:{
                    if($current == '#') {//Begin coment
                        $this->show_pending($pending);
                        $this->initTag(self::C_COMMENT);
                        $pending = $current;
                        $state=self::IN_COMMENT;
                    }/*else if($current == '"') {
                        $this->show_pending($pending);
                        $this->initTag(self::C_STRING);
                        $pending = $current;
                        $state=self::IN_DSTRING;
                    }else if($current == '\\') {
                        $pending .= $current.$next;
                        $current = $next;
                        $i++;
                    }else if($current == "'") {
                        $this->show_pending($pending);
                        $this->initTag(self::C_STRING);
                        $pending = $current;
                        $state=self::IN_STRING;
		    }*/else if($current == '$') {
                        $this->show_pending($pending);
                        if($next == '\''){
                            $this->initTag(self::C_STRING);
                            $pending = $current.$next;
                            $current =$next;
                            $state=self::IN_CSTRING;
                            $i++;
                        }
                        else if(($next >='0' && $next <='9') || $next == '*' || $next == '@'
                        || $next == '#' || $next == '?' || $next == '-' || $next == '$'
                        || $next == '!' || $next == '_'){ //Parms
                            $this->show_pending($pending);
                            $this->initTag(self::C_VARIABLE);
                            $this->show_text($current.$next);
                            $this->endTag();
                            $current =$next;
                            $i++;
                        }else{
                            $pending .= $current;
                        }
                    } else if($current == self::LF) {
                        $this->show_pending($pending);;
                        $this->show_text($current);
                        $this->show_line_number();
                    } else if($this->is_identifier_char($current)) {
                        $pending .= $current;
                    }
                    else{
                        $this->show_pending($pending);
                        $this->show_text($current);
                    }
                    break;
                }
                case self::IN_STRING:{
                    if($current == "'"){
                        $this->show_pending($pending);
                        $this->show_text($current);
                        $this->endTag();
                        $state=self::NORMAL;
                    }else{
                        $pending .= $current;
                    }
                    break;
                }
                case self::IN_DSTRING:{
                    if($current == '"'){
                        if($pending>'' && $pending[0] == '$'){
                            $this->initTag(self::C_VARIABLE);
                            $this->show_pending($pending);
                            $this->endTag();
                        }else{
                            $this->show_pending($pending);
                        }
                        $this->show_text($current);
                        $this->endTag();
                        $state = self::NORMAL;
                    }else if($current == '\\'){
                        $pending.= $current.$next;
                        $current = $next;
                        $i++;
                    }else if($current=='$') {
                        if($pending>'' && $pending[0] == '$'){
                            $this->initTag(self::C_VARIABLE);
                            $this->show_pending($pending);
                            $this->endTag();
                        }else{
                            $this->show_pending($pending);
                        }
                        $pending .= $current;
                    } else{
                        if($pending>'' && $pending[0] == '$' && !$this->is_identifier_char($current)) {
                            $this->initTag(self::C_VARIABLE);
                            $this->show_pending($pending);
                            $this->endTag();
                        }
                        $pending .= $current;
                    }
                    break;
                }
                case self::IN_CSTRING:{
                    if($current == '\''){
                        $this->show_pending($pending);
                        $this->show_text($current);
                        $this->endTag();
                        $state=self::NORMAL;
                    }
                    else if($current == '\\'){
                        $pending .= $current.$next;
                        $current = $next;
                        $i++;
                    }
                    break;
                }
                case self::IN_COMMENT:{
                    if($current == self::LF){
                        $this->show_pending($pending);
                        $this->endTag();
                        $this->show_text($current);
                        $this->show_line_number();
                        $state=self::NORMAL;
                    } else{
                        $pending .= $current;
                    }
                    break;
                }
            }
        }
        $this->show_pending($pending);
        if($state != self::NORMAL){
            $this->endTag();
        }
        $this->end();
    }
}
