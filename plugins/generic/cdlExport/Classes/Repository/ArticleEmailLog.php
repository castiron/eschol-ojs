<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;

class ArticleEmailLog {
    public function fetchByArticle($article)
    {
        return DAOFactory::get()->getDAO('articleEmailLog')->getArticleLogEntries($article->getId());
    }
}
