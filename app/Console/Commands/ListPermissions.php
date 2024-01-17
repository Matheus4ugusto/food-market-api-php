<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:list {userEmail : Email do usuário}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lista as permissões de um usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->argument('userEmail');

        $user = User::where('email', $userEmail)->first();

        $this->info($user->name);

        $this->table(['Id', 'Nome'],
        $user->permissions->map(function ($permission) {
            return ['id' => $permission->id, 'name' => $permission->name];
        }));
        
    }
}
