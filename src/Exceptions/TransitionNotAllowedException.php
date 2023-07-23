<?php

namespace Norotaro\Enumata\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class TransitionNotAllowedException extends Exception
{
    public function __construct(
        protected ?UnitEnum $from,
        protected UnitEnum $to,
        protected Model $model
    ) {
        $classname = get_class($model);
        $message = "Transition from '$from?->name' to '$to->name' is not allowed for model '$classname'";

        parent::__construct($message);
    }

    public function getFrom(): ?UnitEnum
    {
        return $this->from;
    }

    public function getTo(): UnitEnum
    {
        return $this->to;
    }

    public function getModel(): Model
    {
        return $this->model;
    }
}
