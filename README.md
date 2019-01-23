如何使用Keenly database ORM 目前只支持mysql 连接库
# 查找
##### 一个简单的查找
```php
model::find(['id'])->where(['name'=>'keenly'])->one(); #获取一条数据
```
```php
model::find(['id'])->where(['name'=>'keenly'])->all(); #获取多条数据
```
keenly database 返回结果集以数组方式返回
##### 介绍条件语句
|  方法 |  使用 |描述|
| ------------ | ------------ |------------|
| inwhere  |  inwhere('filed',['q','b']) |参数1：字段，参数2：数组或者字符串|
| pwhere|pwhere(['id'=>'100','name'=>'ssc']) |参数1：数组|
|pwhere|pwhere('<',['id'=>'100','name'=>'ssc'])|参数1：运算符，参数2：数组|
|pwhere|pwhere(">",['id'=>'100','name'=>'ssc'],'and')|参数1：运算符，参数2：数组,参数3：and or |
|likeWhere|likewhere('field','name','a')|left = l , right = r , all = a|
# 更新
```php
$user = new user();
$user->Update($data,$where,flase); #更新数据 
```
| 参数1  |参数2   |参数3|
| ------------ | ------------ | ------------ |
|更新值   | 条件语句 |如果是true 表示预处理语句更新 false 非预处理语句更新数据|

```php
# 使用主键值更新
$user = new user($id);
$user->name = 'jack_yang';
$user->save();
```
# 添加
```php
$user = new user();
$user->add($data); #添加数据 
```
| 参数1 
| ------------ |
|['name'=>'yang','age'=>18| 

```php
#使用AR添加
$user = new user();
$user->name = 'jack_yang';
$user->save();
```
# 删除
```
#删除条件以数组方式或字符串方式传参
$user = new User();
$user->Delete(['id'=>1,'name'=>'ccc']); 
```


# 返回SQL
```php
model::find(['id'])->where(['name'=>'keenly'])->all(false) #all()或者 one()参数为FALSE
```

# 求总数
```php
model::find('id')->where(['name'=>'keenly'])->count(); # 返回结果 int 整数
```
# 判断是否存在
```php
model::find('id')->where(['name'=>'keenly'])->exist(); #返回结果 bool
```
# 判断是否存在
```php
model::find('id')->where(['name'=>'keenly'])->exist(); #返回结果 bool
```
# 返回 top 10
```php
model::find('id')->where(['name'=>'keenly'])->top(10); #返回数组 
```
# 计数器
```php
user::UpdateCounter(['name'=>2],['id'=>2]); ##参数1 字段=>2 or -2 参数2：where 语句
```
# 切换数据库
```php
User::SwitchDB('test'); # test 是database 配置里面连接的数据库名称
$user = User::find()->where([])->all();
User::InitDB();
```

# 事务
### 开启事务
```
$user = new user;
$user->begin();

```
### 检查是否在一个事务内
```
$user = new user;
$user->InTransaction();

```
### 提交事务
```
$user = new user;
$user->commit();
```
### 回滚事务
```
$user = new user;
$user->back();
```
