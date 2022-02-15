<?php

namespace App\Http\Responses;

/**
 * Class BaseResponse
 * @package App\Http\Responses
 */
class CRUDResponse extends BaseResponse
{
    const STATUS_SUCCESS = 'success';
    const STATUS_ERROR   = 'error';

    /**
     * @var string
     */
    protected $status = self::STATUS_SUCCESS;

    /**
     * @var string
     */
    protected $message = "";

    /**
     * @return mixed
     */
    public function getStatus(): string
    {
        if (empty($this->getErrors())) {
            return self::STATUS_SUCCESS;
        }

        return self::STATUS_ERROR;
    }

    /**
     * @return mixed
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param  string  $message
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function send()
    {
        return response()->json(
            [
                'message' => $this->getMessage(),
                'status'  => $this->getStatus(),
                'errors'  => $this->getErrors()
            ] + $this->getData(),
            $this->getCode()
        );
    }
}
