<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /includes/classes/basis.php
//

namespace core\classes;

class basis implements \SplSubject {
	public  $classes 	= array ();
	public  $_observers = array ();
	public  $module		= 'phreedom';
	public  $page 		= 'main';
	public  $template;
	public  $observer	= 'core\classes\outputPage';
	public  $custom_html		= false;
    public  $include_header		= true;
    public  $include_footer		= true;
	public  $dataBaseConnection = null;
	public  $mainmenu 	= array ();
	private $events 	= array ();

	public function __construct() {
		global $mainmenu;
		$this->journal = new \core\classes\journal ();
		$this->cInfo = (object)array_merge ( $_GET, $_POST );
		$this->events = $this->cInfo->action;
		if ($this->getNumberOfAdminClasses () == 0 || empty ( $this->mainmenu )) {
			$dirs = @scandir ( DIR_FS_MODULES );
			if ($dirs === false) throw new \core\classes\userException ( "couldn't read or find directory " . DIR_FS_MODULES );
			foreach ( $dirs as $dir ) { // first pull all module language files, loaded or not
				if ($dir == '.' || $dir == '..') continue;
				gen_pull_language ( $dir, 'menu' );
				if (is_dir ( DIR_FS_MODULES . $dir )) {
					$class = "\\$dir\classes\admin";
					$this->attachAdminClasses ( $dir, new $class () );
				}
			}
			$this->mainmenu = $mainmenu;
		}
	}

	public function __wakeup() {
		print ("basis __wakeup is called") ;
		$this->cInfo = (object)array_merge ( $_GET, $_POST );
	}

	public function attach(\SplObserver $observer) {
		$this->_observers[get_class($observer)] = $observer;
	}

	public function detach(\SplObserver $observer) {
		unset($this->_observers[ get_class($observer) ]);
	}

	/**
	 * this method sends a notify to the template page to start sending information in requested format.
	 */
	public function notify() {
		global $messageStack;
		foreach ( $this->_observers as $key => $observer ) {
			$messageStack->debug ( "\n calling ". get_class($observer)." for output" );
			$this->observer = get_class($observer);
			$observer->update ( $this );
		}
		ob_end_flush();
		session_write_close();
		$messageStack->write_debug();
		die();
	}

	public function returnCurrentObserver(){
		return $this->_observers[$this->observer];
	}

	public function ReturnAdminClasses() {
		return $this->classes;
	}

	/**
	 * this method returns the number of admin classes stored in its private array
	 *
	 * @return integer
	 */
	public function getNumberOfAdminClasses() {
		return sizeof ( $this->classes );
	}

	/**
	 * method adds a admin class to its private array.
	 *
	 * @param string $moduleName
	 * @param \core\classes\admin $admin_class
	 */
	public function attachAdminClasses($moduleName, \core\classes\admin $admin_class) {
		if (array_search ( $admin_class, $this->classes ) === false) {
			$this->classes [$moduleName] = $admin_class;
		}
		uasort ( $this->classes, array (
				$this,
				'arangeObjectBySortOrder'
		) );
	}

	/**
	 * this method is for sorting a array of objects by the sort_order variable
	 */
	function arangeObjectBySortOrder($a, $b) {
		return strcmp ( $a->sort_order, $b->sort_order );
	}

	/**
	 * this method add the event to the second position of the array.
	 * this will allow the program to finish the first position and then continue with the second.
	 *
	 * @param string $event
	 */
	public function fireEvent($event) {
		$this->events = array_merge(array_slice((array) $this->events, 0, 1 ), array($event), array_slice ((array)$this->events, 1 ));
		$this->startProcessingEvents();
	}

	/**
	 * this method walks over the event stack.
	 * tries to call before_event, event, after_event on all admin_classes.
	 * then removes event from event stack to prevent it from returning.
	 *
	 * @throws exception if the event stack is empty
	 */
	public function startProcessingEvents() {
		global $messageStack;
		if ( count($this->events ) == 0) throw new \Exception ( "trying to start processing events but the events array is empty" );
		while ( list ( $key, $event ) = each ( $this->events ) ) {
			$messageStack->debug ( "\n starting with event: $event" );
			if (! $event) break;
			$ActionBefore = "before_$event";
			foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $ActionBefore )) {
					$messageStack->debug ( "\n class {$module_class->id} has action method $ActionBefore" );
					$module_class->$ActionBefore ( $this );
				}
			}

			foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $event )) {
					$messageStack->debug ( "\n class {$module_class->id} has action method $event" );
					$module_class->$event ( $this );
				}
			}
			$ActionAfter = "after_$event";
			foreach ( $this->classes as $module_class ) {
				if ($module_class->installed && method_exists ( $module_class, $ActionAfter )) {
					$messageStack->debug ( "\n class {$module_class->id} has action method $ActionAfter" );
					$module_class->$ActionAfter ( $this );
				}
			}
			unset ( $this->events [$key] );
			reset ( $this->events );
		}
	}

	/**
	 * This method will add the requested event to the end of the stack.
	 *
	 * @param string $event
	 * @throws exception if event is emtpy
	 */
	public function addEventToStack($event) {
		if (! $event)
			throw new exception ( "in the basis class method addEventToStack we received a empty event." );
		if (! in_array ( $event, (array) $this->events))
			array_push ($this->events, $event );
	}

	/**
	 * empties the event stack and then adds the new event
	 *
	 * @param string $event
	 * @throws exception if event is empty
	 */
	public function removeEventsAndAddNewEvent($event) {
		if (! $event) throw new exception ( "in the basis class method  we received a empty event." );
		$this->events = array();
		$this->addEventToStack ( $event );
	}

	/**
	 * this method empties the event stack
	 */
	public function clearEventsStack() {
		$this->events = array();
	}
	function __destruct() {
//		print_r($this);
		$this->dataBaseConnection = null;
	}
}
?>