<?php
/*
 * File name: CreateDoctorReviewRequest.php
 * Last modified: 2024.05.03 at 21:01:17
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Requests;

use App\Models\DoctorReview;
use Illuminate\Foundation\Http\FormRequest;

class CreateDoctorReviewRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return DoctorReview::$rules;
    }
}
