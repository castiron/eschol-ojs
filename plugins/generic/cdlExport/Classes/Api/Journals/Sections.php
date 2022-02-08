<?php namespace CdlExportPlugin\Api\Journals;

use CdlExportPlugin\Api\ApiRoute;

class Sections extends ApiRoute {
    protected $journalRepository;
    protected $sectionRepository;

    /**
     * @param $args
     * @return array
     * @throws \Exception
     */
    public function execute($args)
    {
        $journal = $this->journalRepository->fetchOneById($args['journal']);
        $sectionsResultSet = $this->sectionRepository->fetchByJournal($journal);

        $sections = [];
        foreach ($sectionsResultSet->toArray() as $section) {
            $sections[] = [
                'title' => $section->getLocalizedTitle(),
                'abbreviation' => $section->getLocalizedAbbrev(),
                'id' => $section->getId()
            ];
        }

        return $sections;
    }
}