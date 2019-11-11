<?php

/*
 * The MIT License
 *
 * Copyright 2017 Rafael Nájera <rafael@najera.ca>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace DataTable;

use Exception;
use PDO;
use RuntimeException;

class MySqlDataTableWithRandomIds extends MySqlDataTable
{
    private $minId;
    private $maxId;

    const ERROR_RANDOM_NUMBER_GENERATOR_ERROR = 2001;
    const MAX_ATTEMPTS = 1000;
    /**
     *
     * $min and $max should be carefully chosen so that
     * the method to get new unused id doesn't take too
     * long.
     * @param PDO $dbConnection
     * @param string $tableName
     * @param int $min
     * @param int $max
     */
    public function __construct(PDO $dbConnection, string $tableName, int $min = 1, int $max = PHP_INT_MAX)
    {
        $this->minId = $min;
        $this->maxId = $max;
        
        parent::__construct($dbConnection, $tableName);
    }
    
    public function getOneUnusedId() : int
    {
        for ($i = 0; $i < self::MAX_ATTEMPTS; $i++) {
            try {
                $theId = random_int($this->minId, $this->maxId);
            } catch (Exception $e) {
                throw new RuntimeException($e->getMessage(), self::ERROR_RANDOM_NUMBER_GENERATOR_ERROR);
            }
            if (!$this->rowExists($theId)) {
                return $theId;
            }
        }
        // This part of the code should almost never run in real life!
        $this->setWarning('No unused Id found after  ' . self::MAX_ATTEMPTS . ' random attempts');
        return $this->getMaxId()+1;
    }
}
