<?php
namespace StarLabs\Project\Cli;

use Symfony\Component\Console\Input\InputArgument;

/**
 * Сборщик данных
 *
 * Class ScheduleSite
 * @package StarLabs\Project\Cli
 */

class HelloCli extends \Symfony\Component\Console\Command\Command
{
	protected function configure()
	{
		$this->setName('hello')
			->setDescription('Пример скрипта, исполняемы через cli')
			->addArgument('name', InputArgument::OPTIONAL, "name is not require params");

	}

	protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output)
	{
		$name = $input->getArgument('name');

		if( $name <> '')
			$output->writeln('Команда работает, привет '.$name);
		else
			$output->writeln('Команда работает');
	}

}
