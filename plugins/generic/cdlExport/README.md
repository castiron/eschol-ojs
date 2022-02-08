# CDL Export

An OJS plugin that exports editorial and publication history for import into Janeway.

# CLI Controllers

Data is displayed as JSON from two handlers, `api` and `journals`. `journals` served as a proof-of-concept for the
extraction of data from OJS, but still can be helpful because it will display all data associated with an object,
whether a journal, article, section or issue. `api` is intended to be a more robust set of endpoints that expose all
the editorial history of a journal to be synced to other systems.

Because all of these endpoints return raw JSON, it's useful to pipe them through `jq` to increase human readability.
They can also be piped through `jq` and `less` with colors preserved like so: `| jq -C | less -R`.

`api`

Call these endpoints with `php tools/importExport.php CdlExportPlugin api [PATHish]`.

The `[PATHish]` is a absolute path-like string, such as `/journals` or `/journals/15/articles/16140/synthetics/history`.
The allowed PATHish routes are defined in `Classes/Command/Api.php`.

Currently, paths are:

* `/journals`
* `/journals/[JOURNAL_ID]`
* `/journals/[JOURNAL_ID]/issues`
* `/journals/[JOURNAL_ID]/sections`
* `/journals/[JOURNAL_ID]/articles`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]/digest/emails`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]/digest/emails.txt`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]/digest/log`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]/digest/log`
* `/journals/[JOURNAL_ID]/articles/[ARTICLE_ID]/synthetics/history`

`journals`

Call these endpoints with `php tools/importExport.php CdlExportPlugin journals [ARGS...]`. The responses for the
endpoints are defined in `Classes/Command/Journals.php` and `Classes/Command/Journals/Journal.php`.

The arguments are space separated, like a "normal" CLI app. Without any arguments passed, a list of journals is 
returned. When a journal id is appended, `journals 15`, for example, detailed journal data is displayed. Additional
slices of journal data can be displayed:

* `journals 15 issues`
* `journals 15 sections`
* `journals 15 articles`

Individual articles, with ALL associated data objects from OJS can be viewed by specifying an article id:

* `journals [JOURNAL_ID] articles [ARTICLE_ID]`

`journals` can be used for exploring raw data, whereas `api` is intentionally tailored toward data that is worthy of 
transfer to another system.