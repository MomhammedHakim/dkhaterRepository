<?php
/**
 * File name: HiddenCriteria.php
 * Last modified: 2021.01.02 at 19:09:36
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Criteria\Specialities;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class HiddenCriteria.
 *
 * @package namespace App\Criteria\Specialities;
 */
class HiddenCriteria implements CriteriaInterface
{
    private $hidden = [];

    /**
     * HiddenCriteria constructor.
     * @param array $hidden
     */
    public function __construct(array $hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * Apply criteria in query repository
     *
     * @param string $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository): mixed
    {
        return $repository->hidden($this->hidden);
    }
}
