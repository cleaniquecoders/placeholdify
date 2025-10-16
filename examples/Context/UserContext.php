<?php

namespace App\Services\Placeholders;

use CleaniqueCoders\Placeholdify\PlaceholderHandler;

/**
 * Example context class for user-related placeholders
 */
class UserContext
{
    public static function build($user): PlaceholderHandler
    {
        $handler = new PlaceholderHandler;

        return $handler
            ->add('name', $user->name)
            ->add('email', $user->email)
            ->addNullable('phone', $user->phone, $user->mobile, 'Contact not available')
            ->addDate('joined_at', $user->created_at, 'F j, Y')
            ->addFormatted('role', $user->role, 'title')
            ->addLazy('profile_url', function () use ($user) {
                return route('profile.show', $user->id);
            });
    }

    /**
     * Register context mapping for user objects
     */
    public static function registerMapping(PlaceholderHandler $handler): void
    {
        $handler->registerContextMapping('user', [
            'name' => 'name',
            'email' => 'email',
            'phone' => fn ($user) => $user->phone ?? $user->mobile ?? 'N/A',
            'joined' => fn ($user) => $user->created_at?->format('F j, Y') ?? 'Unknown',
            'role' => fn ($user) => ucwords($user->role ?? 'member'),
            'full_address' => fn ($user) => trim("{$user->address} {$user->city} {$user->state}"),
        ]);
    }
}
