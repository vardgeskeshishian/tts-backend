<?php

namespace App\Http\Controllers\Api;

use Throwable;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Repositories\BasicRepository;
use App\Facades\TelegramLoggerFacade;
use App\Contracts\TelegramLoggerContract;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class ApiController
 * @package App\Http\Controllers\Api
 */
class ApiController extends Controller
{
    protected $resource = null;
    protected $repository = BasicRepository::class;

    /**
     * @return User|null
     */
    protected function user()
    {
        return auth()->user();
    }

    protected function success($data = [])
    {
        return response()->json([
            'data' => $data,
        ]);
    }

    /**
     * @param $model
     *
     * @param null $resource
     *
     * @param array $where
     *
     * @param array $relations
     * @param int $perPage
     *
     * @return LengthAwarePaginator|AnonymousResourceCollection
     */
    protected function pagination($model, $resource = null, $where = [], $relations = [], $perPage = 15)
    {
        /**
         * @var $repository BasicRepository
         */
        $repository = $this->repository === BasicRepository::class
            ? new BasicRepository($model)
            : resolve($this->repository);

        if (!is_array($relations)) {
            $relations = [$relations];
        }

        if (!empty($relations)) {
            $repository = $repository->applyRelation($relations);
        }

        $result = $repository->filter($where)->sort()->paginate($perPage);

        /**
         * @var $resource JsonResource
         */
        return $resource
            ? $resource::collection($result)
            : $result;
    }

    protected function error($error, $message, $code = 0)
    {
        return response()->json([
            'code' => $code,
            'error' => $error,
            'message' => $message,
        ], $code);
    }

    protected function errorWrapped(Throwable $error)
    {
        return response()->json([
            'data' => [
                'code' => 200,
                'error' => $error->getMessage(),
                'details' => [
                    $error->getFile(),
                    $error->getLine(),
                    $error->getTrace(),
                ],
            ]
        ]);
    }

    /**
     * Wrapping method call to catch errors and return result
     *
     * @todo move from this to just a service call
     *
     * @param $class
     * @param string $method
     * @param array $args
     *
     * @return JsonResponse
     */
    protected function wrapCall($class, string $method, ...$args)
    {
        try {
            $service = is_string($class) ? resolve($class) : $class;

            $result = call_user_func_array([$service, $method], $args);

            return $this->success($result);
        } catch (ValidationException $e) {
            return $this->error($e->errors(), $e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            $className = is_string($class) ? $class : get_class($class);

            TelegramLoggerFacade::pushToChat(
                TelegramLoggerContract::CHANNEL_DEBUG_ID,
                "Error from calling {$className}@{$method}",
                [
                    'message' => $e->getMessage(),
                    'trace' => array_slice($e->getTrace(), 0, 2),
                ]
            );

            return $this->error($e->getLine(), "something went wrong", $e->getCode());
        }
    }
}
