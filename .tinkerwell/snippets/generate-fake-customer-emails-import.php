<?php

$emails = '';

foreach (range(1, 21_000) as $i) {
    $emails .= fake()->unique()->companyEmail()."\n";
}

ray()->clearAll();
ray([$emails]); // wrap to array, to be copyable on ray app
