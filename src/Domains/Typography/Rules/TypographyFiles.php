<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Rules;

use Enmaca\Backoffice\FontManager\Exceptions\FontManagerException;
use Illuminate\Support\Str;

class TypographyFiles
{

    public static function getDestinationPath(string $extension): array
    {
        $uuid = (string)Str::uuid();

        $firstThreeUUID = str_split(substr($uuid, 0, 3));

        $restOfStringUUID = substr($uuid, 3);

        $local_file = $restOfStringUUID . '.' . $extension;

        $destinationPath = storage_path('app/fonts/' . implode('/', $firstThreeUUID));

        return ['path' => $destinationPath, 'local_file' => $local_file];
    }

    /**
     * @throws FontManagerException
     */
    public static function moveFile(mixed $file, mixed $path, mixed $local_file): string
    {
        if (!is_a($file, \Symfony\Component\HttpFoundation\File\UploadedFile::class)) {
            throw new FontManagerException('Invalid file object must be an instance of Symfony\Component\HttpFoundation\File\UploadedFile');
        }
        try {
            $file->move($path, $local_file);
        } catch (\Exception $e) {
            throw new FontManagerException('Error moving file to destination path ['.$path.']');
        }

        return 'file://' . $path . '/' . $local_file;
    }
}
