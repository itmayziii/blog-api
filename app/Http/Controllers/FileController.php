<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class FileController extends Controller
{
    /**
     * @var Filesystem
     */
    private $fileSystem;
    private $imagePath = 'assets/images/';

    public function __construct(Filesystem $filesystem)
    {
        $this->fileSystem = $filesystem;
    }

    public function uploadImage(Request $request)
    {
        if (Gate::denies('store', $this->fileSystem)) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $files = $request->allFiles();

        if (empty($files)) {
            return new Response('No file given to upload', Response::HTTP_BAD_REQUEST);
        }

        $uploadedImages = [];
        foreach ($files as $fileName => $file) {
            $uploadedFile = $request->file($fileName);
            $isImageUploaded = $this->fileSystem->put($this->imagePath . $uploadedFile->getClientOriginalName(), $uploadedFile);

            if (!$isImageUploaded) {
                Log::error(FileController::class . " failed to upload file $fileName");
                continue;
            }

            $uploadedImages[] = $this->imagePath . $uploadedFile->getClientOriginalName();
        }

        return new Response(json_encode($uploadedImages, JSON_UNESCAPED_SLASHES), Response::HTTP_OK);
    }
}