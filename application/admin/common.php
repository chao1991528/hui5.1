<?php
function is_login(){
	return session('admin') ? true : false;
}