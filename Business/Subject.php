<?php
	/**
	 * ��������
	 * route: 	��·
	 * pm:		��·�ķ��� 1-ʼ��վto�յ�վ 2-�յ�վtoʼ��վ 3-����
	 */

	/**
	 * ָ�����
	 * s[��·����] ׷��ָ����·
	 * s ׷��Ψattention��Ψһ����·
	 * s[q]	�˳�׷��
	 */

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
		 * �����ı���Ϣ
		 * ���s�����q����ô����remove���������remove
		 * 
		 */
		public function perform($textMsg) {

		}

		/**
		 * ����һ����·׷����Ϣ
		 */
		public function add($textMsg) {
			$wxAppId = $textMsg->FromUserName;

			$userId;
			;
		}

		/**
		 * ������·��վ��
		 */
		public function track($locationMsg) {
			// body...
		}

		/**
		 * ɾ��һ����·׷����Ϣ
		 */
		public function remove($wxAppId) {
			// body...
		}

		function _execSql($sql) {
			return $this->_dbAccess->execSql($sql);
		}

		/**
		 * ��������: 
		 * ��s��ͷ���ı���Ϣ������
		 * 1. ���s��Ϊ�գ����attention�����ҵ�Ψһ�ļ�¼(�����Ψһ����ʾ�û�����������s����: s[��·����])
		 * 2. ���s��Ϊ�գ������s����ַ�Ϊ��·����
		 * param $paramStr 
		 * return route's id, pm
		 */
		function _parseCmdForAdd($userId, $paramStr) {
			$array = [
				"route" => 0,
				"pm" => 1
			];

			$lineName = null;
			if (strlen($paramStr) > 0) {
				$lineName = trim($paramStr);
			}

			$sql = sprintf("select route.id, route.linename"
				. " from route, attention, user"
				. " where user.id = attention.user"
				. " and attention.route = route.id"
				. " and user.id = %s",
				$userId
			);
			$result = $this->_execSql($sql);
			$num = $result->num_rows;
			if ($num > 0) {
				if (isset($lineName)) {		// s [��·����]
					$matchTimes = 0;
					while ($row=$result->fetch_assoc()) {
						$match = strstr($row["route.linename"], $lineName);
						if ($match) {
							$array["route"] = $row["route.id"];
							$matchTimes++;
						}
					}

					if ($matchTimes == 0) {		// û��ƥ�䵽��ע��·
						$array["route"] = 0;
					}
					else if ($matchTimes > 1) {	// ƥ�䵽������Ƶ���·
						$array["route"] = -1;
					}
				}
				else {	// s
					if ($num == 1) {
												
					}
				}
				$result->free();
			}
			else {
				// û��׷���κ���·
				return null;
			}

			return $array;
		}

		/**
		 * ���һ����¼��track��
		 */
		function _generate($cityId, $userId, $routeId, $pm) {
			// body...
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
			$explicit = ($cmd.count() > 1);		// ָ�������
			$attentionIndex = 0;
			if ($explicit) {
				$attentionIndex = intval($cmd[1])
			}

			if (!isset(($_SESSION[$wxAppId]))) {
				$sql = sprintf("select user.id, user.city_id, from user, attention"
					. " where user.id = attention.user "
					. " and user.wxAppId = '%s'", $wxAppId);
				$result = $_dbAccess->execSql($sql);
				$num = $result->num_rows;
				if ($num <= 0) {
					// ����û�������û���·��������Ϣ
				}
				else {
					$index = 0;
					while ($row = $result->fetch_assoc()) {
						if ($index == $attentionIndex) {
							$userId = $row["user.id"];
							$cityId = $row["user.city_id"];
							$routeId = $row["attention.route"];
							$pm = $row["attention.pm_morning"];
							$now = date("Y-m-d H:i:s", time());
							$this->track($cityId, $userId, $routeId, $pm, $now);
							break;
						}
					}
					$result->free();
				}

				$_SESSION[$textMsg->FromUserName] = true;
			}
		}

		/**
		 * ���������Ϣ��track����
		 */
		public function track($cityId, $userId, $trackId, $pm, $lastUpdate) {
			// ��·���ƣ�����/����
			// ���뵽track����
			// ����һ��sessionά��subject��Ϣ��
			$sql = sprintf("insert into track(city,user,state,lastupdate,pm)
				values(%d,%d,%d,'%s',%d)",
					$cityId,
					$userId,
					$trackId,
					$lastUpdate,
					$pm);

			$this->_dbAccess.execSql($sql);
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
