<?php


namespace ES\Bundle\BaseBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

class SendEmailCommand extends ContainerAwareCommand
{
	/**
	 * @see Command
	 */
	protected function configure()
	{
		$this->setName('es:mailer:send')
			->setDefinition(array(
				new InputArgument('email', InputArgument::REQUIRED),
				new InputArgument('template', InputArgument::REQUIRED),
				new InputOption('attachment', 'a', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED)
			));
	}

	/**
	 * @see Command
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();
		$container->enterScope('request');
		$container->set('request', new Request(), 'request');

		$toEmail  = $input->getArgument('email');
		$template = $input->getArgument('template');

		$attachments = [];
		foreach ($input->getOption('attachment') as $attachment) {
			$attachments[] = realpath($attachment);
		}
		$this->getContainer()->get('es_base.mailer')->send($template, $toEmail, [], null, $attachments);
	}
}