<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;

class Section {

    public function fetchByJournal($journal)
    {
        return DAOFactory::get()->getDAO('section')->getJournalSections($journal->getId());
    }

}
