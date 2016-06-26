<?php

namespace rlip\intereststree\migrations;

class release_1_0_0 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return $this->db_tools->sql_table_exists($this->table_prefix . 'inttree_interests');
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
        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "inttree_interest` (" .
            " `interest_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `interest_parent_id` INT(11) NULL DEFAULT NULL," .
            " `interest_title` VARCHAR(100) NOT NULL DEFAULT ''," .
            " `interest_selection_allowed` TINYINT(1) NOT NULL DEFAULT '0'," .
            " PRIMARY KEY (`interest_id`)," .
            " INDEX `interest_parent_id` (`interest_parent_id`)," .
            " CONSTRAINT `fk_interest_parent_id` FOREIGN KEY (`interest_parent_id`) REFERENCES `" . $this->table_prefix . "inttree_interest` (`interest_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "inttree_user_has_interest` (" .
            " `userhasinterest_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `userhasinterest_user_id` MEDIUMINT(8) UNSIGNED NOT NULL," .
            " `userhasinterest_interest_id` INT(11) NOT NULL," .
            " `userhasinterest_rate` TINYINT(4) NOT NULL," .
            " PRIMARY KEY (`userhasinterest_id`)," .
            " INDEX `userhasinterest_user_id` (`userhasinterest_user_id`)," .
            " INDEX `userhasinterest_interest_id` (`userhasinterest_interest_id`)," .
            " UNIQUE INDEX `userhasinterest_user_id_userhasinterest_interest_id` (`userhasinterest_user_id`, `userhasinterest_interest_id`)," .
            " CONSTRAINT `fk_userhasinterest_user_id` FOREIGN KEY (`userhasinterest_user_id`) REFERENCES `" . $this->table_prefix . "users` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE," .
            " CONSTRAINT `fk_userhasinterest_interest_id` FOREIGN KEY (`userhasinterest_interest_id`) REFERENCES `" . $this->table_prefix . "inttree_interest` (`interest_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "inttree_proposal` (" .
            " `proposal_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `proposal_user_id` MEDIUMINT(8) UNSIGNED NOT NULL," .
            " `proposal_interest_id` INT(11) NULL," .
            " `proposal_text` VARCHAR(512) NOT NULL," .
            " `proposal_created_at` TIMESTAMP NOT NULL," .
            " PRIMARY KEY (`proposal_id`)," .
            " INDEX `proposal_user_id` (`proposal_user_id`)," .
            " CONSTRAINT `fk_proposal_user_id` FOREIGN KEY (`proposal_user_id`) REFERENCES `" . $this->table_prefix . "users` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE," .
            " CONSTRAINT `fk_proposal_interest_id` FOREIGN KEY (`proposal_interest_id`) REFERENCES `" . $this->table_prefix . "inttree_interest` (`interest_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);

        $sql = "CREATE TABLE IF NOT EXISTS`" . $this->table_prefix . "inttree_proposal_vote` (" .
            " `proposalvote_id` INT(11) NOT NULL AUTO_INCREMENT," .
            " `proposalvote_user_id` MEDIUMINT(8) UNSIGNED NOT NULL," .
            " `proposalvote_proposal_id` INT(11) NOT NULL," .
            " `proposalvote_value` INT(11) NULL," .
            " PRIMARY KEY (`proposalvote_id`)," .
            " INDEX `proposalvote_id` (`proposalvote_id`)," .
            " CONSTRAINT `fk_proposalvote_user_id` FOREIGN KEY (`proposalvote_user_id`) REFERENCES `" . $this->table_prefix . "users` (`user_id`) ON UPDATE CASCADE ON DELETE CASCADE," .
            " CONSTRAINT `fk_proposalvote_proposal_id` FOREIGN KEY (`proposalvote_proposal_id`) REFERENCES `" . $this->table_prefix . "inttree_proposal` (`proposal_id`) ON UPDATE CASCADE ON DELETE CASCADE" .
            " )" .
            " COLLATE='utf8_general_ci'" .
            " ENGINE=InnoDB";
        $this->sql_query($sql);

        $sql = "INSERT INTO `" . $this->table_prefix . "inttree_interest` (`interest_id`, `interest_parent_id`, `interest_title`, `interest_selection_allowed`) VALUES
                (1, NULL, 'Książki, film, muzyka', 0),
                (2, 1, 'Książki', 0),
                (3, 2, 'Przygodowe', 1),
                (4, 2, 'Psychologiczne', 1),
                (5, 2, 'Obyczajowe', 1),
                (6, 2, 'Dramaty', 1),
                (7, 2, 'Naukowe', 1),
                (8, 2, 'Fantasy', 1),
                (9, 2, 'Science-fiction', 1),
                (10, 2, 'Literatura klasyczna (przed xx w.)', 1),
                (11, 2, 'Thriller', 1),
                (12, 1, 'Film', 0),
                (13, 12, 'Komedia', 1),
                (14, 12, 'Horror, thriller', 1),
                (15, 12, 'Sensacyjny', 1),
                (16, 12, 'Obyczajowy', 1),
                (17, 12, 'Dokumentalny', 1),
                (18, 12, 'Science-fiction', 1),
                (19, 12, 'Kino niezależne', 1),
                (20, 1, 'Muzyka', 0),
                (21, 20, 'Dance', 1),
                (22, 21, 'Dubstep', 1),
                (23, 21, 'Techno', 1),
                (24, 21, 'House', 1),
                (25, 21, 'Trance', 1),
                (26, 21, 'Eurodance', 1),
                (27, 21, 'Hands up', 1),
                (28, 21, 'Uk hardcore', 1),
                (29, 21, 'Happy hardcore', 1),
                (30, 21, 'Nightcore', 1),
                (31, 21, 'Disco polo', 1),
                (32, 20, 'Alternatywna', 1),
                (33, 32, 'Punk', 1),
                (34, 32, 'New wave', 1),
                (35, 32, 'Synthwave', 1),
                (36, 32, 'Synthpop', 1),
                (37, 20, 'Klasyczna', 1),
                (38, 20, 'Filmowa', 1),
                (39, 20, 'R&b', 1),
                (40, 20, 'Rap', 1),
                (41, 40, 'Polski', 1),
                (42, 40, 'Zagraniczny', 1),
                (43, 20, 'Pop', 1),
                (44, 20, 'Rock ', 1),
                (45, 44, 'Metal', 1),
                (46, 44, 'Rock & Roll', 1),
                (47, 20, 'Etniczna', 1),
                (48, 1, 'Seriale', 0),
                (49, 48, 'Polskie', 0),
                (50, 49, 'M jak miłość', 1),
                (51, 49, 'Pierwsza miłość', 1),
                (52, 49, 'Barwy szczęścia', 1),
                (53, 49, '4 pancernych i pies', 1),
                (54, 49, 'Kapitan bomba', 1),
                (55, 48, 'Zagraniczne', 0),
                (56, 55, 'Gra o tron', 1),
                (57, 55, 'The walking dead', 1),
                (58, 55, 'Dr house', 1),
                (59, 55, 'Zagubieni', 1),
                (60, 55, 'Ostry dyżur', 1),
                (61, 55, 'Outcast', 1),
                (62, 55, 'Breaking bad', 1),
                (63, 55, 'True detective', 1),
                (64, NULL, 'Sport i podróże', 0),
                (65, 64, 'Sport', 0),
                (66, 65, 'Piłka nożna', 1),
                (67, 65, 'Piłka ręczna', 1),
                (68, 65, 'Siatkówka', 1),
                (69, 65, 'Koszykówka', 1),
                (70, 65, 'Taniec', 1),
                (71, 65, 'Sporty motorowe', 1),
                (72, 65, 'Pływanie', 1),
                (73, 65, 'Bieganie', 1),
                (75, 64, 'Podróże', 0),
                (76, 75, 'Autostop', 1),
                (77, 75, 'Rower', 1),
                (78, 75, 'Samochód', 1),
                (79, 75, 'Samolot', 1),
                (80, 79, 'Boeing 787 Dreamliner', 1),
                (81, 75, 'Motocykl', 1),
                (82, 75, 'Pociąg', 1),
                (83, 75, 'Piesze wycieczki', 1),
                (84, NULL, 'Gry', 0),
                (85, 84, 'Planszowe', 1),
                (86, 85, 'Szachy', 1),
                (87, 84, 'Komputerowe ', 0),
                (88, 87, 'FPS', 1),
                (89, 87, 'RPG', 1),
                (90, 87, 'MMORPG', 1),
                (91, 87, 'Sportowe', 1),
                (92, 87, 'Strategia ', 1),
                (93, 87, 'Sandbox', 1),
                (94, 87, 'MOBA', 1),
                (95, 87, 'RTS', 1),
                (96, 84, 'Karciane', 1),
                (97, 96, 'Poker', 1),
                (98, 96, 'Tysiąc', 1),
                (99, 96, 'Kop', 1),
                (100, 96, 'Makao', 1),
                (101, 84, 'Terenowe', 1),
                (102, NULL, 'Kolekcjonowanie', 0),
                (103, 102, 'Znaczki', 1),
                (104, 102, 'Monety', 1),
                (105, 102, 'Komiksy', 1),
                (106, 102, 'Birofilistyka', 1),
                (107, NULL, 'Zwierzęta', 0),
                (108, 107, 'Akwarystyka', 1),
                (109, 107, 'Koty', 1),
                (110, 107, 'Króliki', 1),
                (111, 107, 'Gryzonie', 1),
                (112, 107, 'Węże', 1),
                (113, 107, 'Pająki', 1),
                (114, 107, 'Psy', 1),
                (115, 107, 'Konie', 1),
                (116, 107, 'Owady', 1),
                (117, 107, 'Ptaki', 1),
                (118, NULL, 'Kulinaria', 0),
                (119, 118, 'Gotowanie', 1),
                (120, 118, 'Jedzenie', 0),
                (121, 120, 'Weganizm', 1),
                (122, 120, 'Wegetarianizm', 1),
                (123, 120, 'Zdrowe jedzenie', 1),
                (124, 120, 'Diety', 1),
                (125, NULL, 'Pieniądze i moda', 0),
                (126, 125, 'Pieniądze', 0),
                (127, 126, 'Biznes', 1),
                (128, 126, 'MLM', 1),
                (129, 125, 'Moda', 0),
                (130, 129, 'Męska', 1),
                (131, 129, 'Damska', 1),
                (132, 129, 'Krawiectwo', 1),
                (133, NULL, 'Nauki', 0),
                (134, 133, 'Informatyka', 0),
                (135, 134, 'Programowanie', 1),
                (136, 135, 'C++', 1),
                (137, 135, 'Java', 1),
                (138, 134, 'Systemy operacyjne', 1),
                (139, 134, 'Sprzęt, nowinki', 1),
                (140, 134, 'Grafika', 1),
                (141, 134, 'Sztuczna inteligencja', 1),
                (142, 133, 'Języki', 0),
                (143, 142, 'Angielski', 1),
                (144, 142, 'Niemiecki', 1),
                (145, 142, 'Włoski', 1),
                (146, 142, 'Francuski', 1),
                (147, 142, 'Rosyjski', 1),
                (148, 142, 'Hiszpański', 1),
                (149, 142, 'Japoński', 1),
                (150, 142, 'Chiński (mandaryński)', 1),
                (151, 142, 'Klingoński', 1),
                (152, 142, 'Języki wymarłe', 1),
                (153, 142, 'Języki sztuczne', 1),
                (154, 133, 'Historia', 1),
                (155, 133, 'Fizyka i astronomia', 0),
                (156, 155, 'Fizyka', 1),
                (157, 156, 'Klasyczna', 1),
                (158, 156, 'Współczesna', 1),
                (159, 156, 'Teoretyczna', 1),
                (160, 155, 'Astronomia', 1),
                (161, 133, 'Matematyka', 1),
                (162, 133, 'Filozofia', 1),
                (163, 133, 'Geografia', 1),
                (164, 133, 'Biologia', 1),
                (165, 133, 'Chemia', 1),
                (166, 133, 'Medycyna', 1),
                (167, 166, 'Medycyna ratunkowa', 1),
                (168, 166, 'Pielęgniarstwo', 1),
                (169, 133, 'Psychologia', 1),
                (170, 169, 'MBTI', 1),
                (171, 170, 'ISTJ', 1),
                (172, 170, 'ISTP', 1),
                (173, 170, 'ISFJ', 1),
                (174, 170, 'ISFP', 1),
                (175, 170, 'INTJ', 1),
                (176, 170, 'INTP', 1),
                (177, 170, 'INFJ', 1),
                (178, 170, 'INFP', 1),
                (179, 170, 'EXXX', 1),
                (180, 169, 'Ennagram', 1),
                (181, 180, 'Jedynka', 1),
                (182, 180, 'Dwójka', 1),
                (183, 180, 'Trójka', 1),
                (184, 180, 'Czwórka', 1),
                (185, 180, 'Piątka', 1),
                (186, 180, 'Szóstka', 1),
                (187, 180, 'Siódemka', 1),
                (188, 180, 'Ósemka', 1),
                (189, 180, 'Dziewiątka', 1),
                (190, 169, 'Socjotechnika', 1),
                (191, 169, 'Coaching', 1),
                (192, 133, 'Ekonomia', 1),
                (193, 133, 'Rolnictwo', 1),
                (194, NULL, 'Dom i ogród', 0),
                (195, 194, 'Budownictwo', 1),
                (196, 194, 'Majsterkowanie', 1),
                (197, 194, 'Meble', 1),
                (198, 194, 'Wystrój wnętrz', 1),
                (199, 194, 'Ogród', 1),
                (200, NULL, 'Polityka, kultura i religia', 0),
                (201, 200, 'Religia', 0),
                (202, 201, 'Chrześcijaństwo', 1),
                (203, 202, 'Katolicyzm', 1),
                (204, 202, 'Prawosławie', 1),
                (205, 202, 'Protestantyzm', 1),
                (206, 201, 'Buddyzm', 1),
                (207, 201, 'Hinduizm', 1),
                (208, 201, 'Zoroastryzm', 1),
                (209, 201, 'Islam', 1),
                (210, 201, 'Judaizm', 1),
                (211, 201, 'Pastafarianizm', 1),
                (212, 201, 'Ateizm', 1),
                (213, 200, 'Kultura', 1),
                (214, 213, 'Polska', 1),
                (215, 214, 'Folklor', 1),
                (216, 213, 'Japońska', 1),
                (217, 216, 'Anime', 1),
                (218, 200, 'Polityka', 0),
                (219, 218, 'Polska', 1),
                (220, 218, 'Zagraniczna', 1),
                (221, NULL, 'Twórczość własna i sztuka', 0),
                (222, 221, 'Muzyka', 1),
                (223, 221, 'Poezja', 1),
                (224, 221, 'Literatura', 1),
                (225, 221, 'Erotyka', 1),
                (226, 221, 'DIY', 1),
                (227, 221, 'Malarstwo', 1),
                (228, 221, 'Rzeźba', 1),
                (229, 221, 'Blogi', 1),
                (230, 221, 'Architektura', 1),
                (231, 221, 'Design', 1);";
        $this->sql_query($sql);
    }

    public function revert_schema()
    {
        return array(
            'drop_tables' => array(
                $this->table_prefix . 'inttree_user_has_interest',
                $this->table_prefix . 'inttree_proposal',
                $this->table_prefix . 'inttree_interest',
                $this->table_prefix . 'inttree_proposal_vote',
            ),
        );
    }
}
