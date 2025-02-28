<?php

namespace App\Services;

use App\Models\Structure\MainPage;
use App\Constants\MainPageConstants;
use App\Models\Structure\Testimonial;
use App\Http\Resources\Api\TestimonialsResource;
use App\Http\Resources\Any\TestimonialsResource as TestimonialsAnyResource;

class MainPageService extends AbstractModelService
{
    protected $modelClass = MainPage::class;

    protected $validationRules = [
        'sections' => 'array',
    ];

    protected $excludedUpdateFields = [
        'section_id',
    ];

    protected function fillInModel($model, $builtData)
    {
        [$data, $meta, $images] = $builtData;

        if (isset($data['sections'])) {
            foreach ($data['sections'] as $sectionId => $sectionData) {
                foreach ($sectionData as $type => $value) {
                    MainPage::updateOrCreate([
                        'section_id' => $sectionId,
                        'type' => $type,
                        'page_type' => request()->input('page_type', MainPageConstants::TYPE_ROOT),
                    ], [
                        'section_id' => $sectionId,
                        'type' => $type,
                        'text' => $value,
                        'page_type' => request()->input('page_type', MainPageConstants::TYPE_ROOT),
                    ]);
                }
            }
        }

        if (isset($data['testimonials'])) {
            $testimonials = [];

            foreach ($data['testimonials'] as $key => $testimonial) {
                if (isset($testimonial['id'])) {
                    $testimon = Testimonial::find($testimonial['id']);
                } else {
                    $testimon = new Testimonial();
                }

                unset($testimonial['id']);

                $testimon->fill($testimonial);
                $testimon->save();

                $testimonials[$key] = $testimon;
            }

            if (isset($images)) {
                foreach ($images as $key => $image) {
                    $this->imagesService->upload($testimonials[$key], $image);
                }
            }

            unset($testimonials);
        }

        $mainPage = MainPage::where('page_type', request()->input('page_type', MainPageConstants::TYPE_ROOT))->get();

        $sections = [];
        foreach ($mainPage as $data) {
            if (!isset($sections[$data->section_id])) {
                $sections[$data->section_id] = [];
            }

            $sections[$data->section_id][$data->type] = $data->text;
        }

        $testimonials = Testimonial::all();

        $sections['testimonials'] = TestimonialsResource::collection($testimonials);

        return $sections;
    }

    public function getMainPageOfType($pageType)
    {
        $mainPage = MainPage::where('page_type', $pageType)->get();

        $sections = [];
        foreach ($mainPage as $data) {
            if (!isset($sections[$data->section_id])) {
                $sections[$data->section_id] = [];
            }

            $sections[$data->section_id][$data->type] = $data->text;
        }

        $testimonials = Testimonial::all();

        $sections['testimonials'] = TestimonialsAnyResource::collection($testimonials);

        return $sections;
    }

    public function getMainPageOfTypeForSystem($pageType)
    {
        $mainPage = MainPage::where('page_type', $pageType)->get();

        $sections = [];
        foreach ($mainPage as $data) {
            if (!isset($sections[$data->section_id])) {
                $sections[$data->section_id] = [];
            }

            $sections[$data->section_id][$data->type] = [
                'key' => $data->type,
                'value' => $data->text,
                'editable_key' => false,
            ];
        }

        ksort($sections);

        return $sections;
    }

    public function saveSystemMainPage($sections, $mainPageType)
    {
        foreach ($sections as $sectionId => $sectionData) {
            foreach ($sectionData as $type => $data) {
                MainPage::updateOrCreate([
                    'section_id' => $sectionId,
                    'type' => $data['key'],
                    'page_type' => $mainPageType,
                ], [
                    'section_id' => $sectionId,
                    'type' => $data['key'],
                    'text' => $data['value'],
                    'page_type' => $mainPageType,
                ]);
            }
        }
    }

    public function deleteSection($sectionId, $mainPageType)
    {
        MainPage::where([
            'section_id' => $sectionId,
            'page_type' => $mainPageType,
        ])->delete();
    }

    public function deleteItem($sectionId, $itemType, $mainPageType)
    {
        MainPage::where([
            'section_id' => $sectionId,
            'type' => $itemType,
            'page_type' => $mainPageType,
        ])->delete();
    }
}
