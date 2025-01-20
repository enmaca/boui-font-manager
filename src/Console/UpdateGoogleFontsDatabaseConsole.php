<?php

namespace Enmaca\Backoffice\FontManager\Console;

use Enmaca\Backoffice\FontManager\Models\GoogleFontFamilies;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFamilyTags;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Enmaca\Backoffice\FontManager\Models\GoogleFontTags;
use Enmaca\Backoffice\FontManager\Models\GoogleFontVariants;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class UpdateGoogleFontsDatabaseConsole extends Command
{

    private string $googleFontsOficialTagsUrl = 'https://raw.githubusercontent.com/google/fonts/refs/heads/main/tags/all/families.csv';

    private string $googleFontsTagsUrl = "https://raw.githubusercontent.com/katydecorah/font-library/refs/heads/gh-pages/families.json";
    private string $googleFontsUrl = 'https://www.googleapis.com/webfonts/v1/webfonts?key=';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boui-font-manager:update-google-fonts-database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build external dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): bool
    {


        $this->info('Updating Google Fonts database...');

        $this->info('Getting Google Fonts official tags...');
        $googleFontsOfficialTags = $this->getGoogleOfficialTags();
        $googleFontsTags = $this->getGoogleTags();


        $googleFonts = $this->getGoogleFonts();
        foreach( $googleFonts as $font ){
            //$font['family']
            $font_family_data = GoogleFontFamilies::updateOrInsert(
                ['family' => $font['family']],
                [
                    'subsets' => json_encode($font['subsets']),
                    'category' => $font['category'],
                    'last_modified' => $font['lastModified'],
                    'version' => $font['version'],  //TODO: HANDLE VERSION CHANGES ON ALREADY DOWNLOADED FONT
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            )->first();

            foreach( $font['variants'] as $variant ){
                $font_variant_data = GoogleFontVariants::updateOrInsert(
                    ['name' => $variant],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                )->first();

                //TODO: HANDLE VERSION CHANGES ON ALREADY DOWNLOADED FONT
                GoogleFontFiles::updateOrInsert(
                    ['google_font_family_id' => $font_family_data->id, 'google_font_variant_id' => $font_variant_data->id],
                    [
                        'remote_uri' => $font['files'][$variant],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            }
        }

        foreach( $googleFontsOfficialTags as $tag ){
            $family_data = GoogleFontFamilies::where('family', $tag[0])->first();
            if( !$family_data ){
                continue;
            }
            $tags = explode('/', trim($tag[1], '/'));
            foreach( $tags as $_tag ){
                $tag_data = GoogleFontTags::updateOrInsert(
                    ['name' => $_tag],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                )->first();
                GoogleFontFamilyTags::updateOrInsert(
                    ['google_font_family_id' => $family_data->id, 'google_font_tag_id' => $tag_data->id],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        foreach( $googleFontsTags as $family => $tags ){
            $family_data = GoogleFontFamilies::where('family', $family)->first();
            if( !$family_data){
                continue;
            }
            foreach( $tags as $_tag ){
                $tag_data = GoogleFontTags::updateOrInsert(
                    ['name' => $_tag],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                )->first();
                GoogleFontFamilyTags::updateOrInsert(
                    ['google_font_family_id' => $family_data->id, 'google_font_tag_id' => $tag_data->id],
                    [
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }

        return true;
    }

    private function getGoogleFonts(): array
    {
        $fonts = file_get_contents($this->googleFontsUrl.env('GOOGLE_FONTS_API_KEY'));
        $fonts = json_decode($fonts, true);
        return $fonts['items'];
    }


    private function getGoogleOfficialTags(): array
    {
        $families = file_get_contents($this->googleFontsOficialTagsUrl);
        $families = explode("\n", $families);
        return array_map('str_getcsv', $families);
    }

    private function getGoogleTags(): array
    {
        $tags = file_get_contents($this->googleFontsTagsUrl);
        $tags = json_decode($tags, true);
        return $tags;
    }
}

