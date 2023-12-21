<?php

namespace App\Console\Commands;

use App\Business\SystemAPi;
use App\Modules\Crawler;
use Illuminate\Console\Command;

class crawlerCommand extends Command
{
    protected $signature = 'crawler {--store=}';

    protected $description = 'Run the crawler';

    public function handle(): void
    {
        $crawler = new Crawler($this->option('store'));
        $crawler->run();
    }
}
