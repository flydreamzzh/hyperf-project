<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Command;

use App\Core\BaseCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * @Command
 */
class DemoCommand extends BaseCommand
{
    /**
     * 执行的命令行.
     *
     * @var string
     */
    protected $name = 'demo:command';

    public function configure()
    {
        parent::configure();
        $this->addOption('name', 'n1', InputOption::VALUE_NONE, '这是一个名字的参数缩写');
    }

    public function handle()
    {
        $argument = $this->input->getArgument('name') ?? 'World';
        $this->line('Hello ' . $argument, 'info');
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::OPTIONAL, '这是一个名字'],
            ['name2', InputArgument::OPTIONAL, '这是一个备用名字'],
        ];
    }
}
