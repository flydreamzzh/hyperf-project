<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf-cloud/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use App\Core\BaseController;
use App\Model\User;
use App\Services\ManagerService;
use Hyperf\HttpServer\Annotation\AutoController;

/**
 * Class IndexController
 * @package App\Controller
 * @AutoController()
 */
class IndexController extends BaseController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function test()
    {
        $user = User::findOne([['id', '>', 2], ['username', 'like' , 'qq%']]);
        return $this->success('ok', $user->toArray());
    }

}
