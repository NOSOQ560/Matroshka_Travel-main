<?php

namespace App\Jobs;

use App\Models\Story;
use Carbon\Carbon;

class StoryJob
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Story::where('created_at', '<', Carbon::now()->subHours(24))->delete();
    }
}
