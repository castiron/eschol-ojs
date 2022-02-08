<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;

class ArticleEventLog {
    public function fetchByArticle($article)
    {
        return DAOFactory::get()->getDAO('articleEventLog')->getArticleLogEntries($article->getId());
    }
}
