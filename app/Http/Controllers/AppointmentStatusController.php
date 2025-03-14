<?php
/*
 * File name: AppointmentStatusController.php
 * Last modified: 2024.05.03 at 22:00:21
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\DataTables\AppointmentStatusDataTable;
use App\Http\Requests\CreateAppointmentStatusRequest;
use App\Http\Requests\UpdateAppointmentStatusRequest;
use App\Repositories\AppointmentStatusRepository;
use App\Repositories\CustomFieldRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;

class AppointmentStatusController extends Controller
{
    /** @var  AppointmentStatusRepository */
    private AppointmentStatusRepository $appointmentStatusRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;



    public function __construct(AppointmentStatusRepository $appointmentStatusRepo, CustomFieldRepository $customFieldRepo )
    {
        parent::__construct();
        $this->appointmentStatusRepository = $appointmentStatusRepo;
        $this->customFieldRepository = $customFieldRepo;

    }

    /**
     * Display a listing of the AppointmentStatus.
     *
     * @param AppointmentStatusDataTable $appointmentStatusDataTable
     * @return mixed
     */
    public function index(AppointmentStatusDataTable $appointmentStatusDataTable): mixed
    {
        return $appointmentStatusDataTable->render('appointment_statuses.index');
    }

    /**
     * Show the form for creating a new AppointmentStatus.
     *
     * @return View
     */
    public function create():View
    {


        $hasCustomField = in_array($this->appointmentStatusRepository->model(),setting('custom_field_models',[]));
            if($hasCustomField){
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->appointmentStatusRepository->model());
                $html = generateCustomField($customFields);
            }
        return view('appointment_statuses.create')->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Store a newly created AppointmentStatus in storage.
     *
     * @param CreateAppointmentStatusRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateAppointmentStatusRequest $request):RedirectResponse
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->appointmentStatusRepository->model());
        try {
            $appointmentStatus = $this->appointmentStatusRepository->create($input);
            $appointmentStatus->customFieldsValues()->createMany(getCustomFieldsValues($customFields,$request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully',['operator' => __('lang.appointment_status')]));

        return redirect(route('appointmentStatuses.index'));
    }

    /**
     * Display the specified AppointmentStatus.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function show(int $id): RedirectResponse|View
    {
        $appointmentStatus = $this->appointmentStatusRepository->findWithoutFail($id);

        if (empty($appointmentStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.appointment_status')]));
            return redirect(route('appointmentStatuses.index'));
        }
        return view('appointment_statuses.show')->with('appointmentStatus', $appointmentStatus);
    }

    /**
     * Show the form for editing the specified AppointmentStatus.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id):RedirectResponse|View
    {
        $appointmentStatus = $this->appointmentStatusRepository->findWithoutFail($id);


        if (empty($appointmentStatus)) {
            Flash::error(__('lang.not_found',['operator' => __('lang.appointment_status')]));

            return redirect(route('appointmentStatuses.index'));
        }
        $customFieldsValues = $appointmentStatus->customFieldsValues()->with('customField')->get();
        $customFields =  $this->customFieldRepository->findByField('custom_field_model', $this->appointmentStatusRepository->model());
        $hasCustomField = in_array($this->appointmentStatusRepository->model(),setting('custom_field_models',[]));
        if($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }
        return view('appointment_statuses.edit')->with('appointmentStatus', $appointmentStatus)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified AppointmentStatus in storage.
     *
     * @param int $id
     * @param UpdateAppointmentStatusRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdateAppointmentStatusRequest $request):RedirectResponse
    {
        $appointmentStatus = $this->appointmentStatusRepository->findWithoutFail($id);

        if (empty($appointmentStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.appointment_status')]));
            return redirect(route('appointmentStatuses.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->appointmentStatusRepository->model());
        try {
            $appointmentStatus = $this->appointmentStatusRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value){
                $appointmentStatus->customFieldsValues()
                    ->updateOrCreate(['custom_field_id'=>$value['custom_field_id']],$value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }
        Flash::success(__('lang.updated_successfully',['operator' => __('lang.appointment_status')]));
        return redirect(route('appointmentStatuses.index'));
    }

    /**
     * Remove the specified AppointmentStatus from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id):RedirectResponse
    {
        $appointmentStatus = $this->appointmentStatusRepository->findWithoutFail($id);

        if (empty($appointmentStatus)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.appointment_status')]));

            return redirect(route('appointmentStatuses.index'));
        }

        $this->appointmentStatusRepository->delete($id);

        Flash::success(__('lang.deleted_successfully',['operator' => __('lang.appointment_status')]));
        return redirect(route('appointmentStatuses.index'));
    }

        /**
     * Remove Media of AppointmentStatus
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $appointmentStatus = $this->appointmentStatusRepository->findWithoutFail($input['id']);
        try {
            if ($appointmentStatus->hasMedia($input['collection'])) {
                $appointmentStatus->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

}
