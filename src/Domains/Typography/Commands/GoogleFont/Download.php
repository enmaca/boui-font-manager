<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Commands\GoogleFont;

use Enmaca\Backoffice\FontManager\Domains\Typography\Rules\TypographyFiles;
use Enmaca\Backoffice\FontManager\Exceptions\FontManagerException;
use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Exception;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Uxmal\Backend\Attributes\RegisterCommand;
use Uxmal\Backend\Command\CommandBase;
use Uxmal\Backend\Command\Traits\CommandStandardResponse;

#[RegisterCommand('/v1/font-manager/google-font/download', 'post', 'cmd.font-manager.google-font.download.v1')]
class Download extends CommandBase
{
    use CommandStandardResponse;

    public array $payloadValidator = [
    ];

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): array
    {

        $fontFileId = $this->payload['font_file_id'] ?? null;

        if( ! $fontFileId ) {
            throw new FontManagerException('Font file id is required');
        }

        $_fontFileId = GoogleFontFiles::normalizeId($fontFileId);
        $fontFile = GoogleFontFiles::with(['family', 'variant'])->find($_fontFileId);

        $remoteUriData = urldecode($fontFile->remote_uri);
        $remoteUriExtension = pathinfo($remoteUriData, PATHINFO_EXTENSION);
        $remoteUriFileName = pathinfo($remoteUriData, PATHINFO_FILENAME);
        $tempFile = tempnam(sys_get_temp_dir(), 'font-');
        if( !file_put_contents($tempFile, file_get_contents($fontFile->remote_uri)))
        {
            throw new FontManagerException('Error downloading font file from Google Fonts ['.$fontFile->remote_uri.']');
        }

        $fileObj = new UploadedFile(
            $tempFile,
            $remoteUriFileName,
            File::mimeType($tempFile), // Get MIME type
            null,
            true // Simulate a real uploaded file
        );

        $mimeType = $fileObj->getMimeType();
        $size = $fileObj->getSize(); // Size in bytes

        $destinationInfo = TypographyFiles::getDestinationPath($remoteUriExtension);
        $local_uri = TypographyFiles::moveFile($fileObj, $destinationInfo['path'], $destinationInfo['local_file']);

        $fontFile->downloaded = true;
        $fontFile->local_uri = $local_uri;
        $fontFile->save();

        $fontFilesData = FontFiles::create([
            'font_origin_type' => GoogleFontFiles::class,
            'font_origin_id' => $_fontFileId,
            'default' => true,
            'original_name' => $remoteUriFileName,
            'extension' => $remoteUriExtension,
            'mime_type' => $mimeType,
            'size' => $size,
            'uri' => $local_uri,
            'local' => true,
        ]);


        $this->setData([]);
        $this->setMeta([
            'cmd' => 'cmd.font-manager.google-font.download.v1',
            'font_files_data' => $fontFilesData->toArray() ?? null,
        ]);

        return $this->response();
    }
}
