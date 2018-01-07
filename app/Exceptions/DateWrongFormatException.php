<?php
namespace App\Exceptions;

class DateWrongFormatException extends \Exception
{
    protected $message = 'Not supported Date Time format for article';
}