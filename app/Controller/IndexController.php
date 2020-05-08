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

/**
 * Class IndexController
 * @package App\Controller
 */
class IndexController extends BaseController
{
    public function index()
    {
        return 'ok';
    }
}
