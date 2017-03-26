<?php

function abc()
{
	return __FUNCTION__;
}
function xyz()
{
	return abc();
}
echo xyz();
?>