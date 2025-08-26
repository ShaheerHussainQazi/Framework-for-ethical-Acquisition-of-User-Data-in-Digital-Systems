<?php
// Starting the session if not already. (rare case)
session_start();

// Clearing the session data
session_unset();
session_destroy();
?>
