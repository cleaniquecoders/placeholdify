<?php

namespace CleaniqueCoders\Placeholdify\Contexts;

use CleaniqueCoders\Placeholdify\Contracts\ContextInterface;
use Illuminate\Database\Eloquent\Model;

class UserContext implements ContextInterface
{
    /**
     * Get the context name/identifier
     */
    public function getName(): string
    {
        return 'user';
    }

    /**
     * Get the mapping configuration for the context
     */
    public function getMapping(): array
    {
        return [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'full_name' => fn ($user) => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            'initials' => fn ($user) => $this->generateInitials($user),
            'avatar' => 'avatar',
            'profile_photo' => 'profile_photo_path',
            'phone' => 'phone',
            'created_at' => [
                'property' => 'created_at',
                'formatter' => 'date',
            ],
            'updated_at' => [
                'property' => 'updated_at',
                'formatter' => 'date',
            ],
            'email_verified_at' => [
                'property' => 'email_verified_at',
                'formatter' => 'date',
            ],
            'is_verified' => fn ($user) => ! empty(data_get($user, 'email_verified_at')) ? __('Yes') : __('No'),
            'profile_url' => fn ($user) => $this->generateProfileUrl($user),
            'display_name' => fn ($user) => $user->display_name ?? $user->name ?? $user->email ?? __('Unknown User'),
        ];
    }

    /**
     * Validate if the object can be processed by this context
     */
    public function canProcess(mixed $object): bool
    {
        if (! is_object($object)) {
            return false;
        }

        // Check if it's a User model or has user-like properties
        return $this->isUserLike($object);
    }

    /**
     * Get the supported object type(s) for this context
     */
    public function getSupportedTypes(): string|array
    {
        return [
            'App\Models\User',
            'Illuminate\Foundation\Auth\User',
            'Illuminate\Database\Eloquent\Model',
            'object', // Generic object support
        ];
    }

    /**
     * Generate user initials from name fields
     */
    protected function generateInitials($user): string
    {
        $name = '';

        if (isset($user->first_name) && isset($user->last_name)) {
            $name = $user->first_name.' '.$user->last_name;
        } elseif (isset($user->name)) {
            $name = $user->name;
        }

        if (empty($name)) {
            return '';
        }

        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (! empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        return $initials;
    }

    /**
     * Generate profile URL for the user
     */
    protected function generateProfileUrl($user): string
    {
        // Check if Laravel's route function exists
        if (function_exists('route')) {
            try {
                return route('profile.show', $user->id ?? 0);
            } catch (\Exception $e) {
                // Route doesn't exist, fallback to simple URL
            }
        }

        // Fallback to a simple profile URL pattern
        return '/profile/'.($user->id ?? 'unknown');
    }

    /**
     * Check if object has user-like properties
     */
    protected function isUserLike($object): bool
    {
        // Check for common user properties
        $userProperties = ['id', 'name', 'email', 'first_name', 'last_name'];

        foreach ($userProperties as $property) {
            if (property_exists($object, $property) ||
                (method_exists($object, '__get') && isset($object->{$property}))) {
                return true;
            }
        }

        // Check if it's an Eloquent model with a users table-like structure
        if ($object instanceof Model) {
            $table = $object->getTable();

            return in_array($table, ['users', 'user', 'accounts', 'members']);
        }

        return false;
    }
}
