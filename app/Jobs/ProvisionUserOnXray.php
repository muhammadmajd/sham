<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\XrayProvisionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionUserOnXray implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    public User $user;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->user->is_admin) {
                Log::info("Skipping provisioning for admin user {$this->user->id}");
                return;
            }
            $service = app(XrayProvisionService::class);
            $result = $service->addClient($this->user->uuid, "user-{$this->user->id}");

            if (!$result['ok']) {
                Log::error("Provisioning failed for user {$this->user->id}", [
                    'output' => $result['output']
                ]);
            } else {
                Log::info("Provisioning succeeded for user {$this->user->id}");
            }
        } catch (\Throwable $e) {
            Log::error("Provisioning exception for user {$this->user->id}: " . $e->getMessage());
        }
    }
}
