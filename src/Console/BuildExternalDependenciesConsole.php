<?php

namespace Enmaca\Backoffice\FontManager\Console;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BuildExternalDependenciesConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boui-font-manager:build-external-dependencies {--force}';

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
        $this->info('Building glyphr-2-studio...');
        $force = $this->option('force');
        $externalSource = resource_path('external/glyphr-2-studio');
        if (file_exists($externalSource)) {
            $this->info('Glyphr-Studio-2 already exists, skipping clone, updating instead');
            if ($force) {
                $process = new Process(['git', 'reset', '--hard', 'origin/main'], $externalSource);
                $process->run();
            }
            $process = new Process(['git', 'pull'], $externalSource);
            $process->run();
        } else {
            if (! $this->cloneGlyphrStudio2()) {
                return false;
            }
        }

        $this->info('Building glyphr-studio...');
        $targetDir = public_path('font-edit');
        if (file_exists($targetDir)) {
            $this->info('Glyphr-Studio already exists, removing it');
            $process = new Process(['rm', '-rf', $targetDir]);
            $process->run();
        }

        $this->updateAssetsPath($externalSource.'/package.json');

        $process = new Process(['npm', 'install', '--prefix', $externalSource], $externalSource);
        $process->run();

        $process = new Process(['npm', 'run', 'build'], $externalSource);
        $process->run();

        $process = new Process(['mv', $externalSource.'/dist', $targetDir]);
        $process->run();

        $process = new Process(['mv', $targetDir.'/font-edit.html', $targetDir.'/index.html']);
        $process->run();

        $this->info('Done');

        return true;
    }

    private function cloneGlyphrStudio2(): bool
    {
        $targetDir = resource_path('external/glyphr-2-studio');
        $process = new Process(['git', 'clone', 'https://github.com/enmaca/Glyphr-Studio-2.git', $targetDir]);
        $process->run();
        if (! $process->isSuccessful()) {
            $this->error('Failed to clone Glyphr-Studio-2 repository');

            return false;
        }

        return true;
    }

    /**
     * Inside a class, you can define:
     */
    private function updateAssetsPath(string $filePath): void
    {
        // 3. Read the file contents.
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new \RuntimeException("Could not read file: $filePath");
        }

        // 4. Perform the replacement.
        $updatedContents = str_replace('--emptyOutDir --base=/app/', '--emptyOutDir --base=/font-edit/', $fileContents);

        // 5. Write the updated contents back to the file.
        $writeResult = file_put_contents($filePath, $updatedContents);
        if ($writeResult === false) {
            throw new \RuntimeException("Could not write updated contents to file: $filePath");
        }

        // Optionally, you can log or echo a success message here.
        // echo "Paths updated successfully!";
    }
}
