<?php

namespace App\Console\Commands;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;

class SetPermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permission:set {userEmail : Email do usuário} {--permission=* : Permissões paa atribuição}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Define uma ou um conjunto de permissões para um usuário';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userEmail = $this->argument('userEmail');
        $permissions = array_filter(array_unique($this->option('permission')));

        $user = User::where('email', $userEmail)->first();
        if (!$user) {
            $this->error('Usuário não encontrado!');
            return;
        }

        $permissionsDb = Permission::whereIn('name', $permissions)
            ->get(['name', 'id']);

        if ($permissionsDb->count() == 0) {
            $this->error('Nenhuma permissão encontrada!');
            return;
        }

        $permissionsNotFound = array_diff($permissions, $permissionsDb->pluck('name')->toArray());

        if (count($permissionsNotFound) > 0) {
            $this->warn((
                count($permissionsNotFound) > 1 ?
                'As permissões ' :
                'A permissão ') . implode(', ', $permissionsNotFound) . ' não ' . (count($permissionsNotFound) > 1 ?
                'foram encontradas' :
                'foi encontrada'));
        }

        $user->permissions()->sync($permissionsDb->pluck('id'));
        $this->info('Sucesso!');
    }
}
