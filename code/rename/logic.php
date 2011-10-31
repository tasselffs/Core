<?php
/*
 * TODO
 *  - Use RenameStatus and thus also translations
 * LATER
 *  - Make loadable via index
 *  - Unify output via output class which always sends json which might contain html field
 */
final class RenameStatus {
	const SUCCESS="SUCCESS";
	const COULDNOTBERENAMED="COULDNOTBERENAMED";
	const NAMEALREADYEXISTS="NAMEALREADYEXISTS";
	const ORIGNOTFOUND="ORIGNOTFOUND";
}

class Rename extends Controller {
	public static $DFLT="form";
	private $path,$name,$type,$ext;
	
	public function dialog() {
		global $code; //sigh
		//This should mostly be moved to constructor
		$i=Input::_get();
		$this->path=($i->path ?: "");
		$this->name=($i->file ?: "");
		$this->type=($i->type ?: "");
		$this->ext=($i->ext ?: "");
		//Until here
		$vv=new StdClass(); //ViewVariables
		$vv->pframename='rename_'.$this->type;
		$vv->fframename=ecoder_iframe_clean($this->path.$vv->pframename);
		ob_start();
		include "code/rename/dialog.php";
		$html=ob_get_clean();
		echo json_encode(array("html"=>$html));
	}
	
	public function save() {
		global $code; //sigh
		$i=Input::_get();
		$this->path=($i->path ?: "");
		$this->name=($i->file ?: "");
		$this->type=($i->type ?: "");
		$this->ext=($i->ext ?: "");
		$newname=($i->file_new ?: "");
		$newname=preg_replace('/[^0-9A-Za-z.]/', '_',$newname);

		$filepath="%s%s%s%s%s";
		$orig=sprintf($filepath,$code['root'],$this->path,$this->name,"","");
		$new=sprintf($filepath,$code['root'],$this->path,$newname,($this->_isFile() ? "." : ""),($this->_isFile() ? $this->ext : ""));

		$res="";
		$resc="";
		if (file_exists($orig) && ($this->name || $this->path)) {    
			if (!file_exists($new)) {
				$res=@rename($orig,$new);
				$res=$this->type." <strong>".$this->name."</strong> renamed <strong>".$newname.'.'.$this->ext."</strong>";
				$resc=1;
			} else { // new name exists ##        
				$res='the '.$this->type.' <strong>'.$newname.'.'.$this->ext.'</strong> already exist, please choose a different name.';
				$resc=0;
			} 
		} else { // error, not found ##
			$res='the '.$this->type.' <strong>'.$this->name.'</strong> does not exist, please close the tab and try again.';
			$resc=0;
		}

		echo json_encode(array("msg"=>$res,"code"=>$resc));
	}
	
	private function _isFile() {
		return ($this->type=="file");
	}
}

$action=(isset($_GET['action']) ? $_GET['action'] : Rename::$DFLT);
Rename::_init($action);