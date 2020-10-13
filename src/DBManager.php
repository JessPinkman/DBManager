<?php

namespace database_manager;

use wpdb;

class DBManager
{

    public $primary_key = null;
    public wpdb $wpdb;

    public function __construct(string $name)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->dbname = $wpdb->prefix . $name;
    }

    public function createTable(...$lines)
    {
        require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');

        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = implode("\n", [
            "CREATE TABLE $this->dbname (",
            implode(",\n", $lines),
            ") $charset_collate;"
        ]);

        dbDelta($sql);
    }

    public function addForeignKey(
        string $field,
        string $source_table,
        string $source_column,
        string $on_delete = 'CASCADE',
        string $on_update = 'CASCADE'
    ) {
        $key_name = "fk_" . $this->dbname . "_" . $field;

        $key = $this->wpdb->get_var(
            "SELECT CONSTRAINT_NAME FROM information_schema.REFERENTIAL_CONSTRAINTS
            WHERE CONSTRAINT_NAME = '$key_name';"
        );

        if (!$key) {
            $this->wpdb->query(
                "ALTER TABLE $this->dbname
                ADD CONSTRAINT $key_name
                FOREIGN KEY ($field)
                REFERENCES $source_table($source_column)
                ON DELETE $on_delete ON UPDATE $on_update;"
            );
        }
    }

    public function replace($data)
    {
        return $this->wpdb->replace($this->dbname, $data);
    }

    public function insert($data)
    {
        return $this->wpdb->insert($this->dbname, $data);
    }

    public function delete(array $where)
    {
        $this->wpdb->delete($this->dbname, $where);
    }
}
