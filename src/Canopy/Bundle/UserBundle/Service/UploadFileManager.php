<?php

namespace Canopy\Bundle\UserBundle\Service;

use Canopy\Bundle\CommonBundle\Endpoint\MediaEndpoint;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

class UploadFileManager
{
    private $mediaEndpoint;
    private $request;
    private $kernel;

    public function __construct(MediaEndpoint $mediaEndpoint, RequestStack $request, \AppKernel $kernel)
    {
        $this->mediaEndpoint = $mediaEndpoint;
        $this->request = $request->getCurrentRequest();
        $this->kernel = $kernel;
    }

    public function uploadFile()
    {
        $file = $this->request->files->get('file');
        $file = $file->move($this->kernel->getRootDir().'/../web/upload/', $file->getClientOriginalName());

        if (is_null($file)) {
            return new JsonResponse('No file submitted.', Response::HTTP_BAD_REQUEST);
        }

        $media = $this->mediaEndpoint->uploadFile($file);

        return $media['web_path'];
    }
}
