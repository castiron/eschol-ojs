<?php namespace CdlExportPlugin\Utility;

use \DAORegistry;

class DAOFactory {
    /**
     * @var null
     */
    protected static $instance = null;

    /**
     * @var array
     */
    protected $DAOInstances = [];

    /**
     * DAOFactory constructor.
     */
    protected function __construct() {
    }

    /**
     * @return \CdlExportPlugin\Utility\DAOFactory
     */
    public function get() {
        return self::$instance === null ? new self : self::instance;
    }

    /**
     * @param $daoName
     * @return mixed
     */
    public function getDAO($daoName) {
        if(!array_key_exists($daoName, $this->DAOInstances)) {
            // This function will fatal error if the DAO isn't registered. Fine.
            $daoInstance = DAORegistry::getDAO(ucwords($daoName) . "DAO");
            $this->DAOInstances[$daoName] = $daoInstance;
        }

        return $this->DAOInstances[$daoName];
    }
}