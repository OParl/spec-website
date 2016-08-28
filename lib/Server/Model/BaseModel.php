<?php

namespace OParl\Server\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Server\Exceptions\ConnectionNotFoundException;

/**
 * Class BaseModel
 * @package OParl\Server\Model
 */
class BaseModel extends Model
{
    use SoftDeletes;

    protected static $modelConfiguration;

    /**
     * BaseModel constructor.
     *
     * @inheritdoc
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        if (!is_array(self::$modelConfiguration)) {
            self::setModelConfiguration(config('api.default'));
        }

        $connection = self::$modelConfiguration['connection'];

        if (strlen($connection) == 0) {
            throw new ConnectionNotFoundException();
        }

        \Log::debug("Setting database connection {$connection} for class ".get_called_class());
        $this->setConnection($connection);
    }

    public static function getModelConfiguration()
    {
        return self::$modelConfiguration;
    }

    public static function setModelConfiguration($key)
    {
        self::$modelConfiguration = config('api.' . $key);
    }

    /**
     * Adjust the table name with the current model prefix.
     *
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTable()
    {
        $table = parent::getTable();

        return self::$modelConfiguration['prefix'] . $table;
    }
}
