<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public static function log(string $action, Model|string|null $entity = null, array $meta = []): void
    {
        $entityType = null;
        $entityId = null;

        if ($entity instanceof Model) {
            $entityType = class_basename($entity);
            $entityId = $entity->getKey();
        } elseif (is_string($entity)) {
            $entityType = $entity;
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'meta' => $meta,
            'ip' => request()?->ip(),
        ]);
    }
}
