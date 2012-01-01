<?php

include('system/config.php');
include('system/alias.php');

include('system/W2P/System/W2P_System_Utilities.php');
include('system/W2P/System/W2P_System_ErrorNotifier.php');

spl_autoload_register("W2PSU::autoload");
set_error_handler("W2PSEN::handler");

?>