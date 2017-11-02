<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Contracts\Auth\Guard as Auth;

class FileController extends Controller
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    public function __construct(Filesystem $filesystem)
    {
        $this->fileSystem = $filesystem;
    }

    public function uploadImage(Request $request, Auth $auth)
    {
        $user = $auth->user();
        if (!$user->isAdmin()) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $files = $request->allFiles();

        if (empty($files)) {
            return new Response('No file given to upload', Response::HTTP_BAD_REQUEST);
        }

        foreach ($files as $fileName => $file) {
            $uploadedFile = $request->file($fileName);
            $this->fileSystem->put('assets/images/' . $uploadedFile->getClientOriginalName(), $uploadedFile);
        }

        return new Response('File(s) Uploaded', Response::HTTP_OK);
    }
}