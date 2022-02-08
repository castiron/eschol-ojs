<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

class ReviewAssignment extends AbstractDataObjectMapper {
    protected static $mapping = '
        reviewId ->         id                
                            reviewerId
        reviewerFullName -> fullName
                            dateRated        | datetime
                            lastModified     | datetime
                            dateAssigned     | datetime
                            dateNotified     | datetime
                            dateConfirmed    | datetime
                            dateCompleted    | datetime
                            dateAcknowledged | datetime
                            dateReminded     | datetime
                            dateDue          | datetime
                            dateResponseDue  | datetime
                            declined         | boolean
                            replaced         | boolean
                            cancelled        | boolean
                            reviewFile
                            reviewerFile
    ';
}