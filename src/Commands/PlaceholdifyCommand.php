<?php

namespace CleaniqueCoders\Placeholdify\Commands;

use Illuminate\Console\Command;

class PlaceholdifyCommand extends Command
{
    public $signature = 'placeholdify';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
