<?php
/*
 * File name: TestHelper.php
 * Last modified: 2021.02.05 at 21:04:54
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace Tests\Helpers;

use App\Models\User;
use Eloquent as Model;

/**
 * get admin user role
 * @return User|Model
 */
class TestHelper
{
    public static function getAdmin()
    {
        return User::firstWhere('email', 'admin@demo.com');
    }

    /**
     * Get clinic user role
     * @return User|Model
     */
    public static function getProvider()
    {
        return User::firstWhere('email', 'clinic@demo.com');
    }

    /**
     * Get customer user
     * @return User|Model
     */
    public static function getCustomer()
    {
        return User::firstWhere('email', 'customer@demo.com');
    }

    /**
     * Get random user
     * @return User|Model
     */
    public static function getRandomUser()
    {

        return User::firstWhere('email', 'customer@demo.com');
    }

    /**
     * @param int $length
     * @param array $data
     * @return array
     */
    public static function generateJsonArray(int $length, array $data): array
    {
        $result = [];
        for ($i = 0; $i < $length; $i++) {
            $result[] = $data;
        }
        return $result;
    }
}

