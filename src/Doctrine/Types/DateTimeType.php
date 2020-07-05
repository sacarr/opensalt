<?php

namespace App\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;

/**
 * Class DateTimeType.
 *
 * Adds support for datetime(3) in MySQL
 *
 * @see https://gist.github.com/coudenysj/6dc8ba55c43b97143a6c
 */
class DateTimeType extends \Doctrine\DBAL\Types\DateTimeType
{
    /**
     * {@inheritdoc}
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $format = $platform->getDateTimeFormatString();
        if (0 !== (int) $value->format('u')) {
            $format .= '.u';
        }

        return $value->format($format);
    }

    /**
     * {@inheritdoc}
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $timeZone = new \DateTimeZone("+00");

        if (null === $value || $value instanceof \DateTime) {
            return $value;
        }

        $format = $platform->getDateTimeFormatString();

        if (preg_match('/\.\d+(\z|\+|-)/', $value)) {
            $format .= '.u';
        }

        if (  'postgresql' === $platform->getName() ) {
            if( preg_match('/-\d+\z|\+\d+\z/', $value, $timeZonePart, PREG_OFFSET_CAPTURE) ){
                if (count($timeZonePart) === 1) {
                    $value = substr($value, 0, $timeZonePart[0][1]);
                    $timeZone = new \DateTimeZone($timeZonePart[0][0]);
                }
            }
        }

        $val = \DateTime::createFromFormat($format, $value, $timeZone);
        if (!$val) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString().'.u');
        }
        return $val;
    }

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if (!in_array($fieldDeclaration['precision'] ?? 0, [0, 10])) {
            return "DATETIME({$fieldDeclaration['precision']})";
        }

        return parent::getSQLDeclaration($fieldDeclaration, $platform);
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
