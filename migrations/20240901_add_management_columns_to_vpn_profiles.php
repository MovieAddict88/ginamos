<?php
$pdo->exec("ALTER TABLE vpn_profiles ADD COLUMN management_ip VARCHAR(255), ADD COLUMN management_port INT");
