<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

class AbstractSubmission extends AbstractDataObjectMapper {
    protected static $mapping = '
                     editAssignments
                     galleys
        suppFiles -> supplementaryFiles
    ';
}