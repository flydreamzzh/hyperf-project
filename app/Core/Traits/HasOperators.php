<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Core\Traits;

use App\Core\Components\Identity;
use Hyperf\Utils\Context;

trait HasOperators
{
    /**
     * Indicates if the model should be operator.
     *
     * @var bool
     */
    public $operators = true;

    /**
     * Update the model's update operator.
     */
    public function touch(): bool
    {
        if ($this->usesOperators()) {
            $this->updateOperators();
        }

        return parent::touch();
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param mixed $value
     * @return $this
     */
    public function setCreatedBy($value)
    {
        $this->{static::CREATED_BY} = $value;

        return $this;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param mixed $value
     * @return $this
     */
    public function setUpdatedBy($value)
    {
        $this->{static::UPDATED_BY} = $value;

        return $this;
    }

    /**
     * Get a fresh operator for the model.
     * @return string
     */
    public function freshOperator()
    {
        $identity = Context::get(Identity::class);
        return $identity && !$identity->getIsGuest() ? $identity->getIdentity()->getUserName() : '' ;
    }

    /**
     * Determine if the model uses operators.
     */
    public function usesOperators(): bool
    {
        return $this->operators;
    }

    /**
     * Get the name of the "created at" column.
     */
    public function getCreatedByColumn(): ?string
    {
        return static::CREATED_BY;
    }

    /**
     * Get the name of the "updated at" column.
     */
    public function getUpdatedByColumn(): ?string
    {
        return static::UPDATED_BY;
    }

    /**
     * Update the creation and update operators.
     */
    protected function updateOperators()
    {
        $operator = $this->freshOperator();

        if (!is_null(static::UPDATED_BY) && !$this->isDirty(static::UPDATED_BY)) {
            $this->setUpdatedBy($operator);
        }

        if (!$this->exists && !is_null(static::CREATED_BY) &&
            !$this->isDirty(static::CREATED_BY)) {
            $this->setCreatedBy($operator);
        }
    }
}
