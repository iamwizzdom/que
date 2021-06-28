<?php

namespace que\utility\money;

use Exception;
use JsonSerializable;

class Item implements JsonSerializable {

    private static $defaultPrecision;
    protected $currency = "NGN";

    /**
     *
     * @var string
     */
    protected $cents;

    /**
     *
     * @var string
     */
    protected $factor;

    /**
     * Item constructor.
     * @param $amount
     * @param null $precision
     */
    public function __construct($amount, $precision = null){
        if(empty($amount)){
            $amount = 0;
        }
        $this->setFactor($amount, $precision);
    }

    /**
     * @param $amount
     * @param null $precision
     * @return static
     */
    public static function factor($amount, $precision = null){
        return new static($amount, $precision);
    }

    /**
     * @return static
     */
    public static function zero(){
        return new static(0);
    }

    /**
     * @param $cents
     * @param null $precision
     * @return static
     */
    public static function cents($cents, $precision = null){
        return new static(bcdiv($cents, MONEY_CENT_VALVE,
            $precision !== null ? $precision : self::getDefaultPrecision()), $precision);
    }

    /**
     * @param $cents
     * @param null $precision
     */
    public function setCent($cents, $precision = null){
        $this->cents  = $cents;
        $this->factor = bcdiv($cents, MONEY_CENT_VALVE, $this->evaluatePrecision($precision));
    }

    /**
     *
     * @param string $amount
     * @param string $precision
     */
    public function setFactor($amount, $precision = null){
        // Clean Amount
        // Allow Negative values
        $this->factor = preg_replace('/[^0-9.-]+/', '', strval($amount));

        // Calculate Cent
        $this->cents = bcmul($this->factor, (string)MONEY_CENT_VALVE, $this->evaluatePrecision($precision));
    }

    /**
     *
     * @param int $precision
     * @return int
     */
    protected function evaluatePrecision($precision): int {
        return $precision !== null ? $precision : self::getDefaultPrecision();
    }

    /**
     * @param $amount
     * @param null $precision
     * @return Item
     */
    private function newFactor($amount, $precision = null): Item {
        if(empty($amount)) return new static(0);
        return new static($amount, $precision);
    }

    /**
     * @param $cents
     * @param null $precision
     * @return Item
     */
    private function newCents($cents, $precision = null): Item {
        if(empty($cents)) return new static(0);
        return new static(bcdiv($cents, MONEY_CENT_VALVE, $this->evaluatePrecision($precision)));
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->getCents();
    }

    /**
     *
     * Returns the monetary value represented by this object.
     *
     * @param bool $format
     * @param int $decimals
     * @return string
     */
    public function getCents($format = false, $decimals = MONEY_CONFIG_PRECISION): string {
        if($format) return number_format($this->cents, $decimals); // Remove all precision
        return $this->cents;
    }

    /**
     *
     * Returns the monetary value represented by this object.
     * @param bool $format
     * @param int $decimal
     * @return string
     */
    public function getFactor($format = false, $decimal = MONEY_CONFIG_PRECISION): string {
        if($format) return $this->format($decimal);
        return $this->factor;
    }

    /**
     *
     * Returns the monetary value represented by this object.
     *
     * @param $decimal
     * @return string
     */
    public function format($decimal = MONEY_CONFIG_PRECISION){
        return number_format($this->factor, $decimal, MONEY_FORMAT_DECIMAL, MONEY_FORMAT_THOUSAND);
    }

    /**
     * @param string $currency
     */
    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    /**
     *
     * Returns the currency of the monetary value represented by this
     * object.
     *
     * @return string
     */
    public function getCurrency(): string {
        return $this->currency;
    }

    /**
     * @param $defaultPrecision
     */
    public static function setDefaultPrecision($defaultPrecision){
        self::$defaultPrecision = $defaultPrecision;
    }

    /**
     * Get Default Precision
     *
     * @return int
     */
    public static function getDefaultPrecision(): int {
        return MONEY_CONFIG_PRECISION;
    }

    /**
     * Returns a new Object object that represents the monetary value
     * of the sum of this Object object and another.
     *
     * @param Item $other
     * @return Item
     * @throws Exception
     */
    public function add(Item $other): int {
        $this->assertSameCurrency($this, $other);
        $value = bcadd($this->cents, $other->getCents(), $this->getDefaultPrecision());
        return $this->newCents($value);
    }

    /**
     * Returns a new Object object that represents the monetary value
     * of the difference of this Object object and another.
     *
     * @param Item $other
     * @return Item
     * @throws Exception
     */
    public function subtract(Item $other): Item {
        $this->assertSameCurrency($this, $other);
        $value = bcsub($this->cents, $other->getCents(), $this->getDefaultPrecision());
        return $this->newCents($value);
    }

    /**
     * Returns a new Object object that represents the negated monetary value
     * of this Object object.
     *
     * @param null $precision
     * @return Item
     */
    public function negate($precision = null): Item {
        return $this->newCents(bcmul(-1, $this->cents,
            $precision !== null ? $precision : $this->getDefaultPrecision()));
    }

    /**
     * @param $percent
     * @return Item
     * @throws Exception
     */
    public function percentage($percent): Item {
        if(!is_numeric($percent))
            throw new Exception("Percentage Must be a numeric value");

        if(empty($percent) || empty($this->cents)) return $this->newCents(0);

        // Multiply Value
        $cents = bcmul($this->cents, $percent);

        // Divide by 100 to get Percentage
        $cents = bcdiv($cents, 100, $this->getDefaultPrecision());

        // Get Cents
        return $this->newCents($cents);
    }

    /**
     * @param $percent
     * @return Item
     * @throws Exception
     */
    public function percentageAdd($percent): Item {
        $percentAmount = $this->percentage($percent);

        // Get Cents
        return $this->add($percentAmount);
    }

    /**
     * @param $percent
     * @return Item
     * @throws Exception
     */
    public function percentageSubtract($percent): Item {
        $percentAmount = $this->percentage($percent);

        // Get Cents
        return $this->subtract($percentAmount);
    }

    /**
     *
     * Returns a new Object object that represents the monetary value
     * of this Object object multiplied by a given factor.
     *
     * @param $factor
     * @return Item
     */
    public function multiply($factor): Item {
        if($factor instanceof Item) $factor = $factor->getCents();
        return $this->newCents(bcmul($this->cents, $factor, $this->getDefaultPrecision()));
    }

    /**
     *
     * Returns a new Object object that represents the monetary value
     * of this Object object multiplied by a given factor.
     *
     * @param $divide
     * @return Item
     */
    public function divide($divide): Item {
        if($divide instanceof Item)
            $divide = $divide->getCents();

        if($divide == $this->factor)
            return $this->newFactor(1);

        if((int)$divide == 0 || (int)$this->cents == 0)
            return $this->newCents(0);

        return $this->newCents(bcdiv($this->cents, $divide, $this->getDefaultPrecision()));
    }

    /**
     *
     * Compares this Object object to another.
     *
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this Object object is considered to be respectively
     * less than, equal to, or greater than the other Object object.
     *
     * @param Item $other
     * @return int (-1|0|1)
     * @throws Exception
     */
    public function compareTo(Item $other){
        $this->assertSameCurrency($this, $other);

        return bccomp($this->cents, $other->getCents());
    }

    /**
     * Returns TRUE if this Object object equals to another.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function notEquals(Item $other): bool {
        return $this->compareTo($other) != 0;
    }

    /**
     * Returns TRUE if this Object object equals to another.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function equals(Item $other): bool {
        return $this->compareTo($other) == 0;
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is greater than that of another, FALSE otherwise.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function greaterThan(Item $other): bool {
        return $this->compareTo($other) == 1;
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is greater than or equal that of another, FALSE otherwise.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function greaterThanOrEqual(Item $other): bool{
        return $this->greaterThan($other) || $this->equals($other);
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is smaller than that of another, FALSE otherwise.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function lessThan(Item $other): bool {
        return $this->compareTo($other) == -1;
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is smaller than or equal that of another, FALSE otherwise.
     *
     * @param Item $other
     * @return bool
     * @throws Exception
     */
    public function lessThanOrEqual(Item $other): bool {
        return $this->lessThan($other) || $this->equals($other);
    }

    /**
     * @param Item $a
     * @param Item $b
     * @throws Exception
     */
    private function assertSameCurrency(Item $a, Item $b){
        if($a->getCurrency() != $b->getCurrency()){
            throw new Exception("Currency Mismatch");
        }
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is equal to zero, FALSE otherwise.
     *
     * @return bool
     * @throws Exception
     */
    public function isZero(): bool {
        $zero = new Item(0);
        return $this->equals($zero);
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is smaller than or equal to zero, FALSE otherwise.
     *
     * @return bool
     * @throws Exception
     */
    public function isLoss(): bool {
        $zero = new Item(0);
        return $this->lessThan($zero);
    }

    /**
     * Returns TRUE if the monetary value represented by this Object object
     * is smaller than or equal to zero, FALSE otherwise.
     *
     * @return boolean
     * @throws Exception
     */
    public function isaZeroLoss(): bool {
        $zero = new Item(0);
        return $this->lessThan($zero) || $this->equals($zero);
    }

    /**
     * @return string
     */
    public function jsonSerialize(): string {
        return $this->cents;
    }
}