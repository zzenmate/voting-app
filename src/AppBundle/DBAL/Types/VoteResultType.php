<?php

namespace AppBundle\DBAL\Types;

use Fresh\DoctrineEnumBundle\DBAL\Types\AbstractEnumType;

/**
 * VoteResultType
 */
final class VoteResultType extends AbstractEnumType
{
    const VOTED_TRUE = 'true'; // За
    const VOTED_FALSE = 'false'; // Проти
    const ABSENT = 'absent'; // Відсутній
    const ABSTAINED = 'abstained'; // Утримався
    const NOT_VOTED = 'not_voted'; // Не голосував

    protected static $choices = [
        self::VOTED_TRUE => 'Voted true',
        self::VOTED_FALSE => 'Voted false',
        self::ABSENT => 'Absent',
        self::ABSTAINED => 'Abstained',
        self::NOT_VOTED => 'Not voted',
    ];
}
