<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use App\Support\Audit;

/**
 * Trace automatiquement les créations / modifications / suppressions
 * d'un modèle dans la table audit_logs. À appliquer aux modèles sensibles
 * (Payment, Payroll, Grade...). N'enregistre RIEN sur les insert() en masse
 * (qui ne déclenchent pas les events Eloquent) ni quand Audit est désactivé.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAuditLog('created', [], $model->auditableSnapshot());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) {
                return;
            }
            $old = array_intersect_key($model->getOriginal(), $changes);
            $model->writeAuditLog('updated', $old, $changes);
        });

        static::deleted(function ($model) {
            $model->writeAuditLog('deleted', $model->auditableSnapshot(), []);
        });
    }

    /** Attributs significatifs (hors horodatages). */
    protected function auditableSnapshot(): array
    {
        $attrs = $this->getAttributes();
        unset($attrs['created_at'], $attrs['updated_at']);

        return $attrs;
    }

    protected function writeAuditLog(string $event, array $old, array $new): void
    {
        if (! Audit::$enabled) {
            return;
        }

        try {
            AuditLog::create([
                'user_id'        => auth()->id(),
                'user_name'      => auth()->user()?->name,
                'event'          => $event,
                'auditable_type' => static::class,
                'auditable_id'   => $this->getKey(),
                'label'          => method_exists($this, 'auditLabel') ? $this->auditLabel() : null,
                'old_values'     => $old ?: null,
                'new_values'     => $new ?: null,
                'ip_address'     => function_exists('request') ? request()->ip() : null,
            ]);
        } catch (\Throwable) {
            // L'audit ne doit JAMAIS casser une opération métier.
        }
    }
}
