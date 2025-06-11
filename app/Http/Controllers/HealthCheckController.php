<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class HealthCheckController extends Controller
{
    public function check()
    {
        Log::channel('daily')->info('HEALTH: Health check requested');

        $status = [
            'server' => true,
            'database' => $this->checkDatabase(),
            'filesystem' => $this->checkFilesystem(),
            'timestamp' => now()->toIso8601String()
        ];

        $status['overall'] = $status['server'] && $status['database'] && $status['filesystem'];

        $statusCode = $status['overall'] ? 200 : 500;

        return response()->json($status, $statusCode);
    }

    private function checkDatabase()
    {
        try {
            // Simple query to check database connection
            DB::select('SELECT 1');
            return true;
        } catch (\Exception $e) {
            Log::channel('daily')->error('HEALTH: Database check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function checkFilesystem()
    {
        try {
            // Check if we can write to and read from storage
            $testFile = 'health_check_test_' . time() . '.txt';
            Storage::put($testFile, 'Health check test');
            $content = Storage::get($testFile);
            Storage::delete($testFile);

            return $content === 'Health check test';
        } catch (\Exception $e) {
            Log::channel('daily')->error('HEALTH: Filesystem check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
