<?php namespace CdlExportPlugin\Api;

use CdlExportPlugin\Builder\Mapper\NestedMapper;
use CdlExportPlugin\Utility\DataObjectUtility;
use Config;

class Journals extends ApiRoute {
    protected $journalRepository;

    public function execute($args)
    {
        return @$args['journal'] ? $this->getJournal($args['journal']) : $this->getJournals();
    }

    /**
     * @param $id
     * @return array|mixed|\stdClass
     * @throws \Exception
     */
    protected function getJournal($id)
    {
        $journal = $this->journalRepository->fetchOneById($id);
        $data = NestedMapper::nest($journal);
        $pageHeaderTitleImage = $journal->getSettings()['pageHeaderTitleImage']['en_US'];
        $data['logoPath'] = getcwd().'/'.
            Config::getVar('files', 'public_files_dir').'/journals/'.$journal->getId().'/'.
            $pageHeaderTitleImage['uploadName'];
        return $data;
    }

    /**
     * @return array
     */
    protected function getJournals()
    {
        $journalsResultSet = $this->journalRepository->fetchAll();
        $journals = [];
        foreach ($journalsResultSet->toArray() as $journal) {
            $journals[] = [
                'title' => $journal->getLocalizedTitle(),
                'path' => $journal->getPath(),
                'id' => $journal->getId()
            ];
        }
        return $journals;
    }
}