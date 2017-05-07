<?php

namespace AppBundle\Command;

use AppBundle\DBAL\Types\VoteResultType;
use AppBundle\Entity\Session;
use Doctrine\ORM\EntityManager;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * ParseVoting Command
 */
class ParseVotingCommand extends ContainerAwareCommand
{
    const FIRST_PAGE_DOCUMENT = 0;
    const DOUBLE_SPACE = '  ';
    const LENGTH_DOUBLE_SPACE = 2;
    const NUMBER_CHAR_TO_NUMBER_VOTING = 5;
    const NUMBER_DEPUTIES = 37;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:tools:parse-voting')
            ->setDescription('Command to parse voting');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parser = new Parser();

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $availableSessions = $em->getRepository('AppBundle:Session')->findAll();

        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');
        $votingDir = sprintf('%s/../web/uploads', $rootDir);

        $finder = new Finder();
        $finder->files()->in($votingDir);

        $progress = new ProgressBar($output, $finder->count());
        $progress->start();

        foreach ($finder as $fileName => $file) {
            $pdf = $parser->parseFile($file->getRealPath());
            $pages = $pdf->getPages();
            $voteResults = [];
            foreach ($pages as $pageNumber => $page) {
                $text = $truncatedText = $page->getText();
                if ($pageNumber == self::FIRST_PAGE_DOCUMENT) {
                    list($sessionName, $sessionDate) = $this->getSessionData($text);

                    if ($this->isSessionExists($availableSessions, $sessionName)) {
                        $progress->advance();

                        continue 2;
                    }
                }

                list($nameVote, $numberVote, $typeVote, $truncatedText) = $this->getVoteDataWithTruncatedText($text);

                $votingResultsStr = preg_split('/(За)|(Проти)|(Відсутній)|(Утримався)|(Не голосував)|(Не голосував)/', $truncatedText, -1, PREG_SPLIT_OFFSET_CAPTURE);

                for ($i = 0; $i < count($votingResultsStr) - 1; $i++) {
                    $fullName = null;
                    list($id, $fullName, $name, $middleName) = sscanf($votingResultsStr[$i][0], "%d%s%s%s");
                    if ($name != null && $middleName != null) {
                        $fullName = sprintf('%s %s %s', $fullName, $name, $middleName);
                    }
                    if ($id != null && $fullName != null) {
                        $stringRes = trim(substr($truncatedText, $votingResultsStr[$i][1], $votingResultsStr[$i + 1][1] - $votingResultsStr[$i][1]));
                        $votingResult = $this->getResultVotingByParseString($stringRes);

                        $voteResults[$pageNumber]['name'] = trim($nameVote);
                        $voteResults[$pageNumber]['number'] = $numberVote;
                        $voteResults[$pageNumber]['type'] = $typeVote;
                        $voteResults[$pageNumber]['results'][] = [
                            'id' => $id,
                            'fullName' => trim($fullName),
                            'result' => $votingResult,
                        ];
                    }
                }

                if ($pageNumber != self::FIRST_PAGE_DOCUMENT
                    && array_key_exists($pageNumber - 1, $voteResults)
                    && count($voteResults[$pageNumber - 1]['results']) != self::NUMBER_DEPUTIES
                    && count($voteResults[$pageNumber]['results']) != self::NUMBER_DEPUTIES
                ) {
                    $resultFromAnotherPage = array_values($voteResults[$pageNumber]['results']);
                    foreach ($resultFromAnotherPage as $result) {
                        $voteResults[$pageNumber - 1]['results'][] = $result;
                    }
                    unset($voteResults[$pageNumber]);
                    $voteResults = array_values($voteResults);
                }
            }

            $this->insertSessionsToDB($em, $sessionName, $sessionDate);

            $sessionID = $em->getConnection()->lastInsertId();
            $this->insertVoteResultsToDB($em, $voteResults, $sessionID);

            $progress->advance();
        }

        $progress->finish();
    }

    /**
     * @param string $text
     *
     * @return array
     */
    private function getSessionData($text)
    {
        $startPosTitle = strpos($text, 'Броварська міська рада') + strlen('Броварська міська рада');
        $lengthTitle = strpos($text, 'Результат поіменного голосування') - $startPosTitle;
        $sessionName = substr($text, $startPosTitle, $lengthTitle);
        $sessionDate = (new \DateTime())->createFromFormat('d.m.y', substr(trim($sessionName), -8))
                                        ->setTime(0, 0, 0)
                                        ->format('Y-m-d H:i:s');

        return [$sessionName, $sessionDate];
    }

    /**
     * @param array  $availableSessions
     * @param string $sessionName
     *
     * @return bool
     */
    private function isSessionExists($availableSessions, $sessionName)
    {
        /** @var Session $session */
        foreach ($availableSessions as $session) {
            if ($session->getName() == $sessionName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $text
     *
     * @return array
     */
    private function getVoteDataWithTruncatedText($text)
    {
        $nameVote = null;
        $numberVote = null;
        $typeVote = null;
        $truncatedText = $text;
        $startPosNameVoting = strpos($text, 'Результат поіменного голосування:')
                              + strlen('Результат поіменного голосування:');
        $endPosNameVoting = strpos(substr($text, $startPosNameVoting), '№');

        if ($startPosNameVoting == false || $endPosNameVoting == false) {
            $positionResultVotingLabel = strpos($text, 'ПІДСУМКИ ГОЛОСУВАННЯ');
            if ($positionResultVotingLabel != false) {
                $truncatedText = substr($text, 0, $positionResultVotingLabel);
            }
        } else {
            $nameVote = substr($text, $startPosNameVoting, $endPosNameVoting);

            $startPosNumberVoting = strpos($text, $nameVote) + strlen($nameVote);
            $truncatedText = substr($text, $startPosNumberVoting);
            $endPosNumberVoting = strpos($truncatedText, self::DOUBLE_SPACE);
            $numberVote = substr($truncatedText, self::NUMBER_CHAR_TO_NUMBER_VOTING, $endPosNumberVoting
                                                                                     - self::NUMBER_CHAR_TO_NUMBER_VOTING);

            $truncatedText = substr($truncatedText, $endPosNumberVoting + self::LENGTH_DOUBLE_SPACE);
            $typeVote = trim(substr($truncatedText, 0, strpos($truncatedText, '№')));

            $resultVotingLabel = strpos($truncatedText, 'Результатголосування1');
            // У деяких випадках "Результат" і "голосування" ідуть не одним рядком, а мають між собою різну кількість пробілів.
            if ($resultVotingLabel === false) {
                $resultVotingLabel = strrpos($truncatedText, 'голосування');
                $startNewTextClipped = strpos(substr($truncatedText, $resultVotingLabel), '1')
                                       + $resultVotingLabel;
            } else {
                $startNewTextClipped = $resultVotingLabel + strlen('Результатголосування1') - 1;
            }
            $endNewTextClipped = strpos($truncatedText, 'ПІДСУМКИ ГОЛОСУВАННЯ');
            if ($endNewTextClipped == false) {
                $truncatedText = substr($truncatedText, $startNewTextClipped);
            } else {
                $truncatedText = substr($truncatedText, $startNewTextClipped, $endNewTextClipped
                                                                              - $startNewTextClipped);
            }
        }

        return [$nameVote, $numberVote, $typeVote, $truncatedText];
    }

    /**
     * @param string $result
     *
     * @return null|string
     */
    private function getResultVotingByParseString($result)
    {
        $votingResult = null;
        switch ($result) {
            case strpos($result, 'За') !== false:
                $votingResult = VoteResultType::VOTED_TRUE;
                break;
            case strpos($result, 'Проти') !== false:
                $votingResult = VoteResultType::VOTED_FALSE;
                break;
            case strpos($result, 'Утримався') !== false:
                $votingResult = VoteResultType::ABSTAINED;
                break;
            case strpos($result, 'Відсутній') !== false:
                $votingResult = VoteResultType::ABSENT;
                break;
            case strpos($result, 'Не голосував') !== false:
            case strpos($result, 'Не голосував') !== false:
                $votingResult = VoteResultType::NOT_VOTED;
                break;
        }

        return $votingResult;
    }

    /**
     * @param EntityManager $em
     * @param string        $sessionName
     * @param string        $sessionDate
     */
    private function insertSessionsToDB(EntityManager $em, $sessionName, $sessionDate)
    {
        $sessionInsertSQL = 'INSERT INTO sessions (name, `date`) VALUES (:name, :date)';
        $stmt = $em->getConnection()->prepare($sessionInsertSQL);
        $stmt->bindParam(':name', $sessionName);
        $stmt->bindParam(':date', $sessionDate);
        $stmt->execute();
    }

    /**
     * @param EntityManager $em
     * @param array         $voteResults
     * @param int           $sessionID
     */
    private function insertVoteResultsToDB(EntityManager $em, array $voteResults, $sessionID)
    {
        $voteInsertSQL = 'INSERT INTO votes (session_id, name, number, type) VALUES (:session_id, :name, :number, :type)';
        foreach ($voteResults as $voteResult) {
            $stmt = $em->getConnection()->prepare($voteInsertSQL);
            $stmt->bindParam(':session_id', $sessionID);
            $stmt->bindParam(':name', $voteResult['name']);
            $stmt->bindParam(':number', $voteResult['number']);
            $stmt->bindParam(':type', $voteResult['type']);
            $stmt->execute();

            $voteID = $em->getConnection()->lastInsertId();
            $voteResultInsertSQL = 'INSERT INTO vote_results (vote_id, deputy_number, deputy_full_name, result) VALUES ';
            foreach ($voteResult['results'] as $key => $result) {
                $voteResultInsertSQL .= sprintf(
                    "(%s, %s, '%s', '%s'),",
                    $voteID,
                    $result['id'],
                    $result['fullName'],
                    $result['result']
                );

                if (next($voteResult['results']) === FALSE) {
                    $voteResultInsertSQL = substr($voteResultInsertSQL, 0, -1);
                }
            }

            $em->getConnection()->exec($voteResultInsertSQL);
        }
    }
}
