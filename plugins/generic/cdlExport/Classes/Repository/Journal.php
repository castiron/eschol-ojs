<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;
use CdlExportPlugin\Utility\Traits\DAOCache;

class Journal {

    public function fetchAll()
    {
        return DAOFactory::get()->getDAO('journal')->getJournals();
    }

    public function fetchOneById($id)
    {
        $journal = DAOFactory::get()->getDAO('journal')->getJournal($id);
        if(is_null($journal)) throw new \Exception("Journal $id not found");
        return $journal;
    }
}
