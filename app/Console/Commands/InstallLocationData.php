<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ZipArchive;

class InstallLocationData extends Command
{
    protected $signature = 'install:location-data';
    protected $description = 'Import countries, states and cities data into database';

    public function handle()
    {
        $this->info('Starting location data installation...');

        $publicPath = public_path();

        // Import Countries
        if(!Schema::hasTable('countries')){
            $this->importSqlFile($publicPath . '/countries.sql', 'Countries');
        }else{
            $this->info("Countries Table already exists");
        }
        
        // Import States
        if(!Schema::hasTable('states')){
            $this->importSqlFile($publicPath . '/states.sql', 'States');
        }else{
            $this->info("States Table already exists");
        }

        // Import Cities directly from ZIP (no extraction)
        if(!Schema::hasTable('cities')){
            $this->importCitiesFromZip($publicPath . '/cities.zip');
        }else{
            $this->info("Cities Table already exists");
        }

        $this->info('✅ Location data installed successfully.');
        return Command::SUCCESS;
    }

    /**
     * Import normal SQL file
     */
    protected function importSqlFile(string $path, string $label)
    {
        if (!File::exists($path)) {
            $this->error("$label SQL file not found!");
            return;
        }

        try {
            DB::unprepared(File::get($path));
            $this->info("✔ $label imported successfully.");
        } catch (\Exception $e) {
            $this->error("❌ Failed importing $label: " . $e->getMessage());
        }
    }

    /**
     * Import cities.sql directly from cities.zip without extracting
     */
    protected function importCitiesFromZip(string $zipPath)
    {
        if (!File::exists($zipPath)) {
            $this->error('cities.zip not found!');
            return;
        }

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            $this->error('Unable to open cities.zip');
            return;
        }

        // Expecting cities.sql inside zip
        $stream = $zip->getStream('cities.sql');

        if (!$stream) {
            $this->error('cities.sql not found inside zip!');
            $zip->close();
            return;
        }

        $this->info('Importing cities from zip...');

        $query = '';
        while (!feof($stream)) {
            $line = fgets($stream);
            $query .= $line;

            // Execute when query ends
            if (str_ends_with(trim($line), ';')) {
                DB::unprepared($query);
                $query = '';
            }
        }

        fclose($stream);
        $zip->close();

        $this->info('✔ Cities imported successfully (from ZIP).');
    }
}
