<?php


namespace App\Entity;


class AnonymousUser extends User
{
    public const ANONYMOUS_USERNAME = 'Anonymous';

    public function __construct()
    {
        parent::__construct();
        $this->setUsername(self::ANONYMOUS_USERNAME);
    }
}