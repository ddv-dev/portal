<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestADConnection extends Command
{
    protected $signature = 'ad:test {username?}';
    protected $description = 'Test Active Directory connection';

    public function handle()
    {
        $this->info('Testing AD connection...');
        
        // Проверяем wbinfo
        $wbinfo = shell_exec('which wbinfo 2>/dev/null');
        if (empty(trim($wbinfo))) {
            $this->error('❌ wbinfo not found');
            return 1;
        }
        $this->info('✅ wbinfo found: ' . trim($wbinfo));
        
        // Проверяем соединение с AD
        $test = shell_exec('wbinfo -t 2>&1');
        if (strpos($test, 'succeeded') !== false) {
            $this->info('✅ AD connection successful');
        } else {
            $this->error('❌ AD connection failed: ' . $test);
            return 1;
        }
        
        // Проверяем пользователя если указан
        if ($username = $this->argument('username')) {
            $this->info("Testing user: {$username}");
            $check = shell_exec("wbinfo -i {$username} 2>&1");
            if (strpos($check, $username) !== false) {
                $this->info('✅ User found');
                $this->line($check);
            } else {
                $this->error('❌ User not found');
                $this->line($check);
            }
        }
        
        $this->info('✅ All checks passed');
        return 0;
    }
}