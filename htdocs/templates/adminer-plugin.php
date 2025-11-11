<?php
/** Adminer customization allowing usage of plugins
* @link http://www.adminer.org/plugins/#use
* @author Jakub Vrana, http://www.vrana.cz/
* @license http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerPlugin extends Adminer {
	/** @access protected */
	var $plugins;
	
	function _findRootClass($class) { // is_subclass_of(string, string) is available since PHP 5.0.3
		do {
			$return = $class;
		} while ($class = get_parent_class($class));
		return $return;
	}
	
	/** Register plugins
	* @param array object instances or null to register all classes starting by 'Adminer'
	*/
	function AdminerPlugin($plugins) {
		if ($plugins === null) {
			$plugins = array();
			foreach (get_declared_classes() as $class) {
				if (preg_match('~^Adminer.~i', $class) && strcasecmp($this->_findRootClass($class), 'Adminer')) { //! can use interface
					$plugins[$class] = new $class;
				}
			}
		}
		$this->plugins = $plugins;
		//! it is possible to use ReflectionObject to find out which plugins defines which methods at once
	}
	
	function _callParent($function, $args) {
		return call_user_func_array(array('parent', $function), $args);
	}
	
	function _applyPlugin($function, $args) {
		foreach ($this->plugins as $plugin) {
			if (method_exists($plugin, $function)) {
				switch (count($args)) { // call_user_func_array() doesn't work well with references
					case 0: $return = $plugin->$function(); break;
					case 1: $return = $plugin->$function($args[0]); break;
					case 2: $return = $plugin->$function($args[0], $args[1]); break;
					case 3: $return = $plugin->$function($args[0], $args[1], $args[2]); break;
					case 4: $return = $plugin->$function($args[0], $args[1], $args[2], $args[3]); break;
					case 5: $return = $plugin->$function($args[0], $args[1], $args[2], $args[3], $args[4]); break;
					case 6: $return = $plugin->$function($args[0], $args[1], $args[2], $args[3], $args[4], $args[5]); break;
					default: trigger_error('Too many parameters.', E_USER_WARNING);
				}
				if ($return !== null) {
					return $return;
				}
			}
		}
		return $this->_callParent($function, $args);
	}
	
	function _appendPlugin($function, $args) {
		$return = $this->_callParent($function, $args);
		foreach ($this->plugins as $plugin) {
			if (method_exists($plugin, $function)) {
				$return += call_user_func_array(array($plugin, $function), $args);
			}
		}
		return $return;
	}

	// applyPlugin
	
	function name() {
		$args = func_get_args();
		return $this->_applyPlugin(__FUNCTION__, $args);
	}

	function credentials() {
		$args = func_get_args();
		return $this->_applyPlugin(__FUNCTION__, $args);
	}
}
