<?php
/**
 * Copyright 2007, 2008 Yuri Timofeev tim4dev@gmail.com
 *
 * This file is part of Webacula.
 *
 * Webacula is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Webacula is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Webacula.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Yuri Timofeev <tim4dev@gmail.com>
 * @package webacula
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU Public License
 *
 */

/* Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';

class VolumeController extends Zend_Controller_Action
{

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
		// load model
		Zend_Loader::loadClass('Volume'); 
		Zend_Loader::loadClass('Media');
		$this->view->translate = Zend_Registry::get('translate');
	}

    function findNameAction()
    {
    	// http://localhost/webacula/volume/find-name/volname/pool.file.7d.0005
    	$volname = addslashes( $this->_request->getParam('volname') );
    	$order   = addslashes(trim( $this->_request->getParam('order', 'VolumeName') ));
    	$this->view->volname = $volname;
    	if ( $volname )	{
    		$this->view->title = $this->view->translate->_("Volume") . " " . $volname;
    		$volume = new Volume();
			$this->view->result = $volume->getByName($volname, $order);			
    	}
    	else
    		$this->view->result = null;
    }


    function findPoolIdAction()
    {
    	// http://localhost/webacula/volume/find-pool-id/id/2/name/pool.file.7d
    	$pool_id   = intval( $this->_request->getParam('id') );
    	$pool_name = addslashes( $this->_request->getParam('name') );
    	$order     = addslashes(trim( $this->_request->getParam('order', 'VolumeName') ));

    	$this->view->pool_id   = $pool_id;
    	$this->view->pool_name = $pool_name;
    	if ( $pool_id  )	{
    		$this->view->title = $this->view->translate->_("Pool") . " " . $pool_name;
    		$media = new Media();
			$this->view->result = $media->getById($pool_id, $order);    		
    	}
    	else
    		$this->view->result = null;
    }


    /**
     * Volumes with errors/problems
     *
     */
    function problemAction()
    {
		$this->view->titleProblemVolumes = $this->view->translate->_("Volumes with errors");
		$order = addslashes(trim( $this->_request->getParam('order', 'VolumeName') ));
		// get data from model
    	$vols = new Volume();
    	$ret = $vols->GetProblemVolumes();
    	$this->view->resultProblemVolumes = $ret->fetchAll(null, $order);
    }

}