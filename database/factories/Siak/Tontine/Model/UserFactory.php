<?php

namespace Database\Factories\Siak\Tontine\Model;

use Database\Factories\UserFactory as BaseUserFactory;
use Siak\Tontine\Model\User;

/**
 * @extends \Database\Factories\UserFactory<\Siak\Tontine\Model\User>
 */
class UserFactory extends BaseUserFactory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = User::class;
}
