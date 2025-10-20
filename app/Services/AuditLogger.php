<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an application action to the audit trail.
     *
     * @param Model|null $actor  The actor performing the action (defaults to Auth::user()).
     * @param string $action     A machine-readable action key (e.g., 'driver.availability.toggled').
     * @param Model|null $entity The target entity affected by the action.
     * @param array $metadata    Optional contextual details (sanitized).
     */
    public static function log(?Model $actor, string $action, ?Model $entity = null, array $metadata = []): void
    {
        try {
            $actor = $actor ?: (Auth::check() ? Auth::user() : null);

            $meta = self::sanitizeMetadata($metadata);

            AuditLog::create([
                'actor_type'  => $actor ? get_class($actor) : null,
                'actor_id'    => $actor ? $actor->getKey() : null,
                'action'      => $action,
                'entity_type' => $entity ? get_class($entity) : null,
                'entity_id'   => $entity ? $entity->getKey() : null,
                'metadata'    => $meta ?: null,
                'ip_address'  => request()?->ip(),
                'user_agent'  => request()?->header('User-Agent'),
            ]);
        } catch (\Throwable $e) {
            // Never break core flows due to logging; fail silently
        }
    }

    /**
     * Remove sensitive keys and overly large values from metadata.
     */
    protected static function sanitizeMetadata(array $metadata): array
    {
        $blocked = [
            'password', 'password_confirmation', 'token', 'api_key', 'secret',
            'authorization', 'cookie', 'set-cookie',
        ];

        $clean = [];
        foreach ($metadata as $key => $value) {
            $k = strtolower((string) $key);
            if (in_array($k, $blocked, true)) {
                continue;
            }
            if (is_string($value) && strlen($value) > 10000) {
                continue;
            }
            $clean[$key] = $value;
        }

        return $clean;
    }
}