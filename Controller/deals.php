<?php
require_once ('../Model/connectionBitrix24.php');

$result = $_REQUEST;
ConnectionBitrix24::GET_Deal($_REQUEST);