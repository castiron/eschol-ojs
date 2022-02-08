<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;

class Article {

    public function fetchByJournal($journal)
    {
        return DAOFactory::get()->getDAO('article')->getArticlesByJournalId($journal->getId());
    }

    public function fetchById($id)
    {
        $article = DAOFactory::get()->getDAO('article')->getArticle($id);
        if(is_null($article)) throw new \Exception("Article $id not found");
        return $article;
    }

    public function fetchByIdAndJournal($id, $journal)
    {
        $article = DAOFactory::get()->getDAO('article')->getArticle($id, $journal->getId());
        if(is_null($article)) throw new \Exception("Article $id not found");
        return $article;
    }

}
