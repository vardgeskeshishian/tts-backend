<?php

namespace App\Services;

use App\Models\Questionnaire\QuestionBlock;
use App\Models\Questionnaire\Section;
use Illuminate\Database\Eloquent\Model;

class QuestionsService
{
    public function getForModel(Model $model)
    {
        $findBy = [
            'type_key' => $model->getMorphClass(),
            'type_id' => $model->id,
        ];

        return Section::where($findBy)
            ->with('blocks', 'blocks.questionRel')
            ->get()
            ->toArray();
    }

    public function getForModelAsArray(Model $model)
    {
        $findBy = [
            'type_key' => $model->getMorphClass(),
            'type_id' => $model->id,
        ];

        $sections = Section::where($findBy)->with('blocks')->get();

        $array = [];

        foreach ($sections as $section) {
            $array[$section->id] = [
                'section_id' => $section->id,
                'section_name' => $section->name,
                'section_blocks' => [],
            ];

            foreach ($section->blocks as $block) {
                $array[$section->id]['section_blocks'][] = [
                    'block_id' => $block->id,
                    'block_name' => $block->name,
                    'block_question' => $block->question,
                    'block_answer' => $block->answer,
                ];
            }
        }

        return $array;
    }

    public function saveFromArray(Model $model, array $sections, array $removedBlocks)
    {
        \DB::transaction(function () use ($model, $sections, $removedBlocks) {
            foreach ($removedBlocks as $block) {
                QuestionBlock::where(['id' => $block['block_id']])->delete();
            }

            foreach ($sections as $section) {
                /**
                 * @var $objSection Section
                 */
                $objSection = Section::updateOrCreate([
                    'id' => $section['section_id'],
                    'type_key' => $model->getMorphClass(),
                    'type_id' => $model->id
                ], [
                    'name' => $section['section_name'],
                ]);

                foreach ($section['section_blocks'] as $block) {
                    $objBlock = $objSection->blocks()->updateOrCreate([
                        'id' => $block['block_id'],
                    ], [
                        'name' => $block['block_name'],
                    ]);

                    $objBlock->questionRel()->delete();
                    $objBlock->questionRel()->create([
                        'question' => $block['block_question'],
                        'answer' => $block['block_answer'],
                    ]);
                }
            }
        });

        \ResponseCache::clear();

        return true;
    }
}
