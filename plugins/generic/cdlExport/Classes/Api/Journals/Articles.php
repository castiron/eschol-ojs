<?php namespace CdlExportPlugin\Api\Journals;

use CdlExportPlugin\Utility\DataObjectUtility;
use CdlExportPlugin\Api\ApiRoute;

class Articles extends ApiRoute  {
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
        return @$args['article'] ? $this->getArticle($args['article'], $journal) : $this->getArticles($journal);
    }

    /**
     * @param $journal
     * @return array
     */
    protected function getArticles($journal)
    {
        $articleResultSet = $this->articleRepository->fetchByJournal($journal);

        $issues = [];
        foreach ($articleResultSet->toArray() as $issue) {
            $issues[] = [
                'title'        => $issue->getLocalizedTitle(),
                'id'           => $issue->getId(),
                'sectionId'    => $issue->getSectionId(),
                'sectionTitle' => $issue->getSectionTitle()
            ];
        }

        return $issues;
    }

    /**
     * @param $id
     * @param $journal
     * @return array
     */
    protected function getArticle($id, $journal)
    {
        $article = $this->articleRepository->fetchByIdAndJournal($id, $journal);
        return DataObjectUtility::dataObjectToArray($article);
    }
}