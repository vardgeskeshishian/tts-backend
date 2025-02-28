<?php

namespace App\Services;

use App\Models\User;

class AuthorisationService extends AbstractModelService
{
    protected $modelClass = User::class;

    protected $validationRules = [
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ];

    /**
     * @var AnalyticsService
     */
    private $analyticsService;
    /**
     * @var OrderService
     */
    private $orderService;

    public function __construct(
        ImagesService $imagesService,
        MetaService $metaService,
        TaggingService $taggingService,
        AnalyticsService $analyticsService,
        OrderService $orderService
    ) {
        parent::__construct($imagesService, $metaService, $taggingService);
        $this->analyticsService = $analyticsService;
        $this->orderService = $orderService;
    }

    /**
     * @OA\Schema(
     *     schema="DataTokenArray",
     *     title="DataTokenArray",
     *     @OA\Property(property="access_token", type="string"),
     *     @OA\Property(property="expires_in", type="number"),
     *     @OA\Property(property="header", type="string"),
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="roles", type="string"),
     *     @OA\Property(property="subscription", type="object",
     *          @OA\Property(property="type", type="string"),
     *          @OA\Property(property="customerId", type="string"),
     *     ),
     * )
     *
     * @param string $token
     * @param User $user
     * @return array
     */
    public function returnToken(string $token, User $user, bool $registered = false): array
    {
        return [
            'access_token' => $token,
            'expires_in' => auth()->factory()->getTTL() * 86400,
            'header' => 'Bearer ' . $token,
            'email' => $user->email,
            'roles' => $user->roles->pluck('name'),
			'user_id' => $user->id,
			'registered' => $registered,
            'subscription' => [
                'type' => $user->plan_subscriptions,
                'customerId' => $user->customer_id
            ],
        ];
    }
}
