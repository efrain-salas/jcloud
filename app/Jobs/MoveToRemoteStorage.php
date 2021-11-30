<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class MoveToRemoteStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected string $uploadedFilePath,
        protected string $remoteFileName,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Upload to default storage (remote)
        $uploadedFile = new File($this->uploadedFilePath);
        Storage::putFileAs('/', $uploadedFile, $this->remoteFileName);

        // Delete chunks
        foreach (glob(storage_path('app/chunks/' . basename($this->uploadedFilePath) . '*')) as $chunkFilePath) {
            unlink($chunkFilePath);
        }
    }
}
