<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\PanelRegistry;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class ManagePanelAccess extends Command
{
    protected $signature = 'users:panel-access
        {email : Employee user email}
        {panels* : Panel ids: kancelaria, crm, cms}
        {--revoke : Revoke the selected panel access instead of granting it}';

    protected $description = 'Grants or revokes employee access to selected Filament panels.';

    public function handle(PermissionRegistrar $permissionRegistrar): int
    {
        $panels = array_values(array_unique($this->argument('panels')));
        $validPanels = array_keys(PanelRegistry::definitions());
        $invalidPanels = array_diff($panels, $validPanels);

        if ($invalidPanels !== []) {
            $this->error('Unknown panel ids: '.implode(', ', $invalidPanels));
            $this->line('Valid panel ids: '.implode(', ', $validPanels));

            return self::FAILURE;
        }

        /** @var User|null $user */
        $user = User::query()
            ->where('email', $this->argument('email'))
            ->first();

        if (! $user) {
            $this->error('Employee user not found.');

            return self::FAILURE;
        }

        if (! $user->is_employee) {
            $this->error('Panel access can only be managed for employee users.');

            return self::FAILURE;
        }

        $permissionNames = array_map(
            fn (string $panel): string => "access_{$panel}_panel",
            $panels,
        );

        foreach ($permissionNames as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        if ($this->option('revoke')) {
            $user->revokePermissionTo($permissionNames);
            $action = 'Revoked';
        } else {
            $user->givePermissionTo($permissionNames);
            $action = 'Granted';
        }

        $permissionRegistrar->forgetCachedPermissions();

        $availablePanels = collect($validPanels)
            ->filter(fn (string $panel): bool => $user->fresh()?->canAccessPredaPanel($panel) === true)
            ->values()
            ->all();

        $this->info($action.' panel access for '.$user->email.': '.implode(', ', $panels));
        $this->line('Current available panels: '.($availablePanels === [] ? 'none' : implode(', ', $availablePanels)));

        return self::SUCCESS;
    }
}
