<?php
	/**
	 * 
	 */
	class Session
	{
		private static $_array = null;
		
		/**
		 * 
		 */
		public function __construct()
		{
			if (!self::_array) {
				self::$_array = array();
			}
		}

		public static function add($key, $value) {
			self::$_array[$key] = $value;
		}

		public static function remove($key) {
			unset(self::$_array[$key]);
		}

		public static function exist($key) {
			return isset(self::$_array[$key]);
		}

		public static function count() {
			count(self::$_array);
		}

		public static function getValue($key) {
			return self::$_array[$key];
		}
		public static function setValue($key, $value) {
			if (!self::exist($key)) {
				self::add($key, $value);
			}
			else {
				self::$_array[$key] = $value;
			}
		}

		public static function clear() {
			unset(self::$_array);
		}
	}
?>
