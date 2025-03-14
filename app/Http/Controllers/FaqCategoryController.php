<?php
/*
 * File name: FaqspecialityController.php
 * Last modified: 2021.01.22 at 12:35:31
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\DataTables\FaqCategoryDataTable;
use App\Http\Requests\CreateFaqCategoryRequest;
use App\Http\Requests\UpdateFaqCategoryRequest;
use App\Repositories\CustomFieldRepository;
use App\Repositories\FaqCategoryRepository;
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

class FaqCategoryController extends Controller
{
    /** @var  FaqCategoryRepository */
    private FaqCategoryRepository $faqCategoryRepository;

    /**
     * @var CustomFieldRepository
     */
    private CustomFieldRepository $customFieldRepository;


    public function __construct(FaqCategoryRepository $faqCategoryRepo, CustomFieldRepository $customFieldRepo)
    {
        parent::__construct();
        $this->faqCategoryRepository = $faqCategoryRepo;
        $this->customFieldRepository = $customFieldRepo;

    }

    /**
     * Display a listing of the FaqCategory.
     *
     * @param FaqCategoryDataTable $faqCategoryDataTable
     * @return mixed
     */
    public function index(FaqCategoryDataTable $faqCategoryDataTable):mixed
    {
        return $faqCategoryDataTable->render('faq_categories.index');
    }

    /**
     * Show the form for creating a new FaqCategory.
     *
     * @return View
     */
    public function create():View
    {
        $hasCustomField = in_array($this->faqCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->faqCategoryRepository->model());
            $html = generateCustomField($customFields);
        }
        return view('faq_categories.create')->with("customFields", isset($html) ? $html : false);
    }


    /**
     * Store a newly created FaqCategory in storage.
     * @param CreateFaqCategoryRequest $request
     * @return RedirectResponse
     */
    public function store(CreateFaqCategoryRequest $request):RedirectResponse
    {
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->faqCategoryRepository->model());
        try {
            $faqCategory = $this->faqCategoryRepository->create($input);
            $faqCategory->customFieldsValues()->createMany(getCustomFieldsValues($customFields, $request));

        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.saved_successfully', ['operator' => __('lang.faq_category')]));

        return redirect(route('faqCategories.index'));
    }

    /**
     * Show the form for editing the specified FaqCategory.
     *
     * @param int $id
     *
     * @return RedirectResponse|View
     */
    public function edit(int $id):RedirectResponse|View
    {
        $faqCategory = $this->faqCategoryRepository->findWithoutFail($id);


        if (empty($faqCategory)) {
            Flash::error(__('lang.not_found', ['operator' => __('lang.faq_category')]));

            return redirect(route('faqCategories.index'));
        }
        $customFieldsValues = $faqCategory->customFieldsValues()->with('customField')->get();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->faqCategoryRepository->model());
        $hasCustomField = in_array($this->faqCategoryRepository->model(), setting('custom_field_models', []));
        if ($hasCustomField) {
            $html = generateCustomField($customFields, $customFieldsValues);
        }

        return view('faq_categories.edit')->with('faqCategory', $faqCategory)->with("customFields", isset($html) ? $html : false);
    }

    /**
     * Update the specified FaqCategory in storage.
     *
     * @param int $id
     * @param UpdateFaqCategoryRequest $request
     *
     * @return RedirectResponse
     */
    public function update(int $id, UpdateFaqCategoryRequest $request):RedirectResponse
    {
        $faqCategory = $this->faqCategoryRepository->findWithoutFail($id);

        if (empty($faqCategory)) {
            Flash::error('Faq Speciality not found');
            return redirect(route('faqCategories.index'));
        }
        $input = $request->all();
        $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->faqCategoryRepository->model());
        try {
            $faqCategory = $this->faqCategoryRepository->update($input, $id);


            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $faqCategory->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidatorException $e) {
            Flash::error($e->getMessage());
        }

        Flash::success(__('lang.updated_successfully', ['operator' => __('lang.faq_category')]));

        return redirect(route('faqCategories.index'));
    }

    /**
     * Remove the specified FaqCategory from storage.
     *
     * @param int $id
     *
     * @return RedirectResponse
     */
    public function destroy(int $id):RedirectResponse
    {
        $faqCategory = $this->faqCategoryRepository->findWithoutFail($id);

        if (empty($faqCategory)) {
            Flash::error('Faq Speciality not found');

            return redirect(route('faqCategories.index'));
        }

        $this->faqCategoryRepository->delete($id);

        Flash::success(__('lang.deleted_successfully', ['operator' => __('lang.faq_category')]));

        return redirect(route('faqCategories.index'));
    }

    /**
     * Remove Media of FaqCategory
     * @param Request $request
     */
    public function removeMedia(Request $request): void
    {
        $input = $request->all();
        $faqCategory = $this->faqCategoryRepository->findWithoutFail($input['id']);
        try {
            if ($faqCategory->hasMedia($input['collection'])) {
                $faqCategory->getFirstMedia($input['collection'])->delete();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
