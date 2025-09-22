<?php

namespace Enmaca\Backoffice\FontManager\Console;

use Enmaca\Backoffice\FontManager\Models\GoogleFontFamilies;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFamilyTags;
use Enmaca\Backoffice\FontManager\Models\GoogleFontFiles;
use Enmaca\Backoffice\FontManager\Models\GoogleFontTags;
use Enmaca\Backoffice\FontManager\Models\GoogleFontVariants;
use Illuminate\Console\Command;

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
    protected $signature = 'boui-font-manager:update-google-fonts-database {--api-key=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build external dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {


        $this->info('Updating Google Fonts database...');

        $startedAt = microtime(true);
        $familiesProcessed = 0;
        $familiesCreated = 0;
        $familiesUpdated = 0;
        $familiesVersionChanged = 0;
        $variantsCreated = 0;
        $filesCreated = 0;
        $filesUpdated = 0;
        $tagLinksCreated = 0;
        $tagsCreated = 0;
        $skippedFamilies = 0;

        $this->info('Getting Google Fonts official tags...');
        $googleFontsOfficialTags = $this->getGoogleOfficialTags();
        $this->info('Fetched official tags rows: ' . count($googleFontsOfficialTags));

        $this->info('Getting community tags (font-library)...');
        $googleFontsTags = $this->getGoogleTags();
        $this->info('Fetched community-tagged families: ' . (is_array($googleFontsTags) ? count($googleFontsTags) : 0));

        $this->info('Fetching Google Fonts catalog (API)...');
        $googleFonts = $this->getGoogleFonts();
        $this->info('Fetched font families from API: ' . count($googleFonts));
        if (empty($googleFonts)) {
            $this->error('No fonts were returned by the API. Aborting.');
            return Command::FAILURE;
        }

        foreach ($googleFonts as $font) {
            $familiesProcessed++;
            $familyName = $font['family'];
            $this->info("Processing family: {$familyName} (version: {$font['version']})...");

            // Check existing family and detect version change
            $existingFamily = GoogleFontFamilies::where('family', $familyName)->first();
            $versionChanged = $existingFamily && ($existingFamily->version ?? null) !== ($font['version'] ?? null);
            if ($versionChanged) {
                $this->info("  · Version change detected: {$existingFamily->version} → {$font['version']}");
                $familiesVersionChanged++;
            }

            // Upsert family and get model instance
            $familyModel = GoogleFontFamilies::updateOrCreate(
                ['family' => $familyName],
                [
                    'subsets' => json_encode($font['subsets']),
                    'category' => $font['category'] ?? null,
                    'last_modified' => $font['lastModified'] ?? null,
                    'version' => $font['version'] ?? null,
                ]
            );

            if ($familyModel->wasRecentlyCreated) {
                $familiesCreated++;
                $this->info('  · Family created');
            } else {
                $familiesUpdated++;
                $this->info('  · Family updated');
            }

            // If version changed, remove existing file records to ensure a clean refresh
            if ($versionChanged) {
                $deleted = GoogleFontFiles::where('google_font_family_id', $familyModel->id)->delete();
                $this->info("  · Cleared {$deleted} existing file record(s) due to version change");
            }

            // Variants and files
            foreach ($font['variants'] as $variant) {
                $variantModel = GoogleFontVariants::updateOrCreate(
                    ['name' => $variant],
                    []
                );
                if ($variantModel->wasRecentlyCreated) {
                    $variantsCreated++;
                }

                // Create/Update file entry per family+variant
                $fileModel = GoogleFontFiles::updateOrCreate(
                    [
                        'google_font_family_id' => $familyModel->id,
                        'google_font_variant_id' => $variantModel->id,
                    ],
                    [
                        'remote_uri' => $font['files'][$variant] ?? null,
                    ]
                );
                if ($fileModel->wasRecentlyCreated) {
                    $filesCreated++;
                    $this->info("    · File created for variant '{$variant}'");
                } else {
                    $filesUpdated++;
                    $this->info("    · File updated for variant '{$variant}'");
                }
            }
        }

        foreach ($googleFontsOfficialTags as $tag) {
            if (!isset($tag[0], $tag[1])) { continue; }
            $familyName = $tag[0];
            $familyModel = GoogleFontFamilies::where('family', $familyName)->first();
            if (!$familyModel) {
                $skippedFamilies++;
                $this->info("Skipping tags for unknown family: {$familyName}");
                continue;
            }
            $tags = explode('/', trim($tag[1], '/'));
            foreach ($tags as $_tag) {
                $tagModel = GoogleFontTags::updateOrCreate(
                    ['name' => $_tag],
                    []
                );
                if ($tagModel->wasRecentlyCreated) { $tagsCreated++; }

                $pivot = GoogleFontFamilyTags::updateOrCreate(
                    ['google_font_family_id' => $familyModel->id, 'google_font_tag_id' => $tagModel->id],
                    []
                );
                if ($pivot->wasRecentlyCreated) { $tagLinksCreated++; }
            }
        }

        foreach ($googleFontsTags as $family => $tags) {
            $familyModel = GoogleFontFamilies::where('family', $family)->first();
            if (!$familyModel) {
                $skippedFamilies++;
                $this->info("Skipping community tags for unknown family: {$family}");
                continue;
            }
            foreach ($tags as $_tag) {
                $tagModel = GoogleFontTags::updateOrCreate(
                    ['name' => $_tag],
                    []
                );
                if ($tagModel->wasRecentlyCreated) { $tagsCreated++; }

                $pivot = GoogleFontFamilyTags::updateOrCreate(
                    ['google_font_family_id' => $familyModel->id, 'google_font_tag_id' => $tagModel->id],
                    []
                );
                if ($pivot->wasRecentlyCreated) { $tagLinksCreated++; }
            }
        }

        $elapsed = round(microtime(true) - $startedAt, 2);
        $this->info('----------------------------------------');
        $this->info('Summary');
        $this->info("Families processed: {$familiesProcessed}");
        $this->info("Families created:   {$familiesCreated}");
        $this->info("Families updated:   {$familiesUpdated}");
        $this->info("Version changes:    {$familiesVersionChanged}");
        $this->info("Variants created:   {$variantsCreated}");
        $this->info("Files created:      {$filesCreated}");
        $this->info("Files updated:      {$filesUpdated}");
        $this->info("Tags created:       {$tagsCreated}");
        $this->info("Tag links created:  {$tagLinksCreated}");
        $this->info("Unknown families skipped (tags): {$skippedFamilies}");
        $this->info("Elapsed: {$elapsed}s");

        return Command::SUCCESS;
    }

    private function getGoogleFonts(): array
    {
        // Use API key from argument, else env, else ask interactively
        $apiKey = $this->option('api-key') ?: env('GOOGLE_FONTS_API_KEY');

        if (empty($apiKey)) {
            $apiKey = $this->ask('Please enter your Google Fonts API key');
        }

        $url = $this->googleFontsUrl . $apiKey;
        $fontsJson = @file_get_contents($url);
        if ($fontsJson === false) {
            $this->error('Unable to fetch Google Fonts list. Check your API key or network connectivity. URL: ' . $url);
            return [];
        }

        $fonts = json_decode($fontsJson, true);
        if (!is_array($fonts) || !isset($fonts['items']) || !is_array($fonts['items'])) {
            $this->error('Unexpected response from Google Fonts API.');
            return [];
        }

        return $fonts['items'];
    }


    private function getGoogleOfficialTags(): array
    {
        $this->info('Downloading official tags CSV from: ' . $this->googleFontsOficialTagsUrl);
        $families = file_get_contents($this->googleFontsOficialTagsUrl);
        $families = explode("\n", $families);
        return array_map('str_getcsv', $families);
    }

    private function getGoogleTags(): array
    {
        $this->info('Downloading community tags JSON from: ' . $this->googleFontsTagsUrl);
        $tags = file_get_contents($this->googleFontsTagsUrl);
        $tags = json_decode($tags, true);
        return $tags;
    }
}

