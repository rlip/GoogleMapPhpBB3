<?php

namespace rlip\intereststree\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'interests');
    }

    static public function depends_on()
    {
        return array('\phpbb\db\migration\data\v310\alpha2');
    }

    public function update_data()
    {
        return array(
            array('custom', array(array($this, 'create_tables')))
        );
    }

    public function create_tables()
    {
        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "interest` (" .
            " `interest_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `interest_parent_id` INT(11) NULL DEFAULT NULL," .
            " `interest_title` VARCHAR(100) NOT NULL DEFAULT ''," .
            " `interest_selection_allowed` TINYINT(1) NOT NULL DEFAULT '0'," .
            " PRIMARY KEY (`interest_id`)," .
            " INDEX `interest_parent_id` (`interest_parent_id`)," .
            " CONSTRAINT `fk_interest_parent_id` FOREIGN KEY (`interest_parent_id`) REFERENCES `" . $this->table_prefix . "interest` (`interest_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "user_has_interest` (" .
            " `userhasinterest_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `userhasinterest_user_id` MEDIUMINT(8) UNSIGNED NOT NULL," .
            " `userhasinterest_interest_id` INT(11) NOT NULL," .
            " `userhasinterest_rate` TINYINT(4) NOT NULL," .
            " PRIMARY KEY (`userhasinterest_id`)," .
            " INDEX `userhasinterests_user_id` (`userhasinterest_user_id`)," .
            " INDEX `userhasinterests_interest_id` (`userhasinterest_interest_id`)," .
            " CONSTRAINT `fk_userhasinterest_user_id` FOREIGN KEY (`userhasinterest_user_id`) REFERENCES `" . $this->table_prefix . "users` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE," .
            " CONSTRAINT `fk_userhasinterest_interest_id` FOREIGN KEY (`userhasinterest_interest_id`) REFERENCES `" . $this->table_prefix . "interest` (`interest_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'interest',
            ),
        );
    }
}
