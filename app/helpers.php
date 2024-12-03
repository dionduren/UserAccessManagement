<?php

if (!function_exists('log_audit_trail')) {
    /**
     * Log an audit trail entry.
     *
     * @param string $activityType Type of activity (e.g., create, update, delete, login).
     * @param string|null $modelType The model class name.
     * @param int|null $modelId The model ID.
     * @param array|null $beforeData Data before the action (for update/delete).
     * @param array|null $afterData Data after the action (for create/update).
     * @param int|null $userId User ID performing the activity.
     * @return void
     */
    function log_audit_trail(
        string $activityType,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $beforeData = null,
        ?array $afterData = null,
        ?int $userId = null
    ) {
        $user = auth()->user();
        \App\Models\AuditTrail::create([
            'activity_type' => $activityType,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'before_data' => $beforeData,
            'after_data' => $afterData,
            'user_id' => $userId ?? $user->id ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'logged_at' => now(),
        ]);
    }
}
