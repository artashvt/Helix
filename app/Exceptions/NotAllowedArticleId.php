<?php
namespace App\Exceptions;

class NotAllowedArticleId extends \Exception
{
    protected $message = 'Articles Id is not numeric';
}