<?php

/*
	[SupeSite/X-Space] (C)2001-2006 Comsenz Inc.
	无限级分类

	$RCSfile: tree.class.php,v $
	$Revision: 1.2 $
	$Date: 2007/03/16 20:35:39 $
*/

class Tree {
	var $data = array();
	var $child = array(-1 => array());
	var $layer = array(-1 => -1);
	var $parent = array();
	
	function Tree($value) {}
	
	function setNode($id, $parent, $value) {
		
		$parent = $parent?$parent:0;
	
		$this->data[$id] = $value;
		$this->child[$id] = array();
		$this->child[$parent][]  = $id;
		$this->parent[$id] = $parent;
		
		if(!isset($this->layer[$parent])) {
			$this->layer[$id] = 0;
		} else {
			$this->layer[$id] = $this->layer[$parent] + 1;
		}
	}
	
	function getList(&$tree, $root= 0) {
		foreach($this->child[$root] as $key => $id) {
			$tree[] = $id;
			if($this->child[$id]) $this->getList($tree, $id);
		}
	}
	
	function getValue($id) {
		return $this->data[$id];
	}
	
	function getLayer($id, $space = false) {
		return $space?str_repeat($space, $this->layer[$id]):$this->layer[$id];
	}
	
	function getParent($id) {
		return $this->parent[$id];
	}
	
	function getParents($id) {
		while($this->parent[$id] != -1) {
			$id = $parent[$this->layer[$id]] = $this->parent[$id];
		}
		
		ksort($parent);
		reset($parent);
	
		return $parent;
	}
	
	function getChild($id) {
		return $this->child[$id];
	}
	
	function getChilds($id = 0) {
		$child = array();
		$this->getList($child, $id);
		
		return $child;
	}
}

?>