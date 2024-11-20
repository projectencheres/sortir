<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class FileUploader
{
    private readonly string $targetDirectory;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->targetDirectory = $parameterBag->get('uploads_directory');
    }

    public function upload(UploadedFile $file): string {
        $fileName = uniqid() . '.' . $file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException($e->getMessage());
        }
        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    public function delete(?string $fileName, string $directory): void {
        if (null != $fileName) {
            if(file_exists($directory . '/' . $fileName)) {
                unlink($directory . '/' . $fileName);
            }
        }
    }
}
