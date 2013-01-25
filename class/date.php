<?php
class DateClass {
	private $year;
	private $month;
	private $day;
	private $H;
	private $i;
	private $s;
	
	/**
	 * ��ȡ�����·ݵ�����
	 * @param unknown_type $date ʱ���ʽ����ʱ���   ����(2012-08-20) ��1345451347
	 * @return Array {"2012-08-01","2012-08-02"....,"2012-08-31"}
	 */
    function getDayArrOnMon($date) {
		try {
			$days = array ();
			$index = strpos ( $date ,"-" );
			$timestamp = $date;
			if (($index - 0) > 0) {
				$timestamp = strtotime ( $date );
			}
			$this->year = date ( "Y", $timestamp );
			$this->month = date ( "m", $timestamp );
			$seardate = date("Y-m",$timestamp);//��ȡ��������
			$curdate = date("Y-m");//��ȡ��ǰ����
			$days = date ( 't', $timestamp );//��ȡ�·ݵ�����
			if(strcmp($seardate, $curdate)==0){//��������
				//��ȡ���������
				$days = date("d",time());

			}
		} catch ( Exception $e ) {
			echo "�����ʱ���ʽ";
			echo $e->getMessage ();
		}
		return $this->getDayArr($days);
	}
	
	/**
	 * ��ȡ��ǰ�·ݵ�����
	 */
	function getDayArrCurMon() {
		return date ( "t" );
	}
	
	function getDayArr($days) {
		$daysarr = array();
		$curday = 1;
		while($curday <= $days){
			$tempday = $curday;
			if($curday<10){
				$tempday = "0".$curday;
			}
			$daysarr[] = ""+$this->year."-".$this->month."-".$tempday."";
			$curday++;
		}
		return $daysarr;
	}

}
?>