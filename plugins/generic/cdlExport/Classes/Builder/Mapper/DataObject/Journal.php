<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

class Journal extends AbstractDataObjectMapper {
    protected static $mapping = '
           id
           path
           localizedTitle -> title
           journalInitials -> initials
            
    ';
}