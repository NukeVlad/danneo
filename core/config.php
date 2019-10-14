<?php
if (file_exists('setup/')) { 
    header('Location: '.$_SERVER['REQUEST_URI'].'setup/index.php');
} else { 
    echo "The catalog <strong>setup</strong> doesn't exist!";
}
exit();
