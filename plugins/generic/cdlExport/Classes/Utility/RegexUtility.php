<?php namespace CdlExportPlugin\Utility;

class RegexUtility
{
    /**
     * Extract the named matches from a regex, with a regex!
     * @param $route
     * @return array|mixed
     */
    static public function getRegexNamedMatches($route) {
        return preg_match_all('/\?P<([a-zA-Z0-9_]+)>/', $route, $matches) ? $matches[1] : [];
    }
}