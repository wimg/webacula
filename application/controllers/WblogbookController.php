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

class WblogbookController extends Zend_Controller_Action
{

    // PRE code improperly processed in regexp in eregi_replace in index.phtml
	// код PRE неправильно обрабатывается regexp'ом в eregi_replace в index.phtml
	protected $aAllowedTags = array('pre','b', 'h1', 'h2', 'h3', 'p', 'i', 'em', 'u', 'br', 'code', 'del', 'sub',
		'sup', 'tt', 'a');
	protected $aAllowedAttrs = array('href');

	function init()
	{
		$this->view->baseUrl = $this->_request->getBaseUrl();
		// load model
		Zend_Loader::loadClass('Wblogbook');
		// load validators
		Zend_Loader::loadClass('MyClass_Validate_BaculaJobId');
		Zend_Loader::loadClass('MyClass_Validate_LogBookId');
		Zend_Loader::loadClass('Zend_Validate_Digits');
		Zend_Loader::loadClass('Zend_Validate_NotEmpty');

		$this->view->translate = Zend_Registry::get('translate');
	}

	/**
	 * Define order
	 * Определяет порядок сортировки
	 *
	 * @param string $so1
	 * @param string $so2
	 */
	function defSortOrder($so1)
	{
        if ( isset($so1) ) {
            if ( trim($so1) == 'ASC' ) {
                $sort_order = 'ASC';
            } else {
                $sort_order = 'DESC';
            }
   		} else {
   		    $sort_order == 'DESC';
   		}
   		return $sort_order;
	}

	/**
	 * Строковое описание для порядка сортировки
	 *
	 * @param string $sorto
	 */
	function defStrSortOrder($sorto)
	{
	    if ( $sorto == 'ASC' ) {
            $str_sort_order = $this->view->translate->_('in ascending order');
        } else {
            $str_sort_order = $this->view->translate->_('in descending order');
        }
        return $str_sort_order;
	}

	/**
	 * LogBook view action without any filters
	 *
	 */
    function indexAction()
    {
    	$this->view->title = $this->view->translate->_("Logbook");

    	//print_r($this->_request->getParams()); exit; //debug !!!

    	$date_begin  = date('Y-m-d', time()-2678400);
   		$date_end    = date('Y-m-d', time());

   		// порядок сортировки
        $sort_order = 'DESC';
        $str_sort_order = $this->defStrSortOrder($sort_order);

        // даты
        $tmp_title = sprintf($this->view->translate->_(" %s (from %s to %s)"), $str_sort_order, $date_begin, $date_end);
 		$this->view->title .= $tmp_title;
   		$this->view->date_begin = $date_begin;
   		$this->view->date_end = $date_end;

		// get data from model
    	$logs = new wbLogBook();
    	$ret = $logs->IndexLogBook($date_begin, $date_end, $sort_order);
    	if ($ret)	{
    	   $this->view->result = $ret->fetchAll();
    	}

  		$this->view->printable = 0;
    }

	/**
	 * LogBook view action with filter by Date
	 *
	 */
    function filterbydateAction()
    {
    	$this->view->title = $this->view->translate->_("Logbook");

    	//print_r($this->_request->getParams()); exit; //debug !!!

    	$date_begin  = trim( $this->_request->getParam('date_begin', date('Y-m-d', time()-2678400)) );
   		$date_end    = trim( $this->_request->getParam('date_end', date('Y-m-d', time())) );

   		// порядок сортировки
        $sort_order     = $this->defSortOrder( trim( $this->_request->getParam('sortorder_by_date') ) );
        $str_sort_order = $this->defStrSortOrder($sort_order);

        // даты
        $tmp_title = sprintf($this->view->translate->_(" %s (from %s to %s)"), $str_sort_order, $date_begin, $date_end);
 		$this->view->title .= $tmp_title;
   		$this->view->date_begin = $date_begin;
   		$this->view->date_end   = $date_end;

		// get data from model
    	$logs = new wbLogBook();
    	$ret = $logs->IndexLogBook($date_begin, $date_end, $sort_order);
    	if ($ret)	{
    	   $this->view->result = $ret->fetchAll();
    	}

    	$v1 = $this->_request->getParam('printable_by_date');
    	if ( empty($v1) )
    	{
    		$this->view->printable = 0;
    	} else {
    		$this->view->printable = 1;
    	}
    	echo $this->renderScript('wblogbook/index.phtml');
    }

    /**
	 * LogBook view action with filter by logId
	 *
	 */
    function filterbyidAction()
    {
    	$id_begin = intval( $this->_request->getParam('id_begin', 0) );
    	$id_end   = intval( $this->_request->getParam('id_end', $id_begin) );
		if ( $id_end == 0 ) {
			 $id_end = $id_begin;
		}
    	//echo '<pre>id_begin = ' . $id_begin . '<br>id_end = ' . $id_end . '</pre>'; exit();

    	// порядок сортировки
      $sort_order     = $this->defSortOrder( trim($this->_request->getParam('sortorder_by_id')) );
      $str_sort_order = $this->defStrSortOrder($sort_order);

    	$this->view->title    = sprintf($this->view->translate->_("Logbook by Id %s (from %u to %u)"), $str_sort_order, $id_begin, $id_end);
    	$this->view->id_begin = $id_begin;
  		$this->view->id_end   = $id_end;

		// get data from model
    	$logs = new wbLogBook();
    	$ret = $logs->findLogBookById($id_begin, $id_end, $sort_order);
    	if ($ret)	{
    		$this->view->result = $ret->fetchAll();
    	}

    	$v2 = $this->_request->getParam('printable_by_id');
    	if ( empty($v2) )
    	{
    		$this->view->printable = 0;
    	} else {
    		$this->view->printable = 1;
    	}

    	echo $this->renderScript('wblogbook/index.phtml');
    }


    /**
	 * LogBook view action full text search
	 *
	 */
    function searchtextAction()
    {
    	$id_text = addslashes( substr( $this->_request->getParam('id_text'), 0, 250 ) );

    	// порядок сортировки
      $sort_order     = $this->defSortOrder( trim($this->_request->getParam('sortorder_by_text')) );
      $str_sort_order = $this->defStrSortOrder($sort_order);

    	$this->view->title   = $this->view->translate->_("Logbook") . ". " . $this->view->translate->_("Search text");
    	$this->view->id_text = $id_text;

		// get data from model
		if ( isset($id_text) )	{
	    	$logs = new wbLogBook();
	    	$ret = $logs->findLogBookByText($id_text, $sort_order);
	    	if ($ret)	{
		 		$this->view->result = $ret->fetchAll();
			}
		}

    	$v2 = $this->_request->getParam('printable_by_text');
    	if ( empty($v2) )	{
    		$this->view->printable = 0;
    	} else {
    		$this->view->printable = 1;
    	}

    	echo $this->renderScript('wblogbook/index.phtml');
    }


    function mySendEmail($body, $subj)
    {
		$config = Zend_Registry::get('config_webacula');

		// send email
        Zend_Loader::loadClass('Zend_Mail');
        $mail = new Zend_Mail('utf-8');
        $mail->addHeader('X-MailGenerator', 'webacula');
        $mail->setBodyText($body, 'UTF-8');
        $mail->setFrom($config->email->from, 'Webacula Logbook');
        $mail->addTo($config->email->to_admin, 'Bacula admin');
        $mail->setSubject($subj);
        $mail->send();
    }


    /**
	 * LogBook Add New Record
	 */
    function myAddRecord($logDateCreate, $logTxt)
    {
		// Returns (int) $value
		Zend_Loader::loadClass('Zend_Filter_Int');
		$intf = new Zend_Filter_Int();

		// Returns the string $value, removing all but alphabetic characters.
		// This filter includes an option to also allow white space characters.
		Zend_Loader::loadClass('Zend_Filter_Digits');
		$digit = new Zend_Filter_Digits();
		$digit->allowWhiteSpace = true;

		$logTypeId = $intf->filter(trim($this->_request->getPost('logTypeId')));

		$logbook = new wbLogBook();
    	$data = array(
			'logDateCreate' => $logDateCreate,
			'logTxt'    => $logTxt,
			'logTypeId' => $logTypeId
		);

		$rows_affected = $logbook->insert($data);
		if ( $rows_affected ) {
		    $this->mySendEmail(
		        $this->view->translate->_('Create record :') . " " . $data['logDateCreate'] . "\n" .
		        $this->view->translate->_('Type record :')   . " " . $data['logTypeId'] . "\n" .
		        $this->view->translate->_("Text :")          ."\n-------\n" . $data['logTxt'] . "\n-------\n\n" ,
		        $this->view->translate->_('Webacula Logbook Add record')
		    );
		}
    }


    /**
	 * LogBook Add From New Record action
	 *
	 */
    function addAction()
    {
    	$this->view->title = $this->view->translate->_("Logbook: add new record");
    	$this->view->wblogbook = new Wblogbook();
    	$this->view->amessages = array();

    	if ($this->_request->isPost() && $this->_request->getPost('hiddenNew'))	{
    		// validate

    		// ********************* validate datetime
			Zend_Loader::loadClass('Other_ValidateDatetime');
			$validator_datetime = new Other_ValidateDatetime();

			$logDateCreate = trim($this->_request->getPost('logDateCreate'));

			if ( !$validator_datetime->isValid($logDateCreate) ) {
				$this->view->amessages = array_merge($this->view->amessages, $validator_datetime->getMessages());
			}

			// ********************* validate text
			// This filter returns the input string, with all HTML and PHP tags stripped from it,
    		// except those that have been explicitly allowed.
    		Zend_Loader::loadClass('Zend_Filter_StripTags');
    		// allow :
    		// construct($tagsAllowed = null, $attributesAllowed = null, $commentsAllowed = false)
			$strip_tags = new Zend_Filter_StripTags(
				$this->aAllowedTags, $this->aAllowedAttrs, false);

			$logTxt    = trim($strip_tags->filter($this->_request->getPost('logTxt')));
			$validator_nonempty = new Zend_Validate_NotEmpty();

			if ( !$validator_nonempty->isValid($logTxt) ) {
				$this->view->amessages = array_merge($this->view->amessages, $validator_nonempty->getMessages());
			}

			// ********************* final
			// add record into database
			if ( empty($this->view->amessages))	{
				// validation is OK. add record into logbook
    			$this->myAddRecord($logDateCreate, $logTxt);
    			$this->_redirect('/wblogbook/index');
    			return;
			}

    		// ********************* save user input for correct this
    		$this->view->wblogbook->logTxt    = $strip_tags->filter($this->_request->getPost('logTxt'));
        	$this->view->wblogbook->logTypeId = $this->_request->getPost('logTypeId');
    	} else {
	    	// ********************* setup empty record
        	$this->view->wblogbook->logTxt    = null;
        	$this->view->wblogbook->logTypeId = null;
    	}

    	// draw form
    	Zend_Loader::loadClass('Wblogtype');

    	// get data from wbLogType
    	$typs = new Wblogtype();   	    	
    	$this->view->typs = $typs->fetchAll();
        // common fileds
        $this->view->wblogbook->logDateLast = null;
        $this->view->wblogbook->logId = null;
        $this->view->wblogbook->logDateCreate = date('Y-m-d H:i:s', time());
        $this->view->hiddenNew = 1;
        $this->view->aAllowedTags = $this->aAllowedTags;
    }



    /**
	 * LogBook modify Record action
	 *
	 */
    function modifyAction()
    {
    	$this->view->title = $this->view->translate->_("Logbook: modify record");
    	$this->view->wblogbook = new Wblogbook();
    	$this->view->amessages = array();


    	// ****************************** UPDATE record **********************************
    	if ( $this->_request->isPost() && $this->_request->getPost('hiddenModify') &&
    		$this->_request->getPost('act') == 'update')
    	{
    		$logid = trim($this->_request->getPost('logid'));

    		// ********************* validate datetime
			Zend_Loader::loadClass('Other_ValidateDatetime');
			$validator_datetime = new Other_ValidateDatetime();

			$logDateCreate = trim($this->_request->getPost('logDateCreate'));

			if ( !$validator_datetime->isValid($logDateCreate) ) {
				$this->view->amessages = array_merge($this->view->amessages, $validator_datetime->getMessages());
			}

			// ********************* validate text
			// This filter returns the input string, with all HTML and PHP tags stripped from it,
    		// except those that have been explicitly allowed.
    		Zend_Loader::loadClass('Zend_Filter_StripTags');
    		// allow :
    		// construct($tagsAllowed = null, $attributesAllowed = null, $commentsAllowed = false)
			$strip_tags = new Zend_Filter_StripTags(
				$this->aAllowedTags, $this->aAllowedAttrs, false);
			$logTxt    = trim($strip_tags->filter($this->_request->getPost('logTxt')));
			$validator_nonempty = new Zend_Validate_NotEmpty();

			if ( !$validator_nonempty->isValid($logTxt) ) {
				$this->view->amessages = array_merge($this->view->amessages, $validator_nonempty->getMessages());
			}

			// ********************* final
			// update record into database
			if ( empty($this->view->amessages) )	{
				// validation is OK. add record into logbook

    			$table = new wbLogBook();
				$data = array(
				    'logDateCreate' => $logDateCreate,
				    'logDateLast'   => date('Y-m-d H:i:s', time()),
				    'logTypeId' 	=> (int) trim($this->_request->getPost('logTypeId')),
					'logTxt'   => $logTxt,
    				'logIsDel' => (int) trim($this->_request->getPost('isdel'))
				);

				$where = $table->getAdapter()->quoteInto('logId = ?', $logid);
				$res = $table->update($data, $where);

				// send email
				if ( $res ) {
                    $this->mySendEmail(
                        $this->view->translate->_('Create record :') . ' ' . $data['logDateCreate'] . "\n" .
                        $this->view->translate->_('Update record :') . ' ' . $data['logDateLast'] . "\n" .
                        $this->view->translate->_('Type record :')   . ' ' . $data['logTypeId'] . "\n" .
                        $this->view->translate->_("Text :") . "\n-------\n" . $data['logTxt'] . "\n-------\n\n" ,
                        $this->view->translate->_('Webacula Logbook Update record') );
				}

    			$this->_redirect('/wblogbook/index');
    			return;
			}

    		// ********************* save user input for correct this
    		$this->view->wblogbook->logTxt    = $strip_tags->filter($this->_request->getPost('logTxt'));
        	$this->view->wblogbook->logTypeId = $this->_request->getPost('logTypeId');
    	}



    	// ****************************** DELETE record **********************************
    	if ( $this->_request->isPost() && $this->_request->getPost('hiddenModify') &&
    		$this->_request->getPost('act') == 'delete')
    	{
    		$logid = trim($this->_request->getPost('logid'));
    		$table = new wbLogBook();
			$data = array(
    			'logIsDel' => '1'
			);

			$where = $table->getAdapter()->quoteInto('logId = ?', $logid);
			$res = $table->update($data, $where);

            // send email
            if ( $res ) {
                $this->mySendEmail(
                    $this->view->translate->_("LogId record :") ." " . $logid . "\n",
                    $this->view->translate->_('Webacula Logbook Delete record') );
			}

    		$this->_redirect('/wblogbook/index');
    		return;
    	}


    	// ****************************** UNDELETE record **********************************
    	if ( $this->_request->isPost() && $this->_request->getPost('hiddenModify') &&
    		$this->_request->getPost('act') == 'undelete')
    	{
    		$logid = trim($this->_request->getPost('logid'));
    		$table = new wbLogBook();
			$data = array(
    			'logIsDel' => '0'
			);

			$where = $table->getAdapter()->quoteInto('logId = ?', $logid);
			$res = $table->update($data, $where);
			// send email
            if ( $res ) {
                $this->mySendEmail(
                    $this->view->translate->_("LogId record :") . " " . $logid . "\n",
                    $this->view->translate->_('Webacula Logbook UnDelete record') );
			}

    		$this->_redirect('/wblogbook/index');
    		return;
    	}



    	// ********************* READ ORIGINAL RECORD from database ****************
    	if ( $this->_request->isPost() )
    	{
	    	$logid = trim($this->_request->getPost('logid'));
			// get data from table
			$logs = new wbLogBook();
			$where = $logs->getAdapter()->quoteInto('logId = ?', $logid);
			$row = $logs->fetchRow($where);

   			if ($row)	{
    			$this->view->wblogbook->logId	  = $row->logid;
    			$this->view->wblogbook->logDateCreate = $row->logdatecreate;
				$this->view->wblogbook->logTxt    = $row->logtxt;
        		$this->view->wblogbook->logTypeId = $row->logtypeid;
        		$this->view->wblogbook->logIsDel  = $row->logisdel;
    		}
    	}

    	// for draw form
    	Zend_Loader::loadClass('Wblogtype');

    	// get data from wbLogType
    	$typs = new Wblogtype();
    	$this->view->typs = $typs->fetchAll();

        // common fileds
        $this->view->wblogbook->logDateLast = date('Y-m-d H:i:s', time());
        $this->view->hiddenModify = 1;
        $this->view->aAllowedTags = $this->aAllowedTags;
    }


    function savetopdfAction()
    {
    	/*
http://www.zfforums.com/zend-framework-components-13/mail-formats-search-14/pdf-utf8-bug-not-362.html#post1041
------------------------------
tim4dev
Default Pdf & utf8 - bug or not?
Hi,

I have problems with write UTF-8 strings in russian language, for ex. "Русский язык") to PDF file - some chars are not processed.

Notice: iconv() [function.iconv]: Detected an illegal character in input string in ...library/Zend/Pdf/Resource/Font.php on line 522


A similar problem has been here:
PDF file with UTF-8 string

Therefore, ask again:
Please, how can I generate PDF file from UTF-8 strings with national characters?

thanks.
------------------------------
No answer ;(

    	*/
    	// http://devzone.zend.com/article/2525-Zend_Pdf-tutorial
    	// http://www.ehow.com/how_2226138_wrap-text-zendpdf.html

    	// Disable view script autorendering
/*        $this->_helper->viewRenderer->setNoRender();

    	$fileName = '/tmp/my.pdf';
    	// Load Zend_Pdf class
		Zend_Loader::loadClass('Zend_Pdf');

		// Создание нового документа PDF
		$pdf = new Zend_Pdf();

		// Add new page
		$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
		$pdf->pages[] = $page;

		// Создание нового шрифта
		// !!! ВСТРОЕННЫЕ ШРИФТЫ НЕ ПОДДЕРЖИВАЮТ русский язык !!!
		//$config = new Zend_Config_Ini('../application/config.ini', 'timeline');
		//$fontFile = $config->gdfontpath . "/" . $config->fontname . ".ttf";

		$fontFile = '/usr/share/fonts/dejavu-lgc/DejaVuLGCSans.ttf';
		$page->setFont(Zend_Pdf_Font::fontWithPath($fontFile), 12);
		//$page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);

		$text = "Русский -- ü ï ë -- go here!";
		$text = iconv("UTF-8", "UTF-8//IGNORE", $text);

		// Применение шрифта и написание текста (x:points, y:points)
		//$page->drawText("Рус These characters -- ü ï ë -- go here!", 0, 0, "UTF-8");
		$page->drawText($text, 20, 20, "UTF-8");

		// Save document as a new file or rewrite existing document
		$pdf->save($fileName);*/
    }

}
