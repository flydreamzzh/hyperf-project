<?php


namespace App\Core\Provider;

use \Hyperf\Database\Model\Builder;

/**
 * 功能摘自YII2
 * Class ActiveDataProvider
 * @package App\Core\Provider
 */
class ActiveDataProvider extends BaseDataProvider
{

    /**
     * @var bool | integer
     */
    public $selfCount = false;

    /**
     * @var Builder the query that is used to fetch data models and [[totalCount]]
     * if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;
    /**
     * @var string the DB connection object or the application component ID of the DB connection.
     * If not set, the default DB connection will be used.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db;


    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     */
    public function __construct()
    {
        parent::__construct();
        if (is_string($this->db)) {
           $this->query->connection = $this->db;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareModels()
    {
        $query = clone $this->query;
        if (($pagination = $this->getPagination()) !== false) {
            $pagination->totalCount = $this->getTotalCount();
            if ($pagination->totalCount === 0) {
                return [];
            }
            $query->limit($pagination->getLimit())->offset($pagination->getOffset());
        }

        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareKeys($models)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareTotalCount()
    {
        if ($this->selfCount) {
            return (int)$this->selfCount;
        }
        $query = clone $this->query;
        return (int) $query->limit(1)->offset(0)->count('*');
    }

    public function __clone()
    {
        if (is_object($this->query)) {
            $this->query = clone $this->query;
        }

        parent::__clone();
    }
}