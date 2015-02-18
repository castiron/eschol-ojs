<?php

/**
 * @file plugins/importexport/crossref/CrossRefExportPlugin.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportPlugin
 * @ingroup plugins_importexport_crossref
 *
 * @brief CrossRef/MEDLINE XML metadata export plugin
 */

// $Id$


import('classes.plugins.ImportExportPlugin');

class CrossRefExportPlugin extends ImportExportPlugin {
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True if plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path) {
		$success = parent::register($category, $path);
		$this->addLocaleData();
		return $success;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'CrossRefExportPlugin';
	}

	function getDisplayName() {
		return Locale::translate('plugins.importexport.crossref.displayName');
	}

	function getDescription() {
		return Locale::translate('plugins.importexport.crossref.description');
	}

	function display(&$args) {
		$templateMgr =& TemplateManager::getManager();
		parent::display($args);

		$issueDao =& DAORegistry::getDAO('IssueDAO');

		$journal =& Request::getJournal();

		switch (array_shift($args)) {							
			case 'exportIssues':
				$issueIds = Request::getUserVar('issueId');
				if (!isset($issueIds)) $issueIds = array();
				$issues = array();
				foreach ($issueIds as $issueId) {
					$issue =& $issueDao->getIssueById($issueId);
					if (!$issue) Request::redirect();
					$issues[] =& $issue;
				}
				$this->exportIssues($journal, $issues);
				break;
			case 'exportIssue':
				$issueId = array_shift($args);
				$issue =& $issueDao->getIssueById($issueId);
				if (!$issue) Request::redirect();
				$issues = array($issue);
				$this->exportIssues($journal, $issues);
				break;
			case 'exportArticle':
				$articleIds = array(array_shift($args));
				$result = ArticleSearch::formatResults($articleIds);
				$this->exportArticles($journal, $result);
				break;
			case 'exportArticles':
				$articleIds = Request::getUserVar('articleId');
				if (!isset($articleIds)) $articleIds = array();
				$results =& ArticleSearch::formatResults($articleIds);
				$this->exportArticles($journal, $results);
				break;
			case 'issues':
				// Display a list of issues for export
				$this->setBreadcrumbs(array(), true);
				Locale::requireComponents(array(LOCALE_COMPONENT_OJS_EDITOR));
				$issueDao =& DAORegistry::getDAO('IssueDAO');
				$issues =& $issueDao->getPublishedIssues($journal->getId(), Handler::getRangeInfo('issues'));

				$templateMgr->assign_by_ref('issues', $issues);
				$templateMgr->display($this->getTemplatePath() . 'issues.tpl');
				break;
			case 'articles':
				// Display a list of articles for export
				$this->setBreadcrumbs(array(), true);
				$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');
				$rangeInfo = Handler::getRangeInfo('articles');
				$articleIds = $publishedArticleDao->getPublishedArticleIdsByJournal($journal->getId(), false);
				$totalArticles = count($articleIds);
				if ($rangeInfo->isValid()) $articleIds = array_slice($articleIds, $rangeInfo->getCount() * ($rangeInfo->getPage()-1), $rangeInfo->getCount());
				import('lib.pkp.classes.core.VirtualArrayIterator');
				$iterator = new VirtualArrayIterator(ArticleSearch::formatResults($articleIds), $totalArticles, $rangeInfo->getPage(), $rangeInfo->getCount());
				$templateMgr->assign_by_ref('articles', $iterator);
				$templateMgr->display($this->getTemplatePath() . 'articles.tpl');
				break;
			default:
				$this->setBreadcrumbs();
				$templateMgr->assign_by_ref('journal', $journal);
				$templateMgr->display($this->getTemplatePath() . 'index.tpl');
		}
	}

	function exportArticles(&$journal, &$results, $outputFile = null) {
	    error_log(get_class($this));
        $this->import('CrossRefExportDom');		
		$doc =& CrossRefExportDom::generateCrossRefDom();
		$doiBatchNode =& CrossRefExportDom::generateDoiBatchDom($doc);

		// Create Head Node and all parts inside it
		$head =& CrossRefExportDom::generateHeadDom($doc, $journal);

		// attach it to the root node
		XMLCustomWriter::appendChild($doiBatchNode, $head);

		// the body node contains everything
		$bodyNode =& XMLCustomWriter::createElement($doc, 'body');
		XMLCustomWriter::appendChild($doiBatchNode, $bodyNode);

		// now cycle through everything we want to submit in this batch
		foreach ($results as $result) {
			$journal =& $result['journal'];
			$issue =& $result['issue'];
			$section =& $result['section'];
			$article =& $result['publishedArticle'];

			// Create the metadata node
			// this does not need to be repeated for every article
			// but its allowed to be and its simpler to do so
			$journalNode =& XMLCustomWriter::createElement($doc, 'journal');
			$journalMetadataNode =& CrossRefExportDom::generateJournalMetadataDom($doc, $journal);
			XMLCustomWriter::appendChild($journalNode, $journalMetadataNode);

			// Create the journal_issue node
			$journalIssueNode =& CrossRefExportDom::generateJournalIssueDom($doc, $journal, $issue, $section, $article);
			XMLCustomWriter::appendChild($journalNode, $journalIssueNode);

			// Create the article
			$journalArticleNode =& CrossRefExportDom::generateJournalArticleDom($doc, $journal, $issue, $section, $article);
			XMLCustomWriter::appendChild($journalNode, $journalArticleNode);

			// Create the DOI data--need to get the eScholarship ARK here
			//$DOIdataNode =& CrossRefExportDom::generateDOIdataDom($doc, $article->getDOI(), Request::url(null, 'article', 'view', $article->getId()));
			$ark = $this->assignARK($article);
			$DOIdataNode =& CrossRefExportDom::generateDOIdataDom($doc, $article->getDOI(), $ark);
			XMLCustomWriter::appendChild($journalArticleNode, $DOIdataNode);							
			XMLCustomWriter::appendChild($bodyNode, $journalNode);
			
		}


		// dump out the results
		if (!empty($outputFile)) {
			if (($h = fopen($outputFile, 'w'))===false) return false;
			fwrite($h, XMLCustomWriter::getXML($doc));
			fclose($h);
			$outputStream = XMLCustomWriter::getXML($doc);
			return $outputStream;
		} else {
			header("Content-Type: application/xml");
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=\"crossref.xml\"");
			XMLCustomWriter::printXML($doc);			
		}
		return true;
	}

	function exportIssues(&$journal, &$issues, $outputFile = null) {
		$this->import('CrossRefExportDom');

		$doc =& CrossRefExportDom::generateCrossRefDom();
		$doiBatchNode =& CrossRefExportDom::generateDoiBatchDom($doc);

		$journal =& Request::getJournal();

		// Create Head Node and all parts inside it
		$head =& CrossRefExportDom::generateHeadDom($doc, $journal);

		// attach it to the root node
		XMLCustomWriter::appendChild($doiBatchNode, $head);

		$bodyNode =& XMLCustomWriter::createElement($doc, 'body');
		XMLCustomWriter::appendChild($doiBatchNode, $bodyNode);

		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');

		foreach ($issues as $issue) {
			foreach ($sectionDao->getSectionsForIssue($issue->getId()) as $section) {
				foreach ($publishedArticleDao->getPublishedArticlesBySectionId($section->getId(), $issue->getId()) as $article) {
					// Create the metadata node
					// this does not need to be repeated for every article
					// but its allowed to be and its simpler to do so					
					$journalNode =& XMLCustomWriter::createElement($doc, 'journal');
					$journalMetadataNode =& CrossRefExportDom::generateJournalMetadataDom($doc, $journal);
					XMLCustomWriter::appendChild($journalNode, $journalMetadataNode);

					$journalIssueNode =& CrossRefExportDom::generateJournalIssueDom($doc, $journal, $issue, $section, $article);
					XMLCustomWriter::appendChild($journalNode, $journalIssueNode);

					// Article node
					$journalArticleNode =& CrossRefExportDom::generateJournalArticleDom($doc, $journal, $issue, $section, $article);
					XMLCustomWriter::appendChild($journalNode, $journalArticleNode);

					// DOI data node
					//$DOIdataNode =& CrossRefExportDom::generateDOIdataDom($doc, $article->getDOI(), Request::url(null, 'article', 'view', $article->getId
					$DOIdataNode =& CrossRefExportDom::generateDOIdataDom($doc, $article->getDOI(), $this->assignARK($article));
					XMLCustomWriter::appendChild($journalArticleNode, $DOIdataNode);							
					XMLCustomWriter::appendChild($bodyNode, $journalNode);

				}
			}
		}

		// dump out results
		if (!empty($outputFile)) {
			if (($h = fopen($outputFile, 'w'))===false) return false;
			fwrite($h, XMLCustomWriter::getXML($doc));
			fclose($h);
		} else {
			header("Content-Type: application/xml");
			header("Cache-Control: private");
			header("Content-Disposition: attachment; filename=\"crossref.xml\"");
			XMLCustomWriter::printXML($doc);
		}

		return true;
	}
	/**
	 * PreAssign and ARK to an article
	 * @param $article OJS article for which to generate the ARK
	 * @return $qualifiedArk, $escholURL preassigned eScholarship ARK; this will be the published ark
	 */
	function assignARK ($article){
	     if (!empty($article)) {		    
			$articleID = $article->getID();
			
            if (!empty($articleID)){
                //check first to see if an ARK has already been assigned			
		        $rawQualifiedArk = shell_exec('sqlite3 /apps/subi/subi/xtf-erep/control/db/arks.db "select id from arks where external_id=' .$articleID. '"');
                $qualifiedArk = trim($rawQualifiedArk);
                //No ARK exists, so assign one now                 
		        //if (!$qualifiedArk){
                if ($qualifiedArk){
		             error_log($articleID . " has no ARK in the database; will generate now!");					 
					 $rawQualifiedArk = shell_exec("/apps/subi/subi/xtf-erep/control/tools/mintArk.py ojs $articleID");
                     $qualifiedArk = trim($rawQualifiedArk);  
					 if (empty($qualifiedArk)){
					     error_log("Failed to generate an ARK for $articleID");
                         break;
                         //need to exit out;
					 }
					 else{
					     $escholURL = ereg_replace("ark:13030\/qt","http://www.escholarship.org/uc/item/",$qualifiedArk);
						 error_log("For ARTICLE ID $articleID generated this eSchol URL: $escholURL");
                         return $escholURL;						 
					 }
		        }
				//If an ARK already exists, use that
				else {				
				   error_log($qualifiedArk . " is the ARK for " . $articleID);
				   $escholURL = ereg_replace("ark:13030\/qt","http://www.escholarship.org/uc/item/",$qualifiedArk);
                   error_log("CrossRef Plugin using this eSchol URL:" . 	$escholURL);			   
                   return $escholURL;				   
				}
				
				if (empty($escholURL)){
				    error_log("Failed to preassign an eScholarship ARK to $articleID!");
					return;
				}
   		        
				
			} else {
			    error_log("CrossRefPlugin--no $articleID from which to preassign an eScholarship ARK!");
                return;				
			}		 
		 
		 } else {
		       error_log("CrossRefPlugin--no article from which to assign eScholarship ARK!");
			   return;		  
		   }	
	}
	
	
	/**
	 * Execute import/export tasks using the command-line interface.
	 * @param $args Parameters to the plugin
	 */
	function executeCLI($scriptName, &$args) {
//		$command = array_shift($args);
		$xmlFile = array_shift($args);
		$journalPath = array_shift($args);

		$journalDao =& DAORegistry::getDAO('JournalDAO');
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$sectionDao =& DAORegistry::getDAO('SectionDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$publishedArticleDao =& DAORegistry::getDAO('PublishedArticleDAO');

		$journal =& $journalDao->getJournalByPath($journalPath);

		if (!$journal) {
			if ($journalPath != '') {
				echo Locale::translate('plugins.importexport.crossref.cliError') . "\n";
				echo Locale::translate('plugins.importexport.crossref.error.unknownJournal', array('journalPath' => $journalPath)) . "\n\n";
			}
			$this->usage($scriptName);
			return;
		}

		if ($xmlFile != '') switch (array_shift($args)) {
			case 'articles':
				$results =& ArticleSearch::formatResults($args);
				if (!$this->exportArticles($journal, $results, $xmlFile)) {
					echo Locale::translate('plugins.importexport.crossref.cliError') . "\n";
					echo Locale::translate('plugins.importexport.crossref.export.error.couldNotWrite', array('fileName' => $xmlFile)) . "\n\n";
				}
				return;
			case 'issue':
				$issueId = array_shift($args);
				$issue =& $issueDao->getIssueByBestIssueId($issueId, $journal->getId());
				if ($issue == null) {
					echo Locale::translate('plugins.importexport.crossref.cliError') . "\n";
					echo Locale::translate('plugins.importexport.crossref.export.error.issueNotFound', array('issueId' => $issueId)) . "\n\n";
					return;
				}
				$issues = array($issue);
				if (!$this->exportIssues($journal, $issues, $xmlFile)) {
					echo Locale::translate('plugins.importexport.crossref.cliError') . "\n";
					echo Locale::translate('plugins.importexport.crossref.export.error.couldNotWrite', array('fileName' => $xmlFile)) . "\n\n";
				}
				return;
		}
		$this->usage($scriptName);

	}

	/**
	 * Display the command-line usage information
	 */
	function usage($scriptName) {
		echo Locale::translate('plugins.importexport.crossref.cliUsage', array(
			'scriptName' => $scriptName,
			'pluginName' => $this->getName()
		)) . "\n";
	}
}

?>
