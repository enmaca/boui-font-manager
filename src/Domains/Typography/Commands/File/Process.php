<?php

namespace Enmaca\Backoffice\FontManager\Domains\Typography\Commands\File;

use Enmaca\Backoffice\FontManager\Domains\Typography\Rules\TypographyFiles;
use Enmaca\Backoffice\FontManager\Exceptions\FontManagerException;
use Enmaca\Backoffice\FontManager\Models\Font;
use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Enmaca\Backoffice\FontManager\Models\FontVariant;
use Exception;
use Uxmal\Backend\Attributes\RegisterCommand;
use Uxmal\Backend\Command\CommandBase;
use Uxmal\Backend\Command\Traits\CommandStandardResponse;

#[RegisterCommand('/v1/font-manager/typography/file/process', 'post', 'cmd.font-manager.typography.file.process.v1')]
class Process extends CommandBase
{
    use CommandStandardResponse;

    public array $payloadValidator = [];

    /**
     * Execute the job.
     *
     * @throws Exception
     * @throws FontManagerException
     */
    public function handle(): array
    {

        $file = $this->payload['file'];

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize(); // Size in bytes
        $tempPath = $file->getRealPath();

        $font = \FontLib\Font::load($tempPath);
        $font->parse();  // for getFontWeight() to work this call must be done first!

        $name = $font->getFontName();
        $subFamily = $font->getFontSubfamily();
        $subFamilyId = $font->getFontSubfamilyID();
        $fullName = $font->getFontFullName();
        $version = $font->getFontVersion();
        $weight = $font->getFontWeight();
        $postScriptName = $font->getFontPostscriptName();
        $copyright = $font->getFontCopyright();
        $type = $font->getFontType();

        $font->close();

        $fontModel = Font::where('name', $name)->first();
        if (!$fontModel) {
            $fontModel = new Font;
            $fontModel->name = $name;
            $fontModel->save();
        }

        $fontVariant = FontVariant::where('font_id', $fontModel->id)
            ->where('sub_family_id', $subFamilyId)
            ->where('full_name', $fullName)
            ->where('version', $version)
            ->where('weight', $weight)
            ->where('post_script_name', $postScriptName)
            ->first();

        if (!$fontVariant) {
            $fontVariant = FontVariant::create([
                'font_id' => $fontModel->id,
                'sub_family' => $subFamily,
                'sub_family_id' => $subFamilyId,
                'full_name' => $fullName,
                'version' => $version,
                'weight' => $weight,
                'post_script_name' => $postScriptName,
                'copyright' => $copyright,
                'type' => $type,
            ]);


            $destinationInfo = TypographyFiles::getDestinationPath($extension);
            $file_uri = TypographyFiles::moveFile($file, $destinationInfo['path'], $destinationInfo['local_file']);

            FontFiles::create([
                'font_origin_type' => FontVariant::class,
                'font_origin_id' => $fontVariant->id,
                'default' => true,
                'original_name' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $size,
                'uri' => $file_uri,
                'local' => true,
            ]);
        }

        $this->setData(['id' => $fontVariant->file()->first()->hash]);
        $this->setMeta([
            'cmd' => 'cmd.font-manager.typography.file.process.v1',
            'font' => $fontModel->name,
            'sub_family' => $fontVariant->sub_family,
            'full_name' => $fontVariant->full_name,
            'version' => $fontVariant->version,
            'weight' => $fontVariant->weight,
            'post_script_name' => $fontVariant->post_script_name,
            'type' => $fontVariant->type,
        ]);

        return $this->response();
    }
}
