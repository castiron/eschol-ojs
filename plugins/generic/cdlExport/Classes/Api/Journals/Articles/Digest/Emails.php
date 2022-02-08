<?php namespace CdlExportPlugin\Api\Journals\Articles\Digest;

use CdlExportPlugin\Api\ApiRoute;
use CdlExportPlugin\Utility\DataObjectUtility;

/**
 * TODO: This should be broken up into smaller methods, but let's hold off until we see what functionality
 * is shared among other routes.
 * Class Emails
 * @package CdlExportPlugin\Api\Journals\Articles\Digest
 */
class Emails extends ApiRoute {
    protected $journalRepository;
    protected $articleRepository;
    protected $articleEmailLogRepository;

    /**
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function execute($args)
    {
        // If we need this elsewhere, abstract it
        if(strlen($args['format']) > 0) {
            if(!in_array($args['format'], ['txt', 'json'])) {
                throw new \Exception("Format .{$args['format']} not allowed");
            } else {
                $format = $args['format'];
            }
        } else $format = 'json';

        $journal = $this->journalRepository->fetchOneById($args['journal']);
        $article = $this->articleRepository->fetchByIdAndJournal($args['article'], $journal);
        $resultSet = $this->articleEmailLogRepository->fetchByArticle($article);
        $articleEmailLogEntries = DataObjectUtility::resultSetToArray($resultSet);

        $emails = [];
        foreach($articleEmailLogEntries as $articleEmailLogEntry) {
            $emails[] = (object) [
                'ip'        => $articleEmailLogEntry->iPAddress,
                'from'      => $articleEmailLogEntry->from,
                'to'        => $articleEmailLogEntry->recipients,
                'cc'        => $articleEmailLogEntry->ccs,
                'bcc'       => $articleEmailLogEntry->bccs,
                'subject'   => $articleEmailLogEntry->subject,
                'body'      => $articleEmailLogEntry->body,
                'reference' => $articleEmailLogEntry->__class.':'.$articleEmailLogEntry->id,
                'datetime'  => new \DateTime(
                    $articleEmailLogEntry->dateSent, new \DateTimeZone('America/Los_Angeles')
                ),
            ];
        }

        usort($emails, function($a, $b) { return $a->datetime < $b->datetime ? -1 : 1; });

        //return $emails;
        $digest = [];

        foreach($emails as $email) {
            $digest[] = "
Sent At:   {$email->datetime->format('r')}
Reference: {$email->reference}
From:      {$email->from} ({$email->ip})
To:        {$email->to}
Cc:        {$email->cc}
Bcc:       {$email->bcc}
Subject:   {$email->subject}

{$email->body}
";
        }
        $separator = "\n\n".str_repeat('═', 80)."\n\n";

        $string = wordwrap(implode($separator, $digest), 80);

        return (object) ['__format__' => $format, 'data' => $string];
    }
}