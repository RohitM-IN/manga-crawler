<?php

namespace RohitMIN\MangaCrawler\Commands;

use Illuminate\Console\Command;

class MangaCrawlerCommand extends Command
{
    public $signature = 'manga-crawler';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
