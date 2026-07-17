<?php

namespace App\Data;

final readonly class ContactSubmissionData
{
    public function __construct(
        public string $name,
        public string $phone,
        public string $email,
        public string $comment,
    ) {}

    /**
     * @return array{
     *     name: string,
     *     phone: string,
     *     email: string,
     *     comment: string
     * }
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'comment' => $this->comment,
        ];
    }
}
