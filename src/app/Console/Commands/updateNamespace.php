<?php

namespace Controlink\LaravelWinmax4\app\Console\Commands;

use Illuminate\Console\Command;

class updateNamespace extends Command
{
    protected $signature = 'winmax4:namespace-update {oldNamespace} {newNamespace}';
    protected $description = 'Update namespace in published files';

    public function handle()
    {
        $oldNamespace = $this->argument('oldNamespace');
        $newNamespace = $this->argument('newNamespace');

        // Define os arquivos que deseja atualizar
        $files = [];
        foreach (glob(app_path('Models/Winmax4') . '/*.php') as $file) {
            $files[] = $file;
        }

        dd($files);

        foreach ($files as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                $updatedContent = str_replace($oldNamespace, $newNamespace, $content);
                file_put_contents($file, $updatedContent);
                $this->info("Updated namespace in {$file}");
            } else {
                $this->error("File not found: {$file}");
            }
        }
    }
}
