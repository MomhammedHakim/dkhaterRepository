<?php
/*
 * File name: UpdateClinicEarningTableListener.php
 * Last modified: 2024.04.06 at 05:58:17
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Listeners;

use App\Repositories\EarningRepository;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class UpdateClinicEarningTableListener
 * @package App\Listeners
 */
class UpdateClinicEarningTableListener
{
    /**
     * @var EarningRepository
     */
    private EarningRepository $earningRepository;

    /**
     * EarningTableListener constructor.
     */
    public function __construct(EarningRepository $earningRepository)
    {

        $this->earningRepository = $earningRepository;
    }


    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle(object $event): void
    {
        if ($event->newClinic->accepted) {
            $uniqueInput = ['clinic_id' => $event->newClinic->id];
            try {
                $this->earningRepository->updateOrCreate($uniqueInput);
            } catch (ValidatorException $e) {
            }
        }
    }
}
