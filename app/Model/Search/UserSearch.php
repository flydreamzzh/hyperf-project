<?php

namespace App\Model\Search;

use App\Core\BaseModel;
use App\Core\Components\Pagination;
use App\Core\Provider\ActiveDataProvider;
use App\Model\Rbac\RbacRole;
use App\Model\Rbac\RbacUserRole;
use App\Model\User;
use Hyperf\DbConnection\Db;

/**
 * UserSearch represents the model behind the search form of `common\models\User`.
 */

/**
 * Class UserSearch
 * @package App\Model\Search
 * @property string $key_ 用户查询关键字
 * @property integer $role 角色ID
 * @property string $status 用户状态
 */
class UserSearch extends BaseModel
{
    /**
     * @var array
     */
    public $fillable = ['key_', 'role', 'status'];

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params = [])
    {
        $this->fill($params);
        $dataProvider = new ActiveDataProvider();
        $administrator_id = config('user.administrator_id');
        $query = null;
        $this->key_  = $this->key_ ? "%$this->key_%" : $this->key_;
        if (($this->key_ || trim($this->status) != '') && $this->role) {
            $userQuery = User::find()->alias('user')
                ->select(['user.id', 'username', 'nickname', 'email', 'status', 'pass_status', 'remark', 'user.created_at', 'user.updated_at', 'user.created_by', 'user.updated_by'])
                ->selectRaw("(case id when {$administrator_id} then 1 else 0 end) dis_delete")
                ->andFilterWhere([['username', 'like', $this->key_, 'or'], ['nickname', 'like', $this->key_, 'or'], ['email', 'like', $this->key_, 'or']])
                ->andFilterWhere(['status' => $this->status]);
            $roleQuery = RbacUserRole::find()->alias('user_roles')
                ->select(['user_id', 'roles' => ' group_concat(name)'])
                ->leftJoin(['roles' => RbacRole::tableName()], 'roles.id', '=', 'user_roles.role_id')
                ->where(['roles.id' => $this->role])
                ->groupBy('user_id');
            $query = Db::table(DB::raw("({$userQuery->toSql()}) as user, ({$roleQuery->toSql()}) as role"))
                ->select()
                ->whereRaw('user.id = role.user_id')
                ->mergeBindings($userQuery->getQuery())
                ->mergeBindings($roleQuery->getQuery());
        } else {
            if ($this->role) {
                $roleQuery = RbacUserRole::find()->alias('user_roles')->select(['user_id', 'roles' => 'group_concat(name)'])
                    ->leftJoin(['roles' => RbacRole::tableName()], 'roles.id', '=', 'user_roles.role_id')
                    ->where(['roles.id' => $this->role])
                    ->groupBy('user_id');

                $countQuery = clone $roleQuery;
                $dataProvider->selfCount = $countQuery->count();
                $pagination = new Pagination([
                    'totalCount' => $dataProvider->selfCount,
                    'pageSize' => $dataProvider->getPagination()->getPageSize(),
                ]);
                $userQuery = User::find()->alias('user')
                    ->select(['user.id', 'username', 'nickname', 'email', 'status', 'pass_status', 'remark', 'user.created_at', 'user.updated_at', 'user.created_by', 'user.updated_by'])
                    ->selectRaw("(case id when {$administrator_id} then 1 else 0 end) dis_delete");
                $roleQuery = $roleQuery->limit($pagination->getLimit())->offset($pagination->getOffset());
                $query = BaseModel::find()->from(['user' => $userQuery, 'role' => $roleQuery])
                    ->select()
                    ->whereColumn('user.id', '=', 'role.user_id');
            } else {
                $userQuery = User::find()->alias('user')
                    ->select(['user.id', 'username', 'nickname', 'email', 'status', 'pass_status', 'remark', 'user.created_at', 'user.updated_at', 'user.created_by', 'user.updated_by'])
                    ->selectRaw("(case id when {$administrator_id} then 1 else 0 end) dis_delete")
                    ->andFilterWhere([['username', 'like', $this->key_, 'or'], ['nickname', 'like', $this->key_, 'or'], ['email', 'like', $this->key_, 'or']])
                    ->andFilterWhere(['status' => $this->status])
                    ->orderBy('id');
                $countQuery = clone $userQuery;
                $dataProvider->selfCount = $countQuery->count();
                $pagination = new Pagination([
                    'totalCount' => $dataProvider->selfCount,
                    'pageSize' => $dataProvider->getPagination()->getPageSize(),
                ]);

                $userRoleMapQuery = $userQuery->limit($pagination->getLimit())->offset($pagination->getOffset());
                $userRoleQuery = BaseModel::find()->from(['user_roles_map' => $userRoleMapQuery])
                    ->select(['user_roles_map.id', 'username', 'nickname', 'email', 'status', 'pass_status', 'dis_delete', 'user_roles_map.created_at', 'user_roles_map.updated_at', 'user_roles_map.created_by', 'user_roles_map.updated_by', 'user_roles.role_id'])
                    ->leftJoin(['user_roles' => RbacUserRole::tableName()], 'user_roles.user_id', '=', 'user_roles_map.id');

                $query = BaseModel::find()->from(['user_map' => $userRoleQuery])
                    ->select(['user_map.id', 'username', 'nickname', 'email', 'status', 'pass_status', 'dis_delete', 'user_map.created_at', 'user_map.updated_at', 'user_map.created_by', 'user_map.updated_by', 'roles' => 'group_concat(name)'])
                    ->leftJoin(['roles' => RbacRole::tableName()], 'roles.id', '=', 'user_map.role_id')
                    ->groupBy('user_map.id');

            }
            $dataProvider->getPagination()->setPage(0);
        }
        $dataProvider->query = $query;

        return $dataProvider;
    }
}
