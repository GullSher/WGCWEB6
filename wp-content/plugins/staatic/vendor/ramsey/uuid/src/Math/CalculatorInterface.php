<?php

declare (strict_types=1);
namespace Staatic\Vendor\Ramsey\Uuid\Math;

use Staatic\Vendor\Ramsey\Uuid\Type\Hexadecimal;
use Staatic\Vendor\Ramsey\Uuid\Type\Integer as IntegerObject;
use Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface;
interface CalculatorInterface
{
    /**
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface $augend
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface ...$addends
     */
    public function add($augend, ...$addends) : NumberInterface;
    /**
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface $minuend
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface ...$subtrahends
     */
    public function subtract($minuend, ...$subtrahends) : NumberInterface;
    /**
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface $multiplicand
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface ...$multipliers
     */
    public function multiply($multiplicand, ...$multipliers) : NumberInterface;
    /**
     * @param int $roundingMode
     * @param int $scale
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface $dividend
     * @param \Staatic\Vendor\Ramsey\Uuid\Type\NumberInterface ...$divisors
     */
    public function divide($roundingMode, $scale, $dividend, ...$divisors) : NumberInterface;
    /**
     * @param string $value
     * @param int $base
     */
    public function fromBase($value, $base) : IntegerObject;
    /**
     * @param IntegerObject $value
     * @param int $base
     */
    public function toBase($value, $base) : string;
    /**
     * @param IntegerObject $value
     */
    public function toHexadecimal($value) : Hexadecimal;
    /**
     * @param Hexadecimal $value
     */
    public function toInteger($value) : IntegerObject;
}
