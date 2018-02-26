<?php

namespace App\Http\Controllers;

use App\Http\JsonApi;
use App\Image;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Log\LoggerInterface;

class FileController
{
    /**
     * @var Filesystem
     */
    private $fileSystem;
    /**
     * @var JsonApi
     */
    private $jsonApi;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Gate
     */
    private $gate;
    private $imagePath = 'images/';

    public function __construct(FileSystem $filesystem, JsonApi $jsonApi, Gate $gate, LoggerInterface $logger)
    {
        $this->fileSystem = $filesystem;
        $this->jsonApi = $jsonApi;
        $this->gate = $gate;
        $this->logger = $logger;
    }

    public function uploadImages(Request $request, Response $response)
    {
        if ($this->gate->denies('store', $this->fileSystem)) {
            return $this->jsonApi->respondForbidden($response);
        }

        $files = $request->allFiles();
        if (empty($files)) {
            return $this->jsonApi->respondBadRequest($response, 'No file given to upload');
        }

        $uploadedImages = [];
        foreach ($files as $fileName => $file) {
            $uploadedFile = $request->file($fileName);
            $isImageUploaded = $this->fileSystem->put($this->imagePath . $uploadedFile->getClientOriginalName(), $uploadedFile);

            if ($isImageUploaded === false) {
                $this->logger->error(FileController::class . " failed to upload file $fileName");
                continue;
            }

            $image = new Image();
            $image->path = $this->imagePath . $uploadedFile->getClientOriginalName();
            $image->filename = $uploadedFile->getClientOriginalName();
            $uploadedImages[] = $image;
        }

        return $this->jsonApi->respondImagesUploaded($response, $uploadedImages);
    }
}