<?php
/*
 * Copyright 2015 Matthijs Otterloo.
 */
 
namespace Core;

interface Handler {

    /**
     * Handler name
     *
     * @return string
     */
    function handlerSlug();

    /**
     * Set user credentials
     *
     * @param $siteID
     * @param $username
     * @param $password
     */
    function setCredentials($siteID, $username, $password);

    /**
     * Get user info
     *
     * @return array
     */
    function getUserInfo();

    /**
     * Get weekly shedule for a particular day
     *
     * @param $timestamp
     * @return array
     */
    function getSchedule($timestamp);

}
