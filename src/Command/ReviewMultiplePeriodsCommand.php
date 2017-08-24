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

class ReviewMultiplePeriodsCommand extends Command
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
            ->setName('review:multi')
            ->setDescription('Review the same time period for a number of days.')
            ->setDefinition(array(
                new InputArgument('output', InputArgument::REQUIRED, 'The output directory'),
                new InputArgument('start-day', InputArgument::REQUIRED, 'The start date of the period to review'),
                new InputArgument('period-start', InputArgument::REQUIRED, 'The time period to review each day'),
                new InputArgument('period-end', InputArgument::REQUIRED, 'The time period to review each day'),
                new InputArgument('days', InputArgument::OPTIONAL, 'The number of days to review. May be negative. Omit to go "up to" today. ', 0),
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

        // Build required date objects
        $startDate = new \DateTime($input->getArgument('start-day'));
        $periodStart = new \DateTime($input->getArgument('period-start'));
        $periodEnd = new \DateTime($input->getArgument('period-end'));

        // The number of days to process
        $days = $input->getArgument('days') == 0 ?
            $startDate->diff(new \DateTime())->days + 1 :     // Just go up to today
            intval($input->getArgument('days'));              // Use the specified number

        $forwards = $days > 0;

        // Start on the start date
        $periodStart->setDate($startDate->format('Y'), $startDate->format('m'), $startDate->format('d'));
        $periodEnd->setDate($startDate->format('Y'), $startDate->format('m'), $startDate->format('d'));

        // For each day, scrape the requested period
        while ($days !== 0) {

            $events = $scraper->getEvents(
                $periodStart->getTimestamp(),
                $periodEnd->getTimestamp(),
                $input->getOption('camera') ?: null
            );

            $output->writeln(
                ' * ' .
                $periodStart->format(DATE_ATOM) . ' to ' . $periodEnd->format(DATE_ATOM) .
                ' - <info>found ' . count($events) . ' events</info>'
            );

            // Download the media
            $progress = new ProgressBar($output, count($events));
            $progress->setMessage('Downloading media...');
            $progress->display();

            $mediaScraper = new MediaScraper(Config::getUrl(), $cookie);
            $mediaScraper->getForEvents($events, $input->getArgument('output'), false, function () use ($progress) {
                $progress->advance();
            });

            // Hide the progress bar
            $progress->clear();

            // Go backwards or forwards a day
            $periodStart->add(new \DateInterval('PT' . (86400 * ($forwards ? 1 : -1)) . 'S'));
            $periodEnd->add(new \DateInterval('PT' . (86400 * ($forwards ? 1 : -1)) . 'S'));
            $days += $forwards ? -1 : 1;
        }



    }
}
