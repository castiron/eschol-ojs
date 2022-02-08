<?php

/**
 * @file CdlExportPlugin.php
 *
 * Copyright (c) 2021 Cast Iron Coding
 * Distributed under the GNU GPL v2.
 *
 * @class CdlExportPlugin
 * @ingroup plugins_importexport_cdlexport
 *
 * @brief Export plugin for CDL's Journal Portability project
 */

// $Id$

import('lib.pkp.classes.plugins.GenericPlugin');

use CdlExportPlugin\Command\Controller;

class CdlExportPlugin extends GenericPlugin {
    const PLUGIN_DISPLAY_NAME = 'CDL OJS Export Plugin';
    const PLUGIN_DESCRIPTION = "Export plugin for CDL's Journal Portability project";

    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @return boolean True if plugin initialized successfully; if false,
     * 	the plugin will not be registered.
     */
    function register($category, $path) {
        $this->registerAutoload();

        if(parent::register($category, $path)) {
            if(Config::getVar('cdlexport', 'enable_plugin_endpoints') && $this->requestIsAuthorized()) {
                $this->registerLoadHandlerHook();
            }
        }
    }

    /**
     * Check to see if the current request is authorized
     * @return bool
     */
    protected function requestIsAuthorized() {
        if(!Config::getVar('cdlexport', 'require_auth')) return true;

        if(strlen($_SERVER['PHP_AUTH_USER']) > 0 && strlen($_SERVER['PHP_AUTH_PW']) > 0 &&
            Config::getVar('cdlexport', 'user') === $_SERVER['PHP_AUTH_USER'] &&
            Config::getVar('cdlexport', 'password') === $_SERVER['PHP_AUTH_PW']) return true;

        return false;
    }

    /**
     *
     */
    protected function registerLoadHandlerHook() {
        HookRegistry::register('LoadHandler', function ($hookname, $params) {
            if ($params[0] == 'cdlexport') {
                define('HANDLER_CLASS', 'CdlExportHandler');
                define('CDL_EXPORT_PLUGIN_NAME', $this->getName());
                $handlerFile =& $params[2];
                $handlerFile = $this->getPluginPath() . '/Classes/' . 'CdlExportHandler.php';
            }
        });
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return String name of plugin
     */
    function getName() {
        return __CLASS__;
    }

    /**
     * @return string
     */
    function getDisplayName() {
        return self::PLUGIN_DISPLAY_NAME;
    }

    /**
     * @return string
     */
    function getDescription() {
        return self::PLUGIN_DESCRIPTION;
    }

    function display($args) {
        die("Code in ".__CLASS__."::".__METHOD__." doesn't do anything.");
    }

    /**
     * Execute import/export tasks using the command-line interface.
     * @param $args Parameters to the plugin
     */
    function executeCLI($scriptName, $args) {
        error_reporting(E_ERROR | E_PARSE); // Don't show warnings or notices, lots of rattles in the OJS engine
        $cliController = new Controller();
        $cliController->initializeHandler($args);
        $cliController->execute();
    }

    /**
     *
     */
    function registerAutoload() {
        spl_autoload_register(function($class) {
            $namespace = 'CdlExportPlugin\\';
            if(strpos($class, $namespace) === 0) {
                $file = __DIR__.'/'.str_replace('\\', '/', substr($class, strlen($namespace))).'.php';
                if(file_exists($file)) {
                    include $file;
                }
            }
        });
    }
}

