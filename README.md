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
