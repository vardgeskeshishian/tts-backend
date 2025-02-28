<?php


namespace App\Http\Controllers\Versions\V2\API\Users;

use Throwable;
use Exception;
use App\Http\Controllers\Versions\V2\BaseController;

class UsersController extends BaseController
{
    public function getProfile()
    {
        try {
            return $this->success();
        } catch (Exception $exception) {
            return $this->frontError();
        } catch (Throwable $exception) {
            return $this->exception();
        }
    }
}
