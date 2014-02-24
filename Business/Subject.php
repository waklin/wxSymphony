<?php
	/**
	 * 
	 */
	class Subject
	{
		
		/**
		 * 
		 */
		public function __construct()
		{
			;
		}

		public function track($msg) {
			// 线路名称，上行/下行
			// 加入到track表中
			// 创建一个session维护subject信息，
		}

		public function untrack($msg) {
			// body...
			// 从track表中删除用户的track记录
		}

		public function updateLocation($locationMsg) {
			// 根据locationMsg寻找指定的站点，并更新track表
			// 更新成功提示成功更新站点信息
			// 失败提示更新站点失败的原因(未找到此站点)
		}
	}
?>
