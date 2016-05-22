<?php
/*
 * Copyright 2015 Matthijs Otterloo.
 */

if (isset($app) && isset($handler)) {

	// Path: /_schools
	$app->path('_schools', function () use ($app, $handler) {
	    return $handler->getSchools();
	});

    $app->param('slug', function ($request, $site) use ($app, $handler) {
        $app->param('ctype_print', function ($request, $username) use ($app, $site, $handler) {
            $app->param('ctype_print', function ($request, $password) use ($app, $site, $username, $handler) {
            	
            	// Check if first character is underscore
            	if (substr($site, 0, 1) == '_')
            	{
            		// Remove appended underscore
            		$site = ltrim('_', $site);
            	}

                $handler->setCredentials($site, $username, $password);

                // Path: /:site/:username/:password/user
                $app->path('user', function () use ($app, $handler) {
                    return $handler->getUserInfo();
                });

                // Path: /:site/:username/:password/homework
                // $app->path('homework', function () use ($app, $handler) {
                //     return $handler->getHomework();
                // });

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
