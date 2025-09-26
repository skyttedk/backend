<?php

namespace GFUnit\jobs\mails;
use GFBiz\units\UnitController;

class CheckDomainBlock
{

    const DEBUGMODE = true;
    const LOOKBACK_HOURS = 1.5;

    const EMAIL_THRESHOLD = 900;


    private function log($text) {
        if(self::DEBUGMODE) {
            echo $text . "<br>\n";
        }
    }

    public function __construct()
    {
        
    }

    public function runDomainBlock()
    {

        $createdBlocks = 0;
        $releasedBlocks = 0;
        $existingBlocks = 0;

        // Init job
        \GFCommon\DB\CronLog::startCronJob("MailDomainBlock");
        $this->log("Running domain block job");

        // Extract data
        $hoursBack = self::LOOKBACK_HOURS;
        $emailThreshold = self::EMAIL_THRESHOLD;

        // Make query and fetch
        $sql = "SELECT SUBSTRING_INDEX(recipent_email, '@', -1) AS domain, COUNT(*) AS total_sent FROM mail_queue WHERE sent = 1 AND sent_datetime > NOW() - INTERVAL ".$hoursBack." HOUR GROUP BY domain HAVING total_sent > ".$emailThreshold;
        $domainList = \MailQueue::find_by_sql($sql);

        $this->log("Found ".count($domainList)." domains with more than ".$emailThreshold." emails in the last ".$hoursBack." hours.");

        // Fetch
        $domainsToBlock = [];
        foreach($domainList as $domain) {
            $domainsToBlock[strtolower(trim($domain->domain))] = $domain->total_sent;
        }

        // Trin 2: Tjek og opdater eller tilføj blokeringer
        foreach ($domainsToBlock as $domain => $count) {

            // Tjek om der findes en aktiv blokering for dette domæne
            $sql = "SELECT id, maxcount FROM mail_queue_block WHERE domain = ? AND released IS NULL";
            $activeBlock = \MailQueueBlock::find_by_sql($sql, [$domain]);

            // Ingen blokkeringer
            if(count($activeBlock) == 0) {

                $mailBlock = new \MailQueueBlock();
                $mailBlock->domain = $domain;
                $mailBlock->created = date("Y-m-d H:i:s");
                $mailBlock->maxcount = $count;
                $mailBlock->save();

                $createdBlocks++;
                $this->log("Added block for ".$domain." with ".$count." emails");

            }

            // Opdater eksisterende
            else if($count > $activeBlock[0]->maxcount) {

                $existingBlocks++;

                try {

                    $existingBlock = \MailQueueBlock::find($activeBlock[0]->id);
                    $existingBlock->maxcount = $count;
                    $existingBlock->save();

                    $this->log("Updated existing block ".$activeBlock[0]->id." to ".$count." emails");

                } catch (\Exception $e) {
                    $this->log("Error updating existing block ".$activeBlock[0]->id.": ".$e->getMessage());
                }

            }

            // Ingen ændringer
            else {
                $existingBlocks++;
                $this->log("No changes for ".$domain." with ".$count." emails");
            }

        }

        // Trin 3: Frigiv blokeringer for domæner, der ikke længere overskrider grænsen
        $sql = "SELECT id, domain FROM mail_queue_block WHERE released IS NULL GROUP BY domain";
        $openBlocks = \MailQueueBlock::find_by_sql($sql);

        foreach($openBlocks as $openBlock) {

            $domain = trim(strtolower($openBlock->domain));

            if(!isset($domainsToBlock[$domain])) {

                $block = \MailQueueBlock::find($openBlock->id);
                $block->released = date("Y-m-d H:i:s");
                $block->save();

                $releasedBlocks++;
                $this->log("Released block for ".$domain.".");

            }

        }

        // Write log
        $this->log("Done running domain block job");
        $this->log(" - Created blocks: ".$createdBlocks);
        $this->log(" - Existing blocks: ".$existingBlocks);
        $this->log(" - Released blocks: ".$releasedBlocks);

        // End job
        \GFCommon\DB\CronLog::endCronJob(($createdBlocks+$existingBlocks > 0 ? 3 : 1),($createdBlocks+$existingBlocks)." active blocks");

        // Commit
        \system::connection()->commit();

    }

    public function runDomainBlockMonitor()
    {
        // Antag at \MailQueueBlock::find_by_sql er din ORM-metode til at udføre SQL-forespørgsler
        $sql = "SELECT COUNT(*) AS count FROM mail_queue_block WHERE released IS NULL";
        $result = \MailQueueBlock::find_by_sql($sql);

        // Tjek om der blev fundet nogen aktive blokeringer
        if ($result[0]->count > 0) {
            // Der findes aktive blokeringer, returner fejlkode 500
            http_response_code(500);
            echo "Error: There are currently active domain blocks.";
        } else {
            // Ingen aktive blokeringer, alt er OK
            http_response_code(200);
            echo "OK: No active domain blocks.";
        }
    }



}