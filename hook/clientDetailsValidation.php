<?php

if (!defined("WHMCS")) die("This file cannot be accessed directly");

use WHMCS\Database\Capsule;

add_hook('ClientDetailsValidation', 1, function($vars) {
    
    $fieldid = 1; //by default if no other custom fields are set the id is 1.

    //This select will update the fieldid if other custom fields are set.
    try {
        $data = Capsule::table('tblcustomfields')
            ->where("fieldname", "username")   
            ->orWhere("fieldname", "Username")
            ->first();
        
        if (isset($data->id)) {
            $fieldid = $data->id;
        }         
    }

    catch (\Exception $e) {
        error_log($e->getMessage());
    }


    //run username check only during sign-up
    if ($_SESSION["uid"] == 0) {
        try {
            $data = Capsule::table('tblcustomfieldsvalues')
                ->where("value", $vars['customfield'][$fieldid])
                ->first();
            
            if (isset($data->value)) {
                return ['Username is taken.'];
            }         
        }

        catch (\Exception $e) {
            error_log($e->getMessage());
        }
    } else {    //make sure users aren't changing usernames by manipulating form data
        try {
            $data = Capsule::table('tblcustomfieldsvalues')
                ->where("relid", $_SESSION["uid"])
                ->first();
            
            if (isset($data->value)) {
                if ($vars['customfield'][$fieldid] != $data->value){
                    return ['Please do not attempt to change the username.'];
                }
            }         
        }

        catch (\Exception $e) {
            error_log($e->getMessage());
        }
    }

});
