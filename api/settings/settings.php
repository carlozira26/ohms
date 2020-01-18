<?php

return array(
    "settings" => array(
        "determineRouteBeforeAppMiddleware" => true,
        "displayErrorDetails" => true, // set to false in production
        "addContentLengthHeader" => false,
        "jwt" => "Pr0j3ct-04m$", //getenv("JWT_SECRET")
        "jwtalgo" => array("HS256"), //getenv("JWT_SECRET")
        "db" => array(
            "driver" => "mysql",
            "host" => "localhost",
            "database" => "project-ohms",
            "username" => "root",
            "password" => "",
            "charset"   => "utf8",
            "collation" => "utf8_unicode_ci",
            "prefix"    => ""
        ),
        "uploadDirectory" => str_replace(DIRECTORY_SEPARATOR."settings", "", __DIR__) . DIRECTORY_SEPARATOR . 'uploads'.DIRECTORY_SEPARATOR,
        "baseUrl" => "http://localhost/ohms/api"
    )
);

?>
