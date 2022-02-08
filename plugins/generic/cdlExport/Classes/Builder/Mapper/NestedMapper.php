<?php namespace CdlExportPlugin\Builder\Mapper;

class NestedMapper
{
    public static function nest($mappable, $placeholder = false) {
        if($placeholder) return "PLACEHOLDER";

        if(is_array($mappable)) {
            $out = [];
            foreach($mappable as $item) {
                $out[] = self::nest($item);
            }
        } elseif(is_object($mappable)) {
            $className = '\\CdlExportPlugin\\Builder\\Mapper\\DataObject\\'.ucfirst(get_class($mappable));
            if(class_exists($className)) {
                $out = $className::map($mappable);
            } else {
                $out = "Couldn't find mapper " . $className;
            }
        } else {
            $out = $mappable;
        }
        return $out;
    }
}