<?php
/*
 * File name: AppointmentAPIController.php
 * Last modified: 2024.05.03 at 22:25:44
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;


use App\Criteria\Appointments\AppointmentsOfPatientCriteria;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Notifications\NewAppointment;
use App\Notifications\StatusChangedAppointment;
use App\Repositories\AddressRepository;
use App\Repositories\AppointmentRepository;
use App\Repositories\AppointmentStatusRepository;
use App\Repositories\CouponRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\ClinicRepository;
use App\Repositories\DoctorPatientsRepository;
use App\Repositories\DoctorRepository;
use App\Repositories\NotificationRepository;
use App\Repositories\PatientRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\PaymentStatusRepository;
use App\Repositories\TaxRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * Class AppointmentController
 * @package App\Http\Controllers\API
 */
class AppointmentAPIController extends Controller
{
    /** @var  AppointmentRepository */
    private AppointmentRepository $appointmentRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;
    /**
     * @var AppointmentStatusRepository
     */
    private AppointmentStatusRepository $appointmentStatusRepository;
    /**
     * @var PaymentRepository
     */
    private PaymentRepository $paymentRepository;
    /**
     * @var NotificationRepository
     */
    private NotificationRepository $notificationRepository;
    /**
     * @var AddressRepository
     */
    private AddressRepository $addressRepository;
    /**
     * @var TaxRepository
     */
    private TaxRepository $taxRepository;
    /**
     * @var DoctorRepository
     */
    private DoctorRepository $doctorRepository;
    /**
     * @var DoctorPAtientsRepository
     */
    private DoctorPAtientsRepository $doctorPatientsRepository;
    /**
     * @var ClinicRepository
     */
    private ClinicRepository $clinicRepository;
    /**
     * @var CouponRepository
     */
    private CouponRepository $couponRepository;
    /**
     * @var PatientRepository
     */
    private PatientRepository $patientRepository;
    /**
     * @var PaymentStatusRepository
     */
    private PaymentStatusRepository $paymentStatusRepository;

    public function __construct(
        AppointmentRepository $appointmentRepo,
        CustomFieldRepository $customFieldRepo,
        UserRepository $userRepo,
        AppointmentStatusRepository $appointmentStatusRepo,
        NotificationRepository $notificationRepo,
        PaymentRepository $paymentRepo,
        AddressRepository $addressRepository,
        TaxRepository $taxRepository,
        DoctorRepository $doctorRepository,
        DoctorPatientsRepository $doctorPatientsRepository,
        ClinicRepository $clinicRepository,
        CouponRepository $couponRepository,
        PatientRepository $patientRepository,
        PaymentStatusRepository $paymentStatusRepository
    )
    {
        parent::__construct();
        $this->appointmentRepository = $appointmentRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->userRepository = $userRepo;
        $this->appointmentStatusRepository = $appointmentStatusRepo;
        $this->notificationRepository = $notificationRepo;
        $this->paymentRepository = $paymentRepo;
        $this->addressRepository = $addressRepository;
        $this->taxRepository = $taxRepository;
        $this->doctorRepository = $doctorRepository;
        $this->doctorPatientsRepository = $doctorPatientsRepository;
        $this->clinicRepository = $clinicRepository;
        $this->couponRepository = $couponRepository;
        $this->patientRepository = $patientRepository;
        $this->paymentStatusRepository = $paymentStatusRepository;
    }

    /**
     * Display a listing of the Appointment.
     * GET|HEAD /appointments
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->appointmentRepository->pushCriteria(new RequestCriteria($request));
            $this->appointmentRepository->pushCriteria(new AppointmentsOfPatientCriteria(auth()->id()));
            $this->appointmentRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $appointments = $this->appointmentRepository->all();

        return $this->sendResponse($appointments->toArray(), 'Appointments retrieved successfully');
    }

    /**
     * Display the specified Appointment.
     * GET|HEAD /appointments/{id}
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        try {
            $this->appointmentRepository->pushCriteria(new RequestCriteria($request));
            $this->appointmentRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage());
        }
        $appointment = $this->appointmentRepository->findWithoutFail($id);
        $appointment->doctor->user=$this->userRepository->findWithoutFail($appointment->doctor->user_id);
        if (empty($appointment)) {
            return $this->sendError('Appointment not found');
        }
        return $this->sendResponse($appointment->toArray(), 'Appointment retrieved successfully');


    }

    /**
     * Store a newly created Appointment in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
{
    try {
        $input = $request->all();
        $doctor = $this->doctorRepository->find($input['doctor']['id']);
        $doctor->user = $this->userRepository->find($doctor->user_id);
        $user = $this->userRepository->find($input['user_id']);
        $clinic = $doctor->clinic;
        $patient = $this->patientRepository->find($input['patient']['id']);
        $taxes = $clinic->taxes;

        // Check if the user has any appointments with this doctor with status "done"
        $doneStatusId = $this->appointmentStatusRepository->find(6)->id;
        $failedStatusId = $this->appointmentStatusRepository->find(7)->id;

        $existingAppointments = $this->appointmentRepository->findWhere([
            ['doctor_id', '=', $doctor->id],
            ['user_id', '=', $user->id],
            ['appointment_status_id', '<>', $doneStatusId],
        ])->where('appointment_status_id', '<>', $failedStatusId);

        if ($existingAppointments->isNotEmpty()) {
            return $this->sendError(__('lang.already_has_ongoing_appointment', ['operator' => __('lang.appointment')]));
        }

        if (isset($input['address']) && !$input['address']['address'] == null && !$input['address']['longitude'] == null && !$input['address']['latitude'] == null) {
            $this->validate($request, [
                'address.address' => Address::$rules['address'],
                'address.longitude' => Address::$rules['longitude'],
                'address.latitude' => Address::$rules['latitude'],
            ]);
            $address = $this->addressRepository->updateOrCreate(['address' => $input['address']['address']], $input['address']);
            if (empty($address)) {
                return $this->sendError(__('lang.not_found', ['operator' => __('lang.address')]));
            } else {
                $input['address'] = $address;
            }
        } else {
            $input['address'] = $clinic->address;
        }

            $input['clinic'] = $clinic;
            $input['taxes'] = $taxes;
            $input['doctor'] = $doctor;
            $input['patient'] = $patient;
            $this->doctorPatientsRepository->create(['doctor_id'=>$doctor->id,'patient_id'=>$patient->id]);
            $input['appointment_status_id'] = $this->appointmentStatusRepository->find(1)->id;
            if (isset($input['coupon_id'])) {
                $input['coupon'] = $this->couponRepository->find($input['coupon_id']);
            }
            $appointment = $this->appointmentRepository->create($input);
            Notification::send($doctor->user, new NewAppointment($appointment));

        } catch (ValidationException $e) {
            return $this->sendError(array_values($e->errors()));
        } catch (ValidatorException | ModelNotFoundException | Exception $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
    }

    /**
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $oldAppointment = $this->appointmentRepository->findWithoutFail($id);
        if (empty($oldAppointment)) {
            return $this->sendError('Appointment not found');
        }
        $input = $request->all();
        try {
            if (isset($input['cancel']) && $input['cancel'] == '1') {
                $input['payment_status_id'] = 3;
                $input['appointment_status_id'] = 7;
            }
            $appointment = $this->appointmentRepository->update($input, $id);
            if (isset($input['appointment_status_id']) && $input['appointment_status_id'] != $oldAppointment->appointment_status_id) {
                if ($appointment->appointmentStatus->order < 40) {
                    Notification::send([$appointment->user], new StatusChangedAppointment($appointment));
                } else {
                    Notification::send([$appointment->doctor->user], new StatusChangedAppointment($appointment));
                }
            }

        } catch (ValidatorException $e) {
            return $this->sendError($e->getMessage());
        }

        return $this->sendResponse($appointment->toArray(), __('lang.saved_successfully', ['operator' => __('lang.appointment')]));
    }

}
