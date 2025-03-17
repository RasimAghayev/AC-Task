<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogOperationHelper
{
    public static function handleWithLogOperation(string $operationName, callable $callback)
    {
        try {
            Log::info("{$operationName} started.");
            $result = $callback();
            Log::info("{$operationName} completed successfully.");
            return $result;
        } catch (\Exception $e) {
                Log::error("An error occurred during {$operationName}: " . $e->getMessage());
                throw $e;
            }
    }
}
