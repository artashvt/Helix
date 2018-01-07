<?php
namespace App\Exceptions;

class ImageNotFoundException extends \Exception
{
    protected $message = 'Image Not found';
}