<?php
/*
 * This file is part of the bc-review package.
 *
 * (c) Damien Walsh <me@damow.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BcReview\Command;

use BcReview\Config;
use BcReview\Authentication\Cookie;
use BcReview\Scraper\EventScraper;
use BcReview\Scraper\MediaScraper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ReviewPeriodCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('review:period')
            ->setDescription('Review events for a time period.')
            ->setDefinition(array(
                new InputArgument('output', InputArgument::REQUIRED, 'The output directory'),
                new InputArgument('start', InputArgument::REQUIRED, 'The start of the period to review'),
                new InputArgument('end', InputArgument::OPTIONAL, 'The end of the period to review', 'now'),
                new InputOption('camera', 'c', InputOption::VALUE_REQUIRED, 'Limit searching to a single camera by ID')
            ));
    }

    /**
     * @see Command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cookie = new Cookie(Config::getUrl(), Config::getUsername(), Config::getPassword());
        $scraper = new EventScraper(Config::getUrl(), $cookie);
        $events = $scraper->getEvents(
            strtotime($input->getArgument('start')),
            strtotime($input->getArgument('end')),
            $input->getOption('camera') ?: null
        );
        $output->writeln(' * <info>found ' . count($events) . ' events');

        // Download the media
        $progress = new ProgressBar($output, count($events));
        $progress->setMessage('Downloading media...');
        $progress->display();

        $mediaScraper = new MediaScraper(Config::getUrl(), $cookie);
        $mediaScraper->getForEvents($events, __DIR__ . '/../../.cache/', true, function () use ($progress) {
            $progress->advance();
        });

    }
}
