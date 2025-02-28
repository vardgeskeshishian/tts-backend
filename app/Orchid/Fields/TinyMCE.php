<?php

namespace App\Orchid\Fields;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\TextArea;

class TinyMCE extends Field
{
    protected $view = 'platform::fields.tinymce';

    protected $attributes = [
        'value'               => null,
        'class'               => 'textarea-tinymce',
        'data-tinymce-target' => 'textarea',
    ];

    /**
     * Attributes available for a particular tag.
     */
    protected $inlineAttributes = [
        'accesskey',
        'autofocus',
        'cols',
        'disabled',
        'form',
        'maxlength',
        'name',
        'placeholder',
        'readonly',
        'required',
        'rows',
        'tabindex',
        'wrap',
    ];
}
