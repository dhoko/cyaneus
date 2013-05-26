<?php
namespace Commands;

use Symfony\Component\Console as Console;

class CreateCommand extends Console\Command\Command {

	public function __construct($name = null) {
	    parent::__construct($name);

	    $this->setDescription('Create your post');
	    $this->setHelp('Outputs post title message.');
	    $this->addArgument('name', Console\Input\InputArgument::OPTIONAL, 'The name of your post', 'Post title here');
	    $this->addOption('more', 'm', Console\Input\InputOption::VALUE_NONE, 'Tell me more');
	    $this->addOption('markdown', 'M', Console\Input\InputOption::VALUE_OPTIONAL, 'Post format',true);
	}

    protected function execute(Console\Input\InputInterface $input, Console\Output\OutputInterface $output) {
    	$name = $input->getArgument('name');


        $output->writeln(sprintf('Your post title:  %s', $name));
        $output->writeln(sprintf('Your post file:  %s', \Factory::url($name).'.md'));
        if ($input->getOption('more')) {
            $output->writeln('More options informations');
        }
    }
}