<?php namespace CdlExportPlugin\Api\Journals\Articles\Synthetics;

use CdlExportPlugin\Api\ApiRoute;

class History extends ApiRoute {
    protected $journalRepository;
    protected $articleRepository;

    /**
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function execute($args)
    {
        $journal = $this->journalRepository->fetchOneById($args['journal']);
        $article = $this->articleRepository->fetchByIdAndJournal($args['article'], $journal);

        $history = new \CdlExportPlugin\Builder\History($article);
        return $history->toArray();
    }
}