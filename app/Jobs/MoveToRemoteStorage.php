<?php

namespace App\Jobs;

use App\Models\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
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
        protected File $file,
        protected string $tempFilePath,
    ) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Upload to default storage (remote)
        $uploadedFile = new \Illuminate\Http\File($this->tempFilePath);
        Storage::putFileAs('/', $uploadedFile, $this->file->storage_name);

        $this->file->is_uploaded = true;
        $this->file->save();

        // Delete chunks
        foreach (glob(storage_path('app/chunks/' . basename($this->tempFilePath) . '*')) as $chunkFilePath) {
            unlink($chunkFilePath);
        }
    }
}
