<?php

declare (strict_types=1);
namespace App\Model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id 
 * @property int $user_id 
 * @property string $access_token 
 * @property int $expired 
 * @property int $status 
 */
class UsersAccessed extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_accessed';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'user_id' => 'integer', 'expired' => 'integer', 'status' => 'integer'];
}