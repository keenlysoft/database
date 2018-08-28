如何使用Keenly database ORM 目前只支持mysql 连接库
# 查找
##### 一个简单的查找
```php
model::find(['id'=>100])->where(['name'=>'keenly'])->one(); #获取一条数据
```
```php
model::find(['id'=>100])->where(['name'=>'keenly'])->all(); #获取多条数据
```
keenly database 返回结果集以数组方式返回
##### 介绍条件语句
|  方法 |  使用 |描述|
| ------------ | ------------ |------------|
| inwhere  |  inwhere('filed',['q','b']) |参数1：字段，参数2：数组或者字符串|
| pwhere|pwhere(['id'=>'100','name'=>'ssc']) |参数1：数组|
|pwhere|pwhere('<',['id'=>'100','name'=>'ssc'])|参数1：运算符，参数2：数组|
|pwhere|pwhere(">",['id'=>'100','name'=>'ssc'],'and')|参数1：运算符，参数2：数组,参数3：and or |
|likeWhere|likewhere('field','name',a)|left = l , right = r , all = a|
