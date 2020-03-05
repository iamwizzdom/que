<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/16/2017
 * Time: 2:55 AM
 */

namespace que\utility\iterators;

use Iterator;
use que\common\exception\QueRuntimeException;

class CSVIterator implements Iterator {

    /**
     * @var bool|resource
     */
    protected $fileHandle;

    /**
     * @var bool|string
     */
	protected $line;

    /**
     * @var int
     */
	protected $i = 0;

    /**
     * CSVIterator constructor.
     * @param $fileName
     */
	public function __construct(string $fileName) {

	    if (!$this->fileHandle = fopen($fileName, 'r'))
	        throw new QueRuntimeException("Couldn't open file '{$fileName}'");
	}

	public function rewind() {
		fseek($this->fileHandle, 0);
		$this->line = fgetcsv($this->fileHandle);
		$this->i = 0;
	}

    /**
     * @return bool
     */
	public function valid(): bool {
		return false !== $this->line;
	}

    /**
     * @return bool|mixed|string
     */
	public function current() {
		return $this->line;
	}

    /**
     * @return int
     */
	public function key(): int {
		return $this->i;
	}

    /**
     * @return array|bool|false|string|void|null
     */
	public function next() {
		if (false !== $this->line){
			$this->line = fgetcsv($this->fileHandle);
			$this->i++;
		}
        return $this->line;
	}

	public function __destruct() {
		fclose($this->fileHandle);
	}
}

