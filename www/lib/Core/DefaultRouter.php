<?php
/*
 * Copyright 2015 Scholica V.O.F.
 * Created by Thomas Schoffelen
 */

if (isset($app) && isset($handler)) {

	// Path: /_schools
	if(is_callable(array($handler, 'getSchools'))){
	    $app->path('_schools', function () use ($app, $handler) {
	        return $handler->getSchools();
	    });
    }

    $app->param('slug', function ($request, $site) use ($app, $handler) {
        $app->param('ctype_print', function ($request, $username) use ($app, $site, $handler) {
            $app->param('ctype_print', function ($request, $password) use ($app, $site, $username, $handler) {

                $handler->setCredentials($site, $username, $password);

                // Path: /:site/:username/:password/user
                $app->path('user', function () use ($app, $handler) {
                    return $handler->getUserInfo();
                });
                
                 // Path: /:site/:username/:password/grades
                if(is_callable(array($handler, 'getGrades'))){
	                $app->path('grades', function () use ($app, $handler) {
	                    return $handler->getGrades();
	                });
                }

                // Path: /:site/:username/:password/homework
                if(is_callable(array($handler, 'getHomework'))){
	                $app->path('homework', function () use ($app, $handler) {
	                    return $handler->getHomework();
	                });
                }

                // Path: /:site/:username/:password/picture
                $app->path('picture', function () use ($app, $handler) {
                    $pic = $handler->getUserPicture();
                    if(is_string($pic)) {
                        header('Content-Type: image/jpeg');
                        echo $handler->getUserPicture();
                        die();
                    }else{
                        return $pic;
                    }
                });

                // Path: /:site/:username/:password/meetings/:timestamp
                $app->path('meetings', function () use ($app, $handler) {
                    $app->param('int', function ($request, $timestamp) use ($app, $handler) {
                        if($timestamp <= 0){
                            $timestamp = time();
                        }
                        return $handler->getSchedule($timestamp);
                    });
                    $app->path('now', function() use ($app, $handler){
                        return $handler->getSchedule(time());
                    });
                });

            });
        });
    });

}
