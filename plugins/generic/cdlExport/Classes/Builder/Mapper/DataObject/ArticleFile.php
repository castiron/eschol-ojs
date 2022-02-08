<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

class ArticleFile  extends AbstractDataObjectMapper {
    protected static $mapping = '
        fileId -> id
                  filePath
                  fileName
                  fileType
                  originalFilename
                  dateUploaded 
    ';

    /**
     * @param $model
     * @return string
     */
    protected static function getSystemId($model) {
        return get_class($model).':'.$model->getArticleId().':'.$model->getFileId();
    }
}