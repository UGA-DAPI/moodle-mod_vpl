<?php
/**
 * @version		$Id: sh_java.class.php,v 1.7 2012-06-05 23:22:09 juanca Exp $
 * @package		VPL. Syntaxhighlighter for Java language
 * @copyright	2012 Juan Carlos Rodríguez-del-Pino
 * @license		http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author		Juan Carlos Rodríguez-del-Pino <jcrodriguez@dis.ulpgc.es>
 */

/**
 * vpl Syntaxhighlighter for Java code
 *
 * @author  Juan Carlos Rodriguez del Pino
 * @version $Id: sh_java.class.php,v 1.7 2012-06-05 23:22:09 juanca Exp $
 * @package vpl
 **/

require_once dirname(__FILE__).'/sh_c.class.php';

class vpl_sh_opl extends vpl_sh_c{
	function __construct(){
		parent::__construct();
		$added = array( 'all'=>true, 'and'=>true, 'assert'=>true, 'boolean'=>true, 'constraint'=>true, 'constraints'=>true, 'CP'=>true, 'CPLEX'=>true, 'cumulFunction'=>true, 'DBConnection'=>true, 'DBconnection'=>true, 'DBExecute'=>true, 'DBexecute'=>true, 'DBRead'=>true, 'DBread'=>true, 'DBUpdate'=>true, 'DBupdate'=>true, 'dexpr'=>true, 'diff'=>true, 'div'=>true, 'dvar'=>true, 'else'=>true, 'execute'=>true, 'false'=>true, 'float'=>true, 'float+'=>true, 'forall'=>true, 'from'=>true, 'if'=>true, 'in'=>true, 'include'=>true, 'infinity'=>true, 'int'=>true, 'int+'=>true, 'intensity'=>true, 'inter'=>true, 'interval'=>true, 'invoke'=>true, 'key'=>true, 'main'=>true, 'max'=>true, 'maximize'=>true, 'maxint'=>true, 'min'=>true, 'minimize'=>true, 'mod'=>true, 'notin'=>true, 'optional'=>true, 'or'=>true, 'ordered'=>true, 'piecewise'=>true, 'prepare'=>true, 'prod'=>true, 'pwlFunction'=>true, 'range'=>true, 'reversed'=>true, 'sequence'=>true, 'setof'=>true, 'SheetConnection'=>true, 'SheetRead'=>true, 'SheetWrite'=>true, 'size'=>true, 'sorted'=>true, 'stateFunction'=>true, 'stepFunction'=>true, 'stepwise'=>true, 'string'=>true, 'subjectto'=>true, 'sum'=>true, 'symdiff'=>true, 'to'=>true, 'true'=>true, 'tuple'=>true, 'types'=>true, 'union'=>true, 'using'=>true, 'with'=>true);
		$this->reserved= array_merge($this->reserved, $added);
	}
	function print_file($filename, $filedata, $showln=true){
		$this->begin($filename,$showln);
		$state = self::regular;
		$pending='';
		$first_no_space = '';
		$last_no_space = '';
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
			if($previous == self::LF){
				$last_no_space='';
				$first_no_space = '';
			}
			if($current == self::CR){
				if($next == self::LF) {
					continue;
				}else{
					$current = self::LF;
				}
			}
			if($current != ' ' && $current != "\t") {//Keep first and last char
				if($current != self::LF){
					$last_no_space=$current;
				}
				if($first_no_space == ''){
					$first_no_space = $current;
				}
			}
			switch($state){
				case self::in_comment:
					// Check end of block comment
					if($current=='*') {
						if($next=='/') {
							$state = self::regular;
							$pending .= '*/';
							$this->show_text($pending);
							$pending='';
							$this->endTag();
							$i++;
							continue 2;
						}
					}
					if($current == self::LF){
						$this->show_text($pending);
						$pending='';
						if($this->showln) { //Check to send endtag
							$this->endTag();
						}
						$this->show_line_number();
						if($this->showln) { //Check to send initTagtag
							$this->initTag(self::c_comment);
						}
					}else{
						$pending .= $current;
					}
					break;
				case self::in_linecomment:
					// Check end of comment
					if($current==self::LF){
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						$this->show_line_number();
						$state=self::regular;
					}else{
						$pending .= $current;
					}
					break;
				case self::in_macro:
					// Check end of macro
					if(!(($current >= 'a' && $current <= 'z') ||
					($current >= 'A' && $current <= 'Z') ||
					($current >= '0' && $current <= '9') ||
					$current=='_' || ord($current) > 127)){
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						if($current == self::LF){
							$this->show_line_number();
						}else{
							$this->show_text($current);
						}
						$state = self::regular;
					}else{
						$pending .= $current;
					}
					break;
				case self::in_string:
					// Check end of string
					if($current=='"' && $previous!='\\') {
						$pending .= '"';
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						$state = self::regular;
						break;
					}
					if($current==self::LF){
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						$this->show_line_number();
						$this->initTag(self::c_string);
					}else{
						$pending .= $current;
					}
					//discard two backslash
					if($current=='\\' && $previous=='\\'){
						$current=' ';
					}
					break;
				case self::in_char:
					// Check end of char
					if($current=='\'' && $previous!='\\') {
						$pending .= '\'';
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						$state = self::regular;
						break;
					}
					if($current==self::LF){
						$this->show_text($pending);
						$pending='';
						$this->endTag();
						$this->show_line_number();
						$this->initTag(self::c_string);
					}else{
						$pending .= $current;
					}
					//discard two backslash
					if($current=='\\' && $previous=='\\'){
						$current=' ';
					}
					break;
				case self::regular:
					if($current == '/') {
						if($next == '*') { // Begin block comments
							$state = self::in_comment;
							$this->show_pending($pending);
							$this->initTag(self::c_comment);
							$this->show_text('/*');
							$i++;
							continue 2;
						}
						if($next == '/'){ // Begin line comment
							if($i < ($l-2)) {
								$nextnext = $filedata[$i+2];
								if ($nextnext == '/') {
									$state = self::in_linecomment;
									$this->show_pending($pending);
									$this->initTag(self::c_commentTeacher);
									$this->show_text('//');
									$i++;
									continue 2;
								}	
							}
							$state = self::in_linecomment;
							$this->show_pending($pending);
							$this->initTag(self::c_comment);
							$this->show_text('//');
							$i++;
							continue 2;
						}
					}elseif($current == '"')	{
						$state = self::in_string;
						$this->show_pending($pending);
						$this->initTag(self::c_string);
						$this->show_text('"');
						break;
					}elseif($current == "'"){
						$state = self::in_char;
						$this->show_pending($pending);
						$this->initTag(self::c_string);
						$this->show_text('\'');
						break;
					} elseif($current == '@' && $first_no_space==$current){
						$state = self::in_macro;
						$this->show_pending($pending);
						$this->initTag(self::c_macro);
						$this->show_text('@');
						break;
					}
					if(($current >= 'a' && $current <= 'z') ||
					($current >= 'A' && $current <= 'Z') ||
					($current >= '0' && $current <= '9') ||
					$current=='_' || ord($current) > 127){
						$pending .= $current;
					} else {
						$this->show_pending($pending);
						if($current == '{' || $current == '(' || $current == '['){
							$this->initHover();
						}
						if($current == self::LF){
							$this->show_line_number();
						}else{
							$aux =$current;
							$this->show_pending($aux);
						}
						if($current == ')' || $current == '}' || $current == ']'){
							$this->endHover();
						}
					}
			}
		}

		$this->show_pending($pending);
		if($state != self::regular){
			$this->endTag();
		}
		$this->end();
	}
}

?>
