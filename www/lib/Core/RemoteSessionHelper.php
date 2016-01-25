<?php
/*
 * Copyright 2015 Scholica V.O.F.
 * Created by Thomas Schoffelen
 */

namespace Core;

trait RemoteSessionHelper {

    protected function session($id, $lifetime, $handler){
        $a = '/tmp/sesshelper-' . $id;
        $b = @filemtime($a);
        if($b < time() - $lifetime || !$res = @file_get_contents($a)){
            $res = call_user_func($handler);
            if($res){
                file_put_contents($a, serialize($res));
            }elseif($b){
                @unlink($a);
            }
        }else{
            $res = unserialize($res);
        }
        return $res;
    }

}