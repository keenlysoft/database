<?php

eval('namespace keenly\base; trait Singleton {}');

require dirname(__DIR__).'/BaseActiveRecord.php';
require dirname(__DIR__).'/ActiveRecordInterface.php';
require dirname(__DIR__).'/ActiveRecord.php';
require dirname(__DIR__).'/models.php';

function fail($message)
{
    fwrite(STDERR, $message.PHP_EOL);
    exit(1);
}

function assertSame($expected, $actual, $message)
{
    if ($expected !== $actual) {
        fail($message."\nExpected: ".$expected."\nActual:   ".$actual);
    }
}

class SqlBuilderFixture extends \database\BaseActiveRecord
{
    public function insert($table, array $data)
    {
        return $this->dealInsertSQL($table, $data);
    }

    public function update($table, array $data, array $where, $prepared = true)
    {
        return $this->dealUpdateSQL($table, $data, $where, $prepared);
    }

    public function deleteWhere($table, array $where)
    {
        return $this->dealDeleteSQL($table, $where);
    }
}

$builder = new SqlBuilderFixture();
assertSame(
    'insert into users (`name`,`email`) value (:name,:email)',
    $builder->insert('users', array('name' => 'Ada', 'email' => 'ada@example.test')),
    'Insert SQL should use placeholders.'
);
assertSame(
    'UPDATE users  SET `name` = :name , `email` = :email WHERE `id` = \'7\'',
    $builder->update('users', array('name' => 'Ada', 'email' => 'ada@example.test'), array('id' => 7)),
    'Prepared update SQL should contain each field once.'
);
assertSame(
    'UPDATE users  SET `name` = :name WHERE `id` = \'8\'',
    $builder->update('users', array('name' => 'Grace'), array('id' => 8)),
    'Repeated updates should reset SQL builder state.'
);
assertSame(
    'UPDATE users  SET `name` = :name WHERE `name` = \'O\'\'Reilly\'',
    $builder->update('users', array('name' => 'Grace'), array('name' => "O'Reilly")),
    'Where values should be escaped.'
);
assertSame(
    'DELETE FROM users  WHERE `id` = \'9\'',
    $builder->deleteWhere('users', array('id' => 9)),
    'Delete SQL should include the where clause.'
);

try {
    $builder->update('users', array('name;drop' => 'Ada'), array('id' => 1));
    fail('Invalid SQL identifiers should be rejected.');
} catch (\InvalidArgumentException $e) {
    assertSame('Invalid SQL identifier.', $e->getMessage(), 'Invalid identifier message should be stable.');
}

$model = new \database\models();
$model['name'] = 'Ada';
assertSame('Ada', $model['name'], 'ArrayAccess reads should return model values.');
unset($model['name']);
if (isset($model['name'])) {
    fail('ArrayAccess unset should remove model values.');
}

echo "Database smoke test passed.\n";
