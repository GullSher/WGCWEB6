<?php

declare (strict_types=1);
namespace Staatic\Vendor\Brick\Math;

use Serializable;
use JsonSerializable;
use InvalidArgumentException;
use Staatic\Vendor\Brick\Math\Exception\DivisionByZeroException;
use Staatic\Vendor\Brick\Math\Exception\MathException;
use Staatic\Vendor\Brick\Math\Exception\NumberFormatException;
use Staatic\Vendor\Brick\Math\Exception\RoundingNecessaryException;
abstract class BigNumber implements Serializable, JsonSerializable
{
    private const PARSE_REGEXP = '/^' . '(?<sign>[\\-\\+])?' . '(?:' . '(?:' . '(?<integral>[0-9]+)?' . '(?<point>\\.)?' . '(?<fractional>[0-9]+)?' . '(?:[eE](?<exponent>[\\-\\+]?[0-9]+))?' . ')|(?:' . '(?<numerator>[0-9]+)' . '\\/?' . '(?<denominator>[0-9]+)' . ')' . ')' . '$/';
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $value
     */
    public static function of($value) : BigNumber
    {
        if ($value instanceof BigNumber) {
            return $value;
        }
        if (\is_int($value)) {
            return new BigInteger((string) $value);
        }
        $value = \is_float($value) ? self::floatToString($value) : $value;
        $throw = static function () use($value) : void {
            throw new NumberFormatException(\sprintf('The given value "%s" does not represent a valid number.', $value));
        };
        if (\preg_match(self::PARSE_REGEXP, $value, $matches) !== 1) {
            $throw();
        }
        $getMatch = static function (string $value) use ($matches) : ?string {
            return ($matches[$value] ?? '') !== '' ? $matches[$value] : null;
        };
        $sign = $getMatch('sign');
        $numerator = $getMatch('numerator');
        $denominator = $getMatch('denominator');
        if ($numerator !== null) {
            \assert($denominator !== null);
            if ($sign !== null) {
                $numerator = $sign . $numerator;
            }
            $numerator = self::cleanUp($numerator);
            $denominator = self::cleanUp($denominator);
            if ($denominator === '0') {
                throw DivisionByZeroException::denominatorMustNotBeZero();
            }
            return new BigRational(new BigInteger($numerator), new BigInteger($denominator), \false);
        }
        $point = $getMatch('point');
        $integral = $getMatch('integral');
        $fractional = $getMatch('fractional');
        $exponent = $getMatch('exponent');
        if ($integral === null && $fractional === null) {
            $throw();
        }
        if ($integral === null) {
            $integral = '0';
        }
        if ($point !== null || $exponent !== null) {
            $fractional = $fractional ?? '';
            $exponent = $exponent !== null ? (int) $exponent : 0;
            if ($exponent === \PHP_INT_MIN || $exponent === \PHP_INT_MAX) {
                throw new NumberFormatException('Exponent too large.');
            }
            $unscaledValue = self::cleanUp(($sign ?? '') . $integral . $fractional);
            $scale = \strlen($fractional) - $exponent;
            if ($scale < 0) {
                if ($unscaledValue !== '0') {
                    $unscaledValue .= \str_repeat('0', -$scale);
                }
                $scale = 0;
            }
            return new BigDecimal($unscaledValue, $scale);
        }
        $integral = self::cleanUp(($sign ?? '') . $integral);
        return new BigInteger($integral);
    }
    private static function floatToString(float $float) : string
    {
        $currentLocale = \setlocale(\LC_NUMERIC, '0');
        \setlocale(\LC_NUMERIC, 'C');
        $result = (string) $float;
        \setlocale(\LC_NUMERIC, $currentLocale);
        return $result;
    }
    /**
     * @param string $value
     */
    protected function newBigInteger($value) : BigInteger
    {
        return new BigInteger($value);
    }
    /**
     * @param string $value
     * @param int $scale
     */
    protected function newBigDecimal($value, $scale = 0) : BigDecimal
    {
        return new BigDecimal($value, $scale);
    }
    /**
     * @param BigInteger $numerator
     * @param BigInteger $denominator
     * @param bool $checkDenominator
     */
    protected function newBigRational($numerator, $denominator, $checkDenominator) : BigRational
    {
        return new BigRational($numerator, $denominator, $checkDenominator);
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string ...$values
     * @return static
     */
    public static function min(...$values)
    {
        $min = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($min === null || $value->isLessThan($min)) {
                $min = $value;
            }
        }
        if ($min === null) {
            throw new InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $min;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string ...$values
     * @return static
     */
    public static function max(...$values)
    {
        $max = null;
        foreach ($values as $value) {
            $value = static::of($value);
            if ($max === null || $value->isGreaterThan($max)) {
                $max = $value;
            }
        }
        if ($max === null) {
            throw new InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $max;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string ...$values
     * @return static
     */
    public static function sum(...$values)
    {
        $sum = null;
        foreach ($values as $value) {
            $value = static::of($value);
            $sum = $sum === null ? $value : self::add($sum, $value);
        }
        if ($sum === null) {
            throw new InvalidArgumentException(__METHOD__ . '() expects at least one value.');
        }
        return $sum;
    }
    private static function add(BigNumber $a, BigNumber $b) : BigNumber
    {
        if ($a instanceof BigRational) {
            return $a->plus($b);
        }
        if ($b instanceof BigRational) {
            return $b->plus($a);
        }
        if ($a instanceof BigDecimal) {
            return $a->plus($b);
        }
        if ($b instanceof BigDecimal) {
            return $b->plus($a);
        }
        return $a->plus($b);
    }
    private static function cleanUp(string $number) : string
    {
        $firstChar = $number[0];
        if ($firstChar === '+' || $firstChar === '-') {
            $number = \substr($number, 1);
        }
        $number = \ltrim($number, '0');
        if ($number === '') {
            return '0';
        }
        if ($firstChar === '-') {
            return '-' . $number;
        }
        return $number;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public function isEqualTo($that) : bool
    {
        return $this->compareTo($that) === 0;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public function isLessThan($that) : bool
    {
        return $this->compareTo($that) < 0;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public function isLessThanOrEqualTo($that) : bool
    {
        return $this->compareTo($that) <= 0;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public function isGreaterThan($that) : bool
    {
        return $this->compareTo($that) > 0;
    }
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public function isGreaterThanOrEqualTo($that) : bool
    {
        return $this->compareTo($that) >= 0;
    }
    public function isZero() : bool
    {
        return $this->getSign() === 0;
    }
    public function isNegative() : bool
    {
        return $this->getSign() < 0;
    }
    public function isNegativeOrZero() : bool
    {
        return $this->getSign() <= 0;
    }
    public function isPositive() : bool
    {
        return $this->getSign() > 0;
    }
    public function isPositiveOrZero() : bool
    {
        return $this->getSign() >= 0;
    }
    public abstract function getSign() : int;
    /**
     * @param \Staatic\Vendor\Brick\Math\BigNumber|int|float|string $that
     */
    public abstract function compareTo($that) : int;
    public abstract function toBigInteger() : BigInteger;
    public abstract function toBigDecimal() : BigDecimal;
    public abstract function toBigRational() : BigRational;
    /**
     * @param int $scale
     * @param int $roundingMode
     */
    public abstract function toScale($scale, $roundingMode = RoundingMode::UNNECESSARY) : BigDecimal;
    public abstract function toInt() : int;
    public abstract function toFloat() : float;
    public abstract function __toString() : string;
    public function jsonSerialize() : string
    {
        return $this->__toString();
    }
}
