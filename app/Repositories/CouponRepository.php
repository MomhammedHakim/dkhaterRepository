<?php
/**
 * File name: CouponRepository.php
 * Last modified: 2021.01.03 at 15:29:51
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Repositories;

use App\Models\Coupon;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class CouponRepository
 * @package App\Repositories
 * @version August 23, 2020, 6:10 pm UTC
 *
 * @method Coupon findWithoutFail($id, $columns = ['*'])
 * @method Coupon find($id, $columns = ['*'])
 * @method Coupon first($columns = ['*'])
 */
class CouponRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'code',
        'discount',
        'discount_type',
        'description',
        'doctor_id',
        'market_id',
        'speciality_id',
        'expires_at',
        'enabled'
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Coupon::class;
    }
}
