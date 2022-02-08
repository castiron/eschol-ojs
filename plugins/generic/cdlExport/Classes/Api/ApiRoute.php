<?php namespace CdlExportPlugin\Api;

class ApiRoute
{
    use RepositoryInjectionTrait;

    public function __construct()
    {
        $this->injectRepositories();
    }
}