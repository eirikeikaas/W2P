<?php

class Users extends Model{
	public static function superadmin($orm) {
        return $orm->where('level', 10);
    }
    public static function admin($orm) {
        return $orm->where_gte('level', 5);
    }
    public static function reseller($orm) {
        return $orm->where_gte('level', 3);
    }
    public static function user($orm) {
        return $orm->where_gte('level', 1);
    }
    public static function clearance($orm, $level) {
        return $orm->where_gte('level', $level);
    }
}

?>