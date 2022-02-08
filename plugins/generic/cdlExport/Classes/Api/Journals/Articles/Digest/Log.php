<?php namespace CdlExportPlugin\Api\Journals\Articles\Digest;

use CdlExportPlugin\Utility\DataObjectUtility;
use CdlExportPlugin\Api\ApiRoute;

class Log extends ApiRoute {
    protected $journalRepository;
    protected $articleRepository;
    protected $articleEventLogRepository;

    /**
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function execute($args)
    {
        $journal = $this->journalRepository->fetchOneById($args['journal']);
        $article = $this->articleRepository->fetchByIdAndJournal($args['article'], $journal);
        $resultSet = $this->articleEventLogRepository->fetchByArticle($article);
        return DataObjectUtility::resultSetToArray($resultSet);
    }
}