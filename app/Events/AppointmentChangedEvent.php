<?php
/*
 * File name: AppointmentChangedEvent.php
 * Last modified: 2021.06.09 at 15:53:58
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Appointment $appointment;

    /**
     * AppointmentChangedEvent constructor.
     * @param $appointment
     */
    public function __construct($appointment)
    {
        $this->appointment = $appointment;
    }


}
