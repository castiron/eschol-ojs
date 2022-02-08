<?php namespace CdlExportPlugin\Command\Journals;

use CdlExportPlugin\Command\Traits\CommandHandler;
use CdlExportPlugin\Utility\DAOFactory;
use CdlExportPlugin\Utility\DataObjectUtility;

class Journal {
    use CommandHandler;

    private $journal;

    public function __construct($args) {
        $this->initializeHandler($args);
    }

    /**
     * Looks at the arguments and handles the generation of the response
     */
    public function execute() {
        $journalIdentifier = array_shift($this->args);

        if(preg_match('/^[0-9]+$/', $journalIdentifier)) {
            $this->journal = DAOFactory::get()->getDAO('journal')->getJournal($journalIdentifier);
        } else {
            $this->journal = DAOFactory::get()->getDAO('journal')->getJournalByPath($journalIdentifier);
        }

        if(is_null($this->journal)) {
            throw new \Exception("Could not find a journal with path / id $journalIdentifier");
        }

        $scope = array_shift($this->args);
        if(strlen($scope) > 0) {
            if($scope === 'issues') $data = $this->getIssues();
            if($scope === 'sections') $data = $this->getSections();
            if($scope === 'articles') $data = $this->getArticles($this->args);
        } else {
            $data = DataObjectUtility::dataObjectToArray($this->journal);
        }

        return $data;
    }

    /**
     * Returns the issues of a journal
     * @return array|array[]|\stdClass[]
     */
    protected function getIssues() {
        return DataObjectUtility::resultSetToArray(
            DAOFactory::get()->getDAO('issue')->getIssues($this->journal->getId())
        );
    }

    /**
     * Returns the sections of journal
     * @return array|array[]|\stdClass[]
     */
    protected function getSections() {
        return DataObjectUtility::resultSetToArray(
            DAOFactory::get()->getDAO('section')->getJournalSections($this->journal->getId())
        );
    }

    /**
     * TODO: this merging needs some serious cleanup!
     *
     * This renders the articles output. For a single article, you get a lot of data. For many articles, you get
     * some data.
     * @param array $args
     * @return array|array[]|\stdClass[]
     */
    protected function getArticles($args = []) {
        $articleId = array_shift($args);

        $articleData = !is_null($articleId) ?
            [DataObjectUtility::dataObjectToArray(DAOFactory::get()->getDAO('article')->getArticle($articleId, $this->journal->getId()))] :
            DataObjectUtility::resultSetToArray(
                DAOFactory::get()->getDAO('article')->getArticlesByJournalId($this->journal->getId())
            );

        // Only show all this stuff if we're display an article singly
        if(!is_null($articleId)) {
            foreach($articleData as &$article) {
                $dataMergeConfig = [
                    'authorSubmission->getAuthorSubmission',
                    'editAssignment->getEditAssignmentsByArticleId',
                    'editorSubmission->getEditorSubmission',
                    'sectionEditorSubmission->getSectionEditorSubmission',
                    'reviewAssignment->getReviewAssignmentsByArticleId',
                    'reviewerSubmission->getEditorDecisions',
                    'copyeditorSubmission->getCopyeditorSubmission',
                    'layoutEditorSubmission->getSubmission',
                    'proofreaderSubmission->getSubmission',
                    'articleComment->getArticleComments',
                    'articleFile->getArticleFilesByArticle',
                    'articleGalley->getGalleysByArticle',
                    'suppFile->getSuppFilesByArticle',
                    'articleEmailLog->getArticleLogEntries',
                    'articleEventLog->getArticleLogEntries'
                ];

                foreach($dataMergeConfig as $mergeConfig) {
                    list($dao, $method) = explode('->', $mergeConfig);
                    $daoResult = DAOFactory::get()->getDAO($dao)->$method($article->id);
                    if(DataObjectUtility::isDataObject($daoResult) || is_array($daoResult)) {
                        $data = DataObjectUtility::dataObjectToArray($daoResult);
                    } elseif(DataObjectUtility::isResultSet($daoResult)) {
                        $data = DataObjectUtility::resultSetToArray($daoResult);
                    } else {
                        $data = $daoResult;
                    }

                    $article = DataObjectUtility::mergeWithoutRedundancy($article, $data, '__'.$dao);
                }
            }
        }
        return $articleData;
    }
}