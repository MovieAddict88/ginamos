<?php
$sql = 'ALTER TABLE configurations ADD COLUMN promo_id INT, ADD FOREIGN KEY (promo_id) REFERENCES promos(id);';
?>