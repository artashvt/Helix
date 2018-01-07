<?php
namespace App\Exceptions;

class TitleNotFoundException extends \Exception
{
    protected $message = 'Title Not found';
}