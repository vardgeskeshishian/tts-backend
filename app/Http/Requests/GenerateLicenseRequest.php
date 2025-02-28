<?php

namespace App\Http\Requests;

use App\Enums\TypeContentEnum;
use App\Models\SFX\SFXTrack;
use App\Models\Track;
use App\Models\VideoEffects\VideoEffect;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateLicenseRequest extends FormRequest
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'contentType' => [
                'required',
                Rule::in(TypeContentEnum::cases()),
            ],

            'contentId' => [
                'required',
            ],

            'priceId' => [
                Rule::requiredIf(fn () => !$this->has('subscriptionId'))
            ],

            'transactionId' => [
                Rule::requiredIf(fn () => !$this->has('subscriptionId'))
            ],

            'subscriptionId' => [
                Rule::requiredIf(fn () => !$this->has('priceId') && !$this->has('transactionId'))
            ],
			'typeLicence' => [
				'nullable',
				'string',
			]
        ];
    }
	
	/**
	 * @return mixed
	 */
	public function getTypeLicence(): mixed
	{
		return $this->input('typeLicence');
	}
	
    /**
     * @return mixed
     */
    public function getContentTypeRequest(): mixed
    {
        return $this->input('contentType');
    }

    /**
     * @return mixed
     */
    public function getContentId(): mixed
    {
        return $this->input('contentId');
    }

    /**
     * @return mixed
     */
    public function getPriceId(): mixed
    {
        return $this->has('priceId') ? $this->input('priceId') : 'pri_01jedh23kwb3sa330wxatney55';
    }

    /**
     * @return mixed
     */
    public function getTransactionId(): mixed
    {
        return $this->input('transactionId');
    }

    /**
     * @return mixed
     */
    public function getSubscriptionId(): mixed
    {
        return $this->input('subscriptionId');
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return match ($this->getContentTypeRequest()) {
            'music' => Track::class,
            'templates' => VideoEffect::class,
            'sfx' => SFXTrack::class
        };
    }

    /**
     * @return VideoEffect|Builder|SFXTrack|Track|CachedBuilder
     */
    public function getQuery(): VideoEffect|Builder|SFXTrack|Track|CachedBuilder
    {
        return match($this->getContentTypeRequest()) {
            'music' => Track::query(),
            'templates' => VideoEffect::query(),
            'sfx' => SFXTrack::query(),
        };
    }
}
