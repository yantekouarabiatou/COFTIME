<?php
namespace App\Services;

use App\Models\LogActivite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ActivityLogger
{
    public static function log(string $action, Model $model, string $description = null, array $old = null, array $new = null)
    {
        try {
            $userId = auth()->check() ? auth()->id() : null;
            
            Log::info("Tentative de création de log", [
                'action' => $action,
                'model' => get_class($model),
                'model_id' => $model->id,
                'user_id' => $userId
            ]);
            
            $logData = [
                'user_id' => $userId,
                'action' => $action,
                'loggable_type' => get_class($model),
                'loggable_id' => $model->id,
                'description' => $description,
                'old_values' => $old,
                'new_values' => $new ?? ($action !== 'deleted' ? $model->getAttributes() : null),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'status' => 'success'
            ];
            
            $log = LogActivite::create($logData);
            
            Log::info("Log créé avec succès", ['log_id' => $log->id]);
            
            return $log;
            
        } catch (\Exception $e) {
            Log::error('ERREUR ActivityLogger: ' . $e->getMessage(), [
                'action' => $action,
                'model' => get_class($model),
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
}