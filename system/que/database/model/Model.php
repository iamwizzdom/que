<?php


namespace que\database\model;

use que\database\model\base\BaseModel;

abstract class Model extends BaseModel
{
    protected array $hidden = ['password'];
}
