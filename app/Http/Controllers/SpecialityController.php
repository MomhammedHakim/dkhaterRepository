<?php
/*
 * File name: SpecialityController.php
 * Last modified: 2024.05.03 at 15:38:34
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\DataTables\SpecialityDataTable;
use App\Http\Requests\CreateSpecialityRequest;
use App\Http\Requests\UpdateSpecialityRequest;
use App\Repositories\SpecialityRepository;
use App\Repositories\CustomFieldRepository;
use App\Repositories\UploadRepository;
use Exception;
use Flash;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;

class SpecialityController extends Controller
{
    /** @var  SpecialityRepository */
    private SpecialityRepository $specialityRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;

    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;

    public function __construct(SpecialityRepository $specialityRepo, CustomFieldRepository $customFieldRepo, UploadRepository $uploadRepo)
    {
        parent::__construct();
        $this->specialityRepository = $specialityRepo;
        $this->customFieldRepository = $customFieldRepo;
        $this->uploadRepository = $uploadRepo;
    }

    /**
     * Display a listing of the Speciality.
     *
     * @param SpecialityDataTable $specialityDataTable
     * @return mixed
     */
    public function index(SpecialityDataTable $specialityDataTable):mixed
    {
        return $specialityDataTable->render('specialities.index');
    }

    /**
     * Show the form for creating a new Speciality.
     *
     * @return View
     */
    public function create():View
    {
        $parentSpeciality = $this->specialityRepository->pluck('name', 'id');

        $hasCustomField = in_array($this->specialityRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('specialities.create')->with("customFields", isset($html) ? $html : false)->with("parentSpeciality", $parentSpeciality);
    }

    /**
     * Store a newly created Speciality in storage.
     *
     * @param CreateSpecialityRequest $request
     *
     * @return RedirectResponse
     */
    public function store(CreateSpecialityRequest $request):RedirectResponse
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
        try {
            $speciality = $this->specialityRepository->create($input);
            $speciality->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));
            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($speciality, 'image');
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.speciality')]));

        return redirect(route('specialities.index'));
    }

    /**
     * Display the specified Speciality.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function show(int $id):RedirectResponse|View
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);

        if (empty($speciality)) {
            Flash::error('Speciality not found');

            return redirect(route('specialities.index'));
        }

        return view('specialities.show')->with('speciality', $speciality);
    }

    /**
     * Show the form for editing the specified Speciality.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id):RedirectResponse|View
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);
        $parentSpeciality = $this->specialityRepository->pluck('name', 'id')->prepend(__('lang.speciality_parent_id_placeholder'), '');

        if (empty($speciality)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.speciality')]));

            return redirect(route('specialities.index'));
        }
        $customFieldsValues = $speciality->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
        $hasCustomField = in_array($this->specialityRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('specialities.edit')->with('speciality', $speciality)->with("customFields", isset($html) ? $html : false)->with("parentSpeciality", $parentSpeciality);
    }

    /**
     * Update the specified Speciality in storage.
     *
     * @param int $id
     * @param UpdateSpecialityRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdateSpecialityRequest $request):RedirectResponse
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);

        if (empty($speciality)) {
            Flash::error('Speciality not found');
            return redirect(route('specialities.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->specialityRepository->model());
        try {
            $speciality = $this->specialityRepository->update($input, $id);

            if (isset($input['image']) && $input['image']) {
                $cacheUpload = $this->uploadRepository->getByUuid($input['image']);
                $mediaItem = $cacheUpload->getMedia('image')->first();
                $mediaItem->copy($speciality, 'image');
            }
            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $speciality->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.speciality')]));

        return redirect(route('specialities.index'));
    }

    /**
     * Remove the specified Speciality from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id):RedirectResponse
    {
        $speciality = $this->specialityRepository->findWithoutFail($id);

        if (empty($speciality)) {
            Flash::error('Speciality not found');

            return redirect(route('specialities.index'));
        }

        $this->specialityRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.speciality')]));

        return redirect(route('specialities.index'));
    }

    /**
     * Remove Media of Speciality
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $speciality = $this->specialityRepository->findWithoutFail($input['id']);
        try {
            if ($speciality->hasMedia($input['collection'])) {
                $speciality->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
