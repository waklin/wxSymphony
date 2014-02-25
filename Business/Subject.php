<?php
	session_start();

	/**
	 * 
	 */
	class Subject
	{
		
		private $_dbAccess = new DBAccess();

		/**
		 * 
		 */
		public function __construct()
		{
			;
		}

		/**
		 * s [attention�е����]
		 * s [1]
		 * �����ı���Ϣ
		 */
		public function prepare($textMsg) {
			$wxAppId = $textMsg->FromUserName;
			$content = $textMsg->Content;

			$cmd = explode(" ", $content);
			$assignAttention = ($cmd.count() > 1);		// û��ָ�����
			$attentionIndex = 0
			if ($assignAttention) {
				$attentionIndex = intval($cmd[1])
			}

			if (!isset(($_SESSION[$wxAppId]))) {
				$userId = 0;
				$sql = sprintf("select user.id, user.city_id from user, attention"
					. " where user.id = attention.user "
					. " and user.wxAppId = '%s'", $wxAppId);
				$result = $_dbAccess->execSql($sql);
				$num = $result->num_rows;
				if ($num <= 0) {
					// ����û�������û���·��������Ϣ
				}
				else if ($num == 1) {
					;
				}
				else{
					;
				}

				$_SESSION[$textMsg->FromUserName] = true;
			}
		}

		/**
		 * ���������Ϣ��track����
		 */
		public function track($cityId, $userId, $trackId, $pm) {
			// ��·���ƣ�����/����
			// ���뵽track����
			// ����һ��sessionά��subject��Ϣ��
			$sql = sprintf("insert into track(city,user,state,lastupdate,pm)
				values(%d,%d,%d,'%s',%d)",
					$cityId,
					$userId,
					$trackId,
					$pm);
		}

		public function untrack($textMsg) {
			// ��track����ɾ���û���track��¼
		}

		public function updateLocation($locationMsg) {
			// ����locationMsgѰ��ָ����վ�㣬������track��
			// ���³ɹ���ʾ�ɹ�����վ����Ϣ
			// ʧ����ʾ����վ��ʧ�ܵ�ԭ��(δ�ҵ���վ��)
		}
	}
?>
