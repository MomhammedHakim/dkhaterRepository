<?php
/*
 * File name: SlideDataTable.php
 * Last modified: 2021.11.24 at 19:20:10
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\DataTables;

use App\Models\CustomField;
use App\Models\Slide;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Yajra\DataTables\DataTableAbstract;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder;
use Yajra\DataTables\Services\DataTable;

class SlideDataTable extends DataTable
{
    /**
     * custom fields columns
     * @var array
     */
    public static array $customFields = [];

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return DataTableAbstract
     */
    public function dataTable(mixed $query): DataTableAbstract
    {
        $dataTable = new EloquentDataTable($query);
        $columns = array_column($this->getColumns(), 'data');
        $dataTable = $dataTable
            ->editColumn('text', function ($slide) {
                return $slide->text;
            })
            ->editColumn('button', function ($slide) {
                return $slide->button;
            })
            ->editColumn('image', function ($slide) {
                return getMediaColumn($slide, 'image');
            })
            ->editColumn('text_color', function ($slide) {
                return getColorColumn($slide, 'text_color');
            })
            ->editColumn('background_color', function ($slide) {
                return getColorColumn($slide, 'background_color');
            })
            ->editColumn('button_color', function ($slide) {
                return getColorColumn($slide, 'button_color');
            })
            ->editColumn('indicator_color', function ($slide) {
                return getColorColumn($slide, 'indicator_color');
            })
            ->editColumn('doctor.name', function ($slide) {
                return getLinksColumnByRouteName([$slide->doctor], 'doctors.edit', 'id', 'name');
            })
            ->editColumn('clinic.name', function ($slide) {
                return getLinksColumnByRouteName([$slide->clinic], 'clinics.edit', 'id', 'name');
            })
            ->editColumn('updated_at', function ($slide) {
                return getDateColumn($slide);
            })
            ->editColumn('enabled', function ($slide) {
                return getBooleanColumn($slide, 'enabled');
            })
            ->addColumn('action', 'slides.datatables_actions')
            ->rawColumns(array_merge($columns, ['action']));

        return $dataTable;
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        $columns = [
            [
                'data' => 'order',
                'title' => trans('lang.slide_order'),

            ],
            [
                'data' => 'text',
                'title' => trans('lang.slide_text'),

            ],
            [
                'data' => 'button',
                'title' => trans('lang.slide_button'),

            ],
            [
                'data' => 'text_color',
                'title' => trans('lang.slide_text_color'),

            ],
            [
                'data' => 'button_color',
                'title' => trans('lang.slide_button_color'),

            ],
            [
                'data' => 'background_color',
                'title' => trans('lang.slide_background_color'),

            ],
            [
                'data' => 'indicator_color',
                'title' => trans('lang.slide_indicator_color'),

            ],
            [
                'data' => 'image',
                'title' => trans('lang.slide_image'),
                'searchable' => false, 'orderable' => false, 'exportable' => false, 'printable' => false,
            ],
            [
                'data' => 'doctor.name',
                'name' => 'doctor.name',
                'title' => trans('lang.slide_doctor_id'),

            ],
            [
                'data' => 'clinic.name',
                'name' => 'clinic.name',
                'title' => trans('lang.slide_clinic_id'),

            ],
            [
                'data' => 'enabled',
                'title' => trans('lang.slide_enabled'),

            ],
            [
                'data' => 'updated_at',
                'title' => trans('lang.slide_updated_at'),
                'searchable' => false,
            ]
        ];

        $hasCustomField = in_array(Slide::class, setting('custom_field_models', []));
        if ($hasCustomField) {
            $customFieldsCollection = CustomField::where('custom_field_model', Slide::class)->where('in_table', '=', true)->get();
            foreach ($customFieldsCollection as $key => $field) {
                array_splice($columns, $field->order - 1, 0, [[
                    'data' => 'custom_fields.' . $field->name . '.view',
                    'title' => trans('lang.slide_' . $field->name),
                    'orderable' => false,
                    'searchable' => false,
                ]]);
            }
        }
        return $columns;
    }

    /**
     * Get query source of dataTable.
     *
     * @param Slide $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Slide $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()->with("doctor")->with("clinic")->select("$model->table.*");
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return Builder
     */
    public function html(): Builder
    {
        return $this->builder()
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->addAction(['width' => '80px', 'printable' => false, 'responsivePriority' => '100'])
            ->parameters(array_merge(
                config('datatables-buttons.parameters'), [
                    'language' => json_decode(
                        file_get_contents(base_path('resources/lang/' . app()->getLocale() . '/datatable.json')
                        ), true),
                    'order' => [ [0, 'asc'] ],
                ]
            ));
    }

    /**
     * Export PDF using DOMPDF
     * @return mixed
     */
    public function pdf(): mixed
    {
        $data = $this->getDataForPrint();
        $pdf = PDF::loadView($this->printPreview, compact('data'));
        return $pdf->download($this->filename() . '.pdf');
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'slidesdatatable_' . time();
    }
}
