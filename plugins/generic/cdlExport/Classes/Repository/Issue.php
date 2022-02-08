<?php namespace CdlExportPlugin\Repository;

use CdlExportPlugin\Utility\DAOFactory;

class Issue {

    public function fetchByJournal($journal)
    {
        return DAOFactory::get()->getDAO('issue')->getIssues($journal->getId());
    }

}
