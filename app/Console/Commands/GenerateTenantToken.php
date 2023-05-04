<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\TenantAccessToken;

class GenerateTenantToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-tenant-token {tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new token for the given tenant ID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenant = Tenant::find($this->argument('tenant'));
        if(!$tenant) {
            return $this->error('Tenant not found');
        }

        $token = TenantAccessToken::generate($tenant->id);

        $this->info($token->token);
    }
}
