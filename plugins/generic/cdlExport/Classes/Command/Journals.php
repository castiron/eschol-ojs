<?php namespace CdlExportPlugin\Command;

use CdlExportPlugin\Command\Journals\Journal;
use CdlExportPlugin\Command\Traits\CommandHandler;
use CdlExportPlugin\Utility\DAOFactory;
use CdlExportPlugin\Utility\DataObjectUtility;

class Journals {
    use CommandHandler;

    public function __construct($args) {
        $this->initializeHandler($args);
    }

    /**
     * List things
     */
    function execute() {
        $data = [];
        if(array_key_exists(0, $this->args)) {
            (new Journal($this->args))->execute();
        } else {
            $journalsResultSet = DAOFactory::get()->getDAO('journal')->getJournals();
            foreach($journalsResultSet->toArray() as $journal) {
                $data[] = DataObjectUtility::dataObjectToArray($journal);
            }
            return ['journals' => $data];
        }
    }
}