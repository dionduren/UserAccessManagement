<?php

namespace App\Traits;

trait AuditsActivity
{
    /**
     * Log creation of a model
     */
    protected function auditCreate($model, ?array $additionalData = [])
    {
        log_audit_trail(
            'create',
            get_class($model),
            $model->id ?? null,
            null,
            array_merge($model->toArray(), $additionalData)
        );
    }

    /**
     * Log update of a model
     */
    protected function auditUpdate($model, array $originalData, ?array $additionalData = [])
    {
        log_audit_trail(
            'update',
            get_class($model),
            $model->id ?? null,
            $originalData,
            array_merge($model->toArray(), $additionalData)
        );
    }

    /**
     * Log deletion of a model
     */
    protected function auditDelete($model, ?array $additionalData = [])
    {
        log_audit_trail(
            'delete',
            get_class($model),
            $model->id ?? null,
            array_merge($model->toArray(), $additionalData),
            null
        );
    }

    /**
     * Log a custom activity
     */
    protected function auditActivity(
        string $activityType,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $beforeData = null,
        ?array $afterData = null
    ) {
        log_audit_trail($activityType, $modelType, $modelId, $beforeData, $afterData);
    }
}
