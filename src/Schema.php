<?php

namespace Bonfim\ActiveRecord;

abstract class Schema extends ActiveRecord
{
    private $sql = '';
    private $table;

    abstract public function up();
    abstract public function down();

    public function __construct()
    {
        $this->table = new Table();
    }

    public function create(string $name, $callback)
    {
        call_user_func_array($callback, [$this->table]);

        $q = "CREATE TABLE IF NOT EXISTS $name ({$this->getColumns()}";
        $q .= $this->pk().$this->fk().$this->onUpdate().$this->onDelete();
        $q .= "\n) ENGINE=INNODB;";

        $this->sql = $q;
    }

    public function drop(string $name)
    {
        $this->sql = "DROP TABLE $name";
    }

    private function pk()
    {
        return (!empty($pk = $this->table->getPk()))
            ? $this->sql .= ",\n\tPRIMARY KEY ($pk)"
            : "";
    }

    private function fk()
    {
        return (!empty($fk = $this->table->getFk()))
            ? ",\n\t$fk {$this->table->getReferences()}"
            : "";
    }

    private function onUpdate()
    {
        return (!empty($on = $this->table->getOnupdate()))
            ? " $on"
            : "";
    }

    private function onDelete()
    {
        return (!empty($on = $this->table->getOndelete()))
            ? " $on"
            : "";
    }

    private function getColumns()
    {
        $q = "\n\t";

        $columns = $this->table->getColumns();
        $count = count($columns);

        reset($columns);

        for ($i = 0; $i < $count; $i++) {
            $key = key($columns);
            $q .= key($columns) . ' ' . $columns[$key];
            if ($i < $count - 1) {
                $q .= ",\n\t";
            }
            next($columns);
        }

        return $q;
    }

    public function run()
    {
        self::exec($this->sql);
    }
}
