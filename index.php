<?php

include('system/config.php');

include('system/W2P/System/W2P_System_Utilities.php');
include('system/W2P/System/W2P_System_ErrorNotifier.php');

spl_autoload_register("W2P_System_Utilities::autoload");
set_error_handler("W2P_System_ErrorNotifier::handler");

?>