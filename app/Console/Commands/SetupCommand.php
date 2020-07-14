<?php

namespace App\Console\Commands;

use EFrane\ConsoleAdditions\Batch\Batch;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class SetupCommand extends Command
{
    protected $name = 'setup';
    protected $description = 'Runs all commands necessary for initial setup of the application.';

    public function configure()
    {
        $this->addOption(
            'no-demodata',
            '',
            InputOption::VALUE_NONE,
            "Don't prepare demodata"
        );
    }

    public function handle()
    {
        $this->info('Setting up the application...');

        $this->call('down');

        if (!file_exists(base_path('.env'))) {
            copy(base_path('.env.example'), base_path('.env'));
            $this->warn('Created new environment file, please remember to configure it!');
        }

        try {
            $databaseFiles = [
                config('database.connections.sqlite.database'),
                config('database.connections.sqlite_demo.database'),
            ];

            foreach ($databaseFiles as $databaseFile) {
                $this->info('Creating a database @ ' . $databaseFile);
                touch($databaseFile);
            }
        } catch (\Exception $e) {
            $this->error('Errors occured while setting up the databases: ' . $e);
        }

        try {
            Batch::create($this->getApplication(), $this->getOutput())
                ->add('key:generate')
                ->add('migrate')
                ->add('migrate --database=sqlite_demo')
                ->run();
        } catch (\Exception $e) {
            $this->error('An error occured during primary application setup: ' . $e);
        }

        try {
            $dataBatch = Batch::create($this->getApplication(), $this->getOutput())
                ->add('oparl:init');

            if (!$this->input->getOption('no-demodata')) {
                $dataBatch->add('server:populate');
            }

            $dataBatch->run();
        } catch (\Exception $e) {
            $this->error('Errors occured while initializing the OParl components: ' . $e);
        }

        $requiredDirs = [
            'app',
            'framework/cache',
            'framework/sessions',
            'framework/views',
            'logs',
        ];

        foreach ($requiredDirs as $dir) {
            $dir = storage_path($dir);
            if (!is_dir($dir)) {
                $this->info("Creating {$dir}");
                mkdir($dir, 0755);
            }

            if (fileperms($dir) !== 0755) {
                $this->info("Updating permissions for {$dir}");
                chmod($dir, 0755);
            }
        }

        $this->runExternalCommand('yarn');
        $nodeEnv = (config('app.env') === 'production') ? 'prod' : 'dev';
        $this->runExternalCommand("yarn {$nodeEnv}");

        $this->call('up');
    }

    /**
     * @param string $cmd
     * @param string $workingDir
     */
    protected function runExternalCommand(string $cmd, string $workingDir = '')
    {
        $this->info("Executing `{$cmd}`");
        $workingDir = app_path('../');
        $process = new Process($cmd, $workingDir);
        $process->setTimeout(0);
        $process->run(function ($output, $context) {
            if ($context === Process::STDERR) {
                $this->error($output);
            }
        });
    }
}
