<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 5/9/2020
 * Time: 7:20 PM
 */

namespace custom\model;

use Exception;
use que\database\interfaces\model\Model;

class CustomModel extends \que\database\model\Model
{
    protected string $key = 'custom';
}
