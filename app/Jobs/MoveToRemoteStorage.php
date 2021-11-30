<?php

namespace App\Jobs;

use App\Models\Folder;
use App\Services\StorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MoveToRemoteStorage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(
        protected int $folderId,
        protected string $uploadedFilePath,
        protected string $clientOriginalName,
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
        (new StorageService())->upload(Folder::find($this->folderId), $uploadedFile, $this->clientOriginalName);

        // Delete chunks
        foreach (glob(storage_path('app/chunks/' . $this->clientOriginalName . '*')) as $chunkFilePath) {
            unlink($chunkFilePath);
        }
    }
}
