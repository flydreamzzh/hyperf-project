<?php

declare (strict_types=1);
namespace App\Model;

use App\Core\BaseModel;

/**
 * @property int $id 
 * @property string $username 
 * @property string $nickname 
 * @property string $auth_key 
 * @property string $password_hash 
 * @property string $password_reset_token 
 * @property string $email 
 * @property int $status 
 * @property int $pass_status 
 * @property string $remark 
 * @property \Carbon\Carbon $created_at 
 * @property string $created_by 
 * @property \Carbon\Carbon $updated_at 
 * @property string $updated_by 
 */
class User extends BaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'int', 'status' => 'integer', 'pass_status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}