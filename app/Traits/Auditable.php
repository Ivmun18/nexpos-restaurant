<?php

namespace App\Traits;

use App\Services\AuditService;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditService::registrar(
                'create',
                $model->getAuditModulo(),
                class_basename($model),
                $model->id,
                null,
                $model->attributesToArray(),
                $model->getAuditDetalle('creado')
            );
        });

        static::updated(function ($model) {
            $original = $model->getOriginal();
            $changes  = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) return;

            $oldValues = [];
            foreach ($changes as $key => $value) {
                $oldValues[$key] = $original[$key] ?? null;
            }

            AuditService::registrar(
                'update',
                $model->getAuditModulo(),
                class_basename($model),
                $model->id,
                $oldValues,
                $changes,
                $model->getAuditDetalle('actualizado')
            );
        });

        static::deleted(function ($model) {
            AuditService::registrar(
                'delete',
                $model->getAuditModulo(),
                class_basename($model),
                $model->id,
                $model->attributesToArray(),
                null,
                $model->getAuditDetalle('eliminado')
            );
        });
    }

    protected function getAuditModulo(): string
    {
        return $this->auditModulo ?? class_basename($this);
    }

    protected function getAuditDetalle(string $accion): string
    {
        $label = $this->auditLabel ?? $this->numero_expediente ?? $this->razon_social ?? $this->serie ?? ('ID ' . $this->id);
        return class_basename($this) . " {$label} {$accion}";
    }
}
