<?php
/**
 * File name: PatientsCriteria.php
 * Last modified: 2021.01.02 at 19:15:05
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Criteria\Users;

use Prettus\Repository\Contracts\CriteriaInterface;
use Prettus\Repository\Contracts\RepositoryInterface;

/**
 * Class DoctorsCriteria.
 *
 * @package namespace App\Criteria\Users;
 */
class PatientsCriteria implements CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param string              $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository): mixed
    {
        return $model->whereHas("roles", function($q){ $q->where("name", "customer"); });
    }
}
