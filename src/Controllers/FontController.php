<?php

namespace Enmaca\Backoffice\FontManager\Controllers;

use Enmaca\Backoffice\FontManager\Models\FontFiles;
use Enmaca\Backoffice\FontManager\Models\FontVariant;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class FontController
{
    public function get(Request $request, $id): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $fontFileRecord = FontFiles::find(FontFiles::hashToId($id));
        $fontFile = parse_url($fontFileRecord->uri);

        switch ($fontFile['scheme']) {
            case 'file':
                return response()->file($fontFile['path'], [
                    'Content-Type' => $fontFileRecord->mime_type,
                    'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                    'Pragma' => 'no-cache',
                    'Content-Disposition' => 'inline; filename="' . $fontFileRecord->original_name . '"',
                ]);
            case 'http':
            case 'https':
                $localTempFile = tempnam(sys_get_temp_dir(), 'font');
                file_put_contents($localTempFile, file_get_contents($fontFileRecord->uri));

                return response()->file($localTempFile);
            default:
                throw new \Exception('Invalid font file uri', 400);
        }
    }

    /**
     * @throws \Exception
     */
    public function put(Request $request, $id): JsonResponse
    {
        $fontFileRecord = FontFiles::find(FontFiles::hashToId($id));

        try {
            $data = json_decode($request->getContent(), true);

            if ($fontFileRecord && $data['fontInBase64']) {

                $fileData = base64_decode($data['fontInBase64']);

                $uuid = (string)Str::uuid();

                $firstThreeUUID = str_split(substr($uuid, 0, 3));

                $restOfStringUUID = substr($uuid, 3);

                $destinationPath = storage_path('app/fonts/' . implode('/', $firstThreeUUID));

                $local_file = $restOfStringUUID . '.otf';

                $uri = 'file://' . $destinationPath . '/' . $local_file;

                $tmpFile = tempnam(sys_get_temp_dir(), 'font');

                if (!file_put_contents($tmpFile, $fileData)) {
                    throw new \Exception('Error saving file', 500);
                }

                if (!is_dir($destinationPath)) {
                    if (!mkdir($destinationPath, 0777, true)) {
                        throw new \Exception('Error creating directory', 500);
                    }

                    if (!rename($tmpFile, $destinationPath . '/' . $local_file)) {
                        throw new \Exception('Error moving file', 500);
                    }
                }

                FontFiles::where([
                    'font_origin_type' => $fontFileRecord->font_origin_type,
                    'font_origin_id' => $fontFileRecord->font_origin_id
                ])->update(['default' => false]);

                $version = FontFiles::where([
                        'font_origin_type' => $fontFileRecord->font_origin_type,
                        'font_origin_id' => $fontFileRecord->font_origin_id
                    ])->max('version') + 1;

                FontFiles::create([
                    'font_origin_type' => $fontFileRecord->font_origin_type,
                    'font_origin_id' => $fontFileRecord->font_origin_id,
                    'version' => $version,
                    'version_comments' => $data['comments'] ?? null,
                    'default' => true,
                    'original_name' => $fontFileRecord->original_name,
                    'extension' => $fontFileRecord->extension,
                    'mime_type' => 'application/vnd.ms-opentype',
                    'size' => $fontFileRecord->size,
                    'uri' => $uri,
                    'local' => $fontFileRecord->local,
                ]);

                return response()->json(['message' => 'Font file updated'], 200);

            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        return response()->json(['message' => 'Font file not updated'], 500);
    }
}
