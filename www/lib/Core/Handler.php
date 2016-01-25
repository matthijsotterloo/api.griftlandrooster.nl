<?php
/*
 * Copyright 2015 Scholica V.O.F.
 * Created by Thomas Schoffelen
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
     * Get user picture
     *
     * @return string
     */
    function getUserPicture();

    /**
     * Get weekly shedule for a particular day
     *
     * @param $timestamp
     * @return array
     */
    function getSchedule($timestamp);

}