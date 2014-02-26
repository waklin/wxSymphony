<?php
	require_once("global.php");
	require_once("DBAccess.php");

	/**
	 * ��������
	 * route: 	��·
	 * pm:		��·�ķ��� 1-ʼ��վto�յ�վ 2-�յ�վtoʼ��վ 3-����
	 */
	define("ROUTE", "route");
	define("PM", "pm");

	/**
	 * ָ�����
	 * s[��·����] ׷��ָ����·
	 * s ׷��Ψattention��Ψһ����·
	 * s[q]	�˳�׷��
	 */

	//session_start();

	/**
	 * 
	 */
	class Subject
	{
		private $_dbAccess;

		public function __construct()
		{
			$this->_dbAccess = new DBAccess();
		}

		/**
		 * �����ı���Ϣ
		 * ���s�����q����ô����remove���������remove
		 */
		public function perform($textMsg) {
			$cmd = trim($textMsg->Content);
			$secondChr = substr($textMsg, 1, 1);

			$responseMsg = new TextMessage();
			$responseMsg->FromUserName = $textMsg->ToUserName;
			$responseMsg->ToUserName = $textMsg->FromUserName;
			if ($secondChr && $secondChr == "q") {
				$this->remove($textMsg->FromUserName);
				$responseMsg->Content = "quit track!";
			}
			else {
				$this->add($textMsg);
				$responseMsg->Content = "add track!";
			}
			$responseMsg->CreateTime = date('Y-m-d H:i:s', time());
			xDump($responseMsg, "responseMsg");
			return $responseMsg->generateContent();
		}

		/**
		 * ����һ����·׷����Ϣ
		 */
		public function add($textMsg) {
			$wxAppId = $textMsg->FromUserName;
			$sql = sprintf("select user.id, user.city_id from user"
				. " where user.wxAppId = '%s'",
				$wxAppId
			);

			xDump($sql, "sql");

			$result = $this->_execSql($sql);
			$userId = null;
			$cityId = null;
			if ($result) {
				$row = $result->fetch_assoc();
				xDump($row, "row");
				$userId = $row["id"];
				$cityId = $row["city_id"];

				$result->free();
			}

			$paramStr = substr($textMsg->Content, 1);
			xDump($paramStr, "paramStr");

			$array = $this->_parseCmdForAdd($userId, $paramStr);
			xDump($array, "array");

			if ($array) {
				if ($array[ROUTE] > 0) {
					$sql = sprintf("insert into track(city, user, route, pm)"
						. " values(%s, %s, %s, %s)",
						$cityId,
						$userId,
						$array[ROUTE],
						$array[PM]
					);
					xDump($sql, "sql");
					$this->_execSql($sql);
				}
			}
			else {
				;
			}
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
			$array = array(
				ROUTE => 0,
				PM => 1
			);

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

			xDump($sql,"sql");

			$result = $this->_execSql($sql);
			$num = $result->num_rows;
			if ($num > 0) {
				if (isset($lineName)) {		// s [��·����]
					$matchTimes = 0;
					while ($row=$result->fetch_assoc()) {
						$match = strstr($row["linename"], $lineName);
						if ($match) {
							$array[ROUTE] = $row["id"];
							$matchTimes++;
						}
					}

					if ($matchTimes == 0) {		// û��ƥ�䵽��ע��·
						$array[ROUTE] = 0;
					}
					else if ($matchTimes > 1) {	// ƥ�䵽������Ƶ���·
						$array[ROUTE] = -1;
					}
				}
				else {	// s
					if ($num == 1) {
						$row=$result->fetch_assoc();
						$array["route"] = $row["route.id"];
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
				$attentionIndex = intval($cmd[1]);
			}

			if (!isset($_SESSION[$wxAppId])) {
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
		public function track1($cityId, $userId, $trackId, $pm, $lastUpdate) {
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
