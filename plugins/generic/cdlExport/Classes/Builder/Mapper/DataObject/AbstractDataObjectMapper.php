<?php namespace CdlExportPlugin\Builder\Mapper\DataObject;

use CdlExportPlugin\Builder\Mapper\NestedMapper;

class AbstractDataObjectMapper {

    /**
     * Extend this class, and name the child class after a OJS class. Add a static parameter called $mapping, which
     * is a string. Each line of the string contains a field name. If the field name is being mapped to a different
     * field that the source field name, use this syntax `sourceField -> targetField`. If you need to call a method in the
     * mapper, we'll do it another way which hasn't been implemented yet.
     * @param $model
     * @return array
     */
    public static function map($model)
    {
        $out = [];
        $mapping = explode("\n", trim(static::$mapping));
        foreach ($mapping as $mappingConfig) {
            $parts = explode('|', $mappingConfig);
            $fieldConfig = trim(array_shift($parts));

            $trimmed = trim($fieldConfig);

            list($theirs, $ours) = explode('->', $trimmed);

            $ours = trim($ours);
            $theirs = trim($theirs);

            if(strlen($ours) === 0) {
                $ours = $theirs;
            }
            $methodName = 'get' . ucfirst($theirs);

            // Special handling for all local ids
            if($ours === 'id') {
                $value = static::getSystemId($model, $theirs);
            } else {
                $value = NestedMapper::nest($model->$methodName());
            }

            // Process filters
            if(count($parts)) {
                foreach($parts as $filter) {
                    $value = self::applyFilter(trim($filter), $value);
                }
            }

            $out[$ours] = $value;
        }
        return $out;
    }

    /**
     * @param $model
     * @return string
     */
    protected static function getSystemId($model, $theirs = 'id') {
        $method = 'get'.ucfirst($theirs);
        return get_class($model).':'.$model->$method();
    }

    /**
     * @param $filter
     * @param $value
     * @return bool|mixed|string
     * @throws \Exception
     */
    protected static function applyFilter($filter, $value) {
        if($filter === 'boolean') return static::applyBooleanFilter($value);
        if($filter === 'datetime') return static::applyDatetimeFilter($value);

        throw new \Exception("Filter $filter does not exist");
    }

    /**
     * @param $value
     * @return string|null
     * @throws \Exception
     */
    protected static function applyDatetimeFilter($value) {
        if(!is_null($value) && strlen($value) > 0) {
            return (new \DateTime($value, new \DateTimeZone('America/Los_Angeles')))
                ->format('c');
        }
        return null;
    }

    /**
     * @param $value
     * @return bool
     */
    protected static function applyBooleanFilter($value) {
        return (bool) $value;
    }
}