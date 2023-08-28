<?php

namespace App\Console\Commands;

use App\Business\SystemAPi;
use App\Modules\Crawler;
use Illuminate\Console\Command;

class crawlerCommand extends Command
{
    protected $signature = 'crawler';

    protected $description = 'Command description';

    public function handle(): void
    {
        $crawler = new Crawler();
        $crawler->run();
    }
}
