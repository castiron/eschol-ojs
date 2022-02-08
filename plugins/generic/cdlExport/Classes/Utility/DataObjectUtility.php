<?php namespace CdlExportPlugin\Utility;

class DataObjectUtility {
    /**
     * Given a resultSet returns an array of data objects cast as arrays
     * @param $resultSet
     * @return array|array[]|\stdClass[]
     */
    public static function resultSetToArray($resultSet, $exclude = ['allData']) {
        return array_map(
            function($dataObject) use($exclude) {
              return DataObjectUtility::dataObjectToArray($dataObject, $exclude);
            },
            $resultSet->toArray()
        );
    }

    /**
     * Given a DataObject, iterates the getters and builds an array of the results. If the getters return
     * a DataObject, we recurse. If it's not given a DO or an array, it returns the value.
     * @param $dataObject
     * @return mixed
     */
    public static function dataObjectToArray($dataObject, $exclude = ['allData']) {
        if(is_object($dataObject) && is_subclass_of($dataObject, 'DataObject')) {
            $out = new \stdClass;
            $out->__class = get_class($dataObject);
            foreach(self::getGetters($dataObject) as $method) {
                $key = self::stripGetFromGetter($method);
                $output = $dataObject->$method();
                if(!in_array($key, $exclude)) $out->$key = self::dataObjectToArray($output);
            }
            return $out;
        } elseif(is_array($dataObject)) {
            $out = [];
            foreach ($dataObject as $k => $v) {
                $out[$k] = self::dataObjectToArray($v);
            }
            return $out;
        } else {
            return $dataObject;
        }
    }

    /**
     * Takes a getter name and returns it as a property name. If it doesn't begin with "get" the original
     * value is returned.
     * @param string $methodName
     * @return string
     */
    public static function stripGetFromGetter(string $methodName) {
        if(substr($methodName, 0, 3) === 'get') {
            return strtolower($methodName[3]) . substr($methodName, 4);
        }
        return $methodName;
    }

    /**
     * Given an instance of a DataObject class, returns an array of all method names that begin with `get` and
     * take 0 arguments.
     * @param object $instance
     * @return mixed
     */
    public static function getGetters(object $instance) {
        $class = get_class($instance);
        return array_filter(
            get_class_methods($instance),
            function($methodName) use($class) {
                if(substr($methodName, 0, 3) !== 'get') return false;
                $methodReflection = new \ReflectionMethod($class, $methodName);
                return count($methodReflection->getParameters()) === 0;
            }
        );
    }


    /**
     * This function accepts an object $a, and an object, array (or anything) $b, and reduces $b if it's an object to
     * only properties that $a has a different value for, or do not exist on $a. For array or other other values, it
     * simply adds the value. The value (whether an object, array or other) always gets added to the $propertyName
     * property in the returned object. To disable the redundancy checking, set $fullForceMerge to true. To turn off the
     * debug stats, set $disableDebugStats to true.
     * @param $a
     * @param $b
     * @param $propertyName
     * @param false $forceFullMerge
     * @param false $disableDebugStats
     * @return mixed
     */
    public static function mergeWithoutRedundancy($a, $b, $propertyName, $forceFullMerge = false, $disableDebugStats = false) {
        if(gettype($b) !== 'object' || $forceFullMerge) {
            $supplemental = $b;
        } else {
            $differentValues = [];
            $duplicates = [];
            $uniqueProperties = [];
            $supplemental = new \stdClass;
            $objectProperties = get_object_vars($b);
            foreach ($objectProperties as $property => $value) {
                if (property_exists($a, $property)) {
                    if ($a->$property != $value) {
                        $supplemental->$property = $b->$property;
                        $differentValues[] = $property;
                    } else {
                        $duplicates[] = $property;
                    }
                } else {
                    $supplemental->$property = $value;
                    $uniqueProperties[] = $property;
                }
            }
            if(!$disableDebugStats) {
                asort($differentValues);
                asort($duplicates);
                asort($uniqueProperties);

                $supplemental->__mergeWithoutRedundancyStats = (object) [
                    '__differentValues' => implode(', ', $differentValues),
                    '__duplicates' => implode(', ', $duplicates),
                    '__uniqueProperties' => implode(', ', $uniqueProperties)
                ];
            }
        }
        $a->$propertyName = $supplemental;
        return $a;
    }

    /**
     * @param $object
     * @return mixed
     */
    public static function isDataObject($object) {
        return is_subclass_of($object, 'DataObject');
    }

    /**
     * @param $object
     * @return mixed
     */
    public static function isResultSet($object) {
        return get_class($object) == 'DAOResultFactory';
    }
}