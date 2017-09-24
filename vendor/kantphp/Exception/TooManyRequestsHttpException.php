<?php

/**
 * @package KantPHP
 * @author  Zhenqiang Zhang <zhenqiang.zhang@hotmail.com>
 * @copyright (c) KantPHP Studio, All rights reserved.
 * @license http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 */
namespace Kant\Exception;

/**
 * TooManyRequestsHttpException represents a "Too Many Requests" HTTP exception with status code 429
 *
 * Use this exception to indicate that a client has made too many requests in a
 * given period of time. For example, you would throw this exception when
 * 'throttling' an API user.
 *
 * @link http://tools.ietf.org/search/rfc6585#section-4
 * @author Dan Schmidt <danschmidt5189@gmail.com>
 * @since 2.0
 */
class TooManyRequestsHttpException extends HttpException
{

    /**
     * Constructor.
     * 
     * @param string $message
     *            error message
     * @param int $code
     *            error code
     * @param \Exception $previous
     *            The previous exception used for the exception chaining.
     */
    public function __construct($message = null, $code = 0, \Exception $previous = null)
    {
        parent::__construct(429, $message, $code, $previous);
    }
}
