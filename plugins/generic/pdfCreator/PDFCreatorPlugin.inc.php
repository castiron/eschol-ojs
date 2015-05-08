<?php

/**
 * @file PDFCreatorPlugin.inc.php
 * 
 * @class PDFCreatorPlugin
 *
 *
 * Author: Barbara Hui, 2011
 * NOTES: 
 *	In order to get this plugin working, add an entry for it to the 'registry' table in the ojs database.
 *	Am waiting to hear from PKP as to what the proper programmatic way to do this is. BLH Apr 19 2011.
 *	Note: I heard from James MacGregor on the forums that the way to do this is to add the info to a
 *	"version.xml" file, which is stored in the plugin directory: 
 *	http://pkp.sfu.ca/support/forum/viewtopic.php?f=9&t=7386&sid=2e4878a4638f02e0c291fb44c64485fa#p28779
 *	Need to do this. BLH Apr 21 2011.
 *
 *	Conversion uses LiveDocx, which is part of the Zend framework. Parts of the ZF are included with OJS, 
 *      but only those parts that are needed for PKP Harvester. Users of this plugin therefore
 *      have to get LiveDocx up and running in their own environment independently of OJS.
 *      LiveDocx Documentation used: http://www.phplivedocx.org/articles/
 *
 *      In order to use LiveDocx, you have to create an account at http://www.livedocx.com/.
 *      Then supply this user and password to the PDFCreatorPlugin::CreatePdfWithLiveDocx method as a parameter.
 *      
 *      In order to get the LiveDocx PDF conversion to work, you must login at http://www.livedocx.com/ and 
 *      upload a file (seemingly any will do) to "My Templates".
 *
 *	CHANGELOG:
 *		20110728	BLH	Commented out HookRegistry to disable plugin. Not the proper way to do this, I realize!
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PDFCreatorPlugin extends GenericPlugin {
	function register($category, $path) { 
		if (parent::register($category, $path)) {
			//HookRegistry::register( 
				//'SectionEditorAction::completeFinalCopyedit', 
				//array($this, 'completeFinalCopyedit_callback') 
			//); 
			//not sure if plugin needs to also be registered with 'CopyeditorAction::completeFinalCopyedit'
			//for sites that use the copyeditor role?
			return true; 
		} 
		return false; 
	} 
	
	function getName() { 
		return 'PDFCreatorPlugin'; 
	} 
	
	function getDisplayName() { 
		return 'PDF Creator Plugin'; 
	} 
	
	function getDescription() { 
		return 'This plugin uses LiveDocx to convert word processor documents into PDF format.'; 
	} 

	function completeFinalCopyedit_callback($hookName, $args) { 
		//parameters for SectionEditorAction::CompleteFinalCopyedit hook  - section editor completes final copyedit (copyeditors disabled)
		//this hook is called before the final copyedit file in Step 3 is recorded in the database as the current review file
		$sectionEditorSubmission =& $args[0]; 

		//begin testing output
		/***
                $f = fopen("/subi/apache/htdocs/ojs/plugins/generic/pdfCreator/testlog","a+");
                fwrite($f, "In PDFCreatorPlugin::ConvertDocToPDF!\n");               
                fclose($f);
		***/
		//end testing output

		$reviewFile = $sectionEditorSubmission->getReviewFile(); //returns ArticleFile object
		PDFCreatorPlugin::convertArticleFileToPdf($reviewFile);	
		return false; 
	}

	function convertArticleFileToPdf($articleFile) {

		$LiveDocxUsername = 'barbarahui';
		$LiveDocxPassword = 'sub11s0js';

		$filename = $articleFile->getFilename();
		$templatefile = $articleFile->getFilePath();
		$PDFfilename = $templatefile . '.pdf';
		//create PDF file
		$this->createPdfWithLiveDocx($LiveDocxUsername,$LiveDocxPassword,$templatefile,$PDFfilename);
		//update the database with the info for the PDF file
		$articleFile->setFilename($filename . '.pdf');
		$articleFile->setOriginalFileName($PDFfilename);
		$articleFile->setFileType('application/pdf');
		$articleFileDao = &DAORegistry::getDAO('ArticleFileDAO');
		$articleFileDao->updateArticleFile($articleFile);
	}

	function createPdfWithLiveDocx($LiveDocxUsername,$LiveDocxPassword,$templatefile,$outputfile) {
        	//code below cloned from http://www.phplivedocx.org/2009/02/06/convert-doc-to-pdf-in-php/
        	//author Jonathan Maron
		include('Zend/Service/LiveDocx/MailMerge.php');
		include('Zend/Service/LiveDocx/Exception.php');
		$mailMerge = new Zend_Service_LiveDocx_MailMerge();
 
		$mailMerge->setUsername($LiveDocxUsername)
          		  ->setPassword($LiveDocxPassword);
 
		$mailMerge->setLocalTemplate($templatefile);
 
		// necessary as of LiveDocx 1.2
		$mailMerge->assign('dummyFieldName', 'dummyFieldValue');
 
		$mailMerge->createDocument();
 
		$document = $mailMerge->retrieveDocument('pdf');
 
		file_put_contents($outputfile, $document);
 
		unset($mailMerge);
	}



}

?>
