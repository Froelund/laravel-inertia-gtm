<?php

namespace App\Events;

use App\Models\User;

class UserEvent
{

    /**
     * @param string $event
     * @param array $data
     */
    public function __construct(
        protected string $event,
        protected array $data,
    )
    {
    }

    /**
     * @param string $event
     * @param array $data
     * @param User|null $user
     * @return self
     */
    public static function make(string $event, array $data, ?User $user = null): self
    {
        $userData = $user ? [
            'external_id' => $user->id,
            'email' => $user->email,
            'name' => $user->name
        ] : [];

        return new self($event, array_merge($userData, $data));
    }

    /**
     * @param User $user
     * @return self
     */
    public static function login(User $user): self
    {
        return self::make('login', [], $user);
    }

    /**
     * @return array
     */
    public function build(): array
    {
        return [
            'event' => $this->event,
            ...$this->data,
        ];
    }
}
