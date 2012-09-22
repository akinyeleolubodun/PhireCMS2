<?php
try {
    require_once 'bootstrap.php';
    $project->run();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>