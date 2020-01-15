<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/16/2017
 * Time: 2:55 AM
 */

namespace que\utility\iterators;

use RuntimeException;

class LineIterator implements \Iterator {

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

	public function __construct($fileName) {
		if (!$this->fileHandle = fopen($fileName, 'r'))
            throw new RuntimeException("Couldn't open file '{$fileName}'");
	}

	public function rewind() {
		fseek($this->fileHandle, 0);
		$this->line = fgets($this->fileHandle);
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

	public function next() {
		if (false !== $this->line) {
			$this->line = fgets($this->fileHandle);
			$this->i++;
		}
	}

	public function __destruct() {
		fclose($this->fileHandle);
	}
}

