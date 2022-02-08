<?php namespace CdlExportPlugin\Api\Journals;

use CdlExportPlugin\Api\ApiRoute;

class Issues extends ApiRoute  {
    protected $journalRepository;
    protected $issueRepository;

    /**
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function execute($args)
    {
        $journal = $this->journalRepository->fetchOneById($args['journal']);
        $issuesResultSet = $this->issueRepository->fetchByJournal($journal);

        $issues = [];
        foreach ($issuesResultSet->toArray() as $issue) {
            $issues[] = [
                'title'                => $issue->getLocalizedTitle(),
                'id'                   => $issue->getId(),
                'volume'               => $issue->getVolume(),
                'number'               => $issue->getNumber(),
                'year'                 => $issue->getYear(),
                'published'            => (bool) $issue->getPublished(),
                'current'              => (bool) $issue->getCurrent(),
                'datePublished'        => $issue->getDatePublished(),
                'coverPageDescription' => $issue->getLocalizedCoverPageDescription(),
                'coverPageAltText'     => $issue->getLocalizedCoverPageAltText(),
                'width'                => $issue->getIssueWidth(),
                'height'               => $issue->getIssueHeight(),
                'articlesCount'        => $issue->getNumArticles(),
                'issueFileName'        => $issue->getLocalizedFileName(),
                'originalFileName'     => $issue->getLocalizedOriginalFileName(),
            ];
        }

        return $issues;
    }
}