<?php namespace CdlExportPlugin\Command;

use CdlExportPlugin\Command\Traits\CommandHandler;

class Controller {
    use CommandHandler;

    private $allowedCommands = [
      'journals' => Journals::class,
      'api' => Api::class
    ];

    /**
     *
     */
    function usage() {
        die('moved to HTTP endpoints, clean this up');
        echo <<<EOF
Usage: `php tools/importExport.php CdlExportPlugin [COMMAND] [ARGS...]`

The current commands are:
 
`api`

This is meant to supply data to an intermediary tool for syncing journal publication data to Janeway. There is only
one allowed argument, which will resemble a path in an HTTP request. These "routes" are defined in the
CdlExportPlugin\Command\Api class.

Like the examples for the `journals` command, below, in containerized environments you might use `script/cli` to invoke
the CLI, and you can use `jq` to parse and transform the JSON.

Examples:

`api /journals`
`api /journals/4/articles`
`api /journals/4/articles/43326`
`api /journals/4/sections`
`api /journals/4/issues`

 
`journals`

This was written as a proof of concept for extracting data from OJS as JSON. Arguments are space-separated. Here
are some examples. Note that in the examples below we're using the `script/cli` Docker shortcut.

```
# Raw journals list JSON
script/cli journals

# Journals list, all fields, formatted with jq
script/cli journals | jq

# Journals list, select fields
script/cli journals | jq '.journals | .[] | {title: .journalTitle, path: .path, id: .id}'

# Show single journal data, using the journal path as the identifier
script/cli journals ucla_french_pg | jq

# Show single journal issues, all fields
script/cli journals ucla_french_pg issues | jq

# Show single journal issues, select fields, sorted by publication date
script/cli journals our_bsj issues | jq 'sort_by(.datePublished) | .[] | { title: .issueTitle, id: .id, articleCount: .numArticles, datePublished: .datePublished }'

# Show single journal sections, all fields
script/cli journals ucla_french_pg sections | jq

# List basic article data from single journal
script/cli journals ucla_french_pg articles | jq '.[] | { id: .id, title: .articleTitle }'

# Good example of lots of logs / emails (see http://localhost:8080/index.php/ucla_french_pg/editor/submissionHistory/16140)
script/cli journals ucla_french_pg articles 16140 | jq

# Another journal issues, sorted by ppublication date
script/cli journals cjpp issues  | jq 'sort_by(.datePublished) | .[] | { title: .issueTitle, id: .id, articleCount: .numArticles, datePublished: .datePublished }'

# Single article, interesting because there is are no logged emails except one that I created (see http://localhost:8080/index.php/cjpp/editor/submission/54579)
script/cli journals cjpp articles 54579 | jq
```
EOF;

    }

}