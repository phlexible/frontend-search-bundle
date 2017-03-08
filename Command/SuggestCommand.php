<?php

/*
 * This file is part of the phlexible frontend search package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Command;

use Phlexible\Bundle\FrontendSearchBundle\Search\ElementSearch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Suggest command.
 *
 * @author Stephan Wentz <sw@brainbits.net>
 */
class SuggestCommand extends Command
{
    /**
     * @var ElementSearch
     */
    private $elementSearch;

    /**
     * @param ElementSearch $elementSearch
     */
    public function __construct(ElementSearch $elementSearch)
    {
        parent::__construct();

        $this->elementSearch = $elementSearch;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('frontend-search:suggest')
            ->setDescription('Run suggest query.')
            ->addArgument('query', InputArgument::REQUIRED, 'Query string')
            ->addOption('siterootId', null, InputOption::VALUE_REQUIRED)
            ->addOption('language', null, InputOption::VALUE_REQUIRED, '', 'de')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $queryString = strtolower(trim($input->getArgument('query')));
        $language = $input->getOption('language');
        $siterootId = $input->getOption('siterootId');

        $suggestions = $this->elementSearch->suggest($queryString, $language, $siterootId);

        var_dump($suggestions);

        return 0;
    }
}
