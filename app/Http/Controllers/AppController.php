<?php

namespace App\Http\Controllers;

use App\Jobs\MoveToRemoteStorage;
use App\Models\Folder;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Pion\Laravel\ChunkUpload\Exceptions\UploadMissingFileException;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class AppController extends Controller
{
    public function home()
    {
        return redirect()->route('explorer');
    }

    public function explorer()
    {
        return view('explorer');
    }

    public function upload(Request $request, FileReceiver $receiver)
    {
        if ($receiver->isUploaded() === false) {
            throw new UploadMissingFileException();
        }

        $save = $receiver->receive();

        if ($save->isFinished()) {
            $uploadedFile = $save->getFile();
            (new StorageService())->upload(Folder::find($request->folderId), $uploadedFile);
        }

        $handler = $save->handler();
        return response()->json([
            'done' => $handler->getPercentageDone(),
        ]);
    }
}
