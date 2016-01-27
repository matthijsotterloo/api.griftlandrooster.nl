<?php
/*
 * Copyright 2015 Matthijs Otterloo.
 */

namespace Zermelo;

class Handler implements \Core\Handler {

    /**
     * @var ZermeloHelper
     */
    private $zermelo;

    /**
     * Handler name
     *
     * @return string
     */
    function handlerSlug() {
        return 'zermelo';
    }

    /**
     * Set user credentials
     *
     * @param $siteID
     * @param $username
     * @param $password
     */
    function setCredentials($siteID, $username, $password) {
        $this->zermelo = new ZermeloHelper($siteID);
        $this->zermelo->grabAccessToken($username, $password);
    }

    function getSchools(){
	    $domains = json_decode(file_get_contents('lib/Assets/zportal-domains.json'));

	    $portals = array();
	    foreach($domains as $domain){
		    $domain = str_replace('.zportal.nl','', $domain);
		    $portals[] = array('site' => $domain, 'title' => strlen($domain) < 5 ? strtoupper($domain) : ucfirst($domain));
	    }

	    return array('sites' => $portals);
    }

    /**
     * Get user info
     *
     * @return array
     */
    function getUserInfo() {
	    if(!$this->zermelo->token){
		    return array(
		        'provider_error' => 'Login details onjuist.'
	        );
	    }
	    $person = $this->zermelo->getPerson();
        if(!$person){
            return 403;
        }
        if($person->status == '404'){
            return array(
                'provider_error' => 'Deze user bestaat niet!'
            );
        }

        if($person->status == '401'){
	        return array(
		        'provider_error' => 'Gebruiker is niet ingelogd.'
	        );
        }

        $info = array(
            'name' => str_replace('  ', ' ', $person->firstName . ' ' . $person->prefix . ' ' . $person->lastName),
            'username' => $person->code
        );
        return $info;
    }

    /**
     * Get weekly shedule for a particular day
     *
     * @param $timestamp
     * @return array
     */
    function getSchedule($timestamp) {

        if(!$this->zermelo->token){
            return 403;
        }
        
        $times = array('08:30', '09:30', '11:00', '12:00', '13:30', '14:30', '15:30');
        $break_times = array('10:30', '13:00');

        $subjects = (array) json_decode(file_get_contents('lib/Assets/subjects.json'));
	
	$tz = timezone_open('Europe/Amsterdam');
        $tz_offset = timezone_offset_get($tz, new \DateTime('@'.$timestamp, timezone_open('UTC')));

        $timestamp += $tz_offset+4;

        $weekstart = $this->getFirstDayOfWeek(date('Y', $timestamp), date('W', $timestamp));
        $weekend = strtotime('this Friday', $weekstart);

        $result = array(
            'week_timestamp' => $weekstart,
            'days' => array()
        );

        $curday = $weekstart;
        while($curday <= $weekend){
            $curwd = (int) date('w', $curday);
            $result['days'][$curwd] = array(
                'day_title' => $this->dutchDayName($curday),
                'day_ofweek' => (int)date('w', $curday),
                'items' => array()
            );

		$start = $curday;
		$end = $curday + 86399;
        	$data = $this->zermelo->getStudentGrid($start, $end);

            foreach($data as $item){
	        $item = (object)$item;
	        $start = ((int)$item->start);
                $vakname = isset($subjects[$item->subjects[0]]) ? $subjects[$item->subjects[0]] : $item->subjects[0];
                $teacher = isset($item->teachers[0]) ? $item->teachers[0] : "onbekend";
                $cancelled = $item->cancelled;
                $moved  = $item->moved;
                $cancelled = $item->cancelled;
                $changed = $item->modified;

                $teacher = preg_replace('/^.*-\s*/', '', $teacher);

                if(empty($item->locations)){
			$item->locations = array('onbekend');
		}

                $result['days'][$curwd]['items'][] = array(
                    'title' => $vakname,
                    'subtitle' => 'Lokaal ' . $item->locations[0],
                    'teacher' => strtoupper($teacher),
                    'cancelled' => $cancelled,
                    'moved' => $moved,
                    'start' => $start,
                    'start_str' => date('H:i', $start)
                );
            }
            
            foreach($result['days'] as $i => $day)
            {
            	$t = $day['items'][0]['start_str'];
            	$free_hours = array();
            	
            	// Free hours at the start of the day.
            	foreach ($times as $time)
            	{
            		if ($time != $t)
            		{
            			$free_hour = array(
            				'title' => 'Geen les',
            				'start_str' => $time
            				);
            			$free_hours[] = $free_hour;
            		}
            		else
            			break;
            	}
            	$result['days'][$i]['items'] = array_merge($free_hours, $day['items']);
            	
            	// Breaks.
            	$day_items = array();
            	foreach ($break_times as $break_time)
            	{
	            	foreach ($day['items'] as $item)
	            	{
            			$t = $item['start_str'];
            			if ($t > $break_time)
            			{
	            			$day_item = array(
	            				'title' => 'Pauze',
	            				'start_str' => $break_time
	            				);
            				$day_items[] = $day_item;
            			}
            				$day_items[] = $item;	
			}
            	}
            	$result['days'][$i]['items'] = $day_items;
            }
            
            $curday += 86400;
		}

        return $result;
    }

    private function dutchDayName($time){
        switch(date('N', $time)){
            case 1:
                return 'Maandag';
            case 2:
                return 'Dinsdag';
            case 3:
                return 'Woensdag';
            case 4:
                return 'Donderdag';
            case 5:
                return 'Vrijdag';
        }
    }

    private function getFirstDayOfWeek($year, $weeknr) {
        $offset = date('w', mktime(0, 0, 0, 1, 1, $year));
        $offset = ($offset < 5) ? 1 - $offset : 8 - $offset;
        $monday = mktime(0, 0, 0, 1, 1 + $offset, $year);
        return strtotime('+' . ($weeknr - 1) . ' weeks', $monday);
    }
}
