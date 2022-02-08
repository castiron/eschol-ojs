<?php namespace CdlExportPlugin\Builder;

use CdlExportPlugin\Builder\Mapper\NestedMapper;
use CdlExportPlugin\Utility\DAOFactory;
use CdlExportPlugin\Utility\DataObjectUtility;

class History
{
    protected $article;

    protected $events = [];

    public function __construct($article) {
        $this->article = $article;
        $this->build();
    }

    public function toArray() {
        return $this->events;
    }

    protected function build() {
        $this->logEntries();

// We might be able to get rid of these, as they represent an early implementation. Keeping for now for easy
// access
//        $this->createArticle();
//        $this->assignEditors();
//        $this->assignReviewers();
//        $this->assignCopyeditor();
//        $this->assignLayoutEditor();
//        $this->assignProofReader();
    }

    protected function logEntries() {
        $articleLogEntries = DAOFactory::get()->getDAO('articleEventLog')
            ->getArticleLogEntries($this->article->getId())->toArray();

        usort($articleLogEntries, function($a, $b) {
            return (new \DateTime($a->getDateLogged()) > (new \DateTime($b->getDateLogged())));
        });

        foreach($articleLogEntries as $articleLogEntry) {
            $associatedObject = $this->getAssociatedObject($articleLogEntry);

            $this->append([
                'articleLogEntry' => DataObjectUtility::dataObjectToArray($articleLogEntry),
                'associatedObject' => NestedMapper::nest($associatedObject)
            ]);
        }
    }

    protected function getAssociatedObject($articleLogEntry) {
        switch($articleLogEntry->getAssocType()) {
            case ARTICLE_LOG_TYPE_DEFAULT:

            case ARTICLE_LOG_TYPE_AUTHOR:
            case ARTICLE_LOG_TYPE_EDITOR:
                $dao = 'user';
                $method = 'getUser';
                break;
            break;
            case ARTICLE_LOG_TYPE_REVIEW:
                $dao = 'reviewAssignment';
                $method = 'getReviewAssignmentById';
            case ARTICLE_LOG_TYPE_COPYEDIT:
                // CopyeditAssignment
            case ARTICLE_LOG_TYPE_LAYOUT:
                // LayoutAssignment
            case ARTICLE_LOG_TYPE_PROOFREAD:
                // ProofreadAssignment
        }

        if($dao and $method) {
            return DAOFactory::get()->getDAO($dao)->$method($articleLogEntry->getAssocId());
        }
        return null;
    }

    protected function createArticle() {
        $authorSubmission = DAOFactory::get()->getDAO('authorSubmission')->getAuthorSubmission($this->article->getId());

        $this->append([
            "type" => "article.create",
            "data" => [
                "dateSubmitted" => $this->article->getDateSubmitted(),
                "authors" => NestedMapper::nest($this->article->getAuthors()),
                "removedAuthors" => NestedMapper::nest($this->article->getRemovedAuthors()),
                "user" => NestedMapper::nest($this->article->getUser()),
                "file" => NestedMapper::nest($authorSubmission->getSubmissionFile()),
                "supplementaryFiles" => NestedMapper::nest($authorSubmission->getSuppFiles())
            ]
        ]);
    }

    // TODO: this doesn't quite seem right. Why do we need section editor and editor information to
    // render this bit?
    protected function assignEditors() {
        $editAssignments = DAOFactory::get()->getDAO('editAssignment')
            ->getEditAssignmentsByArticleId($this->article->getId())->toArray();

        $sectionEditorSubmission = DAOFactory::get()->getDAO('sectionEditorSubmission')
            ->getSectionEditorSubmission($this->article->getId());


        $this->append([
            "type" => "article.assignEditor",
            "data" => [
                "editor" => NestedMapper::nest($editAssignments),
                "reviewFile" => NestedMapper::nest($sectionEditorSubmission->getReviewFile()),
                "editorFile" => NestedMapper::nest($sectionEditorSubmission->getEditorFile())
            ]
        ]);
    }

    protected function assignReviewers() {
        $reviewAssignments = DAOFactory::get()->getDAO('reviewAssignment')
            ->getReviewAssignmentsByArticleId($this->article->getId());

        foreach($reviewAssignments as $reviewAssignment) {
            $this->append([
                "type" => "article.assignReviewer",
                "data" => [
                    NestedMapper::nest($reviewAssignment)
                ]
            ]);
        }
    }

    protected function assignCopyeditor() {
        $copyEditorSubmission = DAOFactory::get()->getDAO('copyeditorSubmission')
            ->getCopyeditorSubmission($this->article->getId());

        $this->append([
            "type" => "article.assignCopyEditor",
            "data" => NestedMapper::nest($copyEditorSubmission)
        ]);
    }

    protected function assignLayoutEditor() {
        $layoutEditorSubmission = DAOFactory::get()->getDAO('layoutEditorSubmission')
            ->getSubmission($this->article->getId());

        $this->append([
            "type" => "article.assignLayoutEditor",
            "data" => NestedMapper::nest($layoutEditorSubmission)
        ]);
    }

    protected function assignProofreader() {
        $proofreaderSubmission = DAOFactory::get()->getDAO('proofreaderSubmission')
            ->getSubmission($this->article->getId());

        $this->append([
            "type" => "article.assignProofreader",
            "data" => NestedMapper::nest($proofreaderSubmission)
        ]);
    }



    protected function append($event) {
        $this->events[] = $event;
    }
}