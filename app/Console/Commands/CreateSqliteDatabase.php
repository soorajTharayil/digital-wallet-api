<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateSqliteDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:create-sqlite {--force : Overwrite existing database file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create SQLite database file for portable usage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $databasePath = database_path('database.sqlite');

        // Check if database already exists
        if (File::exists($databasePath) && !$this->option('force')) {
            $this->warn('SQLite database file already exists at: ' . $databasePath);
            $this->info('Use --force to overwrite it.');
            return Command::FAILURE;
        }

        // Ensure database directory exists
        $databaseDir = database_path();
        if (!File::isDirectory($databaseDir)) {
            File::makeDirectory($databaseDir, 0755, true);
        }

        // Create empty SQLite database file
        try {
            File::put($databasePath, '');
            $this->info('✅ SQLite database file created successfully at: ' . $databasePath);
            $this->info('');
            $this->info('Next steps:');
            $this->info('1. Ensure your .env has: DB_CONNECTION=sqlite');
            $this->info('2. Run: php artisan migrate');
            $this->info('3. Run: php artisan db:seed');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create SQLite database file: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

